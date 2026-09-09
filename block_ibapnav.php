<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

/**
 * IBAP navigation block.
 */
class block_ibapnav extends block_base {
    public function init(): void {
        $this->title = get_string('pluginname', 'block_ibapnav');
    }

    public function applicable_formats(): array {
        return [
            'course-view' => true,
            'mod' => true,
            'site' => false,
            'my' => false,
        ];
    }

    public function has_config(): bool {
        return true;
    }

    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';
        $this->content->text = '';

        if (has_capability('moodle/course:manageactivities', $this->context)) {
            $this->content->text = html_writer::div(
                get_string('blockenabledinfo', 'block_ibapnav'),
                'ibapnav-admin-status'
            );
        }

        return $this->content;
    }

    public function instance_create() {
        global $DB;

        $coursecontext = $this->context->get_course_context(false);
        if (!$coursecontext) {
            return;
        }

        $courseid = (int)$coursecontext->instanceid;
        $settings = $DB->get_record('block_ibapnav', ['course' => $courseid]);

        if (!$settings) {
            $record = (object)[
                'course' => $courseid,
                'enabled' => 1,
                'timecreated' => time(),
                'timemodified' => time(),
            ];
            $DB->insert_record('block_ibapnav', $record);
            return;
        }

        if (!(int)$settings->enabled) {
            $settings->enabled = 1;
            $settings->timemodified = time();
            $DB->update_record('block_ibapnav', $settings);
        }
    }

    public function instance_delete() {
        global $DB;

        $coursecontext = $this->context->get_course_context(false);
        if (!$coursecontext) {
            return;
        }

        $courseid = (int)$coursecontext->instanceid;
        $settings = $DB->get_record('block_ibapnav', ['course' => $courseid]);

        if ($settings && (int)$settings->enabled) {
            $settings->enabled = 0;
            $settings->timemodified = time();
            $DB->update_record('block_ibapnav', $settings);
        }
    }
}
