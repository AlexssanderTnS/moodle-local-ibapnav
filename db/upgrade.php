<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade steps for block_ibapnav.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_block_ibapnav_upgrade(int $oldversion): bool {
    global $DB;

    $dbman = $DB->get_manager();
    $table = new xmldb_table('block_ibapnav');

    // Recover safely if a previous installation registered the plugin but the
    // plugin table was never created.
    if (!$dbman->table_exists($table)) {
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('showcourse', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('showactivityname', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('showfinish', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('hometext', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, '');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('course_fk', XMLDB_KEY_FOREIGN, ['course'], 'course', ['id']);
        $table->add_index('course_unique', XMLDB_INDEX_UNIQUE, ['course']);

        $dbman->create_table($table);
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

    if ($oldversion < 2026112601) {
        upgrade_block_savepoint(true, 2026112601, 'ibapnav');
    }

    if ($oldversion < 2026112602) {
        // Version 2.2.1 introduces the new output hook and repairs missing
        // plugin tables from interrupted/legacy installations.
        upgrade_block_savepoint(true, 2026112602, 'ibapnav');
    }

    return true;
}
