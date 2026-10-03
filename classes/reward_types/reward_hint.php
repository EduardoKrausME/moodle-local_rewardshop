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
 * classes/reward_types/reward_hint.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop\reward_types;
class reward_hint extends base {
 public function get_name(): string { return get_string('type_hint','local_rewardshop'); }
 public function get_description(): string { return get_string('type_hint_desc','local_rewardshop'); }
 public function validate_configuration(array $config, \stdClass $reward): array { return trim((string)($config['hint']??''))==='' ? ['hint'=>get_string('errorhint','local_rewardshop')] : []; }
 public function supports_automatic_delivery(): bool { return true; }
 public function deliver(\stdClass $purchase, \stdClass $reward, int $userid): void { /* Hint lives in immutable config snapshot and is shown in My purchases. */ }
}
