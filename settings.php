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
 * Plugin settings.
 *
 * @package    local_rewardshop
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
if ($hassiteconfig) {
 $settings = new admin_settingpage('local_rewardshop', get_string('pluginname','local_rewardshop'));
 $ADMIN->add('localplugins',$settings);
 $settings->add(new admin_setting_configcheckbox('local_rewardshop/enabled', get_string('enabled','local_rewardshop'), get_string('enableddesc','local_rewardshop'), 1));
 $settings->add(new admin_setting_configtext('local_rewardshop/defaultxpstep', get_string('defaultxpstep','local_rewardshop'), '', 500, PARAM_INT));
 $settings->add(new admin_setting_configtext('local_rewardshop/defaultcredits', get_string('defaultcredits','local_rewardshop'), '', 50, PARAM_INT));
}
