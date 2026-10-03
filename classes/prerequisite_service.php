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
 * classes/prerequisite_service.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop;
class prerequisite_service {
 public static function check(\stdClass $reward, int $userid): array {
  $errors=[]; $cfg=json_decode((string)$reward->configjson,true); if(!is_array($cfg)) return $errors;
  if(isset($cfg['minxp']) && (int)$cfg['minxp']>0) {
   $xp=0; if(class_exists('\\local_personalxp\\service\\xp_manager')){$c='\\local_personalxp\\service\\xp_manager';$xp=(int)$c::get_total($userid,(int)$reward->courseid);} if($xp<(int)$cfg['minxp'])$errors[]=get_string('prereqminxp','local_rewardshop',(int)$cfg['minxp']);
  }
  if(!empty($cfg['requiredcmids']) && is_array($cfg['requiredcmids'])) {
   $completion=new \completion_info(get_course($reward->courseid));
   foreach($cfg['requiredcmids'] as $cmid){try{$cm=get_coursemodule_from_id('',(int)$cmid,$reward->courseid,false,MUST_EXIST);$data=$completion->get_data($cm,false,$userid);if(!in_array((int)$data->completionstate,[COMPLETION_COMPLETE,COMPLETION_COMPLETE_PASS],true)){$errors[]=get_string('prereqcompletion','local_rewardshop',$cm->name);}}catch(\Throwable $e){$errors[]=get_string('invalidtarget','local_rewardshop');}}
  }
  return $errors;
 }
}
