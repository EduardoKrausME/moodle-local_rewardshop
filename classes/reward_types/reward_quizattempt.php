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
 * classes/reward_types/reward_quizattempt.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop\reward_types;
/**
 * Class reward_quizattempt.
 */
class reward_quizattempt extends base {
 /**
  * Method get_name.
  *
  * @return string Return value.
  */
 public function get_name(): string { return get_string('type_quizattempt','local_rewardshop'); }
 /**
  * Method get_description.
  *
  * @return string Return value.
  */
 public function get_description(): string { return get_string('type_quizattempt_desc','local_rewardshop'); }
 /**
  * Method validate_configuration.
  *
  * @param array $config Parameter config.
  * @param \stdClass $reward Parameter reward.
  * @return array Return value.
  */
 public function validate_configuration(array $config, \stdClass $reward): array { return empty($config['cmid']) ? ['cmid'=>get_string('errorcmid','local_rewardshop')] : []; }
 /**
  * Method supports_automatic_delivery.
  *
  * @return bool Return value.
  */
 public function supports_automatic_delivery(): bool {
  // Cross-version rule: automatic delivery is enabled only when a supported override service is available.
  return class_exists('\\mod_quiz\\local\\override_manager') && method_exists('\\mod_quiz\\local\\override_manager','save_user_override');
 }
 /**
  * Method deliver.
  *
  * @param \stdClass $purchase Parameter purchase.
  * @param \stdClass $reward Parameter reward.
  * @param int $userid Parameter userid.
  * @return void Return value.
  */
 public function deliver(\stdClass $purchase, \stdClass $reward, int $userid): void {
  if (!$this->supports_automatic_delivery()) { throw new \moodle_exception('quizattemptmanual','local_rewardshop'); }
  $c=$this->config($reward); $cm=get_coursemodule_from_id('quiz',(int)$c['cmid'],$reward->courseid,false,MUST_EXIST);
  $manager='\\mod_quiz\\local\\override_manager';
  if (!method_exists($manager,'save_user_override')) { throw new \moodle_exception('quizattemptmanual','local_rewardshop'); }
  // Deliberately delegated to Moodle API; no direct writes to quiz_overrides.
  $manager::save_user_override((int)$cm->instance,$userid,['attemptsdelta'=>1,'reason'=>'local_rewardshop purchase #'.$purchase->id]);
 }
}
