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

/**
 * Manager class for handling delegated accounts business logic.
 *
 * @package    local_delegateaccount
 * @author     Miguel Rivas Morantes <miguelrivasmorantes@gmail.com>
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {
    /** Active delegation status. */
    public const STATUS_ACTIVE = 'active';

    /** Scheduled delegation status. */
    public const STATUS_SCHEDULED = 'scheduled';

    /** Expired delegation status. */
    public const STATUS_EXPIRED = 'expired';

    /** Revoked delegation status. */
    public const STATUS_REVOKED = 'revoked';

    /** Use the site notification policy. */
    public const NOTIFICATION_SITE = 'site';

    /** Always notify the affected users. */
    public const NOTIFICATION_ALWAYS = 'always';

    /** Do not notify the affected users. */
    public const NOTIFICATION_NEVER = 'never';

    /** Allow the person creating a delegation to choose whether to notify. */
    public const NOTIFICATION_OPTIONAL = 'optional';

    /**
     * Creates delegations between multiple real users and multiple delegated accounts.
     *
     * @param array $realuserids Array of real user IDs.
     * @param array $delegateduserids Array of delegated account user IDs.
     * @param array $options Delegation period and notification options.
     * @return int Number of successfully created delegations.
     */
    public static function create_delegations(
        array $realuserids,
        array $delegateduserids,
        array $options = []
    ): int {
        global $DB, $USER;

        if (empty($realuserids) || empty($delegateduserids)) {
            return 0;
        }

        $now = time();
        $timestart = (int)($options['timestart'] ?? $now);
        $timeend = (int)($options['timeend'] ?? 0);
        $notificationmode = self::resolve_notification_mode(
            (string) ($options['notificationmode'] ?? self::NOTIFICATION_SITE)
        );
        self::validate_period($timestart, $timeend);
        self::validate_notification_mode($notificationmode);

        $realuserids = array_values(array_unique(array_map('intval', $realuserids)));
        $delegateduserids = array_values(array_unique(array_map('intval', $delegateduserids)));
        self::validate_users($realuserids, $delegateduserids);

        [$realin, $realparams] = $DB->get_in_or_equal($realuserids, SQL_PARAMS_NAMED, 'real');
        [$delin, $delparams] = $DB->get_in_or_equal($delegateduserids, SQL_PARAMS_NAMED, 'del');
        $params = array_merge($realparams, $delparams);

        $sql = "SELECT id, " . $DB->sql_concat('realuserid', "'-'", 'delegateduserid') . " AS delegationkey
                  FROM {local_delegateaccount}
                 WHERE realuserid $realin
                   AND delegateduserid $delin
                   AND activekey = 0";

        $existing = $DB->get_records_sql_menu($sql, $params);
        $existingmap = array_flip($existing);

        $candidates = [];
        $newcounts = [];
        foreach ($realuserids as $realid) {
            foreach ($delegateduserids as $delid) {
                $key = "{$realid}-{$delid}";

                if ($realid === $delid || isset($existingmap[$key])) {
                    continue;
                }

                $candidates[] = (object) [
                    'realuserid' => $realid,
                    'delegateduserid' => $delid,
                ];
                $newcounts[$realid] = ($newcounts[$realid] ?? 0) + 1;
            }
        }

        self::validate_bulk_operation_count(count($candidates));
        self::validate_delegation_limit($newcounts);

        $count = 0;
        $createddelegations = [];
        $transaction = $DB->start_delegated_transaction();

        foreach ($candidates as $candidate) {
            $record = new \stdClass();
            $record->realuserid = $candidate->realuserid;
            $record->delegateduserid = $candidate->delegateduserid;
            $record->timecreated = $now;
            $record->usercreated = (int) $USER->id;
            $record->timestart = $timestart;
            $record->timeend = $timeend;
            $record->timemodified = $now;
            $record->usermodified = (int) $USER->id;
            $record->timerevoked = 0;
            $record->userrevoked = 0;
            $record->activekey = 0;
            $record->notificationmode = $notificationmode;
            $record->timenotified = 0;

            $record->id = (int) $DB->insert_record('local_delegateaccount', $record);
            self::trigger_event('delegation_created', $record, (int) $USER->id);
            $createddelegations[] = $record;
            $count++;
        }

        $transaction->allow_commit();

        foreach ($createddelegations as $delegation) {
            notification_manager::notify(
                $delegation,
                notification_manager::ACTION_CREATED,
                (int) $USER->id
            );
        }

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
        $delegation = $DB->get_record('local_delegateaccount', [
            'realuserid' => $realuserid,
            'delegateduserid' => $delegateduserid,
            'activekey' => 0,
        ]);

        return $delegation !== false && self::get_delegation_status($delegation) === self::STATUS_ACTIVE;
    }

    /**
     * Revokes delegated account records by their primary keys.
     *
     * @param array $delegationids Array of primary key IDs from the local_delegateaccount table.
     */
    public static function delete_delegations(array $delegationids): void {
        self::revoke_delegations($delegationids);
    }

    /**
     * Revokes the selected active delegations while preserving their audit history.
     *
     * @param array $delegationids Array of primary key IDs from the local_delegateaccount table.
     */
    public static function revoke_delegations(array $delegationids): void {
        global $DB, $USER;

        if (empty($delegationids)) {
            return;
        }

        $delegationids = array_values(array_unique(array_map('intval', $delegationids)));
        self::validate_bulk_operation_count(count($delegationids));
        [$inorsql, $params] = $DB->get_in_or_equal($delegationids, SQL_PARAMS_NAMED, 'delegation');
        $records = $DB->get_records_select(
            'local_delegateaccount',
            "id $inorsql AND activekey = 0",
            $params
        );

        if (empty($records)) {
            return;
        }

        $now = time();
        $revokeddelegations = [];
        $transaction = $DB->start_delegated_transaction();
        foreach ($records as $record) {
            $record->timerevoked = $now;
            $record->userrevoked = (int)$USER->id;
            $record->timemodified = $now;
            $record->usermodified = (int)$USER->id;
            $record->activekey = (int)$record->id;
            $DB->update_record('local_delegateaccount', $record);
            self::trigger_event('delegation_revoked', $record, (int)$USER->id);
            $revokeddelegations[] = $record;
        }
        $transaction->allow_commit();

        foreach ($revokeddelegations as $delegation) {
            notification_manager::notify(
                $delegation,
                notification_manager::ACTION_REVOKED,
                (int) $USER->id
            );
        }
    }

    /**
     * Updates an active delegation period and notification decision.
     *
     * @param int $delegationid Delegation identifier.
     * @param int $timestart Unix timestamp when access starts.
     * @param int $timeend Unix timestamp when access ends, or zero for no end date.
     * @param string $notificationmode Site, always, or never.
     * @return bool Whether an active delegation was updated.
     */
    public static function update_delegation(
        int $delegationid,
        int $timestart,
        int $timeend,
        string $notificationmode
    ): bool {
        global $DB, $USER;

        self::validate_period($timestart, $timeend);
        $notificationmode = self::resolve_notification_mode($notificationmode);
        $record = $DB->get_record('local_delegateaccount', ['id' => $delegationid, 'activekey' => 0]);

        if ($record === false) {
            return false;
        }

        $record->timestart = $timestart;
        $record->timeend = $timeend;
        $record->notificationmode = $notificationmode;
        $record->timemodified = time();
        $record->usermodified = (int)$USER->id;
        $DB->update_record('local_delegateaccount', $record);
        self::trigger_event('delegation_updated', $record, (int)$USER->id);

        return true;
    }

    /**
     * Returns the derived lifecycle status of a delegation.
     *
     * @param \stdClass $delegation Delegation database record.
     * @param int|null $time Time used to evaluate the period, or the current time.
     * @return string One of the STATUS_* constants.
     */
    public static function get_delegation_status(\stdClass $delegation, ?int $time = null): string {
        $time = $time ?? time();

        if ((int)$delegation->timerevoked > 0 || (int)$delegation->activekey !== 0) {
            return self::STATUS_REVOKED;
        }
        if ((int)$delegation->timestart > $time) {
            return self::STATUS_SCHEDULED;
        }
        if ((int)$delegation->timeend > 0 && (int)$delegation->timeend <= $time) {
            return self::STATUS_EXPIRED;
        }

        return self::STATUS_ACTIVE;
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

            $sqlwhere .= ' AND (' . implode(' OR ', [
                $DB->sql_like('u1.firstname', '?', false),
                $DB->sql_like('u1.lastname', '?', false),
                $DB->sql_like('u1.email', '?', false),
                $DB->sql_like('u2.firstname', '?', false),
                $DB->sql_like('u2.lastname', '?', false),
                $DB->sql_like('u2.email', '?', false),
            ]) . ')';

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
        [$sqlwhere, $params] = self::get_delegations_filters_sql($search);

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
        [$sqlwhere, $params] = self::get_delegations_filters_sql($search);

        $userfields1 = \core_user\fields::for_name()->get_sql('u1', false, 'real', '', false)->selects;
        $userfields2 = \core_user\fields::for_name()->get_sql('u2', false, 'del', '', false)->selects;

        $sql = "SELECT da.id,
                       u1.email AS realemail,
                       u2.email AS delemail,
                       da.timecreated,
                       da.timestart,
                       da.timeend,
                       da.timerevoked,
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
                   AND da.activekey = 0
                   AND da.timestart <= :timestartnow
                   AND (da.timeend = 0 OR da.timeend > :timeendnow)
                   AND u.deleted = 0
                   AND u.suspended = 0";

        $now = time();
        return $DB->get_records_sql($sql, [
            'realuserid' => $realuserid,
            'timestartnow' => $now,
            'timeendnow' => $now,
        ]);
    }

    /**
     * Validates a delegation period.
     *
     * @param int $timestart Unix timestamp when access starts.
     * @param int $timeend Unix timestamp when access ends, or zero for no end date.
     */
    private static function validate_period(int $timestart, int $timeend): void {
        if ($timestart <= 0 || ($timeend > 0 && $timeend <= $timestart)) {
            throw new \coding_exception('A delegation end date must be later than its start date.');
        }

        if ($timeend === 0 && !self::get_config_bool('allowopenended', true)) {
            throw new \moodle_exception('error_openendednotallowed', 'local_delegateaccount');
        }

        $maximumdurationdays = self::get_config_int('maximumdurationdays', 0);
        if ($maximumdurationdays > 0 && $timeend > $timestart + ($maximumdurationdays * DAYSECS)) {
            throw new \moodle_exception(
                'error_maximumduration',
                'local_delegateaccount',
                '',
                $maximumdurationdays
            );
        }
    }

    /**
     * Validates a per-delegation notification mode.
     *
     * @param string $notificationmode Site, always, or never.
     */
    private static function validate_notification_mode(string $notificationmode): void {
        if (!in_array(
            $notificationmode,
            [
                self::NOTIFICATION_SITE,
                self::NOTIFICATION_ALWAYS,
                self::NOTIFICATION_NEVER,
            ],
            true
        )) {
            throw new \coding_exception('Invalid delegation notification mode.');
        }
    }

    /**
     * Resolves a requested notification decision against the site policy.
     *
     * @param string $notificationmode Requested notification mode.
     * @return string Effective notification mode.
     */
    private static function resolve_notification_mode(string $notificationmode): string {
        self::validate_notification_mode($notificationmode);

        $policy = get_config('local_delegateaccount', 'notificationpolicy');
        if ($policy === self::NOTIFICATION_ALWAYS) {
            return self::NOTIFICATION_ALWAYS;
        }
        if ($policy === self::NOTIFICATION_NEVER) {
            return self::NOTIFICATION_NEVER;
        }

        return $notificationmode;
    }

    /**
     * Validates that requested users can participate in a delegation.
     *
     * @param array $realuserids Authorised user identifiers.
     * @param array $delegateduserids Target user identifiers.
     */
    private static function validate_users(array $realuserids, array $delegateduserids): void {
        global $DB;

        $alluserids = array_values(array_unique(array_merge($realuserids, $delegateduserids)));
        if (empty($alluserids) || min($alluserids) <= 0) {
            throw new \moodle_exception('error_invaliduser', 'local_delegateaccount');
        }

        $users = $DB->get_records_list('user', 'id', $alluserids, '', 'id, deleted, suspended');
        if (count($users) !== count($alluserids)) {
            throw new \moodle_exception('error_invaliduser', 'local_delegateaccount');
        }

        foreach ($users as $user) {
            if ((int) $user->deleted !== 0 || (int) $user->suspended !== 0) {
                throw new \moodle_exception('error_ineligibleuser', 'local_delegateaccount');
            }
        }

        if (self::get_config_bool('protectprivilegedtargets', true)) {
            foreach ($delegateduserids as $delegateduserid) {
                if (is_siteadmin($delegateduserid)) {
                    throw new \moodle_exception('error_privilegedtarget', 'local_delegateaccount');
                }
            }
        }
    }

    /**
     * Enforces the configured limit of current or scheduled accounts per user.
     *
     * @param array $newcounts Number of candidate delegations indexed by authorised user ID.
     */
    private static function validate_delegation_limit(array $newcounts): void {
        global $DB;

        $maximum = self::get_config_int('maxdelegationsperuser', 10);
        if ($maximum === 0 || empty($newcounts)) {
            return;
        }

        [$inorsql, $params] = $DB->get_in_or_equal(array_keys($newcounts), SQL_PARAMS_NAMED, 'realuser');
        $params['timeendnow'] = time();
        $existingcounts = $DB->get_records_sql_menu(
            "SELECT realuserid, COUNT(1)
               FROM {local_delegateaccount}
              WHERE realuserid $inorsql
                AND activekey = 0
                AND (timeend = 0 OR timeend > :timeendnow)
           GROUP BY realuserid",
            $params
        );

        foreach ($newcounts as $realuserid => $newcount) {
            $existingcount = (int) ($existingcounts[$realuserid] ?? 0);
            if ($existingcount + $newcount > $maximum) {
                throw new \moodle_exception('error_maxdelegations', 'local_delegateaccount', '', $maximum);
            }
        }
    }

    /**
     * Enforces the configured maximum number of records in one action.
     *
     * @param int $count Number of delegation records affected by the action.
     */
    private static function validate_bulk_operation_count(int $count): void {
        $maximum = self::get_config_int('maxbulkoperations', 100);
        if ($maximum > 0 && $count > $maximum) {
            throw new \moodle_exception('error_maxbulkoperations', 'local_delegateaccount', '', $maximum);
        }
    }

    /**
     * Reads an integer plugin configuration value.
     *
     * @param string $name Configuration name.
     * @param int $default Default value when the setting is absent.
     * @return int Configured integer value.
     */
    private static function get_config_int(string $name, int $default): int {
        $value = get_config('local_delegateaccount', $name);
        return $value === false ? $default : (int) $value;
    }

    /**
     * Reads a boolean plugin configuration value.
     *
     * @param string $name Configuration name.
     * @param bool $default Default value when the setting is absent.
     * @return bool Configured boolean value.
     */
    private static function get_config_bool(string $name, bool $default): bool {
        $value = get_config('local_delegateaccount', $name);
        return $value === false ? $default : (bool) $value;
    }

    /**
     * Emits a Moodle event for a delegation state change.
     *
     * @param string $eventname Event class short name.
     * @param \stdClass $delegation Delegation database record.
     * @param int $actorid User who performed the action.
     */
    private static function trigger_event(string $eventname, \stdClass $delegation, int $actorid): void {
        $classname = '\\local_delegateaccount\\event\\' . $eventname;
        $event = $classname::create([
            'context' => \context_system::instance(),
            'objectid' => (int)$delegation->id,
            'relateduserid' => (int)$delegation->realuserid,
            'userid' => $actorid,
            'other' => [
                'delegateduserid' => (int)$delegation->delegateduserid,
                'timestart' => (int)$delegation->timestart,
                'timeend' => (int)$delegation->timeend,
                'notificationmode' => $delegation->notificationmode,
            ],
        ]);
        $event->trigger();
    }
}
