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
 * classes/event/reward_rejected.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop\event;
/**
 * Class reward_rejected.
 */
class reward_rejected extends \core\event\base {
    /**
     * Method init.
     *
     * @return mixed Return value.
     */
    protected function init() {
        $this->data['contextlevel'] = CONTEXT_COURSE;
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Method get_name.
     *
     * @return mixed Return value.
     */
    public static function get_name() {
        return get_string('event_reward_rejected', 'local_rewardshop');
    }

    /**
     * Method get_description.
     *
     * @return mixed Return value.
     */
    public function get_description() {
        return 'Reward Rejected';
    }

    /**
     * Method get_url.
     *
     * @return mixed Return value.
     */
    public function get_url() {
        return new \moodle_url('/local/rewardshop/my.php', ['courseid' => $this->courseid]);
    }
}
