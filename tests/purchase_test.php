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
 * tests/purchase_test.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop;
/** @covers \local_rewardshop\purchase_service */
final class purchase_test extends \advanced_testcase {
 private function reward(int $courseid,array $x=[]): \stdClass {global $DB;$d=(object)array_merge(['courseid'=>$courseid,'name'=>'Hint','description'=>'','descriptionformat'=>FORMAT_HTML,'rewardtype'=>'reward_hint','cost'=>50,'stock'=>null,'maxperuser'=>1,'enabled'=>1,'timestart'=>0,'timeend'=>0,'requiresapproval'=>0,'configjson'=>json_encode(['hint'=>'Use the index.']),'sortorder'=>1,'timecreated'=>time(),'timemodified'=>time()],$x);$d->id=$DB->insert_record('local_rewardshop_rewards',$d);return $d;}
 private function setupuser($course): \stdClass {$u=$this->getDataGenerator()->create_user();$this->getDataGenerator()->enrol_user($u->id,$course->id,'student');$this->setUser($u);api::add_credits($u->id,$course->id,500,'seed','','','seed-'.$u->id);return $u;}
 public function test_duplicate_request_token_returns_same_purchase(): void {$this->resetAfterTest();$c=$this->getDataGenerator()->create_course();$u=$this->setupuser($c);$r=$this->reward($c->id);$p1=purchase_service::purchase($r->id,$u->id,'abcdefghijklmnop');$p2=purchase_service::purchase($r->id,$u->id,'abcdefghijklmnop');$this->assertSame($p1->id,$p2->id);$this->assertSame(450,api::get_balance($u->id,$c->id));}
 public function test_per_user_limit(): void {$this->resetAfterTest();$c=$this->getDataGenerator()->create_course();$u=$this->setupuser($c);$r=$this->reward($c->id);purchase_service::purchase($r->id,$u->id,'aaaaaaaaaaaaaaaa');$check=purchase_service::can_purchase($r->id,$u->id);$this->assertFalse($check['allowed']);}
 public function test_stock_and_dates(): void {$this->resetAfterTest();$c=$this->getDataGenerator()->create_course();$u=$this->setupuser($c);$r=$this->reward($c->id,['stock'=>0]);$this->assertFalse(purchase_service::can_purchase($r->id,$u->id)['allowed']);$r2=$this->reward($c->id,['name'=>'Future','timestart'=>time()+3600]);$this->assertFalse(purchase_service::can_purchase($r2->id,$u->id)['allowed']);}
 public function test_approval_rejection_refunds(): void {$this->resetAfterTest();$c=$this->getDataGenerator()->create_course();$u=$this->setupuser($c);$r=$this->reward($c->id,['requiresapproval'=>1]);$p=purchase_service::purchase($r->id,$u->id,'bbbbbbbbbbbbbbbb');$this->assertSame(450,api::get_balance($u->id,$c->id));$teacher=$this->getDataGenerator()->create_user();$this->getDataGenerator()->enrol_user($teacher->id,$c->id,'editingteacher');$this->setUser($teacher);purchase_service::reject($p->id,$teacher->id);$this->assertSame(500,api::get_balance($u->id,$c->id));}
}
