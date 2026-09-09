<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox(
        'block_ibapnav/showcourse',
        get_string('showcourse', 'block_ibapnav'),
        get_string('showcourse_desc', 'block_ibapnav'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'block_ibapnav/showactivityname',
        get_string('showactivityname', 'block_ibapnav'),
        get_string('showactivityname_desc', 'block_ibapnav'),
        0
    ));
}
