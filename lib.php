<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

/**
 * Decide whether the IBAP navigation should be displayed on the current page.
 *
 * The navigation is intended to move between Moodle activities, not to replace
 * the internal navigation of multi-step activities such as quiz attempts,
 * SCORM players or other module-specific screens.
 *
 * @return bool
 */
function block_ibapnav_should_display(): bool {
    global $PAGE;

    if (empty($PAGE->cm) || empty($PAGE->cm->modname)) {
        return false;
    }

    $modname = clean_param($PAGE->cm->modname, PARAM_COMPONENT);
    if ($modname === '') {
        return false;
    }

    $expectedpath = '/mod/' . $modname . '/view.php';

    if (!empty($PAGE->url)) {
        $pagepath = $PAGE->url->get_path();
        if ($pagepath !== $expectedpath) {
            return false;
        }
    } else {
        $scriptpath = parse_url($_SERVER['SCRIPT_NAME'] ?? '', PHP_URL_PATH);
        if ($scriptpath !== $expectedpath) {
            return false;
        }
    }

    if ($modname === 'lesson' && optional_param('pageid', 0, PARAM_INT) > 0) {
        return false;
    }

    return true;
}

/**
 * Render the IBAP navigation for the current activity/resource.
 *
 * @return string
 */
function block_ibapnav_render_navigation(): string {
    global $COURSE, $DB, $PAGE, $CFG;

    static $rendered = false;

    if ($rendered) {
        return '';
    }

    $debug = (bool)get_config('block_ibapnav', 'debug');

    // Hooks may run during installation or upgrade. Never query plugin tables
    // until the plugin is fully installed and its table is available.
    if (during_initial_install() || !empty($CFG->upgraderunning)) {
        return block_ibapnav_debug('installation or upgrade in progress', $debug);
    }

    if (empty(get_config('block_ibapnav', 'version'))) {
        return block_ibapnav_debug('plugin not fully installed', $debug);
    }

    $table = new xmldb_table('block_ibapnav');
    if (!$DB->get_manager()->table_exists($table)) {
        return block_ibapnav_debug('settings table not available', $debug);
    }

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

    if (!block_ibapnav_should_display()) {
        return block_ibapnav_debug('hidden on internal activity page', $debug);
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
