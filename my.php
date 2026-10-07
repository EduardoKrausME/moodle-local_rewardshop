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
 * my.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_rewardshop\api;

require_once(__DIR__ . '/../../config.php');
$courseid = required_param('courseid', PARAM_INT);
$course = get_course($courseid);
require_login($course);
$context = context_course::instance($courseid);
require_capability('local/rewardshop:view', $context);
$PAGE->set_url('/local/rewardshop/my.php', ['courseid' => $courseid]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('myhistory', 'local_rewardshop'));
$PAGE->set_heading($course->fullname);
$ledger = $DB->get_records('local_rewardshop_ledger',
    ['userid' => $USER->id, 'courseid' => $courseid], 'timecreated DESC', '*', 0, 100);
$purchases = $DB->get_records('local_rewardshop_purchases',
    ['userid' => $USER->id, 'courseid' => $courseid], 'timecreated DESC', '*', 0, 100);
$rows = [];
foreach ($ledger as $l) {
    $rows[] = [
        'date' => userdate($l->timecreated),
        'description' => s($l->description ?: $l->reference),
        'amount' => $l->amount,
        'balance' => $l->balanceafter,
        'positive' => $l->amount > 0,
    ];
}
$ps = [];
foreach ($purchases as $p) {
    $r = $DB->get_record('local_rewardshop_rewards', ['id' => $p->rewardid]);
    $snap = json_decode((string)$p->configsnapshot, true) ?: [];
    $hint = '';
    if (($snap['rewardtype'] ?? '') === 'reward_hint' &&
        in_array($p->status, ['approved', 'delivered'], true)) {
        $hint = clean_text((string)($snap['config']['hint'] ?? ''));
    }
    $ps[] = [
        'date' => userdate($p->timecreated),
        'name' => format_string($r->name ?? ($snap['name'] ?? '')),
        'cost' => $p->cost, 'status' => get_string('status_' . $p->status, 'local_rewardshop'),
        'hint' => $hint,
    ];
}
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_rewardshop/my', [
    'balance' => api::get_balance($USER->id, $courseid),
    'ledger' => $rows,
    'purchases' => $ps,
    'shopurl' => new moodle_url('/local/rewardshop/index.php', ['courseid' => $courseid]),
]);
echo $OUTPUT->footer();
