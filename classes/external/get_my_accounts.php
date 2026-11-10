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

/**
 * One page of the accounts the current person may open now.
 *
 * @package    local_delegateaccount
 * @category   external
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class get_my_accounts extends list_service {
    /**
     * Describes the function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'search' => new external_value(PARAM_TEXT, 'Name or email fragment.', VALUE_DEFAULT, ''),
            'page' => new external_value(PARAM_INT, 'Zero-based page.', VALUE_DEFAULT, 0),
            'perpage' => new external_value(PARAM_INT, 'Rows per page, from 1 to 100, or 0 for all.', VALUE_DEFAULT, 12),
        ]);
    }

    /**
     * Executes the function.
     *
     * @param string $search Name or email fragment.
     * @param int $page Zero-based page.
     * @param int $perpage Rows per page, 0 for all.
     * @return array The page.
     */
    public static function execute(string $search = '', int $page = 0, int $perpage = 12): array {
        global $USER;

        $params = self::validate_parameters(self::execute_parameters(), compact('search', 'page', 'perpage'));
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/delegateaccount:use', $context);
        self::validate_page($params['page'], $params['perpage']);
        if (\core\session\manager::is_loggedinas()) {
            return ['total' => 0, 'rows' => []];
        }

        $result = listing::my_accounts((int)$USER->id, trim($params['search']), $params['page'], $params['perpage']);

        return [
            'total' => $result['total'],
            'rows' => array_map(fn(\stdClass $row): array => exporter::account_row($row), $result['rows']),
        ];
    }

    /**
     * Describes the function result.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'total' => new external_value(PARAM_INT, 'Accounts that match the search.'),
            'rows' => new external_multiple_structure(new external_single_structure([
                'user' => self::user_structure(),
                'delegationid' => new external_value(PARAM_INT, 'Delegation identifier.'),
                'start' => new external_value(PARAM_TEXT, 'Start of the access, as a date.'),
                'end' => new external_value(PARAM_TEXT, 'End of the access, as a date.'),
                'hasend' => new external_value(PARAM_BOOL, 'Whether the access ends.'),
                'loginurl' => new external_value(PARAM_URL, 'Address that opens the account.'),
                'loginlabel' => new external_value(PARAM_TEXT, 'Label of the button that opens it.'),
            ])),
        ]);
    }
}
