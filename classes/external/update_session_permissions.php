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

use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use local_delegateaccount\session_capabilities;

/**
 * Changes the permission overrides of many delegations at once, or copies the overrides of one delegation to many.
 *
 * The delegations are the ones chosen, or every one that matches the filters of a list, so an action on tens of thousands of
 * delegations is one call. The database is written in bounded batches.
 *
 * @package    local_delegateaccount
 * @category   external
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class update_session_permissions extends list_service {
    /**
     * Describes the function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'selection' => self::selection_structure(),
            'changes' => new external_multiple_structure(self::change_structure(), 'Changes to apply.', VALUE_DEFAULT, []),
            'copyfrom' => new external_value(PARAM_INT, 'Delegation whose overrides are copied, or zero.', VALUE_DEFAULT, 0),
            'replace' => new external_value(
                PARAM_BOOL,
                'When copying, remove the overrides the source does not have.',
                VALUE_DEFAULT,
                false
            ),
        ]);
    }

    /**
     * Executes the function.
     *
     * @param array $selection The delegations to change.
     * @param array $changes Changes to apply, when nothing is copied.
     * @param int $copyfrom Delegation whose overrides are copied, or zero.
     * @param bool $replace When copying, remove the overrides the source does not have.
     * @return array How many delegations were reached.
     */
    public static function execute(array $selection, array $changes = [], int $copyfrom = 0, bool $replace = false): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), compact('selection', 'changes', 'copyfrom', 'replace'));
        self::require_manage_permissions();
        $delegationids = self::resolve_selection($params['selection']);

        if ($params['copyfrom'] > 0) {
            $DB->get_record('local_delegateaccount', ['id' => $params['copyfrom']], 'id', MUST_EXIST);
            // Copying gives the targets every Allow of the source, so the same rule as for a change applies to each of them.
            self::changes_to_map(array_map(
                fn(string $capability, int $permission): array => ['capability' => $capability, 'permission' => $permission],
                array_keys(session_capabilities::get_overrides($params['copyfrom'])),
                array_values(session_capabilities::get_overrides($params['copyfrom']))
            ));
            // Copying an Allow override is allowing it again, so it needs the same right.
            if (!is_siteadmin() && in_array(CAP_ALLOW, session_capabilities::get_overrides($params['copyfrom']), true)) {
                throw new \moodle_exception(
                    'error_cannotallow',
                    'local_delegateaccount',
                    '',
                    get_string('permissionallow', 'local_delegateaccount')
                );
            }
            $count = session_capabilities::copy($params['copyfrom'], $delegationids, $params['replace']);
        } else {
            if (!$params['changes']) {
                throw new \invalid_parameter_exception('Nothing to change.');
            }
            $count = session_capabilities::update_many($delegationids, self::changes_to_map($params['changes']));
        }

        return ['count' => $count];
    }

    /**
     * Describes the function result.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'count' => new external_value(PARAM_INT, 'How many delegations were reached.'),
        ]);
    }
}
