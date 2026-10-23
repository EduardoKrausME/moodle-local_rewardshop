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
 * Course restore integration for Reward Shop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

/**
 * Restore course configuration and mapped participant data from course backups.
 */
class restore_local_rewardshop_plugin extends restore_local_plugin {
    /**
     * Register the XML paths handled by the course restore.
     *
     * @return restore_path_element[] Restore paths.
     */
    protected function define_course_plugin_structure() {
        $paths = [];
        foreach ([
            "rewardshop_reward" => "/rewards/rewardshop_reward",
            "rewardshop_rule" => "/rules/rewardshop_rule",
            "rewardshop_wallet" => "/wallets/rewardshop_wallet",
            "rewardshop_ledger" => "/ledger/rewardshop_ledger",
            "rewardshop_purchase" => "/purchases/rewardshop_purchase",
            "rewardshop_unlock" => "/unlocks/rewardshop_unlock",
        ] as $name => $path) {
            $paths[] = new restore_path_element($name, $this->get_pathfor($path));
        }
        return $paths;
    }

    /**
     * Restore a configured course reward.
     *
     * @param array $data Source data.
     */
    public function process_rewardshop_reward(array $data): void {
        global $DB;
        $record = (object)$data;
        $oldid = (int)$record->id;
        unset($record->id);
        $record->courseid = $this->task->get_courseid();
        // Activity references in configjson are remapped after activities have been restored.
        $newid = $DB->insert_record("local_rewardshop_rewards", $record);
        $this->set_mapping("rewardshop_reward", $oldid, $newid);
    }

    /**
     * Restore a course XP conversion rule.
     *
     * @param array $data Source data.
     */
    public function process_rewardshop_rule(array $data): void {
        global $DB;
        $record = (object)$data;
        unset($record->id);
        $record->courseid = $this->task->get_courseid();
        $existing = $DB->get_record("local_rewardshop_rules", ["courseid" => $record->courseid]);
        if ($existing) {
            $record->id = $existing->id;
            $DB->update_record("local_rewardshop_rules", $record);
        } else {
            $DB->insert_record("local_rewardshop_rules", $record);
        }
    }

    /**
     * Restore a user wallet, if its user is included in the restore.
     *
     * @param array $data Source data.
     */
    public function process_rewardshop_wallet(array $data): void {
        global $DB;
        $record = (object)$data;
        $oldid = (int)$record->id;
        unset($record->id);
        $record->userid = (int)$this->get_mappingid("user", (int)$record->userid);
        if (!$record->userid) {
            return;
        }
        $record->courseid = $this->task->get_courseid();
        $existing = $DB->get_record("local_rewardshop_wallet",
            ["userid" => $record->userid, "courseid" => $record->courseid]);
        if (!$existing) {
            $newid = $DB->insert_record("local_rewardshop_wallet", $record);
            $this->set_mapping("rewardshop_wallet", $oldid, $newid);
        }
    }

    /**
     * Restore an immutable ledger record, using a fresh idempotency hash.
     *
     * @param array $data Source data.
     */
    public function process_rewardshop_ledger(array $data): void {
        global $DB;
        $record = (object)$data;
        $oldid = (int)$record->id;
        unset($record->id);
        $record->userid = (int)$this->get_mappingid("user", (int)$record->userid);
        if (!$record->userid) {
            return;
        }
        $record->courseid = $this->task->get_courseid();
        $record->uniquehash = hash("sha256",
            "restore|" . $this->task->get_restoreid() . "|ledger|" . $oldid);
        $DB->insert_record("local_rewardshop_ledger", $record);
    }

    /**
     * Restore a purchase, remapping the reward, student and optional approver.
     *
     * @param array $data Source data.
     */
    public function process_rewardshop_purchase(array $data): void {
        global $DB;
        $record = (object)$data;
        $oldid = (int)$record->id;
        unset($record->id);
        $record->userid = (int)$this->get_mappingid("user", (int)$record->userid);
        $record->rewardid = (int)$this->get_mappingid("rewardshop_reward", (int)$record->rewardid);
        if (!$record->userid || !$record->rewardid) {
            return;
        }
        $record->approvedby = !empty($record->approvedby) ?
            ($this->get_mappingid("user", (int)$record->approvedby) ?: null) : null;
        $record->courseid = $this->task->get_courseid();
        $record->requesttoken = hash("sha256",
            "restore|" . $this->task->get_restoreid() . "|purchase|" . $oldid);
        $newid = $DB->insert_record("local_rewardshop_purchases", $record);
        $this->set_mapping("rewardshop_purchase", $oldid, $newid);
    }

    /**
     * Restore a content unlock; remap its course module after activities are restored.
     *
     * @param array $data Source data.
     */
    public function process_rewardshop_unlock(array $data): void {
        global $DB;
        $record = (object)$data;
        $oldid = (int)$record->id;
        unset($record->id);
        $record->userid = (int)$this->get_mappingid("user", (int)$record->userid);
        $record->purchaseid = (int)$this->get_mappingid("rewardshop_purchase", (int)$record->purchaseid);
        if (!$record->userid || !$record->purchaseid) {
            return;
        }
        $record->courseid = $this->task->get_courseid();
        $newid = $DB->insert_record("local_rewardshop_unlocks", $record);
        $this->set_mapping("rewardshop_unlock", $oldid, $newid);
    }

    /**
     * Map course module IDs only after core has restored the activities.
     */
    public function after_restore_course(): void {
        global $DB;
        $restoreid = $this->task->get_restoreid();
        foreach (["rewardshop_reward", "rewardshop_unlock"] as $kind) {
            $records = $DB->get_recordset("backup_ids_temp",
                ["backupid" => $restoreid, "itemname" => $kind], "", "itemid,newitemid");
            foreach ($records as $map) {
                if (!$map->newitemid) {
                    continue;
                }
                if ($kind === "rewardshop_reward") {
                    $reward = $DB->get_record("local_rewardshop_rewards",
                        ["id" => $map->newitemid, "courseid" => $this->task->get_courseid()]);
                    if (!$reward) {
                        continue;
                    }
                    $config = json_decode((string)$reward->configjson, true);
                    if (!is_array($config) || empty($config["cmid"])) {
                        continue;
                    }
                    $newcmid = (int)$this->get_mappingid("course_module", (int)$config["cmid"]);
                    if ($newcmid) {
                        $config["cmid"] = $newcmid;
                    } else {
                        // Never leave a copied reward pointing into a different course.
                        $config["cmid"] = 0;
                        $reward->enabled = 0;
                    }
                    $reward->configjson = json_encode($config);
                    $DB->update_record("local_rewardshop_rewards", $reward);
                } else {
                    $unlock = $DB->get_record("local_rewardshop_unlocks",
                        ["id" => $map->newitemid, "courseid" => $this->task->get_courseid()]);
                    if (!$unlock) {
                        continue;
                    }
                    $newcmid = (int)$this->get_mappingid("course_module", (int)$unlock->cmid);
                    if ($newcmid) {
                        $unlock->cmid = $newcmid;
                        $DB->update_record("local_rewardshop_unlocks", $unlock);
                    } else {
                        $DB->delete_records("local_rewardshop_unlocks", ["id" => $unlock->id]);
                    }
                }
            }
            $records->close();
        }
    }
}
