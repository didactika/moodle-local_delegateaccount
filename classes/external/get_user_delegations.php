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

namespace local_delegateaccount\external;

use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use local_delegateaccount\manager;

/**
 * Returns one authorised user's paginated delegation history.
 *
 * @package    local_delegateaccount
 * @category   external
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class get_user_delegations extends delegation_service {
    /**
     * Describes the function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'realuserid' => new external_value(PARAM_INT, 'Authorised user identifier.'),
            'page' => new external_value(PARAM_INT, 'Zero-based page number.', VALUE_DEFAULT, 0),
            'perpage' => new external_value(PARAM_INT, 'Records per page, from 1 to 100.', VALUE_DEFAULT, 25),
            'status' => new external_value(PARAM_ALPHA, 'Optional lifecycle status.', VALUE_DEFAULT, ''),
            'search' => new external_value(PARAM_TEXT, 'Optional target identity search.', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Executes the function.
     *
     * @param int $realuserid Authorised user identifier.
     * @param int $page Zero-based page number.
     * @param int $perpage Records per page.
     * @param string $status Optional lifecycle status.
     * @param string $search Optional identity search.
     * @return array Delegation page.
     */
    public static function execute(
        int $realuserid,
        int $page = 0,
        int $perpage = 25,
        string $status = '',
        string $search = ''
    ): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'realuserid' => $realuserid,
            'page' => $page,
            'perpage' => $perpage,
            'status' => $status,
            'search' => $search,
        ]);
        self::require_granular_capability('local/delegateaccount:view');
        self::validate_page($params['page'], $params['perpage']);

        return self::serialise_delegation_page(manager::get_delegations_page(
            $params['page'],
            $params['perpage'],
            $params['realuserid'],
            $params['status'],
            trim($params['search'])
        ));
    }

    /**
     * Describes the function result.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return self::delegation_page_structure();
    }
}
