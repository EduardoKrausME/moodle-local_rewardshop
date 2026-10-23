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
 * Reward Shop database upgrades.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade Reward Shop plugin database.
 *
 * @param int $oldversion Installed plugin version.
 * @return bool Upgrade success.
 */
function xmldb_local_rewardshop_upgrade(int $oldversion): bool {
    global $DB;
    if ($oldversion < 2026102300) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table("local_rewardshop_wallet");
        $field = new xmldb_field("xpconverted", XMLDB_TYPE_INTEGER, "10",
            null, XMLDB_NOTNULL, null, "-1", "lifetimespent");
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2026102300, "local", "rewardshop");
    }
    return true;
}
