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
use local_delegateaccount\output\exporter;
use local_delegateaccount\permission;
use local_delegateaccount\session_capabilities;

/**
 * One page of delegations, of one person or of everyone, with the counts of its filter chips.
 *
 * @package    local_delegateaccount
 * @category   external
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class get_delegation_list extends list_service {
    /**
     * Describes the function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'filters' => self::filters_structure(),
            'sort' => new external_value(PARAM_ALPHA, 'account, user, start or end.', VALUE_DEFAULT, 'account'),
            'direction' => new external_value(PARAM_ALPHA, 'asc or desc.', VALUE_DEFAULT, 'asc'),
            'page' => new external_value(PARAM_INT, 'Zero-based page.', VALUE_DEFAULT, 0),
            'perpage' => new external_value(PARAM_INT, 'Rows per page, from 1 to 100.', VALUE_DEFAULT, 20),
        ]);
    }

    /**
     * Executes the function.
     *
     * @param array $filters realuserid, status and search.
     * @param string $sort Sort column.
     * @param string $direction Sort direction.
     * @param int $page Zero-based page.
     * @param int $perpage Rows per page.
     * @return array The page.
     */
    public static function execute(
        array $filters,
        string $sort = 'account',
        string $direction = 'asc',
        int $page = 0,
        int $perpage = 20
    ): array {
        $params = self::validate_parameters(self::execute_parameters(), compact('filters', 'sort', 'direction', 'page', 'perpage'));
        self::require_permission(permission::VIEW);
        self::validate_page($params['page'], $params['perpage']);

        $result = listing::delegations(
            self::clean_filters($params['filters']),
            $params['sort'],
            $params['direction'],
            $params['page'],
            $params['perpage']
        );
        $overrides = session_capabilities::count_overrides(array_map(fn(\stdClass $row): int => (int)$row->id, $result['rows']));

        return [
            'total' => $result['total'],
            'counts' => $result['counts'],
            'rows' => array_map(
                fn(\stdClass $row): array => exporter::delegation_row($row, $overrides[(int)$row->id] ?? 0),
                $result['rows']
            ),
        ];
    }

    /**
     * Describes the function result.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'total' => new external_value(PARAM_INT, 'Rows that match the filters.'),
            'counts' => self::counts_structure(false),
            'rows' => new external_multiple_structure(self::delegation_row_structure()),
        ]);
    }

    /**
     * A row of a list of delegations, as exporter::delegation_row() describes it.
     *
     * @return external_single_structure
     */
    public static function delegation_row_structure(): external_single_structure {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'Delegation identifier.'),
            'realuser' => self::user_structure(),
            'delegateduser' => self::user_structure(),
            'status' => new external_value(PARAM_ALPHA, 'Status.'),
            'statuslabel' => new external_value(PARAM_TEXT, 'Name of the status.'),
            'statustone' => new external_value(PARAM_ALPHA, 'Colour of the status.'),
            'timestart' => new external_value(PARAM_INT, 'Start of the access.'),
            'start' => new external_value(PARAM_TEXT, 'Start of the access, as a date.'),
            'timeend' => new external_value(PARAM_INT, 'End of the access, or zero.'),
            'end' => new external_value(PARAM_TEXT, 'End of the access, as a date.'),
            'lastaccess' => new external_value(PARAM_TEXT, 'When the account was last used through the delegation.'),
            'hasaccess' => new external_value(PARAM_BOOL, 'Whether the account was ever used through it.'),
            'overrides' => new external_value(PARAM_INT, 'Permission overrides of the delegation.'),
            'overrideslabel' => new external_value(PARAM_TEXT, 'The same, as a text.'),
            'detailsurl' => new external_value(PARAM_URL, 'Page of the delegation.'),
            'permissionsurl' => new external_value(PARAM_URL, 'Session permissions of the delegation.'),
            'activityurl' => new external_value(PARAM_URL, 'Activity of the delegation.'),
            'canupdate' => new external_value(PARAM_BOOL, 'Whether its period can be changed.'),
            'canrevoke' => new external_value(PARAM_BOOL, 'Whether it can be revoked.'),
            'canviewactivity' => new external_value(PARAM_BOOL, 'Whether its activity can be seen.'),
            'selectable' => new external_value(PARAM_BOOL, 'Whether it can be chosen for an action.'),
            'label' => new external_value(PARAM_TEXT, 'Who may open which account.'),
        ]);
    }
}
