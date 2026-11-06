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

namespace local_delegateaccount;

/**
 * The permission overrides of delegated sessions: stored, combined and applied.
 *
 * An override changes the permission of one capability while someone works in an account through "Log in as" opened by a
 * delegation. Overrides exist for the whole site (delegation 0) and for each delegation; the override of a delegation wins
 * over the one of the site, and a capability with no override keeps the permission the account has. Nothing is stored in a
 * role and no role is created: the access data of the session is rewritten on every request.
 *
 * - Allow and Prohibit are given by a role that exists only in the access data of the request, as if it were assigned in the
 *   system context. A prohibit wins over everything, and an allow over every role that does not prohibit.
 * - Prevent is set, at site level, on the definition of every role the account has in the request, which is what Prevent means
 *   in "Define role". Overrides of those roles in courses or activities still apply below it.
 *
 * @package    local_delegateaccount
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class session_capabilities {
    /** Delegation identifier that holds the overrides of the whole site. */
    public const SITE = 0;

    /** Name of the table that keeps the overrides. */
    public const TABLE = 'local_delegateaccount_caps';

    /** Permissions an override can set. CAP_INHERIT (Not set) removes the override. */
    public const PERMISSIONS = [CAP_ALLOW, CAP_PREVENT, CAP_PROHIBIT];

    /** How many delegations one statement of a bulk change reaches. */
    private const CHUNK = 500;

    /** Identifier of the role that only exists in the access data of a delegated session. */
    private const ROLE_ID = -1;

    /** Offset of the identifiers that copy the roles of the account with Prevent applied: the copy of role 5 is -1000000005. */
    private const COPY_OFFSET = 1000000000;

    /**
     * Returns the overrides of one delegation, or of the site.
     *
     * @param int $delegationid Delegation identifier, or self::SITE.
     * @return int[] Permission by capability name.
     */
    public static function get_overrides(int $delegationid): array {
        global $DB;

        $cache = \cache::make('local_delegateaccount', 'overrides');
        $overrides = $cache->get($delegationid);
        if (!is_array($overrides)) {
            $overrides = array_map('intval', $DB->get_records_menu(
                self::TABLE,
                ['delegationid' => $delegationid],
                'capability',
                'capability, permission'
            ));
            $cache->set($delegationid, $overrides);
        }

        return $overrides;
    }

    /**
     * Returns the permissions a session opened through a delegation gets: the site overrides with the delegation's on top.
     *
     * @param int $delegationid Delegation identifier.
     * @return int[] Permission by capability name.
     */
    public static function get_effective_permissions(int $delegationid): array {
        $permissions = self::get_overrides(self::SITE);
        if ($delegationid !== self::SITE) {
            $permissions = self::get_overrides($delegationid) + $permissions;
        }
        ksort($permissions);

        return $permissions;
    }

    /**
     * Counts the overrides of several delegations.
     *
     * @param int[] $delegationids Delegation identifiers.
     * @return int[] Number of overrides by delegation identifier; delegations without any are left out.
     */
    public static function count_overrides(array $delegationids): array {
        global $DB;

        $delegationids = array_values(array_unique(array_filter(array_map('intval', $delegationids))));
        if (!$delegationids) {
            return [];
        }
        [$insql, $params] = $DB->get_in_or_equal($delegationids, SQL_PARAMS_NAMED, 'delegation');

        $counts = array_map('intval', $DB->get_records_sql_menu(
            "SELECT delegationid, COUNT(id)
               FROM {" . self::TABLE . "}
              WHERE delegationid $insql
           GROUP BY delegationid",
            $params
        ));
        ksort($counts);

        return $counts;
    }

    /**
     * Checks a list of changes and returns it in its canonical form.
     *
     * @param array $changes Permission by capability name; CAP_INHERIT removes the override.
     * @return int[] The same changes, sorted, with integer permissions.
     * @throws \invalid_parameter_exception When a capability does not exist or a permission cannot be set.
     */
    public static function validate_changes(array $changes): array {
        global $DB;

        $clean = [];
        foreach ($changes as $capability => $permission) {
            $permission = (int)$permission;
            if ($permission !== CAP_INHERIT && !in_array($permission, self::PERMISSIONS, true)) {
                throw new \invalid_parameter_exception('Unsupported permission ' . $permission . ' for ' . $capability . '.');
            }
            $clean[(string)$capability] = $permission;
        }
        if (!$clean) {
            return [];
        }

        // Removing an override is always possible, also for a capability that went away with its plugin.
        $set = array_keys(array_filter($clean, fn(int $permission): bool => $permission !== CAP_INHERIT));
        $known = [];
        foreach (array_chunk($set, self::CHUNK) as $chunk) {
            [$insql, $params] = $DB->get_in_or_equal($chunk, SQL_PARAMS_NAMED, 'capability');
            $known += array_flip($DB->get_fieldset_select('capabilities', 'name', "name $insql", $params));
        }
        $unknown = array_diff_key(array_flip($set), $known);
        if ($unknown) {
            throw new \invalid_parameter_exception(get_string(
                'error_unknowncapability',
                'local_delegateaccount',
                s(implode(', ', array_keys($unknown)))
            ));
        }
        ksort($clean);

        return $clean;
    }

    /**
     * Changes some overrides of one delegation, or of the site, and leaves the others as they are.
     *
     * @param int $delegationid Delegation identifier, or self::SITE.
     * @param array $changes Permission by capability name; CAP_INHERIT removes the override.
     * @return int How many capabilities changed.
     */
    public static function update(int $delegationid, array $changes): int {
        $changes = self::validate_changes($changes);
        $current = self::get_overrides($delegationid);
        $changes = array_filter(
            $changes,
            fn(int $permission, string $capability): bool => ($current[$capability] ?? CAP_INHERIT) !== $permission,
            ARRAY_FILTER_USE_BOTH
        );
        if (!$changes) {
            return 0;
        }

        self::write([$delegationid], $changes);
        self::trigger($delegationid === self::SITE ? 'site' : 'delegation', [$delegationid], $changes);

        return count($changes);
    }

    /**
     * Replaces every override of one delegation, or of the site, with a new list.
     *
     * @param int $delegationid Delegation identifier, or self::SITE.
     * @param array $overrides Permission by capability name; capabilities left out lose their override.
     * @return int How many capabilities changed.
     */
    public static function replace(int $delegationid, array $overrides): int {
        $overrides = array_filter(self::validate_changes($overrides), fn(int $permission): bool => $permission !== CAP_INHERIT);
        $changes = array_fill_keys(array_keys(self::get_overrides($delegationid)), CAP_INHERIT);

        return self::update($delegationid, $overrides + $changes);
    }

    /**
     * Applies the same changes to many delegations, in statements that each reach a bounded number of them.
     *
     * @param int[] $delegationids Delegation identifiers.
     * @param array $changes Permission by capability name; CAP_INHERIT removes the override.
     * @return int How many delegations were reached.
     */
    public static function update_many(array $delegationids, array $changes): int {
        $delegationids = self::existing_delegations($delegationids);
        $changes = self::validate_changes($changes);
        if (!$delegationids || !$changes) {
            return 0;
        }

        self::write($delegationids, $changes);
        self::trigger('bulk', $delegationids, $changes);

        return count($delegationids);
    }

    /**
     * Copies the overrides of one delegation to others.
     *
     * @param int $sourceid Delegation whose overrides are copied.
     * @param int[] $delegationids Delegations that receive them.
     * @param bool $replace True to remove the overrides the targets have and the source has not; false to keep them.
     * @return int How many delegations were reached.
     */
    public static function copy(int $sourceid, array $delegationids, bool $replace): int {
        global $DB;

        $delegationids = array_values(array_diff(self::existing_delegations($delegationids), [$sourceid]));
        if (!$delegationids) {
            return 0;
        }
        $changes = self::get_overrides($sourceid);
        if ($replace) {
            foreach (array_chunk($delegationids, self::CHUNK) as $chunk) {
                [$insql, $params] = $DB->get_in_or_equal($chunk, SQL_PARAMS_NAMED, 'delegation');
                foreach ($DB->get_fieldset_select(self::TABLE, 'DISTINCT capability', "delegationid $insql", $params) as $name) {
                    $changes[$name] ??= CAP_INHERIT;
                }
            }
        }
        if (!$changes) {
            return 0;
        }

        self::write($delegationids, $changes);
        self::trigger('copy', $delegationids, $changes, $sourceid);

        return count($delegationids);
    }

    /**
     * Removes every override of some delegations, for instance when their records are deleted.
     *
     * @param int[] $delegationids Delegation identifiers.
     */
    public static function delete_for_delegations(array $delegationids): void {
        global $DB;

        $delegationids = array_values(array_unique(array_filter(array_map('intval', $delegationids))));
        foreach (array_chunk($delegationids, self::CHUNK) as $chunk) {
            $DB->delete_records_list(self::TABLE, 'delegationid', $chunk);
        }
        self::purge($delegationids);
    }

    /**
     * Whether the current session was opened through a delegation.
     *
     * @return bool
     */
    public static function is_delegated_session(): bool {
        global $SESSION;

        return !empty($SESSION->local_delegateaccount_delegated) && \core\session\manager::is_loggedinas();
    }

    /**
     * Returns the delegation the current session was opened through, or zero.
     *
     * @return int Delegation identifier.
     */
    public static function get_session_delegation_id(): int {
        global $SESSION, $USER;

        if (!self::is_delegated_session()) {
            return 0;
        }
        $marker = $SESSION->local_delegateaccount_delegated;
        if (is_int($marker) && $marker > 0) {
            return $marker;
        }

        // Sessions opened before the delegation was kept in the session only hold a flag.
        return manager::get_current_delegation_id((int)\core\session\manager::get_realuser()->id, (int)$USER->id);
    }

    /**
     * Applies the overrides to the access data of the current request.
     *
     * It runs on every request of a delegated session, so a change of an override also reaches the sessions that are open,
     * from their next page.
     */
    public static function apply(): void {
        global $USER, $ACCESSLIB_PRIVATE;

        if (!self::is_delegated_session()) {
            return;
        }
        if (!isset($USER->access) || !is_array($USER->access)) {
            load_all_capabilities();
        }
        // A role change since the access data was loaded would make Moodle reload it in the middle of the request and drop the
        // overrides for the rest of it, so it is reloaded now, before they are applied.
        self::reload_if_dirty();
        self::restore($USER->access);

        $permissions = self::get_effective_permissions(self::get_session_delegation_id());
        if ($permissions) {
            $path = \context_system::instance()->path;
            $direct = array_filter($permissions, fn(int $permission): bool => $permission !== CAP_PREVENT);
            $prevented = array_keys(array_diff_key($permissions, $direct));
            if ($direct) {
                $USER->access['ra'][$path][self::ROLE_ID] = self::ROLE_ID;
                $ACCESSLIB_PRIVATE->cacheroledefs[self::ROLE_ID] = [$path => $direct];
            }
            if ($prevented) {
                self::prevent($USER->access, $path, $prevented);
            }
        }
        $ACCESSLIB_PRIVATE->accessdatabyuser[$USER->id] = $USER->access;
    }

    /**
     * Gives the roles of the access data copies whose site definition prevents some capabilities.
     *
     * @param array $access Access data of the session.
     * @param string $path Path of the system context.
     * @param string[] $capabilities Capabilities to prevent.
     */
    private static function prevent(array &$access, string $path, array $capabilities): void {
        global $ACCESSLIB_PRIVATE;

        $roleids = [];
        foreach ($access['ra'] ?? [] as $roles) {
            foreach ($roles as $roleid) {
                $roleids[(int)$roleid] = true;
            }
        }
        foreach ($access['rsw'] ?? [] as $roleid) {
            $roleids[(int)$roleid] = true;
        }
        unset($roleids[self::ROLE_ID]);
        $roleids = array_keys(array_filter($roleids, fn(bool $value, int $roleid): bool => $roleid > 0, ARRAY_FILTER_USE_BOTH));
        if (!$roleids) {
            return;
        }

        $definitions = get_role_definitions($roleids);
        foreach ($roleids as $roleid) {
            $definition = $definitions[$roleid] ?? [];
            foreach ($capabilities as $capability) {
                if (($definition[$path][$capability] ?? null) !== CAP_PROHIBIT) {
                    $definition[$path][$capability] = CAP_PREVENT;
                }
            }
            $ACCESSLIB_PRIVATE->cacheroledefs[self::copy_id($roleid)] = $definition;
        }

        foreach ($access['ra'] ?? [] as $contextpath => $roles) {
            foreach ($roles as $roleid) {
                if ((int)$roleid > 0) {
                    unset($access['ra'][$contextpath][$roleid]);
                    $access['ra'][$contextpath][self::copy_id((int)$roleid)] = self::copy_id((int)$roleid);
                }
            }
        }
        foreach ($access['rsw'] ?? [] as $contextpath => $roleid) {
            if ((int)$roleid > 0) {
                $access['rsw'][$contextpath] = self::copy_id((int)$roleid);
            }
        }
    }

    /**
     * Takes out of the access data what an earlier request of the session added, so it holds the roles of the account again.
     *
     * The access data lives in the session, so what one request changes is still there in the next one.
     *
     * @param array $access Access data of the session.
     */
    private static function restore(array &$access): void {
        global $ACCESSLIB_PRIVATE;

        foreach ($access['ra'] ?? [] as $contextpath => $roles) {
            foreach ($roles as $roleid) {
                $roleid = (int)$roleid;
                if ($roleid === self::ROLE_ID) {
                    unset($access['ra'][$contextpath][$roleid]);
                } else if ($roleid <= -self::COPY_OFFSET) {
                    unset($access['ra'][$contextpath][$roleid]);
                    $access['ra'][$contextpath][self::original_id($roleid)] = self::original_id($roleid);
                }
            }
            if (empty($access['ra'][$contextpath])) {
                unset($access['ra'][$contextpath]);
            }
        }
        foreach ($access['rsw'] ?? [] as $contextpath => $roleid) {
            if ((int)$roleid <= -self::COPY_OFFSET) {
                $access['rsw'][$contextpath] = self::original_id((int)$roleid);
            }
        }
        foreach (array_keys($ACCESSLIB_PRIVATE->cacheroledefs ?? []) as $roleid) {
            if ((int)$roleid < 0) {
                unset($ACCESSLIB_PRIVATE->cacheroledefs[$roleid]);
            }
        }
    }

    /**
     * Reloads the access data of the session when a role changed since it was loaded, as Moodle itself would.
     */
    private static function reload_if_dirty(): void {
        global $ACCESSLIB_PRIVATE, $USER;

        if (CLI_SCRIPT || !isset($USER->access['time'])) {
            return;
        }
        $changedsince = $USER->access['time'] - 2;
        if (!isset($ACCESSLIB_PRIVATE->dirtycontexts)) {
            $ACCESSLIB_PRIVATE->dirtycontexts = get_cache_flags('accesslib/dirtycontexts', $changedsince);
        }
        if (!isset($ACCESSLIB_PRIVATE->dirtyusers[$USER->id])) {
            $ACCESSLIB_PRIVATE->dirtyusers[$USER->id] = get_cache_flag('accesslib/dirtyusers', $USER->id, $changedsince);
        }
        if (!empty($ACCESSLIB_PRIVATE->dirtyusers[$USER->id]) || !empty($ACCESSLIB_PRIVATE->dirtycontexts)) {
            reload_all_capabilities();
        }
    }

    /**
     * Identifier of the copy of a role.
     *
     * @param int $roleid Role identifier.
     * @return int Identifier of its copy.
     */
    private static function copy_id(int $roleid): int {
        return -(self::COPY_OFFSET + $roleid);
    }

    /**
     * Identifier of the role a copy was made from.
     *
     * @param int $copyid Identifier of the copy.
     * @return int Role identifier.
     */
    private static function original_id(int $copyid): int {
        return -$copyid - self::COPY_OFFSET;
    }

    /**
     * Keeps only the delegations that exist.
     *
     * @param int[] $delegationids Delegation identifiers.
     * @return int[] The ones that exist.
     */
    private static function existing_delegations(array $delegationids): array {
        global $DB;

        $delegationids = array_values(array_unique(array_filter(array_map('intval', $delegationids))));
        $existing = [];
        foreach (array_chunk($delegationids, self::CHUNK) as $chunk) {
            [$insql, $params] = $DB->get_in_or_equal($chunk, SQL_PARAMS_NAMED, 'delegation');
            $existing = array_merge($existing, array_map('intval', $DB->get_fieldset_select(
                'local_delegateaccount',
                'id',
                "id $insql",
                $params
            )));
        }
        sort($existing);

        return $existing;
    }

    /**
     * Writes the changes for each delegation: one delete for every capability and chunk, and the new rows in bulk.
     *
     * @param int[] $delegationids Delegation identifiers, or self::SITE alone.
     * @param int[] $changes Permission by capability name; CAP_INHERIT removes the override.
     */
    private static function write(array $delegationids, array $changes): void {
        global $DB, $USER;

        $now = time();
        $transaction = $DB->start_delegated_transaction();
        foreach (array_chunk($delegationids, self::CHUNK) as $chunk) {
            [$insql, $params] = $DB->get_in_or_equal($chunk, SQL_PARAMS_NAMED, 'delegation');
            [$capsql, $capparams] = $DB->get_in_or_equal(array_keys($changes), SQL_PARAMS_NAMED, 'capability');
            $DB->delete_records_select(self::TABLE, "delegationid $insql AND capability $capsql", $params + $capparams);

            $rows = [];
            foreach ($chunk as $delegationid) {
                foreach ($changes as $capability => $permission) {
                    if ($permission !== CAP_INHERIT) {
                        $rows[] = [
                            'delegationid' => $delegationid,
                            'capability' => $capability,
                            'permission' => $permission,
                            'timemodified' => $now,
                            'usermodified' => (int)$USER->id,
                        ];
                    }
                }
            }
            if ($rows) {
                $DB->insert_records(self::TABLE, $rows);
            }
        }
        $transaction->allow_commit();
        self::purge($delegationids);
    }

    /**
     * Forgets the cached overrides of some delegations.
     *
     * @param int[] $delegationids Delegation identifiers.
     */
    private static function purge(array $delegationids): void {
        if ($delegationids) {
            \cache::make('local_delegateaccount', 'overrides')->delete_many($delegationids);
        }
    }

    /**
     * Records the change in the logs.
     *
     * @param string $scope Site, delegation, bulk or copy.
     * @param int[] $delegationids Delegations that changed.
     * @param int[] $changes Permission by capability name.
     * @param int $sourceid Delegation the overrides were copied from, or zero.
     */
    private static function trigger(string $scope, array $delegationids, array $changes, int $sourceid = 0): void {
        global $DB;

        $data = [
            'context' => \context_system::instance(),
            // Zero when the change is not about one delegation: the site, or many delegations at once.
            'objectid' => 0,
            'other' => [
                'scope' => $scope,
                'count' => count($delegationids),
                'sourceid' => $sourceid,
                // The list is cut so that a change of hundreds of capabilities still fits in the log.
                'changes' => array_slice($changes, 0, 50, true),
            ],
        ];
        if (count($delegationids) === 1 && $delegationids[0] !== self::SITE) {
            $delegation = $DB->get_record('local_delegateaccount', ['id' => $delegationids[0]], 'id, realuserid, delegateduserid');
            if ($delegation) {
                $data['objectid'] = (int)$delegation->id;
                $data['relateduserid'] = (int)$delegation->delegateduserid;
                $data['other']['realuserid'] = (int)$delegation->realuserid;
            }
        }
        event\session_permissions_updated::create($data)->trigger();
    }
}
