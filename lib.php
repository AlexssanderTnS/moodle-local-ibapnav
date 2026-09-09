<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

defined('MOODLE_INTERNAL') || die();

/**
 * Primary insertion point: immediately after the standard main region.
 *
 * Moodle 4.4+ internally maps this legacy callback to the output hook API.
 * Keeping the callback here also gives broad compatibility with themes which
 * still call the standard renderer method directly.
 *
 * @return string
 */
function local_ibapnav_standard_after_main_region_html(): string {
    return local_ibapnav_render_navigation_once();
}

/**
 * Fallback insertion point: standard footer HTML.
 *
 * If a custom theme does not expose the after-main-region output point, this
 * callback provides a second chance to render the navigation. A static guard
 * prevents duplicate output when both callbacks are invoked.
 *
 * @return string
 */
function local_ibapnav_standard_footer_html(): string {
    return local_ibapnav_render_navigation_once();
}

/**
 * Render course navigation once per request.
 *
 * @return string
 */
function local_ibapnav_render_navigation_once(): string {
    global $PAGE;

    static $rendered = false;

    if ($rendered) {
        return '';
    }

    if (!local_ibapnav_should_render()) {
        return '';
    }

    $nav = \local_ibapnav\local\navigation::resolve($PAGE->cm);
    if ($nav === null) {
        return '';
    }

    $rendered = true;

    $showcourseconfig = get_config('local_ibapnav', 'showcourse');
    $showcourse = $showcourseconfig === false ? true : (bool)$showcourseconfig;

    $items = [];
    $items[] = local_ibapnav_render_side_button(
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

    $items[] = local_ibapnav_render_side_button(
        'next',
        $nav->next,
        get_string('next', 'local_ibapnav'),
        '→'
    );

    return html_writer::tag(
        'nav',
        implode('', $items),
        [
            'id' => 'local-ibapnav',
            'class' => 'local-ibapnav',
            'aria-label' => get_string('navigationaria', 'local_ibapnav'),
        ]
    );
}

/**
 * Check whether navigation belongs on the current request.
 *
 * Uses context and URL rather than a theme-specific DOM structure or an
 * overly strict page-type string. This allows normal activity/resource view
 * pages from standard and third-party modules to work.
 *
 * @return bool
 */
function local_ibapnav_should_render(): bool {
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

    if (empty($PAGE->context) || $PAGE->context->contextlevel !== CONTEXT_MODULE) {
        return false;
    }

    // Avoid editor/admin-like module pages while allowing module view URLs.
    $path = $PAGE->url ? $PAGE->url->get_path() : '';
    if ($path && preg_match('#/mod/[^/]+/view\.php$#', $path)) {
        return true;
    }

    // Fallback for third-party modules whose canonical view page has a
    // different filename but still uses a module context and a populated cm.
    $pagetype = (string)$PAGE->pagetype;
    return str_starts_with($pagetype, 'mod-') && !preg_match('/-(edit|report|grade|grading|attempt|review)$/', $pagetype);
}

/**
 * Render previous/next control or a layout placeholder.
 *
 * @param string $type previous|next
 * @param object|null $item Navigation item.
 * @param string $label Visible label.
 * @param string $arrow Arrow glyph.
 * @return string
 */
function local_ibapnav_render_side_button(string $type, ?object $item, string $label, string $arrow): string {
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
