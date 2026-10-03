<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * classes/privacy/provider.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop\privacy;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\writer;
class provider implements \core_privacy\local\metadata\provider, \core_privacy\local\request\plugin\provider {
 public static function get_metadata(collection $c): collection {
  $c->add_database_table('local_rewardshop_wallet',['userid'=>'privacy:userid','courseid'=>'privacy:courseid','balance'=>'privacy:balance','lifetimeearned'=>'privacy:lifetimeearned','lifetimespent'=>'privacy:lifetimespent'],'privacy:wallet');
  $c->add_database_table('local_rewardshop_ledger',['userid'=>'privacy:userid','courseid'=>'privacy:courseid','amount'=>'privacy:amount','description'=>'privacy:description'],'privacy:ledger');
  $c->add_database_table('local_rewardshop_purchases',['userid'=>'privacy:userid','courseid'=>'privacy:courseid','status'=>'privacy:status','configsnapshot'=>'privacy:configsnapshot'],'privacy:purchases'); return $c;
 }
 public static function get_contexts_for_userid(int $userid): contextlist { $l=new contextlist();$sql="SELECT DISTINCT ctx.id FROM {context} ctx JOIN {course} c ON ctx.instanceid=c.id LEFT JOIN {local_rewardshop_wallet} w ON w.courseid=c.id AND w.userid=:u1 LEFT JOIN {local_rewardshop_purchases} p ON p.courseid=c.id AND p.userid=:u2 WHERE ctx.contextlevel=:level AND (w.id IS NOT NULL OR p.id IS NOT NULL)";$l->add_from_sql($sql,['u1'=>$userid,'u2'=>$userid,'level'=>CONTEXT_COURSE]);return $l; }
 public static function export_user_data(approved_contextlist $contexts): void { global $DB; $u=$contexts->get_user()->id;foreach($contexts->get_contexts() as $ctx){$c=$ctx->instanceid;$data=['wallet'=>$DB->get_record('local_rewardshop_wallet',['userid'=>$u,'courseid'=>$c]),'ledger'=>array_values($DB->get_records('local_rewardshop_ledger',['userid'=>$u,'courseid'=>$c],'timecreated ASC')),'purchases'=>array_values($DB->get_records('local_rewardshop_purchases',['userid'=>$u,'courseid'=>$c],'timecreated ASC'))];writer::with_context($ctx)->export_data([get_string('pluginname','local_rewardshop')],(object)$data);} }
 public static function delete_data_for_all_users_in_context(\context $context): void { global $DB;if($context->contextlevel!==CONTEXT_COURSE)return;$c=$context->instanceid;foreach(['local_rewardshop_unlocks','local_rewardshop_purchases','local_rewardshop_ledger','local_rewardshop_wallet'] as $t)$DB->delete_records($t,['courseid'=>$c]); }
 public static function delete_data_for_user(approved_contextlist $contexts): void { global $DB;$u=$contexts->get_user()->id;foreach($contexts->get_contexts() as $ctx){$c=$ctx->instanceid;foreach(['local_rewardshop_unlocks','local_rewardshop_purchases','local_rewardshop_ledger','local_rewardshop_wallet'] as $t)$DB->delete_records($t,['courseid'=>$c,'userid'=>$u]);} }
}
