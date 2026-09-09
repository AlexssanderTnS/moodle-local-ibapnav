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

    /**
     * Keep the same broad course availability used by the legacy block.
     */
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

    /**
     * The visible block is only an administrator/teacher activation marker.
     * Students do not need to see a sidebar/card block; navigation is injected
     * at the bottom of activity pages by block_ibapnav_before_footer().
     */
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
                'timecreated' => time(),
                'timemodified' => time(),
            ]);
            return;
        }

        if (!(int)$settings->enabled) {
            $settings->enabled = 1;
            $settings->timemodified = time();
            $DB->update_record('block_ibapnav', $settings);
        }
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
