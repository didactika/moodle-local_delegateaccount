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

namespace local_delegateaccount;

defined('MOODLE_INTERNAL') || die();

/**
 * Manager class for handling delegated accounts business logic.
 *
 * @package   local_delegateaccount
 * @copyright 2026, Miguel Rivas Morantes <miguelrivasmorantes@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {

    /**
     * Assigns multiple delegated accounts to multiple real users.
     *
     * @param array $realuserids Array of real user IDs.
     * @param array $delegateduserids Array of delegated account user IDs.
     * @return int Number of successfully created links.
     */
    public static function assign_accounts(array $realuserids, array $delegateduserids): int {
        global $DB, $USER;
        $count = 0;

        foreach ($realuserids as $realid) {
            foreach ($delegateduserids as $delid) {
                if ($realid == $delid || self::link_exists((int)$realid, (int)$delid)) {
                    continue;
                }

                $record = new \stdClass();
                $record->realuserid = (int)$realid;
                $record->delegateduserid = (int)$delid;
                $record->timecreated = time();
                $record->usercreated = $USER->id;

                $DB->insert_record('local_delegateaccount', $record);
                $count++;
            }
        }
        return $count;
    }

    /**
     * Checks if a specific link already exists.
     */
    public static function link_exists(int $realuserid, int $delegateduserid): bool {
        global $DB;
        return $DB->record_exists('local_delegateaccount', [
            'realuserid' => $realuserid,
            'delegateduserid' => $delegateduserid
        ]);
    }

    /**
     * Deletes a specific link by its ID.
     */
    public static function delete_link(int $id): void {
        global $DB;
        $DB->delete_records('local_delegateaccount', ['id' => $id]);
    }

    /**
     * Retrieves all assigned links formatted for display.
     */
    public static function get_all_links(): array {
        global $DB;
        $sql = "SELECT da.id,
                       u1.firstname AS realfirstname, u1.lastname AS reallastname, u1.email AS realemail,
                       u2.firstname AS delfirstname, u2.lastname AS dellastname, u2.email AS delemail,
                       da.timecreated
                  FROM {local_delegateaccount} da
                  JOIN {user} u1 ON u1.id = da.realuserid
                  JOIN {user} u2 ON u2.id = da.delegateduserid
              ORDER BY u1.lastname ASC, u2.lastname ASC";

        return $DB->get_records_sql($sql);
    }

    /**
     * Deletes multiple links at once (Bulk action).
     * @param array $ids Array of link IDs.
     */
    public static function bulk_delete(array $ids): void {
        global $DB;
        if (empty($ids)) {
            return;
        }
        list($inorsql, $params) = $DB->get_in_or_equal($ids);
        $DB->delete_records_select('local_delegateaccount', "id $inorsql", $params);
    }

    /**
     * Builds the SQL for filtering links.
     */
    private static function get_filters_sql(string $search): array {
        global $DB;

        $sqlwhere = "1=1";
        $params = [];

        if (!empty($search)) {
            $searchparam = '%' . $DB->sql_like_escape(\core_text::strtolower($search)) . '%';

            $sqlwhere .= " AND (
                " . $DB->sql_like('u1.firstname', '?', false) . " OR 
                " . $DB->sql_like('u1.lastname', '?', false) . " OR 
                " . $DB->sql_like('u1.email', '?', false) . " OR 
                " . $DB->sql_like('u2.firstname', '?', false) . " OR 
                " . $DB->sql_like('u2.lastname', '?', false) . " OR 
                " . $DB->sql_like('u2.email', '?', false) . "
            )";

            $params = array_fill(0, 6, $searchparam);
        }

        return [$sqlwhere, $params];
    }

    /**
     * Counts the total number of links (used for pagination).
     */
    public static function count_links(string $search = ''): int {
        global $DB;
        list($sqlwhere, $params) = self::get_filters_sql($search);

        $sql = "SELECT COUNT(da.id) 
                  FROM {local_delegateaccount} da
                  JOIN {user} u1 ON u1.id = da.realuserid
                  JOIN {user} u2 ON u2.id = da.delegateduserid
                 WHERE $sqlwhere";

        return $DB->count_records_sql($sql, $params);
    }

    /**
     * Retrieves links paginated and filtered.
     */
    public static function get_links_paginated(int $page, int $perpage, string $search = ''): array {
        global $DB;
        list($sqlwhere, $params) = self::get_filters_sql($search);

        $userfields1 = \core_user\fields::for_name()->get_sql('u1', false, 'real', '', false)->selects;
        $userfields2 = \core_user\fields::for_name()->get_sql('u2', false, 'del', '', false)->selects;

        $sql = "SELECT da.id,
                       u1.email AS realemail,
                       u2.email AS delemail,
                       da.timecreated,
                       $userfields1,
                       $userfields2
                  FROM {local_delegateaccount} da
                  JOIN {user} u1 ON u1.id = da.realuserid
                  JOIN {user} u2 ON u2.id = da.delegateduserid
                 WHERE $sqlwhere
              ORDER BY da.timecreated DESC";

        return $DB->get_records_sql($sql, $params, $page * $perpage, $perpage);
    }
}
