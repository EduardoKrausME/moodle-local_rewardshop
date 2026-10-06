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
 * classes/form/reward_form.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop\form;
defined('MOODLE_INTERNAL')||die();require_once($CFG->libdir.'/formslib.php');
/**
 * Class reward_form.
 */
class reward_form extends \moodleform {
 /**
  * Method definition.
  *
  * @return mixed Return value.
  */
 public function definition(){global $COURSE;$m=$this->_form;$m->addElement('hidden','id',0);$m->setType('id',PARAM_INT);$m->addElement('hidden','courseid',$COURSE->id);$m->setType('courseid',PARAM_INT);$m->addElement('text','name',get_string('name'),['size'=>60]);$m->setType('name',PARAM_TEXT);$m->addRule('name',null,'required');$m->addElement('editor','description_editor',get_string('description'),null,['maxfiles'=>0]);$m->addElement('select','rewardtype',get_string('rewardtype','local_rewardshop'),\local_rewardshop\reward_type_registry::all());$m->addElement('text','cost',get_string('cost','local_rewardshop'));$m->setType('cost',PARAM_INT);$m->setDefault('cost',0);$m->addElement('text','stock',get_string('stock','local_rewardshop'));$m->setType('stock',PARAM_INT);$m->addElement('text','maxperuser',get_string('maxperuser','local_rewardshop'));$m->setType('maxperuser',PARAM_INT);$m->setDefault('maxperuser',1);$m->addElement('advcheckbox','enabled',get_string('enabled','local_rewardshop'));$m->setDefault('enabled',1);$m->addElement('date_time_selector','timestart',get_string('timestart','local_rewardshop'),['optional'=>true]);$m->addElement('date_time_selector','timeend',get_string('timeend','local_rewardshop'),['optional'=>true]);$m->addElement('advcheckbox','requiresapproval',get_string('requiresapproval','local_rewardshop'));$m->addElement('textarea','configjson',get_string('configjson','local_rewardshop'),'rows="8" cols="70"');$m->setType('configjson',PARAM_RAW);$m->addElement('filemanager','rewardimage',get_string('image','local_rewardshop'),null,['subdirs'=>0,'maxfiles'=>1,'accepted_types'=>['image']]);$this->add_action_buttons();}
 /**
  * Method validation.
  *
  * @param mixed $data Parameter data.
  * @param mixed $files Parameter files.
  * @return mixed Return value.
  */
 public function validation($data,$files){$e=parent::validation($data,$files);if((int)$data['cost']<0)$e['cost']=get_string('invalidcost','local_rewardshop');$cfg=json_decode((string)$data['configjson'],true);if($data['configjson']!==''&&!is_array($cfg))$e['configjson']=get_string('invalidjson','local_rewardshop');if(is_array($cfg)){foreach(\local_rewardshop\reward_type_registry::get($data['rewardtype'])->validate_configuration($cfg,(object)$data) as $k=>$v)$e[$k==='configjson'?$k:'configjson']=$v;}return $e;}
}
