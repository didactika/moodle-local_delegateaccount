<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_delegateaccount\local;

use local_delegateaccount\manager;

/**
 * The queries behind the lists of the plugin: one page of rows, the total, and the counts of each filter chip.
 *
 * Every list is filtered, sorted and paged in the database, so a page costs the same with ten rows or a hundred thousand.
 *
 * @package    local_delegateaccount
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class listing {
    /** Statuses a delegation can be filtered by. */
    public const STATUSES = [
        manager::STATUS_ACTIVE,
        manager::STATUS_SCHEDULED,
        manager::STATUS_EXPIRED,
        manager::STATUS_REVOKED,
    ];

    /** People who may use delegations now. */
    public const SCOPE_AUTHORISED = 'authorised';

    /** People who keep delegations but may not use them any more. */
    public const SCOPE_HISTORICAL = 'historical';

    /** Fields of a user that a row needs to show a name and a picture. */
    private const USER_FIELDS = ['id', 'firstname', 'lastname', 'middlename', 'alternatename', 'firstnamephonetic',
        'lastnamephonetic', 'email', 'picture', 'imagealt'];

    /**
     * One page of the people with delegations, as the management page lists them.
     *
     * @param string $scope self::SCOPE_AUTHORISED or self::SCOPE_HISTORICAL.
     * @param string $search Name, username, email or ID number fragment.
     * @param string $status A status of self::STATUSES, 'none' for people without delegations, or '' for everyone.
     * @param string $sort name, email, active or scheduled.
     * @param string $direction asc or desc.
     * @param int $page Zero-based page.
     * @param int $perpage Rows per page.
     * @return array{total: int, counts: int[], rows: \stdClass[]} Page, total and the count of each status.
     */
    public static function users(
        string $scope,
        string $search,
        string $status,
        string $sort,
        string $direction,
        int $page,
        int $perpage
    ): array {
        global $DB;

        $now = time();
        [$basewhere, $baseparams] = self::user_scope_sql($scope, $search);
        $counts = ['all' => $DB->count_records_sql("SELECT COUNT(1) FROM {user} u WHERE $basewhere", $baseparams)];
        foreach (array_merge(self::STATUSES, ['none']) as $chip) {
            [$statussql, $statusparams] = self::user_status_sql($chip, $now);
            $counts[$chip] = $DB->count_records_sql(
                "SELECT COUNT(1) FROM {user} u WHERE $basewhere AND $statussql",
                $baseparams + $statusparams
            );
        }

        [$statussql, $statusparams] = $status === '' ? ['1 = 1', []] : self::user_status_sql($status, $now);
        $where = "$basewhere AND $statussql";
        $params = $baseparams + $statusparams;
        $total = $DB->count_records_sql("SELECT COUNT(1) FROM {user} u WHERE $where", $params);

        $order = match ($sort) {
            'email' => 'u.email',
            'active' => 'activecount',
            'scheduled' => 'scheduledcount',
            default => 'u.lastname %1$s, u.firstname',
        };
        $direction = $direction === 'desc' ? 'DESC' : 'ASC';
        $order = sprintf($order, $direction) . " $direction, u.id ASC";
        $fields = 'u.' . implode(', u.', self::USER_FIELDS);
        $rows = $DB->get_records_sql(
            "SELECT $fields,
                    (SELECT COUNT(1) FROM {local_delegateaccount} da
                      WHERE da.realuserid = u.id AND da.activekey = 0 AND da.timestart <= :activefrom
                        AND (da.timeend = 0 OR da.timeend > :activeto)) AS activecount,
                    (SELECT COUNT(1) FROM {local_delegateaccount} da
                      WHERE da.realuserid = u.id AND da.activekey = 0 AND da.timestart > :scheduledfrom) AS scheduledcount
               FROM {user} u
              WHERE $where
           ORDER BY $order",
            $params + ['activefrom' => $now, 'activeto' => $now, 'scheduledfrom' => $now],
            $page * $perpage,
            $perpage
        );

        return ['total' => $total, 'counts' => $counts, 'rows' => array_values($rows)];
    }

    /**
     * One page of delegations, of one authorised user or of everyone.
     *
     * @param array $filters realuserid (0 for everyone), status ('' for any) and search.
     * @param string $sort account, user, start or end.
     * @param string $direction asc or desc.
     * @param int $page Zero-based page.
     * @param int $perpage Rows per page.
     * @return array{total: int, counts: int[], rows: \stdClass[]} Page, total and the count of each status.
     */
    public static function delegations(array $filters, string $sort, string $direction, int $page, int $perpage): array {
        global $DB;

        $now = time();
        $base = ['status' => ''] + $filters;
        [$from, $where, $params] = self::delegation_sql($base, $now);
        $counts = ['all' => $DB->count_records_sql("SELECT COUNT(1) FROM $from WHERE $where", $params)];
        foreach (self::STATUSES as $status) {
            [$from, $where, $params] = self::delegation_sql(['status' => $status] + $filters, $now);
            $counts[$status] = $DB->count_records_sql("SELECT COUNT(1) FROM $from WHERE $where", $params);
        }

        [$from, $where, $params] = self::delegation_sql($filters, $now);
        $total = $DB->count_records_sql("SELECT COUNT(1) FROM $from WHERE $where", $params);
        $direction = $direction === 'desc' ? 'DESC' : 'ASC';
        $order = match ($sort) {
            'user' => "u1.lastname $direction, u1.firstname $direction, u2.lastname, u2.firstname",
            'start' => "da.timestart $direction",
            'end' => "CASE WHEN da.timeend = 0 THEN 1 ELSE 0 END $direction, da.timeend $direction",
            default => "u2.lastname $direction, u2.firstname $direction, u1.lastname, u1.firstname",
        };
        $userfields = [];
        foreach (['u1' => 'real', 'u2' => 'delegated'] as $alias => $prefix) {
            foreach (self::USER_FIELDS as $field) {
                $userfields[] = "$alias.$field AS {$prefix}_$field";
            }
        }
        $rows = $DB->get_records_sql(
            "SELECT da.*, " . implode(', ', $userfields) . "
               FROM $from
              WHERE $where
           ORDER BY $order, da.id DESC",
            $params,
            $page * $perpage,
            $perpage
        );

        return ['total' => $total, 'counts' => $counts, 'rows' => array_values($rows)];
    }

    /**
     * Every delegation that matches the filters of a list, for an action on "all of them".
     *
     * @param array $filters realuserid (0 for everyone), status ('' for any) and search.
     * @return int[] Delegation identifiers.
     */
    public static function delegation_ids(array $filters): array {
        global $DB;

        [$from, $where, $params] = self::delegation_sql($filters, time());

        return array_map('intval', $DB->get_fieldset_sql("SELECT da.id FROM $from WHERE $where ORDER BY da.id", $params));
    }

    /**
     * One page of the accounts a person may open now.
     *
     * @param int $userid The authorised user.
     * @param string $search Name or email fragment.
     * @param int $page Zero-based page.
     * @param int $perpage Rows per page.
     * @return array{total: int, rows: \stdClass[]} Page and total.
     */
    public static function my_accounts(int $userid, string $search, int $page, int $perpage): array {
        global $DB;

        $now = time();
        $where = 'da.realuserid = :realuserid AND da.activekey = 0 AND da.timestart <= :startnow
                  AND (da.timeend = 0 OR da.timeend > :endnow) AND u.deleted = 0 AND u.suspended = 0';
        $params = ['realuserid' => $userid, 'startnow' => $now, 'endnow' => $now];
        if ($search !== '') {
            [$searchsql, $searchparams] = self::search_sql('u', $search, 'mine');
            $where .= " AND $searchsql";
            $params += $searchparams;
        }
        $from = '{local_delegateaccount} da JOIN {user} u ON u.id = da.delegateduserid';
        $total = $DB->count_records_sql("SELECT COUNT(1) FROM $from WHERE $where", $params);
        $fields = 'u.' . implode(', u.', array_diff(self::USER_FIELDS, ['id']));
        $rows = $DB->get_records_sql(
            "SELECT da.id, da.delegateduserid, da.timestart, da.timeend, $fields, u.id AS userid
               FROM $from
              WHERE $where
           ORDER BY u.lastname, u.firstname, u.id",
            $params,
            $page * $perpage,
            $perpage
        );
        // The rows come keyed by delegation; the id of each becomes the account's, as the user functions of Moodle expect.
        $records = [];
        foreach ($rows as $delegationid => $row) {
            $row->delegationid = (int)$delegationid;
            $row->id = (int)$row->userid;
            $records[] = $row;
        }

        return ['total' => $total, 'rows' => $records];
    }

    /**
     * Picks the fields of one of the two users of a delegation row.
     *
     * @param \stdClass $row Row of self::delegations().
     * @param string $prefix real or delegated.
     * @return \stdClass The user.
     */
    public static function user_from_row(\stdClass $row, string $prefix): \stdClass {
        $user = new \stdClass();
        foreach (self::USER_FIELDS as $field) {
            $user->{$field} = $row->{$prefix . '_' . $field};
        }

        return $user;
    }

    /**
     * The people of one scope of the management page.
     *
     * @param string $scope self::SCOPE_AUTHORISED or self::SCOPE_HISTORICAL.
     * @param string $search Identity fragment.
     * @return array SQL condition on the alias u and its parameters.
     */
    private static function user_scope_sql(string $scope, string $search): array {
        [$authorisedsql, $params] = manager::get_authorised_users_condition('u.id');
        if ($scope === self::SCOPE_HISTORICAL) {
            $where = "u.deleted = 0 AND EXISTS (SELECT 1 FROM {local_delegateaccount} hda WHERE hda.realuserid = u.id)
                      AND NOT (u.suspended = 0 AND $authorisedsql)";
        } else {
            $where = "u.deleted = 0 AND u.suspended = 0 AND $authorisedsql";
        }
        if ($search !== '') {
            [$searchsql, $searchparams] = self::search_sql('u', $search, 'user');
            $where .= " AND $searchsql";
            $params += $searchparams;
        }

        return [$where, $params];
    }

    /**
     * People with at least one delegation in a status, or with none.
     *
     * @param string $status A status of self::STATUSES, or 'none'.
     * @param int $now Current time.
     * @return array SQL condition on the alias u and its parameters.
     */
    private static function user_status_sql(string $status, int $now): array {
        if ($status === 'none') {
            return ['NOT EXISTS (SELECT 1 FROM {local_delegateaccount} sda WHERE sda.realuserid = u.id)', []];
        }
        [$statussql, $params] = self::status_sql('sda', $status, $now, 'user' . $status);

        return ["EXISTS (SELECT 1 FROM {local_delegateaccount} sda WHERE sda.realuserid = u.id AND $statussql)", $params];
    }

    /**
     * Delegations that match the filters of a list.
     *
     * @param array $filters realuserid, status and search.
     * @param int $now Current time.
     * @return array FROM clause, WHERE clause and parameters.
     */
    private static function delegation_sql(array $filters, int $now): array {
        $from = '{local_delegateaccount} da
                 JOIN {user} u1 ON u1.id = da.realuserid
                 JOIN {user} u2 ON u2.id = da.delegateduserid';
        $where = 'u1.deleted = 0 AND u2.deleted = 0';
        $params = [];
        if (!empty($filters['realuserid'])) {
            $where .= ' AND da.realuserid = :filterrealuserid';
            $params['filterrealuserid'] = (int)$filters['realuserid'];
        }
        if (!empty($filters['delegationids'])) {
            global $DB;
            [$insql, $inparams] = $DB->get_in_or_equal(array_map('intval', $filters['delegationids']), SQL_PARAMS_NAMED, 'only');
            $where .= " AND da.id $insql";
            $params += $inparams;
        }
        if (($filters['status'] ?? '') !== '') {
            [$statussql, $statusparams] = self::status_sql('da', $filters['status'], $now, 'list');
            $where .= " AND $statussql";
            $params += $statusparams;
        }
        $search = trim((string)($filters['search'] ?? ''));
        if ($search !== '') {
            // On the page of one person only the accounts are searched; on the list of everyone, both people.
            [$delegatedsql, $delegatedparams] = self::search_sql('u2', $search, 'delegated');
            if (empty($filters['realuserid'])) {
                [$realsql, $realparams] = self::search_sql('u1', $search, 'real');
                $where .= " AND ($delegatedsql OR $realsql)";
                $params += $delegatedparams + $realparams;
            } else {
                $where .= " AND $delegatedsql";
                $params += $delegatedparams;
            }
        }

        return [$from, $where, $params];
    }

    /**
     * Delegations in one status.
     *
     * @param string $alias Alias of the delegation table.
     * @param string $status A status of self::STATUSES.
     * @param int $now Current time.
     * @param string $prefix Prefix of the parameter names.
     * @return array SQL condition and its parameters.
     */
    private static function status_sql(string $alias, string $status, int $now, string $prefix): array {
        return match ($status) {
            manager::STATUS_ACTIVE => [
                "$alias.activekey = 0 AND $alias.timestart <= :{$prefix}start
                 AND ($alias.timeend = 0 OR $alias.timeend > :{$prefix}end)",
                ["{$prefix}start" => $now, "{$prefix}end" => $now],
            ],
            manager::STATUS_SCHEDULED => [
                "$alias.activekey = 0 AND $alias.timestart > :{$prefix}start",
                ["{$prefix}start" => $now],
            ],
            manager::STATUS_EXPIRED => [
                "$alias.activekey = 0 AND $alias.timeend > 0 AND $alias.timeend <= :{$prefix}end",
                ["{$prefix}end" => $now],
            ],
            manager::STATUS_REVOKED => ["($alias.activekey <> 0 OR $alias.timerevoked > 0)", []],
            default => throw new \invalid_parameter_exception('Unsupported delegation status.'),
        };
    }

    /**
     * Users whose name, username, email or ID number contains a text.
     *
     * @param string $alias Alias of the user table.
     * @param string $search Text to find.
     * @param string $prefix Prefix of the parameter names.
     * @return array SQL condition and its parameters.
     */
    private static function search_sql(string $alias, string $search, string $prefix): array {
        global $DB;

        $like = '%' . $DB->sql_like_escape($search) . '%';
        $conditions = [];
        $params = [];
        foreach (['fullname', 'username', 'email', 'idnumber'] as $field) {
            $column = $field === 'fullname' ? $DB->sql_fullname("$alias.firstname", "$alias.lastname") : "$alias.$field";
            $conditions[] = $DB->sql_like($column, ":{$prefix}{$field}", false, false);
            $params["{$prefix}{$field}"] = $like;
        }

        return ['(' . implode(' OR ', $conditions) . ')', $params];
    }
}
