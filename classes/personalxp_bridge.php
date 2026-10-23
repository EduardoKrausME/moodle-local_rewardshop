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
 * classes/personalxp_bridge.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop;
use local_rewardshop\wallet_service;

/**
 * Synchronise Personal XP with course credits without replaying all historical milestones.
 */
class personalxp_bridge {
    /**
     * Convert only XP not previously credited to the learner, atomically with the wallet.
     *
     * @param int $userid Student.
     * @param int $courseid Course.
     * @return int Amount of credits granted.
     */
    public static function sync_user_course(int $userid, int $courseid): int {
        global $DB;
        if (!class_exists("\\local_personalxp\\service\\xp_manager")) {
            return 0;
        }
        $rule = $DB->get_record("local_rewardshop_rules", ["courseid" => $courseid]);
        $step = $rule ? (int)$rule->xpstep : (int)(get_config("local_rewardshop", "defaultxpstep") ?: 500);
        $credits = $rule ? (int)$rule->credits : (int)(get_config("local_rewardshop", "defaultcredits") ?: 50);
        if (($rule && !$rule->enabled) || $step <= 0 || $credits <= 0) {
            return 0;
        }
        $xpclass = "\\local_personalxp\\service\\xp_manager";
        $xp = max(0, (int)$xpclass::get_total($userid, $courseid));

        return wallet_service::with_wallet_lock($userid, $courseid,
            function () use ($userid, $courseid, $step, $credits, $xp): int {
                global $DB;
                $tx = $DB->start_delegated_transaction();
                $wallet = $DB->get_record("local_rewardshop_wallet",
                    ["userid" => $userid, "courseid" => $courseid]);
                $converted = $wallet ? (int)$wallet->xpconverted : -1;
                if ($converted < 0) {
                    // One-time migration for wallets credited by the old per-milestone ledger algorithm.
                    $converted = self::legacy_converted_xp($userid, $courseid);
                }
                // Compare unconverted XP, not absolute milestone numbers: those change with the rule.
                $steps = intdiv(max(0, $xp - $converted), $step);
                $earned = $steps * $credits;
                $checkpoint = $converted + ($steps * $step);
                if ($earned > 0) {
                    $reference = "personalxp:{$checkpoint}";
                    api::add_credits($userid, $courseid, $earned, $reference,
                        get_string("xpcreditaward", "local_rewardshop",
                            (object)["xp" => $checkpoint, "credits" => $earned]),
                        null, "personalxp|{$userid}|{$courseid}|{$converted}|{$checkpoint}");
                }
                // Persist the checkpoint under the same wallet lock/transaction as the credit award.
                $wallet = $DB->get_record("local_rewardshop_wallet",
                    ["userid" => $userid, "courseid" => $courseid]);
                if ($wallet && (int)$wallet->xpconverted !== $checkpoint) {
                    $wallet->xpconverted = $checkpoint;
                    $DB->update_record("local_rewardshop_wallet", $wallet);
                }
                $tx->allow_commit();
                return $earned;
            });
    }

    /**
     * Derive already converted XP from legacy ledger rows once, after an upgrade.
     *
     * @param int $userid Student.
     * @param int $courseid Course.
     * @return int Highest historical XP threshold.
     */
    private static function legacy_converted_xp(int $userid, int $courseid): int {
        global $DB;
        $like = $DB->sql_like("reference", ":prefix", false);
        $rows = $DB->get_records_select("local_rewardshop_ledger",
            "userid = :userid AND courseid = :courseid AND type = :type AND {$like}",
            ["userid" => $userid, "courseid" => $courseid,
                "type" => "earn", "prefix" => "personalxp:%"], "", "id,reference");
        $converted = 0;
        foreach ($rows as $row) {
            if (preg_match("/^personalxp:(\\d+):(\\d+)$/", $row->reference, $match)) {
                $converted = max($converted, (int)$match[1] * (int)$match[2]);
            }
        }
        return $converted;
    }

    /**
     * Handle a new Personal XP event.
     *
     * @param int $userid Student.
     * @param int $courseid Course.
     */
    public static function on_xp_awarded(int $userid, int $courseid): void {
        self::sync_user_course($userid, $courseid);
    }
}
