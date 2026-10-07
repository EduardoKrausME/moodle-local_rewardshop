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
 * purchases.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_rewardshop\purchase_service;

require_once(__DIR__ . '/../../config.php');
$courseid = required_param('courseid', PARAM_INT);
$course = get_course($courseid);
require_login($course);
$context = context_course::instance($courseid);
require_capability('local/rewardshop:approve', $context);
$PAGE->set_url('/local/rewardshop/purchases.php', ['courseid' => $courseid]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('purchases', 'local_rewardshop'));
$PAGE->set_heading($course->fullname);
$action = optional_param('action', '', PARAM_ALPHA);
if ($action) {
    require_sesskey();
    $id = required_param('id', PARAM_INT);
    if ($action === 'approve') {
        purchase_service::approve($id, $USER->id);
    } else {
        if ($action === 'reject') {
            purchase_service::reject($id, $USER->id);
        } else {
            if ($action === 'refund') {
                purchase_service::refund_purchase($id, $USER->id, get_string('teacherrefund', 'local_rewardshop'));
            } else {
                if ($action === 'deliver') {
                    purchase_service::deliver($id, $USER->id);
                }
            }
        }
    }
    redirect($PAGE->url);
}
$sql = "
    SELECT p.*,r.name
      FROM {local_rewardshop_purchases} p
      JOIN {local_rewardshop_rewards}   r ON r.id=p.rewardid
     WHERE p.courseid=:c
  ORDER BY p.timecreated DESC";
$rows = [];
foreach ($DB->get_records_sql($sql, ['c' => $courseid]) as $p) {
    $u = core_user::get_user($p->userid, 'id,firstname,lastname');
    $base = [
        'courseid' => $courseid,
        'id' => $p->id,
        'sesskey' => sesskey(),
    ];
    $rows[] = [
        'id' => $p->id,
        'user' => fullname($u),
        'reward' => format_string($p->name),
        'cost' => $p->cost,
        'status' => get_string('status_' . $p->status, 'local_rewardshop'),
        'date' => userdate($p->timecreated),
        'pending' => $p->status === 'pending',
        'canrefund' => in_array($p->status, ['approved', 'delivered'], true),
        'approved' => $p->status === 'approved',
        'approveurl' => new moodle_url($PAGE->url, $base + ['action' => 'approve']),
        'rejecturl' => new moodle_url($PAGE->url, $base + ['action' => 'reject']),
        'refundurl' => new moodle_url($PAGE->url, $base + ['action' => 'refund']),
        'deliverurl' => new moodle_url($PAGE->url, $base + ['action' => 'deliver']),
    ];
}
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_rewardshop/purchases', [
    'rows' => $rows,
    'manageurl' => new moodle_url('/local/rewardshop/manage.php', ['courseid' => $courseid]),
]);
echo $OUTPUT->footer();
