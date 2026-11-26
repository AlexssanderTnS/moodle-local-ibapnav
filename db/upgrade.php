<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade steps for block_ibapnav.
 *
 * @param int $oldversion Installed plugin version.
 * @return bool
 */
function xmldb_block_ibapnav_upgrade(int $oldversion): bool {
    global $DB;

    $dbman = $DB->get_manager();
    $table = new xmldb_table('block_ibapnav');

    // Recover installations where plugin metadata was registered but the
    // custom table was never created (for example after an interrupted upgrade).
    if (!$dbman->table_exists($table)) {
        $dbman->install_one_table_from_xmldb_file(__DIR__ . '/install.xml', 'block_ibapnav');
    }

    if ($oldversion < 2026090904) {
        $fields = [
            new xmldb_field('showcourse', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'enabled'),
            new xmldb_field('showactivityname', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'showcourse'),
            new xmldb_field('showfinish', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'showactivityname'),
            new xmldb_field('hometext', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, '', 'showfinish'),
        ];

        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        upgrade_block_savepoint(true, 2026090904, 'ibapnav');
    }

    if ($oldversion < 2026112604) {
        // Remove accidental duplicate rows from earlier versions. Keep the
        // most recently modified row so the latest course settings survive.
        $duplicates = $DB->get_records_sql(
            'SELECT course, COUNT(1) AS recordcount
               FROM {block_ibapnav}
           GROUP BY course
             HAVING COUNT(1) > 1'
        );

        foreach ($duplicates as $duplicate) {
            $records = $DB->get_records(
                'block_ibapnav',
                ['course' => $duplicate->course],
                'timemodified DESC, id DESC',
                'id'
            );

            if (count($records) < 2) {
                continue;
            }

            $keepid = (int) array_key_first($records);
            $DB->delete_records_select(
                'block_ibapnav',
                'course = :course AND id <> :keepid',
                ['course' => $duplicate->course, 'keepid' => $keepid]
            );
        }

        upgrade_block_savepoint(true, 2026112604, 'ibapnav');
    }

    return true;
}
