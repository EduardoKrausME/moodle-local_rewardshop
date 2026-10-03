<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * index.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');
$courseid=required_param('courseid',PARAM_INT);$course=get_course($courseid);require_login($course);$context=context_course::instance($courseid);require_capability('local/rewardshop:view',$context);$PAGE->set_url('/local/rewardshop/index.php',['courseid'=>$courseid]);$PAGE->set_context($context);$PAGE->set_title(get_string('pluginname','local_rewardshop'));$PAGE->set_heading($course->fullname);$PAGE->requires->js_call_amd('local_rewardshop/shop','init');
$balance=\local_rewardshop\api::get_balance($USER->id,$courseid);$rewards=$DB->get_records('local_rewardshop_rewards',['courseid'=>$courseid,'enabled'=>1],'sortorder ASC,id ASC');$items=[];$now=time();
foreach($rewards as $r){if(($r->timestart&&$r->timestart>$now)||($r->timeend&&$r->timeend<$now))continue;$check=\local_rewardshop\api::can_purchase_reward($r->id,$USER->id);$fs=get_file_storage();$files=$fs->get_area_files($context->id,'local_rewardshop','rewardimage',$r->id,'itemid,filepath,filename',false);$img='';if($files){$f=reset($files);$img=moodle_url::make_pluginfile_url($context->id,'local_rewardshop','rewardimage',$r->id,$f->get_filepath(),$f->get_filename())->out(false);} $type=\local_rewardshop\reward_type_registry::get($r->rewardtype);$remaining=null;if($r->stock!==null){list($insql,$params)=$DB->get_in_or_equal(['pending','approved','delivered'],SQL_PARAMS_NAMED,'st');$params['rid']=$r->id;$sold=$DB->count_records_select('local_rewardshop_purchases',"rewardid=:rid AND status $insql",$params);$remaining=max(0,(int)$r->stock-$sold);} $items[]=['id'=>$r->id,'name'=>format_string($r->name),'description'=>format_text($r->description,$r->descriptionformat),'cost'=>$r->cost,'remaining'=>$remaining,'hasremaining'=>$remaining!==null,'image'=>$img,'automatic'=>$type->supports_automatic_delivery()&&!$r->requiresapproval,'approval'=>(bool)$r->requiresapproval,'once'=>(int)$r->maxperuser===1,'available'=>$check['allowed'],'reason'=>implode('; ',$check['errors']),'token'=>bin2hex(random_bytes(16)),'buyurl'=>(new moodle_url('/local/rewardshop/buy.php'))->out(false),'sesskey'=>sesskey()];}
echo $OUTPUT->header();echo $OUTPUT->render_from_template('local_rewardshop/shop',['balance'=>$balance,'rewards'=>$items,'courseid'=>$courseid,'myurl'=>(new moodle_url('/local/rewardshop/my.php',['courseid'=>$courseid]))->out(false),'manageurl'=>has_capability('local/rewardshop:manage',$context)?(new moodle_url('/local/rewardshop/manage.php',['courseid'=>$courseid]))->out(false):'']);echo $OUTPUT->footer();
