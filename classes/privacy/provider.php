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
 * classes/privacy/provider.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop\privacy;

use context;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Class provider.
 */
class provider implements \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider,
        \core_privacy\local\request\core_userlist_provider {
    /**
     * Method get_metadata.
     *
     * @param collection $c Parameter c.
     * @return collection Return value.
     */
    public static function get_metadata(collection $c): collection {
        $c->add_database_table('local_rewardshop_wallet',
            [
                'userid' => 'privacy:userid',
                'courseid' => 'privacy:courseid',
                'balance' => 'privacy:balance',
                'lifetimeearned' => 'privacy:lifetimeearned',
                'lifetimespent' => 'privacy:lifetimespent',
            ], 'privacy:wallet');
        $c->add_database_table('local_rewardshop_ledger',
            [
                'userid' => 'privacy:userid',
                'courseid' => 'privacy:courseid',
                'amount' => 'privacy:amount',
                'description' => 'privacy:description',
            ], 'privacy:ledger');
        $c->add_database_table('local_rewardshop_purchases',
            [
                'userid' => 'privacy:userid',
                'courseid' => 'privacy:courseid',
                'status' => 'privacy:status',
                'configsnapshot' => 'privacy:configsnapshot',
                'approvedby' => 'privacy:approvedby',
            ], 'privacy:purchases');
        $c->add_database_table("local_rewardshop_unlocks", [
            "userid" => "privacy:userid",
            "courseid" => "privacy:courseid",
            "purchaseid" => "privacy:purchaseid",
            "cmid" => "privacy:cmid",
            "timeexpires" => "privacy:timeexpires",
        ], "privacy:unlocks");
        return $c;
    }

    /**
     * Method get_contexts_for_userid.
     *
     * @param int $userid Parameter userid.
     * @return contextlist Return value.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $list = new contextlist();
        $sql = "
            SELECT ctx.id
              FROM {context} ctx
             WHERE ctx.contextlevel = :level
               AND (
                    EXISTS (SELECT 1 FROM {local_rewardshop_wallet} w
                            WHERE w.courseid = ctx.instanceid AND w.userid = :u1)
                 OR EXISTS (SELECT 1 FROM {local_rewardshop_ledger} l
                            WHERE l.courseid = ctx.instanceid AND l.userid = :u2)
                 OR EXISTS (SELECT 1 FROM {local_rewardshop_purchases} p
                            WHERE p.courseid = ctx.instanceid AND (p.userid = :u3 OR p.approvedby = :u4))
                 OR EXISTS (SELECT 1 FROM {local_rewardshop_unlocks} t
                            WHERE t.courseid = ctx.instanceid AND t.userid = :u5)
               )";
        $list->add_from_sql($sql, [
            "level" => CONTEXT_COURSE, "u1" => $userid, "u2" => $userid,
            "u3" => $userid, "u4" => $userid, "u5" => $userid,
        ]);
        return $list;
    }

    /**
     * Report users with data in a particular course, including approving teachers.
     *
     * @param userlist $userlist Users to populate.
     */
    public static function get_users_in_context(userlist $userlist): void {
        $ctx = $userlist->get_context();
        if ($ctx->contextlevel !== CONTEXT_COURSE) {
            return;
        }
        foreach (["local_rewardshop_wallet", "local_rewardshop_ledger",
                "local_rewardshop_purchases", "local_rewardshop_unlocks"] as $table) {
            $userlist->add_from_sql("SELECT userid FROM {" . $table . "} WHERE courseid = :courseid",
                ["courseid" => $ctx->instanceid], "userid");
        }
        $userlist->add_from_sql("SELECT approvedby AS userid FROM {local_rewardshop_purchases}
                WHERE courseid = :courseid AND approvedby IS NOT NULL",
            ["courseid" => $ctx->instanceid], "userid");
    }

    /**
     * Method export_user_data.
     *
     * @param approved_contextlist $contexts Parameter contexts.
     * @return void Return value.
     */
    public static function export_user_data(approved_contextlist $contexts): void {
        global $DB;
        $u = $contexts->get_user()->id;
        foreach ($contexts->get_contexts() as $ctx) {
            $c = $ctx->instanceid;
            $data = [
                'wallet' => $DB->get_record('local_rewardshop_wallet',
                    ['userid' => $u, 'courseid' => $c]),
                'ledger' => array_values($DB->get_records('local_rewardshop_ledger',
                    ['userid' => $u, 'courseid' => $c], 'timecreated ASC')),
                'purchases' => array_values($DB->get_records('local_rewardshop_purchases',
                    ['userid' => $u, 'courseid' => $c], 'timecreated ASC')),
                'unlocks' => array_values($DB->get_records('local_rewardshop_unlocks',
                    ['userid' => $u, 'courseid' => $c], 'timecreated ASC')),
                'approvals' => array_values($DB->get_records('local_rewardshop_purchases',
                    ['approvedby' => $u, 'courseid' => $c], 'timeapproved ASC',
                    'id,courseid,approvedby,timeapproved')),
            ];
            writer::with_context($ctx)->export_data([get_string('pluginname', 'local_rewardshop')], (object)$data);
        }
    }

    /**
     * Method delete_data_for_all_users_in_context.
     *
     * @param context $context Parameter context.
     * @return void Return value.
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;
        if ($context->contextlevel !== CONTEXT_COURSE) {
            return;
        }
        $c = $context->instanceid;

        $ts = ['local_rewardshop_unlocks', 'local_rewardshop_purchases', 'local_rewardshop_ledger', 'local_rewardshop_wallet'];
        foreach ($ts as $t) {
            $DB->delete_records($t, ['courseid' => $c]);
        }
    }

    /**
     * Method delete_data_for_user.
     *
     * @param approved_contextlist $contexts Parameter contexts.
     * @return void Return value.
     */
    public static function delete_data_for_user(approved_contextlist $contexts): void {
        $userid = (int)$contexts->get_user()->id;
        foreach ($contexts->get_contexts() as $ctx) {
            if ($ctx->contextlevel === CONTEXT_COURSE) {
                self::delete_user_course($userid, (int)$ctx->instanceid);
            }
        }
    }

    /**
     * Delete data for an approved collection of users in a course.
     *
     * @param approved_userlist $userlist Approved users.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        $ctx = $userlist->get_context();
        if ($ctx->contextlevel !== CONTEXT_COURSE) {
            return;
        }
        foreach ($userlist->get_userids() as $userid) {
            self::delete_user_course((int)$userid, (int)$ctx->instanceid);
        }
    }

    /**
     * Remove user-owned records and anonymise this user's approval of other users' purchases.
     *
     * @param int $userid User to erase.
     * @param int $courseid Course scope.
     */
    private static function delete_user_course(int $userid, int $courseid): void {
        global $DB;
        $DB->execute("UPDATE {local_rewardshop_purchases} SET approvedby = NULL
                       WHERE courseid = :courseid AND approvedby = :userid",
            ["courseid" => $courseid, "userid" => $userid]);
        foreach (["local_rewardshop_unlocks", "local_rewardshop_purchases",
                "local_rewardshop_ledger", "local_rewardshop_wallet"] as $table) {
            $DB->delete_records($table, ["courseid" => $courseid, "userid" => $userid]);
        }
    }
}
