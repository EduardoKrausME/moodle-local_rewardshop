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
 * classes/reward_types/reward_badge.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop\reward_types;

use moodle_exception;
use stdClass;

/**
 * Class reward_badge.
 */
class reward_badge extends base {
    /**
     * Method get_name.
     *
     * @return string Return value.
     */
    public function get_name(): string {
        return get_string('type_badge', 'local_rewardshop');
    }

    /**
     * Method get_description.
     *
     * @return string Return value.
     */
    public function get_description(): string {
        return get_string('type_badge_desc', 'local_rewardshop');
    }

    /**
     * Method validate_configuration.
     *
     * @param array $config Parameter config.
     * @param stdClass $reward Parameter reward.
     * @return array Return value.
     */
    public function validate_configuration(array $config, stdClass $reward): array {
        return empty($config['badgeid']) ? [
            'badgeid' => get_string('errorbadgeid', 'local_rewardshop'),
        ] : [];
    }

    /**
     * Method supports_automatic_delivery.
     *
     * @return bool Return value.
     */
    public function supports_automatic_delivery(): bool {
        return class_exists("\\core_badges\\badge");
    }

    /**
     * Method deliver.
     *
     * @param stdClass $purchase Parameter purchase.
     * @param stdClass $reward Parameter reward.
     * @param int $userid Parameter userid.
     * @return void Return value.
     */
    public function deliver(stdClass $purchase, stdClass $reward, int $userid): void {
        $config = $this->config($reward);
        $badgeid = (int)($config['badgeid'] ?? 0);
        global $DB, $CFG;
        require_once($CFG->libdir . "/badgeslib.php");
        if (!$badgeid || !class_exists("\\core_badges\\badge")) {
            throw new moodle_exception("deliverynotsupported", "local_rewardshop");
        }
        $record = $DB->get_record("badge", ["id" => $badgeid], "*", MUST_EXIST);
        if ((int)$record->type !== BADGE_TYPE_COURSE || (int)$record->courseid !== (int)$reward->courseid ||
                !in_array((int)$record->status, [BADGE_STATUS_ACTIVE, BADGE_STATUS_ACTIVE_LOCKED], true)) {
            throw new moodle_exception("invalidtarget", "local_rewardshop");
        }
        if (!$DB->record_exists("badge_issued", ["badgeid" => $badgeid, "userid" => $userid])) {
            (new \core_badges\badge($badgeid))->issue($userid);
        }
    }
}
