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
 * Resolves management permissions, where local/delegateaccount:manage grants every management action.
 *
 * A management action is allowed when the user has its own capability, or has
 * local/delegateaccount:manage and none of their system roles sets the action's
 * capability to Prevent or Prohibit. Using delegated accounts (local/delegateaccount:use)
 * is never granted through :manage.
 *
 * @package    local_delegateaccount
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class permission {
    /** View delegations. */
    public const VIEW = 'view';

    /** Create delegations. */
    public const CREATE = 'create';

    /** Change the lifecycle of delegations. */
    public const UPDATE = 'update';

    /** Revoke delegations. */
    public const REVOKE = 'revoke';

    /** View the activity recorded through a delegation. */
    public const VIEWACTIVITY = 'viewactivity';

    /** Capability that grants every management action. */
    public const MANAGE_CAPABILITY = 'local/delegateaccount:manage';

    /**
     * Returns whether a user may perform a management action.
     *
     * @param string $action One of the action constants.
     * @param int|null $userid User identifier, or null for the current user.
     * @return bool Whether the action is allowed.
     */
    public static function has(string $action, ?int $userid = null): bool {
        $context = \context_system::instance();
        $capability = self::get_capability($action);

        if (has_capability($capability, $context, $userid)) {
            return true;
        }
        if (!has_capability(self::MANAGE_CAPABILITY, $context, $userid)) {
            return false;
        }

        return !self::is_explicitly_denied($capability, $userid);
    }

    /**
     * Throws when the current user may not perform a management action.
     *
     * @param string $action One of the action constants.
     * @throws \required_capability_exception When the action is not allowed.
     */
    public static function require_action(string $action): void {
        if (!self::has($action)) {
            throw new \required_capability_exception(
                \context_system::instance(),
                self::get_capability($action),
                'nopermissions',
                ''
            );
        }
    }

    /**
     * Returns the capability that represents a management action.
     *
     * @param string $action One of the action constants.
     * @return string Capability name.
     */
    public static function get_capability(string $action): string {
        if (!in_array($action, [self::VIEW, self::CREATE, self::UPDATE, self::REVOKE, self::VIEWACTIVITY], true)) {
            throw new \coding_exception('Unknown delegated account management action: ' . $action);
        }

        return 'local/delegateaccount:' . $action;
    }

    /**
     * Returns whether one of the user's system roles sets a capability to Prevent or Prohibit.
     *
     * Moodle reports Not set, Prevent and Prohibit identically, so the role definitions
     * are read to tell an explicit refusal apart from a capability that was simply not granted.
     *
     * @param string $capability Capability name.
     * @param int|null $userid User identifier, or null for the current user.
     * @return bool Whether the capability is explicitly refused.
     */
    private static function is_explicitly_denied(string $capability, ?int $userid): bool {
        global $CFG, $DB, $USER;

        $userid = $userid ?? (int)$USER->id;
        $context = \context_system::instance();

        $roleids = array_map(
            static fn(\stdClass $role): int => (int)$role->roleid,
            get_user_roles($context, $userid, false)
        );
        if (!empty($CFG->defaultuserroleid) && !isguestuser($userid)) {
            $roleids[] = (int)$CFG->defaultuserroleid;
        }
        if (empty($roleids)) {
            return false;
        }

        [$rolesql, $params] = $DB->get_in_or_equal(array_unique($roleids), SQL_PARAMS_NAMED, 'role');
        [$permissionsql, $permissionparams] = $DB->get_in_or_equal([CAP_PREVENT, CAP_PROHIBIT], SQL_PARAMS_NAMED, 'permission');
        $params += $permissionparams + [
            'contextid' => $context->id,
            'capability' => $capability,
        ];

        return $DB->record_exists_select(
            'role_capabilities',
            "contextid = :contextid AND capability = :capability AND roleid $rolesql AND permission $permissionsql",
            $params
        );
    }
}
