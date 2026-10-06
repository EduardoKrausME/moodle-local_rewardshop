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
 * edit.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');$courseid=required_param('courseid',PARAM_INT);$id=optional_param('id',0,PARAM_INT);$course=get_course($courseid);require_login($course);$context=context_course::instance($courseid);require_capability('local/rewardshop:manage',$context);$PAGE->set_url('/local/rewardshop/edit.php',['courseid'=>$courseid,'id'=>$id]);$PAGE->set_context($context);$PAGE->set_title(get_string('editreward','local_rewardshop'));$PAGE->set_heading($course->fullname);$form=new \local_rewardshop\form\reward_form();$reward=$id?$DB->get_record('local_rewardshop_rewards',['id'=>$id,'courseid'=>$courseid],'*',MUST_EXIST):null;if($reward){$reward->description_editor=['text'=>$reward->description,'format'=>$reward->descriptionformat];$draft=file_get_submitted_draft_itemid('rewardimage');file_prepare_draft_area($draft,$context->id,'local_rewardshop','rewardimage',$reward->id,['subdirs'=>0,'maxfiles'=>1]);$reward->rewardimage=$draft;$form->set_data($reward);}if($form->is_cancelled())redirect(new moodle_url('/local/rewardshop/manage.php',['courseid'=>$courseid]));if($d=$form->get_data()){$now=time();$rec=(object)['courseid'=>$courseid,'name'=>$d->name,'description'=>$d->description_editor['text'],'descriptionformat'=>$d->description_editor['format'],'rewardtype'=>$d->rewardtype,'cost'=>(int)$d->cost,'stock'=>$d->stock===''?null:(int)$d->stock,'maxperuser'=>(int)$d->maxperuser,'enabled'=>(int)$d->enabled,'timestart'=>(int)$d->timestart,'timeend'=>(int)$d->timeend,'requiresapproval'=>(int)$d->requiresapproval,'configjson'=>trim((string)$d->configjson),'sortorder'=>$reward?(int)$reward->sortorder:(int)$DB->get_field_sql('SELECT COALESCE(MAX(sortorder),0)+1 FROM {local_rewardshop_rewards} WHERE courseid=?',[$courseid]),'timecreated'=>$reward?$reward->timecreated:$now,'timemodified'=>$now];if($reward){$rec->id=$reward->id;$DB->update_record('local_rewardshop_rewards',$rec);$rid=$reward->id;}else{$rid=$DB->insert_record('local_rewardshop_rewards',$rec);}file_save_draft_area_files($d->rewardimage,$context->id,'local_rewardshop','rewardimage',$rid,['subdirs'=>0,'maxfiles'=>1]);redirect(new moodle_url('/local/rewardshop/manage.php',['courseid'=>$courseid]));}echo $OUTPUT->header();$form->display();echo $OUTPUT->footer();
