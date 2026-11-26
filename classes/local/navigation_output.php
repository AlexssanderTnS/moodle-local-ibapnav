<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace block_ibapnav\local;

use html_writer;

/**
 * Renders the IBAP previous/home/next navigation pill.
 *
 * @package    block_ibapnav
 * @copyright  2026 IBAP-RJ
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class navigation_output {
    /**
     * Render the navigation for the current activity/resource.
     *
     * @return string
     */
    public static function render(): string {
        global $CFG, $COURSE, $PAGE;

        static $rendered = false;

        if ($rendered) {
            return '';
        }

        $debug = (bool) get_config('block_ibapnav', 'debug');

        // Hooks are dispatched during installation and upgrades too. Do not
        // touch plugin persistence until Moodle has finished those operations.
        if (during_initial_install() || !empty($CFG->upgraderunning)) {
            return self::debug('installation or upgrade in progress', $debug);
        }

        if (empty(get_config('block_ibapnav', 'version'))) {
            return self::debug('plugin not fully installed', $debug);
        }

        if (!settings_repository::table_exists()) {
            return self::debug('settings table not available', $debug);
        }

        if (empty($COURSE) || (int) $COURSE->id <= SITEID) {
            return self::debug('no course', $debug);
        }

        $settings = settings_repository::get_for_course((int) $COURSE->id);
        if (!$settings) {
            return self::debug('block not enabled for course', $debug);
        }

        if (!(int) $settings->enabled) {
            return self::debug('disabled for course', $debug);
        }

        if (empty($PAGE->cm)) {
            return self::debug('no course module', $debug);
        }

        if (!self::should_display()) {
            return self::debug('hidden on internal activity page', $debug);
        }

        $nav = navigation::resolve($COURSE, $PAGE->cm);
        if ($nav === null) {
            return self::debug('could not resolve navigation', $debug);
        }

        $rendered = true;

        $showcourse = property_exists($settings, 'showcourse') ? (bool) $settings->showcourse : true;
        $showactivityname = property_exists($settings, 'showactivityname')
            ? (bool) $settings->showactivityname
            : true;
        $showfinish = property_exists($settings, 'showfinish') ? (bool) $settings->showfinish : true;
        $hometext = !empty($settings->hometext)
            ? format_string($settings->hometext, true, ['context' => $PAGE->context])
            : get_string('coursehome', 'block_ibapnav');

        $items = [];
        $items[] = self::render_side_button(
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
            $items[] = self::render_side_button(
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
     * Decide whether the pill should be displayed on the current page.
     *
     * It is shown only on a module's canonical view.php page. Internal quiz,
     * SCORM, feedback and similar scripts therefore keep their native controls
     * without a second navigation competing with them.
     *
     * @return bool
     */
    private static function should_display(): bool {
        global $PAGE;

        if (empty($PAGE->cm) || empty($PAGE->cm->modname)) {
            return false;
        }

        $modname = clean_param($PAGE->cm->modname, PARAM_COMPONENT);
        if ($modname === '') {
            return false;
        }

        $path = $PAGE->url->get_path();

        // Quiz review is a legitimate end-of-activity screen after the attempt
        // has been submitted. Allow it only for a finished attempt. attempt.php
        // and summary.php remain excluded.
        if ($modname === 'quiz' && self::is_module_script($path, 'quiz', 'review.php')) {
            return self::quiz_review_is_finished();
        }

        if (!self::is_canonical_view_path($path, $modname)) {
            return false;
        }

        // Lesson uses view.php for its own page-by-page navigation. Hide the
        // IBAP pill while a lesson page is being displayed.
        if ($modname === 'lesson' && optional_param('pageid', 0, PARAM_INT) > 0) {
            return false;
        }

        // Assignment uses view.php for editing/submission/grading actions too.
        // Those screens are workflows, not the activity landing/end page.
        if ($modname === 'assign' && optional_param('action', '', PARAM_ALPHANUMEXT) !== '') {
            return false;
        }

        // Quiz uses separate scripts during attempts, but its landing page is
        // also used before the first attempt. For learners, only expose the
        // course-level navigation after at least one attempt has finished.
        if ($modname === 'quiz' && !self::quiz_has_finished_attempt()) {
            return false;
        }

        return true;
    }

    /**
     * Check whether the current quiz review belongs to a finished attempt.
     *
     * @return bool
     */
    private static function quiz_review_is_finished(): bool {
        global $DB, $PAGE, $USER;

        $attemptid = optional_param('attempt', 0, PARAM_INT);
        if ($attemptid <= 0) {
            return false;
        }

        $conditions = [
            'id' => $attemptid,
            'quiz' => $PAGE->cm->instance,
            'state' => 'finished',
        ];

        if (!has_capability('moodle/course:manageactivities', $PAGE->context)) {
            $conditions['userid'] = $USER->id;
        }

        return $DB->record_exists('quiz_attempts', $conditions);
    }

    /**
     * Check whether the current learner has completed at least one quiz attempt.
     *
     * Teachers and managers retain the navigation for authoring/testing.
     *
     * @return bool
     */
    private static function quiz_has_finished_attempt(): bool {
        global $DB, $PAGE, $USER;

        if (has_capability('moodle/course:manageactivities', $PAGE->context)) {
            return true;
        }

        return $DB->record_exists('quiz_attempts', [
            'quiz' => $PAGE->cm->instance,
            'userid' => $USER->id,
            'state' => 'finished',
        ]);
    }

    /**
     * Check whether a URL path ends in a specific module script.
     *
     * Comparing path segments instead of an absolute path keeps this working
     * when Moodle is installed in a subdirectory.
     *
     * @param string $path URL path.
     * @param string $modname Module component name without the mod_ prefix.
     * @param string $script Script filename.
     * @return bool
     */
    private static function is_module_script(string $path, string $modname, string $script): bool {
        $segments = array_values(array_filter(explode('/', trim($path, '/')), 'strlen'));
        $count = count($segments);

        if ($count < 3) {
            return false;
        }

        return $segments[$count - 3] === 'mod'
            && $segments[$count - 2] === $modname
            && $segments[$count - 1] === $script;
    }

    /**
     * Check whether a URL path is the module's canonical view.php page.
     *
     * @param string $path URL path.
     * @param string $modname Module component name without the mod_ prefix.
     * @return bool
     */
    private static function is_canonical_view_path(string $path, string $modname): bool {
        return self::is_module_script($path, $modname, 'view.php');
    }

    /**
     * Render a previous/next button or a placeholder.
     *
     * @param string $type previous|next.
     * @param object|null $item Navigation item.
     * @param string $label Button label.
     * @param string $arrow Arrow glyph.
     * @param bool $showname Whether to show the activity name.
     * @return string
     */
    private static function render_side_button(
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
     * Emit diagnostic HTML comments only when debug mode is enabled.
     *
     * @param string $message Diagnostic message.
     * @param bool $enabled Whether diagnostics are enabled.
     * @return string
     */
    private static function debug(string $message, bool $enabled): string {
        return $enabled ? '<!-- IBAPNAV: ' . s($message) . ' -->' : '';
    }
}
