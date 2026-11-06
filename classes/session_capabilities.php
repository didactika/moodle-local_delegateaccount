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
 * Sets the permission of chosen capabilities in "log in as" sessions opened through a delegation.
 *
 * The restriction is a role that exists only in the access data of the current request, so no role and no
 * database record changes, and the account owner keeps every permission when logging in normally.
 *
 * @package    local_delegateaccount
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class session_capabilities {
    /** Name of the setting that holds the permission of each capability, as JSON. */
    public const SETTING = 'sessioncapabilities';

    /** Permissions applied until an administrator saves a different list. */
    public const DEFAULT = ['moodle/user:editownprofile' => CAP_PROHIBIT];

    /** Identifier of the temporary role. Real roles never have a negative identifier. */
    private const ROLE_ID = -1;

    /**
     * Returns the permission set for each capability in delegated sessions.
     *
     * @return int[] CAP_ALLOW or CAP_PROHIBIT by capability name.
     */
    public static function get_permissions(): array {
        $configured = get_config('local_delegateaccount', self::SETTING);
        if ($configured === false) {
            return self::DEFAULT;
        }

        $decoded = json_decode($configured, true);

        return is_array($decoded) ? self::sanitise($decoded) : [];
    }

    /**
     * Keeps only well-formed capability names with a permission the plugin supports.
     *
     * @param array $permissions Permission by capability name.
     * @return int[] CAP_ALLOW or CAP_PROHIBIT by capability name.
     */
    public static function sanitise(array $permissions): array {
        return array_filter(
            $permissions,
            fn($permission, $capability) => preg_match('/^[a-z0-9_]+\/[a-z0-9_]+:[A-Za-z0-9_]+$/', (string)$capability) === 1
                && in_array($permission, [CAP_ALLOW, CAP_PROHIBIT], true),
            ARRAY_FILTER_USE_BOTH
        );
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
     * Applies the restriction to the access data of the current request.
     *
     * It runs on every request, so a change of the setting also reaches sessions that are already open.
     */
    public static function apply(): void {
        global $USER, $ACCESSLIB_PRIVATE;

        if (!self::is_delegated_session() || !isset($USER->access) || !is_array($USER->access)) {
            return;
        }

        $path = \context_system::instance()->path;
        unset($USER->access['ra'][$path][self::ROLE_ID], $ACCESSLIB_PRIVATE->cacheroledefs[self::ROLE_ID]);

        $permissions = self::get_permissions();
        if (!$permissions) {
            return;
        }

        // Moodle denies a capability as soon as any role prohibits it, which overrides every allow.
        $USER->access['ra'][$path][self::ROLE_ID] = self::ROLE_ID;
        $ACCESSLIB_PRIVATE->cacheroledefs[self::ROLE_ID][$path] = $permissions;
    }
}
