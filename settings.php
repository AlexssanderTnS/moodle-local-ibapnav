<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_ibapnav', get_string('pluginname', 'local_ibapnav'));

    $settings->add(new admin_setting_configcheckbox(
        'local_ibapnav/showcourse',
        get_string('showcourse', 'local_ibapnav'),
        get_string('showcourse_desc', 'local_ibapnav'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_ibapnav/showactivityname',
        get_string('showactivityname', 'local_ibapnav'),
        get_string('showactivityname_desc', 'local_ibapnav'),
        0
    ));

    $ADMIN->add('localplugins', $settings);
}
