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
 * classes/reward_types/reward_contentunlock.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop\reward_types;
/**
 * Class reward_contentunlock.
 */
class reward_contentunlock extends base {
    /**
     * Method get_name.
     *
     * @return string Return value.
     */
    public function get_name(): string {
        return get_string('type_contentunlock', 'local_rewardshop');
    }

    /**
     * Method get_description.
     *
     * @return string Return value.
     */
    public function get_description(): string {
        return get_string('type_contentunlock_desc', 'local_rewardshop');
    }

    /**
     * Method validate_configuration.
     *
     * @param array $config Parameter config.
     * @param \stdClass $reward Parameter reward.
     * @return array Return value.
     */
    public function validate_configuration(array $config, \stdClass $reward): array {
        return empty($config['cmid']) ? ['cmid' => get_string('errorcmid', 'local_rewardshop')] : [];
    }

    /**
     * Method can_purchase.
     *
     * @param \stdClass $reward Parameter reward.
     * @param int $userid Parameter userid.
     * @param \context_course $context Parameter context.
     * @return array Return value.
     */
    public function can_purchase(\stdClass $reward, int $userid, \context_course $context): array {
        $c = $this->config($reward);
        try {
            $cm = get_coursemodule_from_id('', (int)($c['cmid'] ?? 0), $reward->courseid, false, MUST_EXIST);
        } catch (\Throwable $e) {
            return [get_string('invalidtarget', 'local_rewardshop')];
        }
        return [];
    }

    /**
     * Method supports_automatic_delivery.
     *
     * @return bool Return value.
     */
    public function supports_automatic_delivery(): bool {
        return true;
    }

    /**
     * Method deliver.
     *
     * @param \stdClass $purchase Parameter purchase.
     * @param \stdClass $reward Parameter reward.
     * @param int $userid Parameter userid.
     * @return void Return value.
     */
    public function deliver(\stdClass $purchase, \stdClass $reward, int $userid): void {
        global $DB;
        $c = $this->config($reward);
        $cmid = (int)($c['cmid'] ?? 0);
        if (!$cmid) {
            throw new \moodle_exception('invalidtarget', 'local_rewardshop');
        }
        if (!$DB->record_exists('local_rewardshop_unlocks', ['purchaseid' => $purchase->id])) {
            $DB->insert_record('local_rewardshop_unlocks', (object)['purchaseid' => $purchase->id, 'userid' => $userid, 'courseid' => $reward->courseid, 'cmid' => $cmid, 'timeexpires' => (int)($c['timeexpires'] ?? 0), 'timecreated' => time()]);
        }
    }
}
