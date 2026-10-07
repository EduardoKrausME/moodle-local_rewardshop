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
 * rules.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_rewardshop\form\rule_form;

require_once(__DIR__ . '/../../config.php');
$courseid = required_param('courseid', PARAM_INT);
$course = get_course($courseid);
require_login($course);
$context = context_course::instance($courseid);
require_capability('local/rewardshop:manage', $context);
$PAGE->set_url('/local/rewardshop/rules.php', ['courseid' => $courseid]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('creditrules', 'local_rewardshop'));
$PAGE->set_heading($course->fullname);
$form = new rule_form();
$rule = $DB->get_record('local_rewardshop_rules', ['courseid' => $courseid]);
if (!$rule) {
    $rule = (object)[
        'courseid' => $courseid,
        'enabled' => 1,
        'xpstep' => (int)(get_config('local_rewardshop', 'defaultxpstep') ?: 500),
        'credits' => (int)(get_config('local_rewardshop', 'defaultcredits') ?: 50),
    ];
}
$form->set_data($rule);
if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/rewardshop/manage.php', ['courseid' => $courseid]));
}
if ($d = $form->get_data()) {
    $rec = (object)[
        'courseid' => $courseid,
        'enabled' => (int)$d->enabled,
        'xpstep' => (int)$d->xpstep,
        'credits' => (int)$d->credits,
        'timemodified' => time(),
    ];
    if ($rule->id ?? 0) {
        $rec->id = $rule->id;
        $DB->update_record('local_rewardshop_rules', $rec);
    } else {
        $DB->insert_record('local_rewardshop_rules', $rec);
    }
    redirect(new moodle_url('/local/rewardshop/manage.php',
        ['courseid' => $courseid]), get_string('changessaved'));
}
echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
