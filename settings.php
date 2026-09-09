<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox(
        'block_ibapnav/debug',
        get_string('debug', 'block_ibapnav'),
        get_string('debug_desc', 'block_ibapnav'),
        0
    ));
}
