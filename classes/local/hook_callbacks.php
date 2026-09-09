<?php
// This file is part of Moodle - http://moodle.org/

namespace local_ibapnav\local;

defined('MOODLE_INTERNAL') || die();

use core\hook\output\after_standard_main_region_html_generation;
use core\hook\output\before_standard_head_html_generation;
use html_writer;
use moodle_url;

/**
 * Hook callbacks for the IBAP course navigation.
 */
final class hook_callbacks {
    /** @var bool Prevent duplicate rendering if a theme requests the main region more than once. */
    private static bool $rendered = false;

    /**
     * Load the plugin stylesheet explicitly in the document head.
     *
     * @param before_standard_head_html_generation $hook Hook instance.
     * @return void
     */
    public static function before_standard_head_html_generation(before_standard_head_html_generation $hook): void {
        if (!self::can_prepare()) {
            return;
        }

        $cssurl = new moodle_url('/local/ibapnav/styles.css', [
            'v' => (string)(get_config('local_ibapnav', 'version') ?: '1'),
        ]);

        $hook->add_html(html_writer::empty_tag('link', [
            'rel' => 'stylesheet',
            'href' => $cssurl,
        ]));
    }

    /**
     * Add navigation immediately after Moodle's standard main content region.
     *
     * @param after_standard_main_region_html_generation $hook Hook instance.
     * @return void
     */
    public static function after_standard_main_region_html_generation(after_standard_main_region_html_generation $hook): void {
        global $PAGE;

        if (self::$rendered || !self::can_render()) {
            return;
        }

        self::$rendered = true;

        $nav = navigation::resolve($PAGE->cm);
        if ($nav === null) {
            return;
        }

        $showcourseconfig = get_config('local_ibapnav', 'showcourse');
        $showcourse = $showcourseconfig === false ? true : (bool)$showcourseconfig;

        $items = [];
        $items[] = self::render_side_button(
            'previous',
            $nav->previous,
            get_string('previous', 'local_ibapnav'),
            '←'
        );

        if ($showcourse) {
            $items[] = html_writer::link(
                $nav->courseurl,
                html_writer::span('⌂', 'local-ibapnav__icon', ['aria-hidden' => 'true']) .
                    html_writer::span(get_string('coursehome', 'local_ibapnav'), 'local-ibapnav__label'),
                [
                    'class' => 'local-ibapnav__button local-ibapnav__button--home',
                    'aria-label' => get_string('coursehome', 'local_ibapnav'),
                ]
            );
        } else {
            $items[] = html_writer::span('', 'local-ibapnav__spacer', ['aria-hidden' => 'true']);
        }

        $items[] = self::render_side_button(
            'next',
            $nav->next,
            get_string('next', 'local_ibapnav'),
            '→'
        );

        $html = html_writer::tag(
            'nav',
            implode('', $items),
            [
                'id' => 'local-ibapnav',
                'class' => 'local-ibapnav',
                'aria-label' => get_string('navigationaria', 'local_ibapnav'),
            ]
        );

        $hook->add_html($html);
    }

    /**
     * Basic guard used while the page head is being generated.
     *
     * @return bool
     */
    private static function can_prepare(): bool {
        global $PAGE;

        if (during_initial_install()) {
            return false;
        }

        if ((defined('CLI_SCRIPT') && CLI_SCRIPT) ||
            (defined('AJAX_SCRIPT') && AJAX_SCRIPT) ||
            (defined('WS_SERVER') && WS_SERVER)) {
            return false;
        }

        if (empty($PAGE->cm) || empty($PAGE->course) || (int)$PAGE->course->id === SITEID) {
            return false;
        }

        return self::is_module_view_page();
    }

    /**
     * Determine whether navigation should be rendered on the current page.
     *
     * @return bool
     */
    private static function can_render(): bool {
        return self::can_prepare();
    }

    /**
     * Keep the plugin on activity/resource view pages, not attempts, edit forms, grading screens, etc.
     *
     * @return bool
     */
    private static function is_module_view_page(): bool {
        global $PAGE;

        return (bool)preg_match('/^mod-[a-z0-9_]+-view$/', (string)$PAGE->pagetype);
    }

    /**
     * Render previous/next control or an empty placeholder to preserve the three-column layout.
     *
     * @param string $type previous|next
     * @param object|null $item Link data.
     * @param string $label Visible label.
     * @param string $arrow Arrow glyph.
     * @return string
     */
    private static function render_side_button(string $type, ?object $item, string $label, string $arrow): string {
        if ($item === null) {
            return html_writer::span('', 'local-ibapnav__spacer', ['aria-hidden' => 'true']);
        }

        $showname = (bool)get_config('local_ibapnav', 'showactivityname');
        $content = '';

        if ($type === 'previous') {
            $content .= html_writer::span($arrow, 'local-ibapnav__icon', ['aria-hidden' => 'true']);
        }

        $text = html_writer::span($label, 'local-ibapnav__label');
        if ($showname) {
            $text .= html_writer::span($item->name, 'local-ibapnav__activity');
        }
        $content .= html_writer::span($text, 'local-ibapnav__text');

        if ($type === 'next') {
            $content .= html_writer::span($arrow, 'local-ibapnav__icon', ['aria-hidden' => 'true']);
        }

        $arialabel = $showname ? $label . ': ' . $item->name : $label;

        return html_writer::link($item->url, $content, [
            'class' => 'local-ibapnav__button local-ibapnav__button--' . $type,
            'aria-label' => $arialabel,
        ]);
    }
}
