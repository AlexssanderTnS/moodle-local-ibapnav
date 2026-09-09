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

    if ($oldversion < 2026090904) {
        $table = new xmldb_table('block_ibapnav');

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

    return true;
}
