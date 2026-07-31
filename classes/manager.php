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
     * Creates delegations between multiple real users and multiple delegated accounts.
     *
     * @param array $realuserids Array of real user IDs.
     * @param array $delegateduserids Array of delegated account user IDs.
     * @return int Number of successfully created delegations.
     */
    public static function create_delegations(array $realuserids, array $delegateduserids): int {
        global $DB, $USER;

        if (empty($realuserids) || empty($delegateduserids)) {
            return 0;
        }

        list($realin, $realparams) = $DB->get_in_or_equal($realuserids, SQL_PARAMS_NAMED, 'real');
        list($delin, $delparams) = $DB->get_in_or_equal($delegateduserids, SQL_PARAMS_NAMED, 'del');
        $params = array_merge($realparams, $delparams);

        $sql = "SELECT id, " . $DB->sql_concat('realuserid', "'-'", 'delegateduserid') . " AS delegationkey
                  FROM {local_delegateaccount}
                 WHERE realuserid $realin AND delegateduserid $delin";

        $existing = $DB->get_records_sql_menu($sql, $params);
        $existingmap = array_flip($existing);

        $count = 0;
        $transaction = $DB->start_delegated_transaction();

        foreach ($realuserids as $realid) {
            foreach ($delegateduserids as $delid) {
                $key = "{$realid}-{$delid}";

                if ($realid == $delid || isset($existingmap[$key])) {
                    continue;
                }

                $record = new \stdClass();
                $record->realuserid = (int)$realid;
                $record->delegateduserid = (int)$delid;
                $record->timecreated = time();
                $record->usercreated = $USER->id;

                $DB->insert_record('local_delegateaccount', $record);

                $existingmap[$key] = true;
                $count++;
            }
        }

        $transaction->allow_commit();

        return $count;
    }

    /**
     * Checks if a specific delegation already exists.
     *
     * @param int $realuserid The real user ID.
     * @param int $delegateduserid The delegated user ID.
     * @return bool True if the delegation exists.
     */
    public static function delegation_exists(int $realuserid, int $delegateduserid): bool {
        global $DB;
        return $DB->record_exists('local_delegateaccount', [
            'realuserid' => $realuserid,
            'delegateduserid' => $delegateduserid
        ]);
    }

    /**
     * Deletes delegated account records by their primary keys.
     *
     * @param array $delegationids Array of primary key IDs from the local_delegateaccount table.
     */
    public static function delete_delegations(array $delegationids): void {
        global $DB;
        if (empty($delegationids)) {
            return;
        }
        list($inorsql, $params) = $DB->get_in_or_equal($delegationids);
        $DB->delete_records_select('local_delegateaccount', "id $inorsql", $params);
    }

    /**
     * Builds the SQL for filtering delegations based on user details.
     *
     * @param string $search The search query string.
     * @return array A list containing the SQL WHERE clause and its parameters.
     */
    private static function get_delegations_filters_sql(string $search): array {
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
     * Counts the total number of delegations (useful for pagination).
     *
     * @param string $search The search query string.
     * @return int The total count of delegation records.
     */
    public static function count_delegations(string $search = ''): int {
        global $DB;
        list($sqlwhere, $params) = self::get_delegations_filters_sql($search);

        $sql = "SELECT COUNT(da.id) 
                  FROM {local_delegateaccount} da
                  JOIN {user} u1 ON u1.id = da.realuserid
                  JOIN {user} u2 ON u2.id = da.delegateduserid
                 WHERE $sqlwhere";

        return $DB->count_records_sql($sql, $params);
    }

    /**
     * Retrieves delegations, optionally paginated and filtered.
     *
     * @param int $page The current page number (0-indexed). Defaults to 0.
     * @param int $perpage The number of records per page. 0 means all records.
     * @param string $search The search query string.
     * @return array Array of paginated delegation records including user details.
     */
    public static function get_delegations(int $page = 0, int $perpage = 0, string $search = ''): array {
        global $DB;
        list($sqlwhere, $params) = self::get_delegations_filters_sql($search);

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

        $limitfrom = $page * $perpage;
        return $DB->get_records_sql($sql, $params, $limitfrom, $perpage);
    }

    /**
     * Retrieves the target accounts a specific real user has been delegated to.
     *
     * @param int $realuserid The real user ID.
     * @return array List of target user accounts they can log into.
     */
    public static function get_delegated_accounts_for_user(int $realuserid): array {
        global $DB;

        $userfields = \core_user\fields::for_name()->get_sql('u', false, '', '', false)->selects;

        $sql = "SELECT da.id, da.delegateduserid, $userfields
                  FROM {local_delegateaccount} da
                  JOIN {user} u ON u.id = da.delegateduserid
                 WHERE da.realuserid = :realuserid
                   AND u.deleted = 0
                   AND u.suspended = 0";

        return $DB->get_records_sql($sql, ['realuserid' => $realuserid]);
    }
}
