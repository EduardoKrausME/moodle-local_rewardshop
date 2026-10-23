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
 * Manage manually audited Reward Shop wallet corrections.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_rewardshop\wallet_service;

require_once(__DIR__ . "/../../config.php");

$courseid = required_param("courseid", PARAM_INT);
$course = get_course($courseid);
require_login($course);
$context = context_course::instance($courseid);
require_capability("local/rewardshop:adjustcredits", $context);
$PAGE->set_url("/local/rewardshop/adjust.php", ["courseid" => $courseid]);
$PAGE->set_context($context);
$PAGE->set_title(get_string("adjustcredits", "local_rewardshop"));
$PAGE->set_heading($course->fullname);

if (data_submitted() && !optional_param("success", 0, PARAM_BOOL)) {
    require_sesskey();
    $userid = required_param("userid", PARAM_INT);
    $amount = required_param("amount", PARAM_INT);
    $reason = trim(required_param("reason", PARAM_TEXT));
    $token = required_param("token", PARAM_ALPHANUMEXT);
    if (!preg_match("/^[a-zA-Z0-9]{32}$/", $token)) {
        throw new moodle_exception("adjustinvalidtoken", "local_rewardshop");
    }
    $user = $DB->get_record("user", ["id" => $userid, "deleted" => 0, "suspended" => 0]);
    if (!$user || !is_enrolled($context, $user, "", true)) {
        throw new moodle_exception("adjustinvaliduser", "local_rewardshop");
    }
    if ($amount === 0) {
        throw new moodle_exception("adjustinvalidamount", "local_rewardshop");
    }
    if ($reason === "") {
        throw new moodle_exception("adjustinvalidreason", "local_rewardshop");
    }
    wallet_service::change($userid, $courseid, $amount, "adjustment",
        "manual", $reason . " (actor: " . $USER->id . ")", null,
        "adjustment|{$courseid}|{$userid}|{$token}");
    redirect(new moodle_url("/local/rewardshop/adjust.php", ["courseid" => $courseid, "success" => 1]));
}

echo $OUTPUT->header();
if (optional_param("success", 0, PARAM_BOOL)) {
    echo $OUTPUT->notification(get_string("adjustsuccess", "local_rewardshop"), "success");
}
echo $OUTPUT->render_from_template("local_rewardshop/adjust", [
    "courseid" => $courseid,
    "sesskey" => sesskey(),
    "token" => random_string(32),
    "backurl" => new moodle_url("/local/rewardshop/manage.php", ["courseid" => $courseid]),
]);
echo $OUTPUT->footer();
