<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

$callbacks = [
    [
        'hook' => \core\hook\output\before_standard_footer_html_generation::class,
        'callback' => [\local_ibapnav\local\hook_callbacks::class, 'before_standard_footer_html_generation'],
        'priority' => 100,
    ],
];
