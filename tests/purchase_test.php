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
 * tests/purchase_test.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop;

use advanced_testcase;
use stdClass;

/**
 * Tests for purchase service.
 *
 * @covers \local_rewardshop\purchase_service
 */
final class purchase_test extends advanced_testcase {
    /**
     * Method reward.
     *
     * @param int $courseid Parameter courseid.
     * @param array $x Parameter x.
     * @return stdClass Return value.
     */
    private function reward(int $courseid, array $x = []): stdClass {
        global $DB;
        $d = (object)array_merge([
            'courseid' => $courseid,
            'name' => 'Hint',
            'description' => '',
            'descriptionformat' => FORMAT_HTML,
            'rewardtype' => 'reward_hint',
            'cost' => 50,
            'stock' => null,
            'maxperuser' => 1,
            'enabled' => 1,
            'timestart' => 0,
            'timeend' => 0,
            'requiresapproval' => 0,
            'configjson' => json_encode(['hint' => 'Use the index.']),
            'sortorder' => 1,
            'timecreated' => time(),
            'timemodified' => time(),
        ], $x);
        $d->id = $DB->insert_record('local_rewardshop_rewards', $d);
        return $d;
    }

    /**
     * Method setupuser.
     *
     * @param mixed $course Parameter course.
     * @return stdClass Return value.
     */
    private function setupuser($course): stdClass {
        $u = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($u->id, $course->id, 'student');
        $this->setUser($u);
        api::add_credits($u->id, $course->id, 500, 'seed', '', null, 'seed-' . $u->id);
        return $u;
    }

    /**
     * Method test_duplicate_request_token_returns_same_purchase.
     *
     * @return void Return value.
     */
    public function test_duplicate_request_token_returns_same_purchase(): void {
        $this->resetAfterTest();
        $c = $this->getDataGenerator()->create_course();
        $u = $this->setupuser($c);
        $r = $this->reward($c->id);
        $p1 = purchase_service::purchase($r->id, $u->id, 'abcdefghijklmnop');
        $p2 = purchase_service::purchase($r->id, $u->id, 'abcdefghijklmnop');
        $this->assertSame($p1->id, $p2->id);
        $this->assertSame(450, api::get_balance($u->id, $c->id));
    }

    /**
     * Method test_per_user_limit.
     *
     * @return void Return value.
     */
    public function test_per_user_limit(): void {
        $this->resetAfterTest();
        $c = $this->getDataGenerator()->create_course();
        $u = $this->setupuser($c);
        $r = $this->reward($c->id);
        purchase_service::purchase($r->id, $u->id, 'aaaaaaaaaaaaaaaa');
        $check = purchase_service::can_purchase($r->id, $u->id);
        $this->assertFalse($check['allowed']);
    }

    /**
     * Method test_stock_and_dates.
     *
     * @return void Return value.
     */
    public function test_stock_and_dates(): void {
        $this->resetAfterTest();
        $c = $this->getDataGenerator()->create_course();
        $u = $this->setupuser($c);
        $r = $this->reward($c->id, ['stock' => 0]);
        $this->assertFalse(purchase_service::can_purchase($r->id, $u->id)['allowed']);
        $r2 = $this->reward($c->id, ['name' => 'Future', 'timestart' => time() + 3600]);
        $this->assertFalse(purchase_service::can_purchase($r2->id, $u->id)['allowed']);
    }

    /**
     * Method test_approval_rejection_refunds.
     *
     * @return void Return value.
     */
    public function test_approval_rejection_refunds(): void {
        $this->resetAfterTest();
        $c = $this->getDataGenerator()->create_course();
        $u = $this->setupuser($c);
        $r = $this->reward($c->id, ['requiresapproval' => 1]);
        $p = purchase_service::purchase($r->id, $u->id, 'bbbbbbbbbbbbbbbb');
        $this->assertSame(450, api::get_balance($u->id, $c->id));
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $c->id, 'editingteacher');
        $this->setUser($teacher);
        purchase_service::reject($p->id, $teacher->id);
        $this->assertSame(500, api::get_balance($u->id, $c->id));
    }
    /**
     * Manual reward confirmation must work without calling a deliberately unsupported delivery API.
     */
    public function test_manual_delivery_confirmation(): void {
        global $DB;
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $student = $this->setupuser($course);
        $reward = $this->reward($course->id, ["rewardtype" => "reward_custom", "configjson" => "{}"]);
        $purchase = purchase_service::purchase($reward->id, $student->id, "manualdeliver123");
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, "editingteacher");
        $this->setUser($teacher);
        purchase_service::deliver($purchase->id, $teacher->id);
        $this->assertSame("delivered", $DB->get_field("local_rewardshop_purchases", "status",
            ["id" => $purchase->id]));
    }

    /**
     * A teacher must never mark another course's rewards as delivered.
     */
    public function test_delivery_rejects_other_course(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $othercourse = $this->getDataGenerator()->create_course();
        $student = $this->setupuser($othercourse);
        $reward = $this->reward($othercourse->id, ["rewardtype" => "reward_custom", "configjson" => "{}"]);
        $purchase = purchase_service::purchase($reward->id, $student->id, "othercourse12345");
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, "editingteacher");
        $this->setUser($teacher);
        $this->expectException(\required_capability_exception::class);
        purchase_service::deliver($purchase->id, $teacher->id);
    }

    /**
     * A pending purchase cannot bypass teacher approval through the delivery action.
     */
    public function test_pending_purchase_cannot_be_delivered(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $student = $this->setupuser($course);
        $reward = $this->reward($course->id, [
            "rewardtype" => "reward_custom", "configjson" => "{}", "requiresapproval" => 1,
        ]);
        $purchase = purchase_service::purchase($reward->id, $student->id, "pendingdeliver12");
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, "editingteacher");
        $this->setUser($teacher);
        $this->expectException(\moodle_exception::class);
        purchase_service::deliver($purchase->id, $teacher->id);
    }

    /**
     * Refunding a content-unlock reward removes its active entitlement.
     */
    public function test_refund_revokes_content_unlock(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $page = $this->getDataGenerator()->create_module("page", ["course" => $course->id]);
        $student = $this->setupuser($course);
        $reward = $this->reward($course->id, [
            "rewardtype" => "reward_contentunlock",
            "configjson" => json_encode(["cmid" => $page->cmid]),
        ]);
        $purchase = purchase_service::purchase($reward->id, $student->id, "refundunlock1234");
        $this->assertTrue(api::has_content_unlock($student->id, $page->cmid));
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, "editingteacher");
        $this->setUser($teacher);
        purchase_service::refund_purchase($purchase->id, $teacher->id, "Refund test");
        $this->assertFalse(api::has_content_unlock($student->id, $page->cmid));
    }

}
