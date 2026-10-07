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
/**
 * Class personalxp_bridge.
 */
class personalxp_bridge {
    /**
     * Method sync_user_course.
     *
     * @param int $userid Parameter userid.
     * @param int $courseid Parameter courseid.
     * @return int Return value.
     */
    public static function sync_user_course(int $userid, int $courseid): int {
        global $DB;
        if (!class_exists('\\local_personalxp\\service\\xp_manager')) {
            return 0;
        }
        $rule = $DB->get_record('local_rewardshop_rules', ['courseid' => $courseid]);
        $step = $rule ? (int)$rule->xpstep : (int)(get_config('local_rewardshop', 'defaultxpstep') ?: 500);
        $credits = $rule ? (int)$rule->credits : (int)(get_config('local_rewardshop', 'defaultcredits') ?: 50);
        if ($rule && !$rule->enabled) {
            return 0;
        }
        if ($step <= 0 || $credits <= 0) {
            return 0;
        }
        $class = '\\local_personalxp\\service\\xp_manager';
        $xp = $class::get_total($userid, $courseid);
        $milestones = intdiv($xp, $step);
        $granted = 0;
        for ($m = 1; $m <= $milestones; $m++) {
            $ref = "personalxp:{$step}:{$m}";
            $idkey = "personalxp|{$userid}|{$courseid}|{$step}|{$m}";
            $storedhash = hash('sha256', $idkey);
            if (!$DB->record_exists('local_rewardshop_ledger', ['uniquehash' => $storedhash])) {
                api::add_credits($userid, $courseid, $credits, $ref,
                    get_string('xpcreditaward', 'local_rewardshop', (object)['xp' => $m * $step, 'credits' => $credits]),
                    null, $idkey);
                $granted += $credits;
            }
        }
        return $granted;
    }

    /**
     * Method on_xp_awarded.
     *
     * @param int $userid Parameter userid.
     * @param int $courseid Parameter courseid.
     * @return void Return value.
     */
    public static function on_xp_awarded(int $userid, int $courseid): void {
        self::sync_user_course($userid, $courseid);
    }
}
