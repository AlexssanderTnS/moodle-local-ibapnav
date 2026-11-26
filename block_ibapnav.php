<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

/**
 * IBAP navigation block.
 */
class block_ibapnav extends block_base {
    /**
     * Initialise the block.
     *
     * @return void
     */
    public function init(): void {
        $this->title = get_string('pluginname', 'block_ibapnav');
    }

    /**
     * Define where the block can be added.
     *
     * @return array
     */
    public function applicable_formats(): array {
        return [
            'course' => true,
            'course-category' => false,
            'site' => false,
            'my' => false,
        ];
    }

    /**
     * The block has global settings.
     *
     * @return bool
     */
    public function has_config(): bool {
        return true;
    }

    /**
     * Only one activation block is needed per course page.
     *
     * @return bool
     */
    public function instance_allow_multiple(): bool {
        return false;
    }

    /**
     * Allow course-specific settings even though multiple instances are off.
     *
     * @return bool
     */
    public function instance_allow_config(): bool {
        return true;
    }

    /**
     * Render the small administrator status block.
     *
     * Students do not need to see this block; the navigation pill itself is
     * injected independently through the output hook.
     *
     * @return stdClass|null
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
     * Enable navigation when the block is added to a course.
     *
     * @return bool
     */
    public function instance_create() {
        $coursecontext = $this->context->get_course_context(false);
        if (!$coursecontext) {
            return true;
        }

        \block_ibapnav\local\settings_repository::enable_course((int) $coursecontext->instanceid);
        return true;
    }

    /**
     * Save both normal block configdata and the course-level navigation copy.
     *
     * @param stdClass $data Submitted configuration.
     * @param bool $nolongerused Legacy parameter.
     * @return bool
     */
    public function instance_config_save($data, $nolongerused = false) {
        parent::instance_config_save($data, $nolongerused);

        $coursecontext = $this->context->get_course_context(false);
        if (!$coursecontext) {
            return true;
        }

        $value = static function ($data, string $name, $default = null) {
            if (property_exists($data, $name)) {
                return $data->{$name};
            }

            $configname = 'config_' . $name;
            if (property_exists($data, $configname)) {
                return $data->{$configname};
            }

            return $default;
        };

        \block_ibapnav\local\settings_repository::update_course(
            (int) $coursecontext->instanceid,
            [
                'showcourse' => empty($value($data, 'showcourse', 0)) ? 0 : 1,
                'showactivityname' => empty($value($data, 'showactivityname', 0)) ? 0 : 1,
                'showfinish' => empty($value($data, 'showfinish', 0)) ? 0 : 1,
                'hometext' => trim((string) $value($data, 'hometext', '')),
            ]
        );

        return true;
    }

    /**
     * Disable navigation when the activation block is removed.
     *
     * @return bool
     */
    public function instance_delete() {
        $coursecontext = $this->context->get_course_context(false);
        if (!$coursecontext) {
            return true;
        }

        \block_ibapnav\local\settings_repository::disable_course((int) $coursecontext->instanceid);
        return true;
    }
}
