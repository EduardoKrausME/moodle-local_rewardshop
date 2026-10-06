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
 * buy.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');require_sesskey();$rewardid=required_param('rewardid',PARAM_INT);$token=required_param('token',PARAM_ALPHANUMEXT);$reward=$DB->get_record('local_rewardshop_rewards',['id'=>$rewardid],'*',MUST_EXIST);$course=get_course($reward->courseid);require_login($course);$context=context_course::instance($course->id);require_capability('local/rewardshop:view',$context);
try{\local_rewardshop\api::purchase_reward($rewardid,$USER->id,$token);redirect(new moodle_url('/local/rewardshop/my.php',['courseid'=>$course->id]),get_string('purchasesuccess','local_rewardshop'));}catch(Throwable $e){redirect(new moodle_url('/local/rewardshop/index.php',['courseid'=>$course->id]),$e->getMessage(),null,\core\output\notification::NOTIFY_ERROR);}
