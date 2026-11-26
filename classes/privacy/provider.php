<?php
// This file is part of Moodle - http://moodle.org/

namespace block_ibapnav\privacy;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider for block_ibapnav.
 *
 * The plugin stores only course-level configuration and no personal data.
 */
final class provider implements \core_privacy\local\metadata\null_provider {
    /**
     * Explain why no personal data is stored.
     *
     * @return string Language string identifier.
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}
