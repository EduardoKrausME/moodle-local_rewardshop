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
 * classes/reward_types/reward_badge.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop\reward_types;
class reward_badge extends base {
 public function get_name(): string { return get_string('type_badge','local_rewardshop'); }
 public function get_description(): string { return get_string('type_badge_desc','local_rewardshop'); }
 public function validate_configuration(array $config, \stdClass $reward): array { return empty($config['badgeid']) ? ['badgeid'=>get_string('errorbadgeid','local_rewardshop')] : []; }
 public function supports_automatic_delivery(): bool { return function_exists('badge_award'); }
 public function deliver(\stdClass $purchase, \stdClass $reward, int $userid): void {
  $config=$this->config($reward); $badgeid=(int)($config['badgeid']??0); if (!$badgeid || !function_exists('badge_award')) { throw new \moodle_exception('deliverynotsupported','local_rewardshop'); }
  global $CFG; require_once($CFG->libdir.'/badgeslib.php'); badge_award($badgeid,$userid);
 }
}
