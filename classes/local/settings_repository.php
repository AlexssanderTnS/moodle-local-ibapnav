<?php
// This file is part of Moodle - http://moodle.org/

namespace block_ibapnav\local;

defined('MOODLE_INTERNAL') || die();

use stdClass;

/**
 * Persistence helpers for per-course IBAP navigation settings.
 */
final class settings_repository {
    /**
     * Check whether the plugin settings table is available.
     *
     * @return bool
     */
    public static function table_exists(): bool {
        global $DB;

        return $DB->get_manager()->table_exists('block_ibapnav');
    }

    /**
     * Get the first settings record for a course.
     *
     * Limiting the query to one row keeps runtime safe even if an old/broken
     * installation contains duplicate records. Upgrade steps clean duplicates.
     *
     * @param int $courseid Course id.
     * @return stdClass|null
     */
    public static function get_for_course(int $courseid): ?stdClass {
        global $DB;

        if (!self::table_exists()) {
            return null;
        }

        $records = $DB->get_records(
            'block_ibapnav',
            ['course' => $courseid],
            'id ASC',
            '*',
            0,
            1
        );

        if (!$records) {
            return null;
        }

        return reset($records) ?: null;
    }

    /**
     * Enable navigation for a course, creating its settings record if needed.
     *
     * @param int $courseid Course id.
     * @return void
     */
    public static function enable_course(int $courseid): void {
        global $DB;

        if (!self::table_exists()) {
            return;
        }

        $settings = self::get_for_course($courseid);
        if ($settings) {
            $settings->enabled = 1;
            $settings->timemodified = time();
            $DB->update_record('block_ibapnav', $settings);
            return;
        }

        $now = time();
        $DB->insert_record('block_ibapnav', (object) [
            'course' => $courseid,
            'enabled' => 1,
            'showcourse' => 1,
            'showactivityname' => 1,
            'showfinish' => 1,
            'hometext' => '',
            'timecreated' => $now,
            'timemodified' => $now,
        ]);
    }

    /**
     * Update per-course navigation settings.
     *
     * @param int $courseid Course id.
     * @param array $values Setting values to update.
     * @return void
     */
    public static function update_course(int $courseid, array $values): void {
        global $DB;

        if (!self::table_exists()) {
            return;
        }

        $settings = self::get_for_course($courseid);
        if (!$settings) {
            self::enable_course($courseid);
            $settings = self::get_for_course($courseid);
        }

        if (!$settings) {
            return;
        }

        foreach ($values as $name => $value) {
            if (property_exists($settings, $name)) {
                $settings->{$name} = $value;
            }
        }

        $settings->timemodified = time();
        $DB->update_record('block_ibapnav', $settings);
    }

    /**
     * Disable navigation for a course.
     *
     * @param int $courseid Course id.
     * @return void
     */
    public static function disable_course(int $courseid): void {
        global $DB;

        if (!self::table_exists()) {
            return;
        }

        $settings = self::get_for_course($courseid);
        if (!$settings || !(int) $settings->enabled) {
            return;
        }

        $settings->enabled = 0;
        $settings->timemodified = time();
        $DB->update_record('block_ibapnav', $settings);
    }
}
