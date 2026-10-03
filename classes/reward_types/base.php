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
 * classes/reward_types/base.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop\reward_types;
use local_rewardshop\reward_type_interface;
abstract class base implements reward_type_interface {
 public function get_description(): string { return ''; }
 public function validate_configuration(array $config, \stdClass $reward): array { return []; }
 public function can_purchase(\stdClass $reward, int $userid, \context_course $context): array { return []; }
 public function purchase(\stdClass $purchase, \stdClass $reward, int $userid): void {}
 public function cancel(\stdClass $purchase, \stdClass $reward, int $userid): void {}
 protected function config(\stdClass $reward): array { $v=json_decode((string)($reward->configjson??''),true); return is_array($v)?$v:[]; }
}
