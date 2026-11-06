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

/**
 * One page of the people of the management page, with the counts of its filter chips.
 *
 * @package    local_delegateaccount
 * @category   external
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class get_user_list extends list_service {
    /**
     * Describes the function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'scope' => new external_value(PARAM_ALPHA, 'authorised or historical.', VALUE_DEFAULT, listing::SCOPE_AUTHORISED),
            'search' => new external_value(PARAM_TEXT, 'Identity fragment.', VALUE_DEFAULT, ''),
            'status' => new external_value(PARAM_ALPHA, 'Status, none, or empty for everyone.', VALUE_DEFAULT, ''),
            'sort' => new external_value(PARAM_ALPHA, 'name, email, active or scheduled.', VALUE_DEFAULT, 'name'),
            'direction' => new external_value(PARAM_ALPHA, 'asc or desc.', VALUE_DEFAULT, 'asc'),
            'page' => new external_value(PARAM_INT, 'Zero-based page.', VALUE_DEFAULT, 0),
            'perpage' => new external_value(PARAM_INT, 'Rows per page, from 1 to 100.', VALUE_DEFAULT, 20),
        ]);
    }

    /**
     * Executes the function.
     *
     * @param string $scope authorised or historical.
     * @param string $search Identity fragment.
     * @param string $status Status filter.
     * @param string $sort Sort column.
     * @param string $direction Sort direction.
     * @param int $page Zero-based page.
     * @param int $perpage Rows per page.
     * @return array The page.
     */
    public static function execute(
        string $scope = listing::SCOPE_AUTHORISED,
        string $search = '',
        string $status = '',
        string $sort = 'name',
        string $direction = 'asc',
        int $page = 0,
        int $perpage = 20
    ): array {
        $params = self::validate_parameters(self::execute_parameters(), compact(
            'scope',
            'search',
            'status',
            'sort',
            'direction',
            'page',
            'perpage'
        ));
        self::require_permission(permission::VIEW);
        self::validate_page($params['page'], $params['perpage']);
        if (!in_array($params['scope'], [listing::SCOPE_AUTHORISED, listing::SCOPE_HISTORICAL], true)) {
            throw new \invalid_parameter_exception('Unsupported scope.');
        }
        if ($params['status'] !== '' && !in_array($params['status'], array_merge(listing::STATUSES, ['none']), true)) {
            throw new \invalid_parameter_exception('Unsupported delegation status.');
        }

        $result = listing::users(
            $params['scope'],
            trim($params['search']),
            $params['status'],
            $params['sort'],
            $params['direction'],
            $params['page'],
            $params['perpage']
        );
        $cancreate = $params['scope'] === listing::SCOPE_AUTHORISED && permission::has(permission::CREATE);

        return [
            'total' => $result['total'],
            'counts' => $result['counts'],
            'rows' => array_map(fn(\stdClass $row): array => exporter::user_row($row, $cancreate), $result['rows']),
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
            'counts' => self::counts_structure(true),
            'rows' => new external_multiple_structure(new external_single_structure([
                'user' => self::user_structure(),
                'activecount' => new external_value(PARAM_INT, 'Active delegations.'),
                'scheduledcount' => new external_value(PARAM_INT, 'Scheduled delegations.'),
                'url' => new external_value(PARAM_URL, 'Page of the delegations of the person.'),
                'cancreate' => new external_value(PARAM_BOOL, 'Whether delegations can be created for the person.'),
                'openlabel' => new external_value(PARAM_TEXT, 'Label of the link to the delegations.'),
                'addlabel' => new external_value(PARAM_TEXT, 'Label of the button that creates delegations.'),
            ])),
        ]);
    }
}
