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
 * classes/task/sync_personalxp.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop\task;

use context_course;
use core\task\scheduled_task;
use local_rewardshop\personalxp_bridge;

/**
 * Class sync_personalxp.
 */
class sync_personalxp extends scheduled_task {
    /**
     * Method get_name.
     *
     * @return mixed Return value.
     */
    public function get_name() {
        return get_string('tasksyncxp', 'local_rewardshop');
    }

    /**
     * Method execute.
     *
     * @return mixed Return value.
     */
    public function execute() {
        global $DB;
        if (!class_exists('\\local_personalxp\\service\\xp_manager')) {
            return;
        }
        $courses = $DB->get_fieldset_select('local_rewardshop_rewards', 'DISTINCT courseid', 'enabled=1');
        foreach ($courses as $courseid) {
            $context = context_course::instance((int)$courseid, IGNORE_MISSING);
            if (!$context) {
                continue;
            }
            $users = get_enrolled_users($context, 'local/rewardshop:view', 0, 'u.id');
            foreach ($users as $u) {
                personalxp_bridge::sync_user_course((int)$u->id, (int)$courseid);
            }
        }
    }
}
