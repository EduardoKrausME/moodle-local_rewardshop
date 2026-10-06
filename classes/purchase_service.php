<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * classes/purchase_service.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop;
use core\lock\lock_config;
/**
 * Class purchase_service.
 */
class purchase_service {
 private const ACTIVE=['pending','approved','delivered'];
 /**
  * Method can_purchase.
  *
  * @param int $rewardid Parameter rewardid.
  * @param int $userid Parameter userid.
  * @return array Return value.
  */
 public static function can_purchase(int $rewardid,int $userid): array {
  global $DB; $reward=$DB->get_record('local_rewardshop_rewards',['id'=>$rewardid], '*', MUST_EXIST); $context=\context_course::instance($reward->courseid); $errors=[]; $now=time();
  if (!$reward->enabled) $errors[]=get_string('rewarddisabled','local_rewardshop');
  if ($reward->timestart && $reward->timestart>$now) $errors[]=get_string('notstarted','local_rewardshop'); if ($reward->timeend && $reward->timeend<$now) $errors[]=get_string('ended','local_rewardshop');
  if ((int)$reward->cost<0) $errors[]=get_string('invalidcost','local_rewardshop'); if (api::get_balance($userid,$reward->courseid)<(int)$reward->cost) $errors[]=get_string('insufficientcredits','local_rewardshop');
  list($insql,$params)=$DB->get_in_or_equal(self::ACTIVE,SQL_PARAMS_NAMED,'st'); $params['r']=$rewardid;$params['u']=$userid;
  $count=$DB->count_records_select('local_rewardshop_purchases',"rewardid=:r AND userid=:u AND status $insql",$params); if ($reward->maxperuser>0 && $count >= $reward->maxperuser) $errors[]=get_string('limitreached','local_rewardshop');
  if ($reward->stock !== null) { $sold=$DB->count_records_select('local_rewardshop_purchases',"rewardid=:r AND status $insql",array_merge(['r'=>$rewardid],array_diff_key($params,['u'=>1]))); if ($sold >= (int)$reward->stock) $errors[]=get_string('outofstock','local_rewardshop'); }
  foreach(prerequisite_service::check($reward,$userid) as $e) $errors[]=$e; $type=reward_type_registry::get($reward->rewardtype); foreach($type->can_purchase($reward,$userid,$context) as $e) $errors[]=$e;
  return ['allowed'=>!$errors,'errors'=>$errors,'reward'=>$reward];
 }
 /**
  * Method purchase.
  *
  * @param int $rewardid Parameter rewardid.
  * @param int $userid Parameter userid.
  * @param string $requesttoken Parameter requesttoken.
  * @return \stdClass Return value.
  */
 public static function purchase(int $rewardid,int $userid,string $requesttoken): \stdClass {
  global $DB; if (!preg_match('/^[a-zA-Z0-9_-]{16,64}$/',$requesttoken)) throw new \invalid_parameter_exception('Invalid request token');
  $existing=$DB->get_record('local_rewardshop_purchases',['userid'=>$userid,'requesttoken'=>$requesttoken]); if ($existing) return $existing;
  $reward=$DB->get_record('local_rewardshop_rewards',['id'=>$rewardid], '*', MUST_EXIST); $context=\context_course::instance($reward->courseid); require_capability('local/rewardshop:view',$context);
  $factory=lock_config::get_lock_factory('local_rewardshop'); $lock=$factory->get_lock('reward:'.$rewardid,10); if(!$lock) throw new \moodle_exception('locktimeout','local_rewardshop');
  try {
   $existing=$DB->get_record('local_rewardshop_purchases',['userid'=>$userid,'requesttoken'=>$requesttoken]); if($existing)return $existing;
   $check=self::can_purchase($rewardid,$userid); if(!$check['allowed']) throw new \moodle_exception('cannotpurchase','local_rewardshop','',implode('; ',$check['errors']));
   $tx=$DB->start_delegated_transaction(); $status=$reward->requiresapproval?'pending':'approved'; $now=time();
   $p=(object)['rewardid'=>$rewardid,'userid'=>$userid,'courseid'=>$reward->courseid,'cost'=>(int)$reward->cost,'status'=>$status,'requesttoken'=>$requesttoken,'configsnapshot'=>json_encode(['rewardtype'=>$reward->rewardtype,'name'=>$reward->name,'config'=>json_decode((string)$reward->configjson,true)],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),'approvedby'=>null,'timeapproved'=>$status==='approved'?$now:null,'timecreated'=>$now,'timemodified'=>$now];
   $p->id=$DB->insert_record('local_rewardshop_purchases',$p);
   if ($p->cost>0) api::spend_credits($userid,$reward->courseid,$p->cost,'purchase',get_string('ledgerpurchase','local_rewardshop',$reward->name),$p->id,'purchase:'.$p->id);
   $type=reward_type_registry::get($reward->rewardtype); $type->purchase($p,$reward,$userid);
   \local_rewardshop\event\reward_purchased::create(['context'=>$context,'objectid'=>$p->id,'relateduserid'=>$userid,'other'=>['rewardid'=>$rewardid,'cost'=>$p->cost]])->trigger();
   if ($status==='approved' && $type->supports_automatic_delivery()) { self::deliver($p->id,0); $p=$DB->get_record('local_rewardshop_purchases',['id'=>$p->id]); }
   $tx->allow_commit(); return $p;
  } finally {$lock->release();}
 }
 /**
  * Method approve.
  *
  * @param int $purchaseid Parameter purchaseid.
  * @param int $actorid Parameter actorid.
  * @return void Return value.
  */
 public static function approve(int $purchaseid,int $actorid): void { self::set_decision($purchaseid,$actorid,true); }
 /**
  * Method reject.
  *
  * @param int $purchaseid Parameter purchaseid.
  * @param int $actorid Parameter actorid.
  * @return void Return value.
  */
 public static function reject(int $purchaseid,int $actorid): void { self::set_decision($purchaseid,$actorid,false); }
 /**
  * Method set_decision.
  *
  * @param int $purchaseid Parameter purchaseid.
  * @param int $actorid Parameter actorid.
  * @param bool $approve Parameter approve.
  * @return void Return value.
  */
 private static function set_decision(int $purchaseid,int $actorid,bool $approve): void {
  global $DB; $p=$DB->get_record('local_rewardshop_purchases',['id'=>$purchaseid],'*',MUST_EXIST); $ctx=\context_course::instance($p->courseid); require_capability('local/rewardshop:approve',$ctx); if($p->status!=='pending') throw new \moodle_exception('invalidstatus','local_rewardshop');
  $tx=$DB->start_delegated_transaction(); $p->status=$approve?'approved':'rejected';$p->approvedby=$actorid;$p->timeapproved=time();$p->timemodified=time();$DB->update_record('local_rewardshop_purchases',$p);
  if(!$approve && $p->cost>0) api::refund($p->userid,$p->courseid,$p->cost,'rejection',get_string('ledgerrejection','local_rewardshop'),$p->id,'reject:'.$p->id);
  $event=$approve?'\\local_rewardshop\\event\\reward_approved':'\\local_rewardshop\\event\\reward_rejected';$event::create(['context'=>$ctx,'objectid'=>$p->id,'relateduserid'=>$p->userid])->trigger();
  if($approve){$reward=$DB->get_record('local_rewardshop_rewards',['id'=>$p->rewardid],'*',MUST_EXIST);if(reward_type_registry::get($reward->rewardtype)->supports_automatic_delivery())self::deliver($p->id,$actorid);} $tx->allow_commit();
 }
 /**
  * Method deliver.
  *
  * @param int $purchaseid Parameter purchaseid.
  * @param int $actorid Parameter actorid.
  * @return void Return value.
  */
 public static function deliver(int $purchaseid,int $actorid): void {
  global $DB; $p=$DB->get_record('local_rewardshop_purchases',['id'=>$purchaseid],'*',MUST_EXIST); if(!in_array($p->status,['approved','pending'],true)) { if($p->status==='delivered') return; throw new \moodle_exception('invalidstatus','local_rewardshop'); }
  $reward=$DB->get_record('local_rewardshop_rewards',['id'=>$p->rewardid],'*',MUST_EXIST); $type=reward_type_registry::get($reward->rewardtype); $type->deliver($p,$reward,$p->userid);$p->status='delivered';$p->timemodified=time();$DB->update_record('local_rewardshop_purchases',$p); \local_rewardshop\event\reward_delivered::create(['context'=>\context_course::instance($p->courseid),'objectid'=>$p->id,'relateduserid'=>$p->userid])->trigger();
 }
 /**
  * Method refund_purchase.
  *
  * @param int $purchaseid Parameter purchaseid.
  * @param int $actorid Parameter actorid.
  * @param string $reason Parameter reason.
  * @return void Return value.
  */
 public static function refund_purchase(int $purchaseid,int $actorid,string $reason=''): void {
  global $DB; $p=$DB->get_record('local_rewardshop_purchases',['id'=>$purchaseid],'*',MUST_EXIST);$ctx=\context_course::instance($p->courseid);require_capability('local/rewardshop:approve',$ctx);if(in_array($p->status,['refunded','rejected','cancelled'],true))throw new \moodle_exception('invalidstatus','local_rewardshop');
  $reward=$DB->get_record('local_rewardshop_rewards',['id'=>$p->rewardid],'*',MUST_EXIST);$tx=$DB->start_delegated_transaction(); if($p->cost>0)api::refund($p->userid,$p->courseid,$p->cost,'refund',$reason,$p->id,'refund:'.$p->id);reward_type_registry::get($reward->rewardtype)->cancel($p,$reward,$p->userid);$p->status='refunded';$p->timemodified=time();$DB->update_record('local_rewardshop_purchases',$p);\local_rewardshop\event\reward_refunded::create(['context'=>$ctx,'objectid'=>$p->id,'relateduserid'=>$p->userid])->trigger();$tx->allow_commit();
 }
}
