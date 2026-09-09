<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

/**
 * Output navigation before the standard page footer.
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

    $debug = (bool)get_config('block_ibapnav', 'debug');

    if (empty($COURSE) || (int)$COURSE->id <= SITEID) {
        return block_ibapnav_debug('no course', $debug);
    }

    $settings = $DB->get_record('block_ibapnav', ['course' => $COURSE->id]);
    if (!$settings) {
        return block_ibapnav_debug('block not enabled for course', $debug);
    }

    if (!(int)$settings->enabled) {
        return block_ibapnav_debug('disabled for course', $debug);
    }

    if (empty($PAGE->cm)) {
        return block_ibapnav_debug('no course module', $debug);
    }

    $nav = \block_ibapnav\local\navigation::resolve($COURSE, $PAGE->cm);
    if ($nav === null) {
        return block_ibapnav_debug('could not resolve navigation', $debug);
    }

    $rendered = true;

    $showcourse = property_exists($settings, 'showcourse') ? (bool)$settings->showcourse : true;
    $showactivityname = property_exists($settings, 'showactivityname') ? (bool)$settings->showactivityname : true;
    $showfinish = property_exists($settings, 'showfinish') ? (bool)$settings->showfinish : true;
    $hometext = !empty($settings->hometext)
        ? format_string($settings->hometext, true, ['context' => $PAGE->context])
        : get_string('coursehome', 'block_ibapnav');

    $items = [];
    $items[] = block_ibapnav_render_side_button(
        'previous',
        $nav->previous,
        get_string('previous', 'block_ibapnav'),
        '←',
        $showactivityname
    );

    if ($showcourse) {
        $items[] = html_writer::link(
            $nav->courseurl,
            html_writer::span('⌂', 'ibapnav__icon', ['aria-hidden' => 'true']) .
                html_writer::span($hometext, 'ibapnav__label'),
            [
                'class' => 'ibapnav__button ibapnav__button--home',
                'aria-label' => $hometext,
            ]
        );
    } else {
        $items[] = html_writer::span('', 'ibapnav__spacer', ['aria-hidden' => 'true']);
    }

    if ($nav->next !== null) {
        $items[] = block_ibapnav_render_side_button(
            'next',
            $nav->next,
            get_string('next', 'block_ibapnav'),
            '→',
            $showactivityname
        );
    } else if ($showfinish) {
        $items[] = html_writer::link(
            $nav->courseurl,
            html_writer::span(get_string('finish', 'block_ibapnav'), 'ibapnav__label') .
                html_writer::span('✓', 'ibapnav__icon', ['aria-hidden' => 'true']),
            [
                'class' => 'ibapnav__button ibapnav__button--finish',
                'aria-label' => get_string('finish', 'block_ibapnav'),
            ]
        );
    } else {
        $items[] = html_writer::span('', 'ibapnav__spacer', ['aria-hidden' => 'true']);
    }

    $html = html_writer::tag(
        'nav',
        implode('', $items),
        [
            'id' => 'ibapnav',
            'class' => 'ibapnav',
            'aria-label' => get_string('navigationaria', 'block_ibapnav'),
        ]
    );

    if ($debug) {
        return '<!-- IBAPNAV: start -->' . $html . '<!-- IBAPNAV: end -->';
    }

    return $html;
}

/**
 * Render a previous/next button or a placeholder.
 *
 * @param string $type previous|next
 * @param object|null $item Navigation item.
 * @param string $label Button label.
 * @param string $arrow Arrow glyph.
 * @param bool $showname Whether to show the activity name.
 * @return string
 */
function block_ibapnav_render_side_button(
    string $type,
    ?object $item,
    string $label,
    string $arrow,
    bool $showname
): string {
    if ($item === null) {
        return html_writer::span('', 'ibapnav__spacer', ['aria-hidden' => 'true']);
    }

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

/**
 * Emit debug comments only when debug mode is enabled.
 *
 * @param string $message
 * @param bool $enabled
 * @return string
 */
function block_ibapnav_debug(string $message, bool $enabled): string {
    return $enabled ? '<!-- IBAPNAV: ' . s($message) . ' -->' : '';
}
