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
 * classes/reward_type_registry.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop;

use invalid_parameter_exception;

/**
 * Class reward_type_registry.
 */
class reward_type_registry {
    /** @var string[] */
    private const TYPES = [
        'reward_badge',
        'reward_contentunlock',
        'reward_hint',
        'reward_quizattempt',
        'reward_extension',
        'reward_custom',
    ];

    /**
     * Method get.
     *
     * @param string $type Parameter type.
     * @return reward_type_interface Return value.
     */
    public static function get(string $type): reward_type_interface {
        if (!in_array($type, self::TYPES, true)) {
            throw new invalid_parameter_exception('Unknown reward type');
        }
        $class = '\\local_rewardshop\\reward_types\\' . $type;
        return new $class();
    }

    /**
     * Method all.
     *
     * @return array Return value.
     */
    public static function all(): array {
        $out = [];
        foreach (self::TYPES as $t) {
            $o = self::get($t);
            $out[$t] = $o->get_name();
        }
        return $out;
    }
}
