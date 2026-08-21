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

/**
 * Upgrade steps for the local_delegateaccount plugin.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrades the local_delegateaccount database schema.
 *
 * @param int $oldversion Installed plugin version.
 * @return bool True when the upgrade completes.
 */
function xmldb_local_delegateaccount_upgrade(int $oldversion): bool {
    global $DB;

    $dbman = $DB->get_manager();
    $table = new xmldb_table('local_delegateaccount');

    if ($oldversion < 2026082100) {
        $fields = [
            new xmldb_field('timestart', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'usercreated'),
            new xmldb_field('timeend', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timestart'),
            new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timeend'),
            new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timemodified'),
            new xmldb_field('timerevoked', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'usermodified'),
            new xmldb_field('userrevoked', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timerevoked'),
            new xmldb_field('activekey', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'userrevoked'),
            new xmldb_field('notificationmode', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, 'site', 'activekey'),
            new xmldb_field('timenotified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'notificationmode'),
        ];

        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        $DB->execute('UPDATE {local_delegateaccount} SET timestart = timecreated WHERE timestart = 0');
        $DB->execute('UPDATE {local_delegateaccount} SET timemodified = timecreated WHERE timemodified = 0');
        $DB->execute('UPDATE {local_delegateaccount} SET usermodified = usercreated WHERE usermodified = 0');

        $legacyindex = new xmldb_index('real_delegated_unique', XMLDB_INDEX_UNIQUE, ['realuserid', 'delegateduserid']);
        if ($dbman->index_exists($table, $legacyindex)) {
            $dbman->drop_index($table, $legacyindex);
        }

        $indexes = [
            new xmldb_index(
                'real_delegated_active_unique',
                XMLDB_INDEX_UNIQUE,
                ['realuserid', 'delegateduserid', 'activekey']
            ),
            new xmldb_index('realuser_active', XMLDB_INDEX_NOTUNIQUE, ['realuserid', 'activekey']),
            new xmldb_index('delegated_active', XMLDB_INDEX_NOTUNIQUE, ['delegateduserid', 'activekey']),
            new xmldb_index('lifecycle', XMLDB_INDEX_NOTUNIQUE, ['activekey', 'timestart', 'timeend']),
        ];

        foreach ($indexes as $index) {
            if (!$dbman->index_exists($table, $index)) {
                $dbman->add_index($table, $index);
            }
        }

        upgrade_plugin_savepoint(true, 2026082100, 'local', 'delegateaccount');
    }

    if ($oldversion < 2026082102) {
        upgrade_plugin_savepoint(true, 2026082102, 'local', 'delegateaccount');
    }

    return true;
}
