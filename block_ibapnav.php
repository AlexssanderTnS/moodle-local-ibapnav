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
            'course' => true,
            'course-category' => false,
            'site' => false,
        ];
    }

    public function has_config(): bool {
        return true;
    }

    public function get_content() {
        if (!has_capability('moodle/course:manageactivities', $this->context)) {
            return null;
        }

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';
        $this->content->text = html_writer::div(
            get_string('blockenabledinfo', 'block_ibapnav'),
            'ibapnav-admin-status'
        );

        return $this->content;
    }

    /**
     * Adding the block explicitly enables navigation for that course.
     */
    public function instance_create() {
        global $DB;

        $coursecontext = $this->context->get_course_context(false);
        if (!$coursecontext) {
            return;
        }

        $courseid = (int)$coursecontext->instanceid;
        $settings = $DB->get_record('block_ibapnav', ['course' => $courseid]);

        if (!$settings) {
            $DB->insert_record('block_ibapnav', (object)[
                'course' => $courseid,
                'enabled' => 1,
                'showcourse' => 1,
                'showactivityname' => 1,
                'showfinish' => 1,
                'hometext' => '',
                'timecreated' => time(),
                'timemodified' => time(),
            ]);
            return;
        }

        $settings->enabled = 1;
        $settings->timemodified = time();
        $DB->update_record('block_ibapnav', $settings);
    }

    /**
     * Save the normal block configuration and mirror the useful UX options
     * into the per-course table used by the footer callback.
     */
    public function instance_config_save($data, $nolongerused = false) {
        global $DB;

        $result = parent::instance_config_save($data, $nolongerused);

        $coursecontext = $this->context->get_course_context(false);
        if (!$coursecontext) {
            return $result;
        }

        $courseid = (int)$coursecontext->instanceid;
        $settings = $DB->get_record('block_ibapnav', ['course' => $courseid]);
        if (!$settings) {
            return $result;
        }

        $settings->showcourse = empty($data->showcourse) ? 0 : 1;
        $settings->showactivityname = empty($data->showactivityname) ? 0 : 1;
        $settings->showfinish = empty($data->showfinish) ? 0 : 1;
        $settings->hometext = isset($data->hometext) ? trim((string)$data->hometext) : '';
        $settings->timemodified = time();
        $DB->update_record('block_ibapnav', $settings);

        return $result;
    }

    /**
     * Removing the block disables navigation but preserves the course record.
     */
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
