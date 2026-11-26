<?php
// This file is part of Moodle - http://moodle.org/

namespace block_ibapnav\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Hook callbacks for IBAP navigation.
 */
final class hook_callbacks {
    /**
     * Add the navigation pill before the standard footer.
     *
     * @param \core\hook\output\before_footer_html_generation $hook Footer hook.
     * @return void
     */
    public static function before_footer_html_generation(
        \core\hook\output\before_footer_html_generation $hook
    ): void {
        $hook->add_html(renderer::render());
    }
}
