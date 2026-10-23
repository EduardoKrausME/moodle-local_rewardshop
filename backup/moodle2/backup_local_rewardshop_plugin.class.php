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
 * Course backup integration for Reward Shop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Attach course reward configuration and optional user records to the Moodle backup.
 */
class backup_local_rewardshop_plugin extends backup_local_plugin {
    /**
     * Define the course-level local plugin structure.
     *
     * @return backup_plugin_element Root plugin element.
     */
    protected function define_course_plugin_structure() {
        $plugin = $this->get_plugin_element(null);
        $root = new backup_nested_element($this->get_recommended_name());
        $plugin->add_child($root);

        $this->add_table($root, "rewards", "rewardshop_reward", "local_rewardshop_rewards", [
            "name", "description", "descriptionformat", "rewardtype", "cost", "stock",
            "maxperuser", "enabled", "timestart", "timeend", "requiresapproval",
            "configjson", "sortorder", "timecreated", "timemodified",
        ]);
        $this->add_table($root, "rules", "rewardshop_rule", "local_rewardshop_rules", [
            "xpstep", "credits", "enabled", "timemodified",
        ]);

        if ($this->get_setting_value("users")) {
            $this->add_table($root, "wallets", "rewardshop_wallet", "local_rewardshop_wallet", [
                "userid", "balance", "lifetimeearned", "lifetimespent", "xpconverted", "timemodified",
            ], ["userid"]);
            $this->add_table($root, "ledger", "rewardshop_ledger", "local_rewardshop_ledger", [
                "userid", "amount", "type", "reference", "relatedid", "balanceafter",
                "description", "uniquehash", "timecreated",
            ], ["userid"]);
            $this->add_table($root, "purchases", "rewardshop_purchase", "local_rewardshop_purchases", [
                "rewardid", "userid", "cost", "status", "requesttoken", "configsnapshot",
                "approvedby", "timeapproved", "timecreated", "timemodified",
            ], ["userid", "approvedby"]);
            $this->add_table($root, "unlocks", "rewardshop_unlock", "local_rewardshop_unlocks", [
                "purchaseid", "userid", "cmid", "timeexpires", "timecreated",
            ], ["userid"]);
        }

        return $plugin;
    }

    /**
     * Attach a course-scoped table with a dedicated XML element.
     *
     * @param backup_nested_element $root Plugin root.
     * @param string $plural Parent XML element.
     * @param string $singular Record XML element.
     * @param string $table Database table.
     * @param array $fields Data fields.
     * @param array $userfields User IDs to annotate.
     */
    private function add_table(backup_nested_element $root, string $plural, string $singular,
            string $table, array $fields, array $userfields = []): void {
        $container = new backup_nested_element($plural);
        $record = new backup_nested_element($singular, ["id"], $fields);
        $root->add_child($container);
        $container->add_child($record);
        $record->set_source_table($table, ["courseid" => backup::VAR_COURSEID]);
        foreach ($userfields as $field) {
            $record->annotate_ids("user", $field);
        }
    }
}
