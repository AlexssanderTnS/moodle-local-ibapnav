<?php
// This file is part of Moodle - http://moodle.org/

namespace local_ibapnav\privacy;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider for local_ibapnav.
 */
final class provider implements \core_privacy\local\metadata\null_provider {
    /**
     * Explain that the plugin stores no personal data.
     *
     * @return string
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}
