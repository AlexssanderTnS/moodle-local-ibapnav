<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

/**
 * Output navigation before the standard page footer.
 *
 * This mirrors the proven insertion mechanism used by the legacy navbuttons
 * block. If the block is enabled for the current course and a course module
 * exists, the plugin attempts to render navigation.
 *
 * @return string
 */
function block_ibapnav_before_footer(): string {
    return block_ibapnav_render_navigation();
}

/**
 * Render the IBAP navigation for the current activity/resource.
 *
 * @return string
 */
function block_ibapnav_render_navigation(): string {
    global $COURSE, $DB, $PAGE;

    static $rendered = false;

    if ($rendered) {
        return '';
    }

    if (empty($COURSE) || (int)$COURSE->id <= SITEID) {
        return '<!-- IBAPNAV: no course -->';
    }

    $settings = $DB->get_record('block_ibapnav', ['course' => $COURSE->id]);
    if (!$settings) {
        return '<!-- IBAPNAV: block not enabled for course -->';
    }

    if (!(int)$settings->enabled) {
        return '<!-- IBAPNAV: disabled for course -->';
    }

    if (empty($PAGE->cm)) {
        return '<!-- IBAPNAV: no course module -->';
    }

    $nav = \block_ibapnav\local\navigation::resolve($COURSE, $PAGE->cm);
    if ($nav === null) {
        return '<!-- IBAPNAV: could not resolve navigation -->';
    }

    $rendered = true;

    $items = [];
    $items[] = block_ibapnav_render_side_button(
        'previous',
        $nav->previous,
        get_string('previous', 'block_ibapnav'),
        '←'
    );

    $showcourseconfig = get_config('block_ibapnav', 'showcourse');
    $showcourse = $showcourseconfig === false ? true : (bool)$showcourseconfig;

    if ($showcourse) {
        $items[] = html_writer::link(
            $nav->courseurl,
            html_writer::span('⌂', 'ibapnav__icon', ['aria-hidden' => 'true']) .
                html_writer::span(get_string('coursehome', 'block_ibapnav'), 'ibapnav__label'),
            [
                'class' => 'ibapnav__button ibapnav__button--home',
                'aria-label' => get_string('coursehome', 'block_ibapnav'),
            ]
        );
    } else {
        $items[] = html_writer::span('', 'ibapnav__spacer', ['aria-hidden' => 'true']);
    }

    $items[] = block_ibapnav_render_side_button(
        'next',
        $nav->next,
        get_string('next', 'block_ibapnav'),
        '→'
    );

    return '<!-- IBAPNAV: start -->' .
        html_writer::tag(
            'nav',
            implode('', $items),
            [
                'id' => 'ibapnav',
                'class' => 'ibapnav',
                'aria-label' => get_string('navigationaria', 'block_ibapnav'),
            ]
        ) .
        '<!-- IBAPNAV: end -->';
}

/**
 * Render a previous/next button or a placeholder when no neighbour exists.
 *
 * @param string $type previous|next
 * @param object|null $item Navigation item.
 * @param string $label Button label.
 * @param string $arrow Arrow glyph.
 * @return string
 */
function block_ibapnav_render_side_button(string $type, ?object $item, string $label, string $arrow): string {
    if ($item === null) {
        return html_writer::span('', 'ibapnav__spacer', ['aria-hidden' => 'true']);
    }

    $showname = (bool)get_config('block_ibapnav', 'showactivityname');
    $content = '';

    if ($type === 'previous') {
        $content .= html_writer::span($arrow, 'ibapnav__icon', ['aria-hidden' => 'true']);
    }

    $text = html_writer::span($label, 'ibapnav__label');
    if ($showname) {
        $text .= html_writer::span($item->name, 'ibapnav__activity');
    }
    $content .= html_writer::span($text, 'ibapnav__text');

    if ($type === 'next') {
        $content .= html_writer::span($arrow, 'ibapnav__icon', ['aria-hidden' => 'true']);
    }

    return html_writer::link($item->url, $content, [
        'class' => 'ibapnav__button ibapnav__button--' . $type,
        'aria-label' => $showname ? $label . ': ' . $item->name : $label,
    ]);
}
