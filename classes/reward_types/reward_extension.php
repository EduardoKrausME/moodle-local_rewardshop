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
 * classes/reward_types/reward_extension.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop\reward_types;
class reward_extension extends base {
 public function get_name(): string { return get_string('type_extension','local_rewardshop'); }
 public function get_description(): string { return get_string('type_extension_desc','local_rewardshop'); }
 public function validate_configuration(array $config, \stdClass $reward): array {
  $e=[]; if (empty($config['cmid'])) $e['cmid']=get_string('errorcmid','local_rewardshop'); if ((int)($config['seconds']??0)<=0) $e['seconds']=get_string('errorextension','local_rewardshop'); return $e;
 }
 public function supports_automatic_delivery(): bool { return false; }
 public function deliver(\stdClass $purchase, \stdClass $reward, int $userid): void {
  // Moodle 4.1-4.6 does not expose one stable generic deadline-extension API for every activity.
  // Never write activity-plugin tables directly; unsupported targets stay manual.
  throw new \moodle_exception('extensionmanual','local_rewardshop');
 }
}
