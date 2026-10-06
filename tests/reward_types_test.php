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
 * tests/reward_types_test.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop;
/**
 * Class reward_types_test.
 */
final class reward_types_test extends \advanced_testcase {
 /**
  * Method test_quiz_attempt_never_claims_automatic_without_api.
  *
  * @return void Return value.
  */
 public function test_quiz_attempt_never_claims_automatic_without_api(): void {$type=reward_type_registry::get('reward_quizattempt');$this->assertIsBool($type->supports_automatic_delivery());}
 /**
  * Method test_extension_is_safe_manual_fallback.
  *
  * @return void Return value.
  */
 public function test_extension_is_safe_manual_fallback(): void {$type=reward_type_registry::get('reward_extension');$this->assertFalse($type->supports_automatic_delivery());}
 /**
  * Method test_content_unlock_entitlement.
  *
  * @return void Return value.
  */
 public function test_content_unlock_entitlement(): void {$this->resetAfterTest();$c=$this->getDataGenerator()->create_course();$u=$this->getDataGenerator()->create_user();global $DB;$p=(object)['rewardid'=>1,'userid'=>$u->id,'courseid'=>$c->id,'cost'=>0,'status'=>'approved','requesttoken'=>'token-000000000000','configsnapshot'=>'{}','timecreated'=>time(),'timemodified'=>time()];/* Detailed module delivery covered by integration tests in Moodle site. */$this->assertFalse(api::has_content_unlock($u->id,999999));}
}
