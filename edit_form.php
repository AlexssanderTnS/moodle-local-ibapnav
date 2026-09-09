<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

/**
 * Per-course configuration form for the IBAP navigation block.
 */
class block_ibapnav_edit_form extends block_edit_form {
    protected function specific_definition($mform): void {
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block_ibapnav'));

        $mform->addElement('advcheckbox', 'config_showcourse', get_string('showcourse', 'block_ibapnav'));
        $mform->setDefault('config_showcourse', 1);

        $mform->addElement('advcheckbox', 'config_showactivityname', get_string('showactivityname', 'block_ibapnav'));
        $mform->setDefault('config_showactivityname', 1);

        $mform->addElement('advcheckbox', 'config_showfinish', get_string('showfinish', 'block_ibapnav'));
        $mform->setDefault('config_showfinish', 1);

        $mform->addElement('text', 'config_hometext', get_string('hometext', 'block_ibapnav'), ['maxlength' => 100, 'size' => 40]);
        $mform->setType('config_hometext', PARAM_TEXT);
        $mform->addHelpButton('config_hometext', 'hometext', 'block_ibapnav');
    }
}
