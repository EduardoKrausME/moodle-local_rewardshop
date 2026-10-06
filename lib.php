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
 * Plugin library functions.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
function local_rewardshop_extend_navigation_course($navigation, $course, $context) {
 if (!isloggedin() || isguestuser() || !has_capability('local/rewardshop:view', $context)) { return; }
 $navigation->add(get_string('pluginname','local_rewardshop'), new moodle_url('/local/rewardshop/index.php',['courseid'=>$course->id]), navigation_node::TYPE_CUSTOM, null, 'local_rewardshop');
}
function local_rewardshop_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=[]) {
 if ($context->contextlevel !== CONTEXT_COURSE || $filearea !== 'rewardimage') { return false; }
 require_login($course);
 if (!has_capability('local/rewardshop:view',$context)) { return false; }
 $itemid = array_shift($args); $filename = array_pop($args); $filepath = '/'.implode('/',$args).'/';
 $fs = get_file_storage(); $file = $fs->get_file($context->id,'local_rewardshop',$filearea,$itemid,$filepath,$filename);
 if (!$file || $file->is_directory()) { return false; }
 send_stored_file($file, 0, 0, $forcedownload, $options); return true;
}
