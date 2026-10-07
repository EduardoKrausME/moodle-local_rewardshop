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
 * manage.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_rewardshop\reward_type_registry;

require_once(__DIR__ . '/../../config.php');
$courseid = required_param('courseid', PARAM_INT);
$course = get_course($courseid);
require_login($course);
$context = context_course::instance($courseid);
require_capability('local/rewardshop:manage', $context);
$PAGE->set_url('/local/rewardshop/manage.php', ['courseid' => $courseid]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('manage', 'local_rewardshop'));
$PAGE->set_heading($course->fullname);
if (($action = optional_param('action', '', PARAM_ALPHA)) && confirm_sesskey()) {
    $id = required_param('id', PARAM_INT);
    $r = $DB->get_record('local_rewardshop_rewards',
        ['id' => $id, 'courseid' => $courseid], '*', MUST_EXIST);
    if ($action === 'toggle') {
        $r->enabled = $r->enabled ? 0 : 1;
        $r->timemodified = time();
        $DB->update_record('local_rewardshop_rewards', $r);
    } else {
        if ($action === 'duplicate') {
            unset($r->id);
            $r->name = get_string('copyof', 'local_rewardshop', $r->name);
            $r->enabled = 0;
            $r->sortorder++;
            $r->timecreated = $r->timemodified = time();
            $DB->insert_record('local_rewardshop_rewards', $r);
        } else {
            if (in_array($action, ['up', 'down'], true)) {
                $order = $DB->get_records('local_rewardshop_rewards',
                    ['courseid' => $courseid], 'sortorder ASC,id ASC');
                $ids = array_keys($order);
                $pos = array_search($id, $ids, true);
                $target = $action === 'up' ? $pos - 1 : $pos + 1;
                if ($pos !== false && isset($ids[$target])) {
                    $other = $order[$ids[$target]];
                    $currentsort = (int)$r->sortorder;
                    $othersort = (int)$other->sortorder;
                    if ($currentsort === $othersort) {
                        $currentsort = $pos + 1;
                        $othersort = $target + 1;
                    }
                    $r->sortorder = $othersort;
                    $other->sortorder = $currentsort;
                    $r->timemodified = $other->timemodified = time();
                    $tx = $DB->start_delegated_transaction();
                    $DB->update_record('local_rewardshop_rewards', $r);
                    $DB->update_record('local_rewardshop_rewards', $other);
                    $tx->allow_commit();
                }
            }
        }
    }
    redirect($PAGE->url);
}
$rewards = $DB->get_records('local_rewardshop_rewards', ['courseid' => $courseid], 'sortorder ASC,id ASC');
$items = [];
foreach ($rewards as $r) {
    $items[] = [
        'id' => $r->id,
        'name' => format_string($r->name),
        'cost' => $r->cost,
        'enabled' => $r->enabled,
        'type' => reward_type_registry::get($r->rewardtype)->get_name(),
        'editurl' => new moodle_url('/local/rewardshop/edit.php', ['courseid' => $courseid, 'id' => $r->id]),
        'toggleurl' => new moodle_url('/local/rewardshop/manage.php', ['courseid' => $courseid, 'id' => $r->id, 'action' => 'toggle', 'sesskey' => sesskey()]),
        'duplicateurl' => new moodle_url('/local/rewardshop/manage.php', ['courseid' => $courseid, 'id' => $r->id, 'action' => 'duplicate', 'sesskey' => sesskey()]),
        'upurl' => new moodle_url('/local/rewardshop/manage.php', ['courseid' => $courseid, 'id' => $r->id, 'action' => 'up', 'sesskey' => sesskey()]),
        'downurl' => new moodle_url('/local/rewardshop/manage.php', ['courseid' => $courseid, 'id' => $r->id, 'action' => 'down', 'sesskey' => sesskey()]),
    ];
}
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_rewardshop/manage',
    [
        'rewards' => $items,
        'newurl' => new moodle_url('/local/rewardshop/edit.php', ['courseid' => $courseid]),
        'purchasesurl' => new moodle_url('/local/rewardshop/purchases.php', ['courseid' => $courseid]),
        'rulesurl' => new moodle_url('/local/rewardshop/rules.php', ['courseid' => $courseid]),
    ]);
echo $OUTPUT->footer();
