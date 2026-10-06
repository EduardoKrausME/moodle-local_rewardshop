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
 * classes/api.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop;
/**
 * Class api.
 */
class api {
 /**
  * Method add_credits.
  *
  * @param int $userid Parameter userid.
  * @param int $courseid Parameter courseid.
  * @param int $amount Parameter amount.
  * @param string $reference Parameter reference.
  * @param string $description Parameter description.
  * @param ?int $relatedid Parameter relatedid.
  * @param ?string $idempotencykey Parameter idempotencykey.
  * @return int Return value.
  */
 public static function add_credits(int $userid,int $courseid,int $amount,string $reference,string $description='',?int $relatedid=null,?string $idempotencykey=null): int {
  return wallet_service::change($userid,$courseid,abs($amount),'earn',$reference,$description,$relatedid,$idempotencykey);
 }
 /**
  * Method spend_credits.
  *
  * @param int $userid Parameter userid.
  * @param int $courseid Parameter courseid.
  * @param int $amount Parameter amount.
  * @param string $reference Parameter reference.
  * @param string $description Parameter description.
  * @param ?int $relatedid Parameter relatedid.
  * @param ?string $idempotencykey Parameter idempotencykey.
  * @return int Return value.
  */
 public static function spend_credits(int $userid,int $courseid,int $amount,string $reference,string $description='',?int $relatedid=null,?string $idempotencykey=null): int {
  return wallet_service::change($userid,$courseid,-abs($amount),'spend',$reference,$description,$relatedid,$idempotencykey);
 }
 /**
  * Method refund.
  *
  * @param int $userid Parameter userid.
  * @param int $courseid Parameter courseid.
  * @param int $amount Parameter amount.
  * @param string $reference Parameter reference.
  * @param string $description Parameter description.
  * @param ?int $relatedid Parameter relatedid.
  * @param ?string $idempotencykey Parameter idempotencykey.
  * @return int Return value.
  */
 public static function refund(int $userid,int $courseid,int $amount,string $reference,string $description='',?int $relatedid=null,?string $idempotencykey=null): int {
  return wallet_service::change($userid,$courseid,abs($amount),'refund',$reference,$description,$relatedid,$idempotencykey);
 }
 /**
  * Method get_balance.
  *
  * @param int $userid Parameter userid.
  * @param int $courseid Parameter courseid.
  * @return int Return value.
  */
 public static function get_balance(int $userid,int $courseid): int { return wallet_service::get_balance($userid,$courseid); }
 /**
  * Method can_purchase_reward.
  *
  * @param int $rewardid Parameter rewardid.
  * @param int $userid Parameter userid.
  * @return array Return value.
  */
 public static function can_purchase_reward(int $rewardid,int $userid): array { return purchase_service::can_purchase($rewardid,$userid); }
 /**
  * Method purchase_reward.
  *
  * @param int $rewardid Parameter rewardid.
  * @param int $userid Parameter userid.
  * @param string $requesttoken Parameter requesttoken.
  * @return \stdClass Return value.
  */
 public static function purchase_reward(int $rewardid,int $userid,string $requesttoken): \stdClass { return purchase_service::purchase($rewardid,$userid,$requesttoken); }
 /**
  * Method has_content_unlock.
  *
  * @param int $userid Parameter userid.
  * @param int $cmid Parameter cmid.
  * @return bool Return value.
  */
 public static function has_content_unlock(int $userid,int $cmid): bool {
  global $DB; $now=time(); $sql="SELECT 1 FROM {local_rewardshop_unlocks} WHERE userid=:u AND cmid=:c AND (timeexpires=0 OR timeexpires>:now)";
  return $DB->record_exists_sql($sql,['u'=>$userid,'c'=>$cmid,'now'=>$now]);
 }
}
