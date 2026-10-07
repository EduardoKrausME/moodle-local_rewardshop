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
 * classes/reward_type_interface.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop;
/**
 * Interface reward_type_interface.
 */
interface reward_type_interface {
    /**
     * Method get_name.
     *
     * @return string Return value.
     */
    public function get_name(): string;

    /**
     * Method get_description.
     *
     * @return string Return value.
     */
    public function get_description(): string;

    /**
     * Method validate_configuration.
     *
     * @param array $config Parameter config.
     * @param \stdClass $reward Parameter reward.
     * @return array Return value.
     */
    public function validate_configuration(array $config, \stdClass $reward): array;

    /**
     * Method can_purchase.
     *
     * @param \stdClass $reward Parameter reward.
     * @param int $userid Parameter userid.
     * @param \context_course $context Parameter context.
     * @return array Return value.
     */
    public function can_purchase(\stdClass $reward, int $userid, \context_course $context): array;

    /**
     * Method purchase.
     *
     * @param \stdClass $purchase Parameter purchase.
     * @param \stdClass $reward Parameter reward.
     * @param int $userid Parameter userid.
     * @return void Return value.
     */
    public function purchase(\stdClass $purchase, \stdClass $reward, int $userid): void;

    /**
     * Method deliver.
     *
     * @param \stdClass $purchase Parameter purchase.
     * @param \stdClass $reward Parameter reward.
     * @param int $userid Parameter userid.
     * @return void Return value.
     */
    public function deliver(\stdClass $purchase, \stdClass $reward, int $userid): void;

    /**
     * Method cancel.
     *
     * @param \stdClass $purchase Parameter purchase.
     * @param \stdClass $reward Parameter reward.
     * @param int $userid Parameter userid.
     * @return void Return value.
     */
    public function cancel(\stdClass $purchase, \stdClass $reward, int $userid): void;

    /**
     * Method supports_automatic_delivery.
     *
     * @return bool Return value.
     */
    public function supports_automatic_delivery(): bool;
}
