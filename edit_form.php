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
 * Per-course configuration form for the IBAP navigation block.
 *
 * @package    block_ibapnav
 * @copyright  2026 IBAP-RJ
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_ibapnav_edit_form extends block_edit_form {
    /**
     * Add plugin-specific configuration fields.
     *
     * @param MoodleQuickForm $mform Form instance.
     * @return void
     */
    protected function specific_definition($mform): void {
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block_ibapnav'));

        $mform->addElement('advcheckbox', 'config_showcourse', get_string('showcourse', 'block_ibapnav'));
        $mform->setDefault('config_showcourse', 1);

        $mform->addElement(
            'advcheckbox',
            'config_showactivityname',
            get_string('showactivityname', 'block_ibapnav')
        );
        $mform->setDefault('config_showactivityname', 1);

        $mform->addElement('advcheckbox', 'config_showfinish', get_string('showfinish', 'block_ibapnav'));
        $mform->setDefault('config_showfinish', 1);

        $mform->addElement(
            'text',
            'config_hometext',
            get_string('hometext', 'block_ibapnav'),
            ['maxlength' => 100, 'size' => 40]
        );
        $mform->setType('config_hometext', PARAM_TEXT);
        $mform->addHelpButton('config_hometext', 'hometext', 'block_ibapnav');
    }
}
