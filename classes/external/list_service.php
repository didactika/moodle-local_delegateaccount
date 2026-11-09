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

namespace local_delegateaccount\external;

use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use local_delegateaccount\local\listing;

/**
 * What the web services of the screens share: the structures of a person, of a list and of a selection.
 *
 * This class is internal infrastructure and is not registered as a web-service function.
 *
 * @package    local_delegateaccount
 * @category   external
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class list_service extends delegation_service {
    /** Capability that lets a person change the permission overrides of delegated sessions. */
    public const MANAGE_PERMISSIONS = 'local/delegateaccount:managepermissions';

    /**
     * Validates the page of a list: a page from 0, and 1 to 100 rows per page, or 0 for every row at once.
     *
     * @param int $page Zero-based page number.
     * @param int $perpage Rows per page, 0 for all of them.
     */
    protected static function validate_page(int $page, int $perpage): void {
        if ($page < 0 || $perpage < 0 || $perpage > self::MAX_PAGE_SIZE) {
            throw new \invalid_parameter_exception('Page must be non-negative and perpage must be 0 (all) or between 1 and 100.');
        }
    }

    /**
     * A person, as exporter::user() describes them.
     *
     * @return external_single_structure
     */
    protected static function user_structure(): external_single_structure {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'User identifier.'),
            'fullname' => new external_value(PARAM_TEXT, 'Full name.'),
            'email' => new external_value(PARAM_RAW, 'Email address, when it may be shown.'),
            'hasimage' => new external_value(PARAM_BOOL, 'Whether the person has a picture.'),
            'imageurl' => new external_value(PARAM_URL, 'Address of the picture, or empty.'),
            'initials' => new external_value(PARAM_TEXT, 'Initials shown when there is no picture.'),
            'profileurl' => new external_value(PARAM_URL, 'Address of the profile.'),
            'url' => new external_value(PARAM_URL, 'Address the name of the person opens in this list, or empty.'),
            'linklabel' => new external_value(PARAM_TEXT, 'What that link does, or empty.'),
        ]);
    }

    /**
     * How many rows of a list are in each status.
     *
     * @param bool $withnone Whether the list has people without delegations.
     * @return external_single_structure
     */
    protected static function counts_structure(bool $withnone): external_single_structure {
        $fields = ['all' => new external_value(PARAM_INT, 'Rows with any status.')];
        foreach (listing::STATUSES as $status) {
            $fields[$status] = new external_value(PARAM_INT, 'Rows with the status ' . $status . '.');
        }
        if ($withnone) {
            $fields['none'] = new external_value(PARAM_INT, 'People without delegations.');
        }

        return new external_single_structure($fields);
    }

    /**
     * The filters that choose delegations, for a list or for an action on all of its rows.
     *
     * @return external_single_structure
     */
    protected static function filters_structure(): external_single_structure {
        return new external_single_structure([
            'realuserid' => new external_value(PARAM_INT, 'Authorised user, or zero for everyone.', VALUE_DEFAULT, 0),
            'status' => new external_value(PARAM_ALPHA, 'Status, or empty for any.', VALUE_DEFAULT, ''),
            'search' => new external_value(PARAM_TEXT, 'Name, username, email or ID number fragment.', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Checks the filters of a list of delegations.
     *
     * @param array $filters realuserid, status and search.
     * @return array The same filters, trimmed.
     */
    protected static function clean_filters(array $filters): array {
        $filters['search'] = trim((string)($filters['search'] ?? ''));
        $filters['status'] = (string)($filters['status'] ?? '');
        $filters['realuserid'] = (int)($filters['realuserid'] ?? 0);
        if ($filters['status'] !== '' && !in_array($filters['status'], listing::STATUSES, true)) {
            throw new \invalid_parameter_exception('Unsupported delegation status.');
        }

        return $filters;
    }

    /**
     * The delegations an action reaches: the ones listed, or every one that matches the filters.
     *
     * @return external_single_structure
     */
    protected static function selection_structure(): external_single_structure {
        return new external_single_structure([
            'delegationids' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Delegation identifier.'),
                'The chosen delegations.',
                VALUE_DEFAULT,
                []
            ),
            'all' => new external_value(PARAM_BOOL, 'True for every delegation that matches the filters.', VALUE_DEFAULT, false),
            'filters' => self::filters_structure(),
        ]);
    }

    /**
     * Turns a selection into delegation identifiers.
     *
     * @param array $selection As selection_structure() describes it.
     * @return int[] Delegation identifiers.
     */
    protected static function resolve_selection(array $selection): array {
        if (!empty($selection['all'])) {
            return listing::delegation_ids(self::clean_filters($selection['filters'] ?? []));
        }

        return array_values(array_unique(array_filter(array_map('intval', $selection['delegationids'] ?? []))));
    }

    /**
     * One change of a permission override.
     *
     * @return external_single_structure
     */
    protected static function change_structure(): external_single_structure {
        return new external_single_structure([
            'capability' => new external_value(PARAM_CAPABILITY, 'Capability name.'),
            'permission' => new external_value(PARAM_INT, 'CAP_ALLOW, CAP_PREVENT, CAP_PROHIBIT, or CAP_INHERIT to remove it.'),
        ]);
    }

    /**
     * Turns a list of changes into a map, and refuses to allow what the person may not allow.
     *
     * An Allow override gives a capability to a session, so a person may only allow the capabilities they have themselves.
     *
     * @param array $changes Changes as change_structure() describes them.
     * @return int[] Permission by capability name.
     */
    protected static function changes_to_map(array $changes): array {
        $map = [];
        $system = \context_system::instance();
        foreach ($changes as $change) {
            $permission = (int)$change['permission'];
            if ($permission === CAP_ALLOW && !has_capability($change['capability'], $system)) {
                throw new \moodle_exception('error_cannotallow', 'local_delegateaccount', '', s($change['capability']));
            }
            $map[$change['capability']] = $permission;
        }

        return $map;
    }

    /**
     * Requires the capability that changes permission overrides.
     */
    protected static function require_manage_permissions(): void {
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability(self::MANAGE_PERMISSIONS, $context);
    }
}
