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

namespace local_delegateaccount\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider for the delegated accounts plugin.
 *
 * @package    local_delegateaccount
 * @author     Miguel Rivas Morantes <miguelrivasmorantes@gmail.com>
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Describes the personal data stored by the plugin.
     *
     * @param collection $collection Metadata collection.
     * @return collection Updated metadata collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('local_delegateaccount', [
            'realuserid' => 'privacy:metadata:local_delegateaccount:realuserid',
            'delegateduserid' => 'privacy:metadata:local_delegateaccount:delegateduserid',
            'usercreated' => 'privacy:metadata:local_delegateaccount:usercreated',
            'timecreated' => 'privacy:metadata:local_delegateaccount:timecreated',
            'timestart' => 'privacy:metadata:local_delegateaccount:timestart',
            'timeend' => 'privacy:metadata:local_delegateaccount:timeend',
            'timemodified' => 'privacy:metadata:local_delegateaccount:timemodified',
            'usermodified' => 'privacy:metadata:local_delegateaccount:usermodified',
            'timerevoked' => 'privacy:metadata:local_delegateaccount:timerevoked',
            'userrevoked' => 'privacy:metadata:local_delegateaccount:userrevoked',
            'notificationmode' => 'privacy:metadata:local_delegateaccount:notificationmode',
        ], 'privacy:metadata:local_delegateaccount');

        return $collection;
    }

    /**
     * Gets the user context containing a user's delegation data.
     *
     * @param int $userid User identifier.
     * @return contextlist Contexts containing the user's data.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $contextlist->add_from_sql(
            "SELECT ctx.id
               FROM {context} ctx
              WHERE ctx.contextlevel = :contextlevel
                AND ctx.instanceid = :userid
                AND EXISTS (
                    SELECT 1
                      FROM {local_delegateaccount} da
                     WHERE da.realuserid = :realuserid
                        OR da.delegateduserid = :delegateduserid
                        OR da.usercreated = :usercreated
                        OR da.usermodified = :usermodified
                        OR da.userrevoked = :userrevoked
                )",
            [
                'contextlevel' => CONTEXT_USER,
                'userid' => $userid,
                'realuserid' => $userid,
                'delegateduserid' => $userid,
                'usercreated' => $userid,
                'usermodified' => $userid,
                'userrevoked' => $userid,
            ]
        );

        return $contextlist;
    }

    /**
     * Adds every user with delegation data in a user context.
     *
     * @param userlist $userlist User list to populate.
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if (!$context instanceof \context_user) {
            return;
        }

        $userid = (int) $context->instanceid;
        $userlist->add_from_sql('userid', "
            SELECT realuserid AS userid
              FROM {local_delegateaccount}
             WHERE realuserid = :realuserid
            UNION
            SELECT delegateduserid AS userid
              FROM {local_delegateaccount}
             WHERE delegateduserid = :delegateduserid
            UNION
            SELECT usercreated AS userid
              FROM {local_delegateaccount}
             WHERE usercreated = :usercreated
            UNION
            SELECT usermodified AS userid
              FROM {local_delegateaccount}
             WHERE usermodified = :usermodified
            UNION
            SELECT userrevoked AS userid
              FROM {local_delegateaccount}
             WHERE userrevoked = :userrevoked
        ", [
            'realuserid' => $userid,
            'delegateduserid' => $userid,
            'usercreated' => $userid,
            'usermodified' => $userid,
            'userrevoked' => $userid,
        ]);
    }

    /**
     * Exports a user's delegation records.
     *
     * @param approved_contextlist $contextlist Approved contexts for the user.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $userid = (int) $contextlist->get_user()->id;
        $records = $DB->get_records_select(
            'local_delegateaccount',
            'realuserid = :realuserid
                OR delegateduserid = :delegateduserid
                OR usercreated = :usercreated
                OR usermodified = :usermodified
                OR userrevoked = :userrevoked',
            [
                'realuserid' => $userid,
                'delegateduserid' => $userid,
                'usercreated' => $userid,
                'usermodified' => $userid,
                'userrevoked' => $userid,
            ],
            'timecreated ASC'
        );

        if (empty($records)) {
            return;
        }

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_user || (int) $context->instanceid !== $userid) {
                continue;
            }

            $delegations = [];
            foreach ($records as $record) {
                $roles = [];
                if ((int) $record->realuserid === $userid) {
                    $roles[] = get_string('privacy:role:realuser', 'local_delegateaccount');
                }
                if ((int) $record->delegateduserid === $userid) {
                    $roles[] = get_string('privacy:role:delegateduser', 'local_delegateaccount');
                }
                if ((int) $record->usercreated === $userid) {
                    $roles[] = get_string('privacy:role:creator', 'local_delegateaccount');
                }
                if ((int) $record->usermodified === $userid) {
                    $roles[] = get_string('privacy:role:modifier', 'local_delegateaccount');
                }
                if ((int) $record->userrevoked === $userid) {
                    $roles[] = get_string('privacy:role:revoker', 'local_delegateaccount');
                }

                $delegations[] = (object) [
                    'roles' => implode(', ', $roles),
                    'timecreated' => transform::datetime((int) $record->timecreated),
                    'timestart' => transform::datetime((int) $record->timestart),
                    'timeend' => (int) $record->timeend > 0 ? transform::datetime((int) $record->timeend) : null,
                    'timemodified' => transform::datetime((int) $record->timemodified),
                    'timerevoked' => (int) $record->timerevoked > 0 ?
                        transform::datetime((int) $record->timerevoked) : null,
                    'notificationmode' => $record->notificationmode,
                ];
            }

            writer::with_context($context)->export_data(
                [get_string('privacy:path:delegations', 'local_delegateaccount')],
                (object) ['delegations' => $delegations]
            );
        }
    }

    /**
     * Deletes all delegation data in a user context.
     *
     * @param \context $context User context.
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        if (!$context instanceof \context_user) {
            return;
        }

        self::delete_user_data((int) $context->instanceid);
    }

    /**
     * Deletes delegation data for an approved user.
     *
     * @param approved_contextlist $contextlist Approved contexts for the user.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        $userid = (int) $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context instanceof \context_user && (int) $context->instanceid === $userid) {
                self::delete_user_data($userid);
                return;
            }
        }
    }

    /**
     * Deletes delegation data for approved users in a user context.
     *
     * @param approved_userlist $userlist Approved users in the context.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        $context = $userlist->get_context();
        if (!$context instanceof \context_user) {
            return;
        }

        foreach ($userlist->get_userids() as $userid) {
            if ((int) $userid === (int) $context->instanceid) {
                self::delete_user_data((int) $userid);
            }
        }
    }

    /**
     * Removes a user's access relationships and anonymises their creator link.
     *
     * @param int $userid User identifier.
     */
    private static function delete_user_data(int $userid): void {
        global $DB;

        $DB->delete_records_select(
            'local_delegateaccount',
            'realuserid = :realuserid OR delegateduserid = :delegateduserid',
            ['realuserid' => $userid, 'delegateduserid' => $userid]
        );
        $DB->set_field('local_delegateaccount', 'usercreated', 0, ['usercreated' => $userid]);
        $DB->set_field('local_delegateaccount', 'usermodified', 0, ['usermodified' => $userid]);
        $DB->set_field('local_delegateaccount', 'userrevoked', 0, ['userrevoked' => $userid]);
    }
}
