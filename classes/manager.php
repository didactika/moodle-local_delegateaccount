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
     * Returns active users who currently have permission to use delegated accounts.
     *
     * @param string $search Optional search query.
     * @param int $limit Maximum number of users to return (0 means no limit).
     * @return array<int, string> User IDs mapped to display names.
     */
    public static function get_authorised_users(string $search = '', int $limit = 0): array {
        global $DB;
        $context = \context_system::instance();

        $fields = 'u.id, u.firstname, u.lastname, u.middlename, u.alternatename, u.firstnamephonetic, '
                . 'u.lastnamephonetic, u.deleted, u.suspended';

        $authorisedusers = [];

        if ($search !== '') {
            $searchvalue = '%' . $DB->sql_like_escape($search) . '%';
            $sql = "SELECT $fields
                      FROM {user} u
                     WHERE u.deleted = 0 AND u.suspended = 0
                       AND (" . $DB->sql_like('u.firstname', ':search1', false) . " OR " .
                                $DB->sql_like('u.lastname', ':search2', false) . " OR " .
                                $DB->sql_like('u.username', ':search3', false) . ")
                  ORDER BY u.lastname ASC, u.firstname ASC";

            $params = ['search1' => $searchvalue, 'search2' => $searchvalue, 'search3' => $searchvalue];
            $users = $DB->get_records_sql($sql, $params, 0, 500);

            $admins = get_admins();
            $adminids = [];
            foreach ($admins as $admin) {
                if ((int)$admin->suspended === 0) {
                    $adminids[(int)$admin->id] = true;
                }
            }

            foreach ($users as $user) {
                if (isset($adminids[(int)$user->id]) || has_capability('local/delegateaccount:use', $context, $user->id)) {
                    $authorisedusers[(int)$user->id] = fullname($user);
                }
                if ($limit > 0 && count($authorisedusers) >= $limit) {
                    break;
                }
            }

            return $authorisedusers;
        }

        $limitnum = $limit > 0 ? $limit : '';
        $users = \get_users_by_capability(
            $context,
            'local/delegateaccount:use',
            $fields,
            'u.lastname ASC, u.firstname ASC',
            '',
            $limitnum
        );

        foreach ($users as $user) {
            if ((int)$user->deleted === 0 && (int)$user->suspended === 0) {
                $authorisedusers[(int)$user->id] = fullname($user);
            }
        }

        foreach (get_admins() as $administrator) {
            if ((int)$administrator->suspended === 0) {
                $authorisedusers[(int)$administrator->id] = fullname($administrator);
            }
        }

        asort($authorisedusers, SORT_NATURAL | SORT_FLAG_CASE);

        if ($limit > 0) {
            $authorisedusers = array_slice($authorisedusers, 0, $limit, true);
        }

        return $authorisedusers;
    }
    /**
     * Returns setup guidance when no role grants the use capability yet.
     *
     * Without such a role only site administrators can be chosen as authorised users.
     * The guidance is only returned to users who can define roles.
     *
     * @return string|null Guidance text with links to the role pages, or null when none is needed.
     */
    public static function get_role_setup_hint(): ?string {
        $context = \context_system::instance();
        if (!has_capability('moodle/role:manage', $context)) {
            return null;
        }
        if (\get_users_by_capability($context, 'local/delegateaccount:use', 'u.id', '', 0, 1)) {
            return null;
        }

        return get_string('setup_role_hint', 'local_delegateaccount', (object)[
            'defineroles' => (new \moodle_url('/admin/roles/manage.php'))->out(),
            'assignroles' => (new \moodle_url('/admin/roles/assign.php', ['contextid' => $context->id]))->out(),
        ]);
    }

    /**
     * Returns active accounts that can be offered as delegation targets.
     *
     * Excludes the guest account, protected site administrators and, for one authorised
     * user, that user and the accounts already delegated to them.
     *
     * @param int $realuserid Authorised user the targets are for, or zero.
     * @param string $search Optional name or email fragment.
     * @param int $limit Maximum number of options, or zero for no limit.
     * @return array<int, string> Full names indexed by user ID.
     */
    public static function get_delegated_account_options(int $realuserid = 0, string $search = '', int $limit = 0): array {
        global $DB;

        $wheresql = 'deleted = 0 AND suspended = 0';
        $params = [];
        if ($search !== '') {
            $searchparam = '%' . $DB->sql_like_escape($search) . '%';
            $wheresql .= ' AND (' . implode(' OR ', [
                $DB->sql_like('firstname', ':search1', false, false),
                $DB->sql_like('lastname', ':search2', false, false),
                $DB->sql_like('email', ':search3', false, false),
            ]) . ')';
            $params['search1'] = $searchparam;
            $params['search2'] = $searchparam;
            $params['search3'] = $searchparam;
        }

        // Read a larger page than requested, so that the options left after exclusions still fill the limit.
        $users = $DB->get_records_select(
            'user',
            $wheresql,
            $params,
            'lastname ASC, firstname ASC',
            'id, firstname, lastname, middlename, alternatename, firstnamephonetic, lastnamephonetic',
            0,
            $limit > 0 ? max($limit, 300) : 0
        );

        $excludeduserids = [];
        if ($realuserid > 0) {
            $excludeduserids = array_fill_keys($DB->get_fieldset_select(
                'local_delegateaccount',
                'delegateduserid',
                'realuserid = :realuserid AND activekey = 0',
                ['realuserid' => $realuserid]
            ), true);
            $excludeduserids[$realuserid] = true;
        }

        $options = [];
        $protectprivilegedtargets = self::protect_privileged_targets();
        foreach ($users as $user) {
            $userid = (int)$user->id;
            if (isset($excludeduserids[$userid]) || isguestuser($user)) {
                continue;
            }
            if ($protectprivilegedtargets && is_siteadmin($userid)) {
                continue;
            }
            $options[$userid] = fullname($user);
            if ($limit > 0 && count($options) >= $limit) {
                break;
            }
        }

        return $options;
    }

    /**
     * Returns an SQL condition matching users who hold the use capability or are site administrators.
     *
     * The capability part is Moodle's own get_with_capability_sql() subquery, so the condition
     * stays a single bounded query however many users hold the capability.
     *
     * @param string $useridcolumn Column holding the user identifier, for example 'u.id'.
     * @return array The SQL condition and its named parameters.
     */
    public static function get_authorised_users_condition(string $useridcolumn): array {
        global $CFG, $DB;

        [$capabilitysql, $params] = get_with_capability_sql(\context_system::instance(), 'local/delegateaccount:use');
        $condition = "$useridcolumn IN ($capabilitysql)";

        $adminids = array_filter(array_map('intval', explode(',', (string)$CFG->siteadmins)));
        if ($adminids) {
            [$adminsql, $adminparams] = $DB->get_in_or_equal($adminids, SQL_PARAMS_NAMED, 'siteadmin');
            $condition = "($condition OR $useridcolumn $adminsql)";
            $params += $adminparams;
        }

        return [$condition, $params];
    }

    /**
     * Determines whether a user can currently use an account delegation.
     *
     * @param int $userid User identifier.
     * @return bool Whether the user is active and has the use capability.
     */
    public static function can_use_delegated_accounts(int $userid): bool {
        global $DB;

        $user = $DB->get_record('user', ['id' => $userid], 'id, deleted, suspended');
        if (!$user || (int)$user->deleted !== 0 || (int)$user->suspended !== 0) {
            return false;
        }

        return is_siteadmin($userid) || has_capability(
            'local/delegateaccount:use',
            \context_system::instance(),
            $userid
        );
    }

    /**
     * Returns why an authorised user cannot currently open a delegated account.
     *
     * Every condition is evaluated when access is requested, so a target that
     * became a site administrator, or was suspended, after the delegation was
     * created cannot be opened.
     *
     * @param int $realuserid Authorised user identifier.
     * @param int $targetuserid Target account identifier.
     * @return string|null Language string identifier describing the problem, or null when access is allowed.
     */
    public static function get_delegated_access_error(int $realuserid, int $targetuserid): ?string {
        global $DB;

        if (!self::can_use_delegated_accounts($realuserid) || !self::delegation_exists($realuserid, $targetuserid)) {
            return 'error_unauthorised';
        }

        $target = $DB->get_record('user', ['id' => $targetuserid], 'id, deleted, suspended');
        if (!$target || (int)$target->deleted !== 0 || (int)$target->suspended !== 0 || isguestuser($target)) {
            return 'error_targetunavailable';
        }

        if (self::protect_privileged_targets() && is_siteadmin($targetuserid)) {
            return 'error_privilegedtarget';
        }

        return null;
    }

    /**
     * Starts a delegated session for the current user and marks it for later verification.
     *
     * @param int $targetuserid Target account identifier, already checked with get_delegated_access_error().
     */
    public static function start_delegated_session(int $targetuserid): void {
        global $SESSION;

        \core\session\manager::loginas($targetuserid, \context_system::instance());
        // The login-as session has a fresh $SESSION, so the marker only exists inside the delegated session.
        $SESSION->local_delegateaccount_delegated = true;
    }

    /**
     * Logs out a delegated session whose delegation is no longer usable.
     *
     * @return bool Whether the current session was ended.
     */
    public static function end_invalid_delegated_session(): bool {
        global $SESSION, $USER;

        if (empty($SESSION->local_delegateaccount_delegated)) {
            return false;
        }
        if (!\core\session\manager::is_loggedinas()) {
            unset($SESSION->local_delegateaccount_delegated);
            return false;
        }

        $realuserid = (int)\core\session\manager::get_realuser()->id;
        if (self::get_delegated_access_error($realuserid, (int)$USER->id) === null) {
            return false;
        }

        require_logout();
        return true;
    }

    /**
     * Determines whether site administrator accounts are protected as delegation targets.
     *
     * @return bool Whether privileged target protection is enabled.
     */
    public static function protect_privileged_targets(): bool {
        return self::get_config_bool('protectprivilegedtargets', true);
    }

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

        $existingmap = self::get_current_delegation_ids($realuserids, $delegateduserids);

        $candidates = [];
        $newcounts = [];
        foreach ($realuserids as $realid) {
            foreach ($delegateduserids as $delid) {
                if ($realid === $delid || isset($existingmap[$realid . ':' . $delid])) {
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
     * Revokes the selected active delegations while preserving their audit history.
     *
     * @param array $delegationids Array of primary key IDs from the local_delegateaccount table.
     * @return int Number of delegations revoked.
     */
    public static function revoke_delegations(array $delegationids): int {
        global $DB, $USER;

        if (empty($delegationids)) {
            return 0;
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
            return 0;
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

        return count($revokeddelegations);
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
        self::validate_update([$record], $timeend);

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
     * Applies one lifecycle configuration to several active delegations atomically.
     *
     * @param int[] $delegationids Delegation identifiers selected by an administrator.
     * @param int $realuserid Authorised user that must own every selected delegation.
     * @param int $timestart Unix timestamp when access starts.
     * @param int $timeend Unix timestamp when access ends, or zero for no end date.
     * @param string $notificationmode Site, always, or never.
     * @return int Number of updated delegations.
     */
    public static function update_delegations(
        array $delegationids,
        int $realuserid,
        int $timestart,
        int $timeend,
        string $notificationmode
    ): int {
        global $DB, $USER;

        $delegationids = array_values(array_unique(array_map('intval', $delegationids)));
        if (!$delegationids) {
            return 0;
        }

        self::validate_bulk_operation_count(count($delegationids));
        self::validate_period($timestart, $timeend);
        $notificationmode = self::resolve_notification_mode($notificationmode);
        [$insql, $params] = $DB->get_in_or_equal($delegationids, SQL_PARAMS_NAMED, 'bulkupdate');
        $params['realuserid'] = $realuserid;
        $records = $DB->get_records_select(
            'local_delegateaccount',
            "id $insql AND realuserid = :realuserid AND activekey = 0",
            $params
        );
        if (count($records) !== count($delegationids)) {
            throw new \moodle_exception('error_invaliddelegations', 'local_delegateaccount');
        }
        self::validate_update($records, $timeend);

        $now = time();
        $transaction = $DB->start_delegated_transaction();
        foreach ($records as $record) {
            $record->timestart = $timestart;
            $record->timeend = $timeend;
            $record->notificationmode = $notificationmode;
            $record->timemodified = $now;
            $record->usermodified = (int)$USER->id;
            $DB->update_record('local_delegateaccount', $record);
            self::trigger_event('delegation_updated', $record, (int)$USER->id);
        }
        $transaction->allow_commit();

        return count($records);
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
     * Returns the instant when access through a delegation actually stopped.
     *
     * A configured end date and a later logical revocation can both exist on a
     * historical record. The earlier positive timestamp is the true access
     * boundary used by activity reports.
     *
     * @param \stdClass $delegation Delegation database record.
     * @return int Effective end timestamp, or zero for continuing access.
     */
    public static function get_delegation_access_end(\stdClass $delegation): int {
        $ends = array_filter([
            (int)$delegation->timeend,
            (int)$delegation->timerevoked,
        ]);

        return empty($ends) ? 0 : min($ends);
    }

    /**
     * Returns the end timestamp that administrators expect in lifecycle views.
     *
     * Revocation is an explicit administrative end and therefore takes
     * precedence over the originally configured end date when displayed.
     *
     * @param \stdClass $delegation Delegation database record.
     * @return int Display end timestamp, or zero for an open-ended delegation.
     */
    public static function get_delegation_display_end(\stdClass $delegation): int {
        return (int)$delegation->timerevoked > 0
            ? (int)$delegation->timerevoked
            : (int)$delegation->timeend;
    }

    /**
     * Retrieves the target accounts a specific real user has been delegated to.
     *
     * @param int $realuserid The real user ID.
     * @param int $limit Maximum number of accounts to return, or zero for all accounts.
     * @return array List of target user accounts they can log into.
     */
    public static function get_delegated_accounts_for_user(int $realuserid, int $limit = 0): array {
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
                   AND u.suspended = 0
              ORDER BY u.lastname, u.firstname, u.id";

        $now = time();
        return $DB->get_records_sql($sql, [
            'realuserid' => $realuserid,
            'timestartnow' => $now,
            'timeendnow' => $now,
        ], 0, max(0, $limit));
    }

    /**
     * Returns one stable page of delegation records for component and external consumers.
     *
     * @param int $page Zero-based page number.
     * @param int $perpage Number of records per page.
     * @param int $realuserid Optional authorised-user filter.
     * @param string $status Optional lifecycle status filter.
     * @param string $search Optional identity search.
     * @return array{total: int, delegations: array} Page data and total count.
     */
    public static function get_delegations_page(
        int $page,
        int $perpage,
        int $realuserid = 0,
        string $status = '',
        string $search = ''
    ): array {
        global $DB;

        $where = ['u1.deleted = 0', 'u2.deleted = 0'];
        $params = [];
        if ($realuserid > 0) {
            $where[] = 'da.realuserid = :realuserid';
            $params['realuserid'] = $realuserid;
        }
        if ($search !== '') {
            $searchvalue = '%' . $DB->sql_like_escape($search) . '%';
            $searchparts = [];
            foreach (
                [
                    'u1.firstname', 'u1.lastname', 'u1.username', 'u1.email',
                    'u2.firstname', 'u2.lastname', 'u2.username', 'u2.email',
                ] as $index => $field
            ) {
                $paramname = 'search' . $index;
                $searchparts[] = $DB->sql_like($field, ':' . $paramname, false);
                $params[$paramname] = $searchvalue;
            }
            $where[] = '(' . implode(' OR ', $searchparts) . ')';
        }

        $now = time();
        if ($status === self::STATUS_ACTIVE) {
            $where[] = 'da.activekey = 0 AND da.timestart <= :activestart
                        AND (da.timeend = 0 OR da.timeend > :activeend)';
            $params['activestart'] = $now;
            $params['activeend'] = $now;
        } else if ($status === self::STATUS_SCHEDULED) {
            $where[] = 'da.activekey = 0 AND da.timestart > :scheduledstart';
            $params['scheduledstart'] = $now;
        } else if ($status === self::STATUS_EXPIRED) {
            $where[] = 'da.activekey = 0 AND da.timeend > 0 AND da.timeend <= :expiredend';
            $params['expiredend'] = $now;
        } else if ($status === self::STATUS_REVOKED) {
            $where[] = '(da.activekey <> 0 OR da.timerevoked > 0)';
        } else if ($status !== '') {
            throw new \invalid_parameter_exception('Unsupported delegation status.');
        }

        $from = '{local_delegateaccount} da
                 JOIN {user} u1 ON u1.id = da.realuserid
                 JOIN {user} u2 ON u2.id = da.delegateduserid';
        $wheresql = implode(' AND ', $where);
        $total = $DB->count_records_sql(sprintf('SELECT COUNT(da.id) FROM %s WHERE %s', $from, $wheresql), $params);
        $records = $DB->get_records_sql(
            sprintf('SELECT da.* FROM %s WHERE %s ORDER BY da.id DESC', $from, $wheresql),
            $params,
            $page * $perpage,
            $perpage
        );

        $userids = [];
        foreach ($records as $record) {
            $userids[] = (int)$record->realuserid;
            $userids[] = (int)$record->delegateduserid;
        }
        $users = empty($userids) ? [] : $DB->get_records_list(
            'user',
            'id',
            array_values(array_unique($userids)),
            '',
            'id, firstname, lastname, firstnamephonetic, lastnamephonetic, middlename, alternatename'
        );
        foreach ($records as $record) {
            $record->realuserfullname = fullname($users[(int)$record->realuserid]);
            $record->delegateduserfullname = fullname($users[(int)$record->delegateduserid]);
            $record->status = self::get_delegation_status($record);
        }

        return ['total' => $total, 'delegations' => array_values($records)];
    }

    /**
     * Returns the current non-revoked delegation identifier for a user pair.
     *
     * @param int $realuserid Authorised user identifier.
     * @param int $delegateduserid Target account identifier.
     * @return int Delegation identifier, or zero when no current record exists.
     */
    public static function get_current_delegation_id(int $realuserid, int $delegateduserid): int {
        global $DB;

        return (int)$DB->get_field('local_delegateaccount', 'id', [
            'realuserid' => $realuserid,
            'delegateduserid' => $delegateduserid,
            'activekey' => 0,
        ]);
    }

    /**
     * Returns the current non-revoked delegation identifiers for every pair of the given users.
     *
     * @param int[] $realuserids Authorised user identifiers.
     * @param int[] $delegateduserids Target account identifiers.
     * @return array<string, int> Delegation identifiers indexed by "realuserid:delegateduserid".
     */
    public static function get_current_delegation_ids(array $realuserids, array $delegateduserids): array {
        global $DB;

        if (empty($realuserids) || empty($delegateduserids)) {
            return [];
        }

        [$realsql, $params] = $DB->get_in_or_equal($realuserids, SQL_PARAMS_NAMED, 'real');
        [$delegatedsql, $delegatedparams] = $DB->get_in_or_equal($delegateduserids, SQL_PARAMS_NAMED, 'delegated');
        $records = $DB->get_records_select(
            'local_delegateaccount',
            "realuserid $realsql AND delegateduserid $delegatedsql AND activekey = 0",
            $params + $delegatedparams,
            '',
            'id, realuserid, delegateduserid'
        );

        $ids = [];
        foreach ($records as $record) {
            $ids[$record->realuserid . ':' . $record->delegateduserid] = (int)$record->id;
        }

        return $ids;
    }

    /**
     * Returns the log store reader used for delegated activity, or null when no SQL reader is enabled.
     *
     * @return \core\log\sql_reader|null Log reader.
     */
    public static function get_log_reader(): ?\core\log\sql_reader {
        $readers = get_log_manager()->get_readers(\core\log\sql_reader::class);
        $reader = reset($readers);

        return $reader ?: null;
    }

    /**
     * Builds the log selector for everything done through one delegation period.
     *
     * Anonymous events are left out unless the current user may view them, as in Moodle's log report.
     *
     * @param \stdClass $delegation Delegation database record.
     * @return array The selector and its named parameters.
     */
    public static function get_delegation_log_selector(\stdClass $delegation): array {
        $where = [
            'userid = :delegateduserid',
            'realuserid = :realuserid',
            'timecreated >= :delegationstart',
        ];
        $params = [
            'delegateduserid' => (int)$delegation->delegateduserid,
            'realuserid' => (int)$delegation->realuserid,
            'delegationstart' => (int)$delegation->timestart,
        ];
        $accessend = self::get_delegation_access_end($delegation);
        if ($accessend > 0) {
            $where[] = 'timecreated < :delegationend';
            $params['delegationend'] = $accessend;
        }
        if (!has_capability('moodle/site:viewanonymousevents', \context_system::instance())) {
            $where[] = 'anonymous = 0';
        }

        return [implode(' AND ', $where), $params];
    }

    /**
     * Returns when an authorised user last acted through a delegation period.
     *
     * @param \stdClass $delegation Delegation database record.
     * @return int Timestamp of the latest logged event, or zero when there is none.
     */
    public static function get_last_delegated_access(\stdClass $delegation): int {
        $reader = self::get_log_reader();
        if (!$reader) {
            return 0;
        }

        [$where, $params] = self::get_delegation_log_selector($delegation);
        $events = $reader->get_events_select($where, $params, 'timecreated DESC, id DESC', 0, 1);
        $event = reset($events);

        return $event ? (int)$event->timecreated : 0;
    }

    /**
     * Returns one stable page of activity attributed to a delegation period.
     *
     * @param int $delegationid Delegation identifier.
     * @param int $page Zero-based page number.
     * @param int $perpage Number of records per page.
     * @param int $timefrom Optional inclusive timestamp filter.
     * @param int $timeuntil Optional exclusive timestamp filter.
     * @param string $component Optional component fragment.
     * @param string $action Optional action fragment.
     * @return array{total: int, events: array} Activity page and total count.
     */
    public static function get_delegation_activity_page(
        int $delegationid,
        int $page,
        int $perpage,
        int $timefrom = 0,
        int $timeuntil = 0,
        string $component = '',
        string $action = ''
    ): array {
        global $DB;

        $delegation = $DB->get_record('local_delegateaccount', ['id' => $delegationid], '*', MUST_EXIST);
        $reader = self::get_log_reader();
        if (!$reader) {
            return ['total' => 0, 'events' => []];
        }

        [$where, $params] = self::get_delegation_log_selector($delegation);
        if ($timefrom > 0) {
            $where .= ' AND timecreated >= :timefrom';
            $params['timefrom'] = $timefrom;
        }
        if ($timeuntil > 0) {
            $where .= ' AND timecreated < :timeuntil';
            $params['timeuntil'] = $timeuntil;
        }
        if ($component !== '') {
            $where .= ' AND ' . $DB->sql_like('component', ':component', false);
            $params['component'] = '%' . $DB->sql_like_escape($component) . '%';
        }
        if ($action !== '') {
            $where .= ' AND ' . $DB->sql_like('action', ':action', false);
            $params['action'] = '%' . $DB->sql_like_escape($action) . '%';
        }

        $total = $reader->get_events_select_count($where, $params);
        $events = $reader->get_events_select($where, $params, 'timecreated DESC, id DESC', $page * $perpage, $perpage);

        $records = [];
        foreach ($events as $logid => $event) {
            $record = (object)($event->get_data() + $event->get_logextra());
            $record->id = (int)$logid;
            $records[] = $record;
        }

        return ['total' => $total, 'events' => $records];
    }

    /**
     * Returns why a delegation period is not allowed, as a language string identifier and its parameter.
     *
     * @param int $timestart Unix timestamp when access starts.
     * @param int $timeend Unix timestamp when access ends, or zero for no end date.
     * @return array|null The string identifier and its parameter, or null when the period is allowed.
     */
    private static function get_period_problem(int $timestart, int $timeend): ?array {
        if ($timeend > 0 && $timeend <= $timestart) {
            return ['error_invalidperiod', null];
        }
        if ($timeend === 0 && !self::get_config_bool('allowopenended', true)) {
            return ['error_openendednotallowed', null];
        }
        // The maximum duration only applies when every delegation must have an end date.
        $maximumdurationdays = self::get_config_bool('allowopenended', true)
            ? 0
            : self::get_config_int('maximumdurationdays', 0);
        if ($maximumdurationdays > 0 && $timeend > $timestart + ($maximumdurationdays * DAYSECS)) {
            return ['error_maximumduration', $maximumdurationdays];
        }

        return null;
    }

    /**
     * Returns why a delegation period is not allowed by the site settings.
     *
     * @param int $timestart Unix timestamp when access starts.
     * @param int $timeend Unix timestamp when access ends, or zero for no end date.
     * @return string|null Localised error, or null when the period is allowed.
     */
    public static function get_period_error(int $timestart, int $timeend): ?string {
        $problem = self::get_period_problem($timestart, $timeend);

        return $problem === null ? null : get_string($problem[0], 'local_delegateaccount', $problem[1]);
    }

    /**
     * Validates a delegation period.
     *
     * @param int $timestart Unix timestamp when access starts.
     * @param int $timeend Unix timestamp when access ends, or zero for no end date.
     */
    private static function validate_period(int $timestart, int $timeend): void {
        if ($timestart <= 0) {
            throw new \coding_exception('A delegation must have a start date.');
        }
        if ($problem = self::get_period_problem($timestart, $timeend)) {
            throw new \moodle_exception($problem[0], 'local_delegateaccount', '', $problem[1]);
        }
    }

    /**
     * Validates a per-delegation notification mode.
     *
     * @param string $notificationmode Site, always, or never.
     */
    private static function validate_notification_mode(string $notificationmode): void {
        if (
            !in_array(
                $notificationmode,
                [
                    self::NOTIFICATION_SITE,
                    self::NOTIFICATION_ALWAYS,
                    self::NOTIFICATION_NEVER,
                ],
                true
            )
        ) {
            throw new \coding_exception('Invalid delegation notification mode.');
        }
    }

    /**
     * Resolves a requested notification decision against the site policy.
     *
     * The stored decision is always 'always' or 'never'. A request for the site
     * decision ('site') under the policy that lets the creator choose sends the
     * notification, matching the default of the creation form.
     *
     * @param string $notificationmode Requested notification mode: site, always or never.
     * @return string Effective notification mode: always or never.
     */
    private static function resolve_notification_mode(string $notificationmode): string {
        self::validate_notification_mode($notificationmode);

        $policy = get_config('local_delegateaccount', 'notificationpolicy');
        if ($policy === self::NOTIFICATION_ALWAYS || $policy === self::NOTIFICATION_NEVER) {
            return $policy;
        }

        return $notificationmode === self::NOTIFICATION_SITE ? self::NOTIFICATION_ALWAYS : $notificationmode;
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
            if ((int) $user->deleted !== 0 || (int) $user->suspended !== 0 || isguestuser($user)) {
                throw new \moodle_exception('error_ineligibleuser', 'local_delegateaccount');
            }
        }

        // Bulk capability preloading to avoid N+1 queries inside loops.
        $syscontext = \context_system::instance();
        $adminmap = [];
        foreach (get_admins() as $admin) {
            $adminmap[(int)$admin->id] = true;
        }

        foreach ($realuserids as $realuserid) {
            if (!isset($adminmap[$realuserid]) && !has_capability('local/delegateaccount:use', $syscontext, $realuserid)) {
                throw new \moodle_exception('error_unauthorised_realuser', 'local_delegateaccount');
            }
        }

        if (self::protect_privileged_targets()) {
            foreach ($delegateduserids as $delegateduserid) {
                if (isset($adminmap[$delegateduserid])) {
                    throw new \moodle_exception('error_privilegedtarget', 'local_delegateaccount');
                }
            }
        }
    }

    /**
     * Applies the creation rules to delegations whose lifecycle is being changed.
     *
     * The users must still be eligible, and a delegation that becomes current or
     * scheduled again counts towards the authorised user's limit.
     *
     * @param \stdClass[] $records Delegation records before the change.
     * @param int $timeend Requested end timestamp, or zero for no end date.
     */
    private static function validate_update(array $records, int $timeend): void {
        $now = time();
        $realuserids = [];
        $delegateduserids = [];
        $newcounts = [];
        foreach ($records as $record) {
            $realuserid = (int)$record->realuserid;
            $realuserids[$realuserid] = $realuserid;
            $delegateduserids[(int)$record->delegateduserid] = (int)$record->delegateduserid;

            $wascounted = (int)$record->timeend === 0 || (int)$record->timeend > $now;
            $willcount = $timeend === 0 || $timeend > $now;
            if ($willcount && !$wascounted) {
                $newcounts[$realuserid] = ($newcounts[$realuserid] ?? 0) + 1;
            }
        }

        self::validate_users(array_values($realuserids), array_values($delegateduserids));
        self::validate_delegation_limit($newcounts);
    }

    /**
     * Checks if the proposed delegation operations exceed the user limits.
     *
     * @param array $newcounts Number of requested assignments keyed by real user ID.
     * @return string|null Localized error message if any limit is exceeded, null otherwise.
     */
    public static function get_delegation_limit_error(array $newcounts): ?string {
        global $DB;

        $maximum = self::get_config_int('maxdelegationsperuser', 10);
        if ($maximum === 0 || empty($newcounts)) {
            return null;
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
                return get_string('error_maxdelegations', 'local_delegateaccount', $maximum);
            }
        }

        return null;
    }

    /**
     * Enforces the configured limit of current or scheduled accounts per user.
     *
     * @param array $newcounts Number of candidate delegations indexed by authorised user ID.
     */
    private static function validate_delegation_limit(array $newcounts): void {
        if ($error = self::get_delegation_limit_error($newcounts)) {
            $maximum = self::get_config_int('maxdelegationsperuser', 10);
            throw new \moodle_exception('error_maxdelegations', 'local_delegateaccount', '', $maximum);
        }
    }

    /**
     * Checks if a bulk operation exceeds the configured limit.
     *
     * @param int $count Number of delegation records affected by the action.
     * @return string|null Localized error message if exceeded, null otherwise.
     */
    public static function get_bulk_operation_error(int $count): ?string {
        $maximum = self::get_config_int('maxbulkoperations', 100);
        if ($maximum > 0 && $count > $maximum) {
            return get_string('error_maxbulkoperations', 'local_delegateaccount', $maximum);
        }
        return null;
    }

    /**
     * Throw an exception when a bulk operation exceeds the configured maximum.
     *
     * @param int $count Number of delegation records affected by the action.
     * @throws \moodle_exception If the configured maximum is exceeded.
     */
    private static function validate_bulk_operation_count(int $count): void {
        if ($error = self::get_bulk_operation_error($count)) {
            $maximum = self::get_config_int('maxbulkoperations', 100);
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
