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
use local_delegateaccount\local\listing;
use local_delegateaccount\manager;
use local_delegateaccount\permission;

/**
 * The delegations that match the filters of a list and can still be changed, for an action on all of them.
 *
 * Changing the period and revoking keep the limit of records per bulk action of the site, so this says when it is exceeded
 * instead of returning more identifiers than the action may take.
 *
 * @package    local_delegateaccount
 * @category   external
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class get_delegation_ids extends list_service {
    /**
     * Describes the function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'filters' => self::filters_structure(),
        ]);
    }

    /**
     * Executes the function.
     *
     * @param array $filters realuserid, status and search.
     * @return array The identifiers, or the error that the bulk limit gives.
     */
    public static function execute(array $filters): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), compact('filters'));
        self::require_permission(permission::VIEW);
        $ids = listing::delegation_ids(self::clean_filters($params['filters']));
        // Revoked delegations cannot be edited or revoked again.
        $open = [];
        foreach (array_chunk($ids, 1000) as $chunk) {
            [$insql, $inparams] = $DB->get_in_or_equal($chunk, SQL_PARAMS_NAMED, 'delegation');
            $open = array_merge($open, array_map('intval', $DB->get_fieldset_select(
                'local_delegateaccount',
                'id',
                "id $insql AND activekey = 0 AND timerevoked = 0",
                $inparams
            )));
        }
        $error = manager::get_bulk_operation_error(count($open));

        return ['ids' => $error === null ? $open : [], 'total' => count($open), 'error' => (string)$error];
    }

    /**
     * Describes the function result.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'ids' => new external_multiple_structure(new external_value(PARAM_INT, 'Delegation identifier.')),
            'total' => new external_value(PARAM_INT, 'How many delegations match.'),
            'error' => new external_value(PARAM_TEXT, 'Why they cannot be changed together, or empty.'),
        ]);
    }
}
