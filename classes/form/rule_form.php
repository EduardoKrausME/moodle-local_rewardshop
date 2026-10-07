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
 * classes/form/rule_form.php for local_rewardshop.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewardshop\form;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

/**
 * Class rule_form.
 */
class rule_form extends \moodleform {
    /**
     * Method definition.
     *
     * @return mixed Return value.
     */
    public function definition() {
        $m = $this->_form;
        $m->addElement('hidden', 'courseid');
        $m->setType('courseid', PARAM_INT);
        $m->addElement('advcheckbox', 'enabled', get_string('enabled', 'local_rewardshop'));
        $m->addElement('text', 'xpstep', get_string('xpstep', 'local_rewardshop'));
        $m->setType('xpstep', PARAM_INT);
        $m->addRule('xpstep', null, 'required');
        $m->addElement('text', 'credits', get_string('creditsperstep', 'local_rewardshop'));
        $m->setType('credits', PARAM_INT);
        $m->addRule('credits', null, 'required');
        $this->add_action_buttons();
    }

    /**
     * Method validation.
     *
     * @param mixed $d Parameter d.
     * @param mixed $f Parameter f.
     * @return mixed Return value.
     */
    public function validation($d, $f) {
        $e = [];
        if ((int)$d['xpstep'] <= 0) $e['xpstep'] = get_string('mustbepositive', 'local_rewardshop');
        if ((int)$d['credits'] <= 0) $e['credits'] = get_string('mustbepositive', 'local_rewardshop');
        return $e;
    }
}
