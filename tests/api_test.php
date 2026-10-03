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
 * tests/api_test.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop;
/** @covers \local_rewardshop\api */
final class api_test extends \advanced_testcase {
 public function test_wallet_and_idempotency(): void {$this->resetAfterTest();$c=$this->getDataGenerator()->create_course();$u=$this->getDataGenerator()->create_user();$this->assertSame(100,api::add_credits($u->id,$c->id,100,'test','',null,'earn-1'));$this->assertSame(100,api::add_credits($u->id,$c->id,100,'test','',null,'earn-1'));$this->assertSame(60,api::spend_credits($u->id,$c->id,40,'test','',null,'spend-1'));$this->assertSame(60,api::get_balance($u->id,$c->id));global $DB;$this->assertCount(2,$DB->get_records('local_rewardshop_ledger',['userid'=>$u->id,'courseid'=>$c->id]));}
 public function test_insufficient_balance(): void {$this->resetAfterTest();$c=$this->getDataGenerator()->create_course();$u=$this->getDataGenerator()->create_user();$this->expectException(\moodle_exception::class);api::spend_credits($u->id,$c->id,1,'test','','','spend-no-balance');}
 public function test_refund_is_new_ledger_row(): void {$this->resetAfterTest();$c=$this->getDataGenerator()->create_course();$u=$this->getDataGenerator()->create_user();api::add_credits($u->id,$c->id,50,'earn','','','e');api::spend_credits($u->id,$c->id,30,'spend','','','s');api::refund($u->id,$c->id,30,'refund','','','r');global $DB;$this->assertSame(50,api::get_balance($u->id,$c->id));$this->assertCount(3,$DB->get_records('local_rewardshop_ledger',['userid'=>$u->id]));}
}
