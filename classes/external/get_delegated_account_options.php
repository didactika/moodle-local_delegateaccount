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

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use local_delegateaccount\manager;
use local_delegateaccount\permission;

/**
 * Returns accounts that can safely be selected as delegation targets via AJAX.
 *
 * @package    local_delegateaccount
 * @author     Miguel Rivas Morantes <miguelrivasmorantes@gmail.com>
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_delegated_account_options extends external_api {
    /**
     * Describes the parameters for execute.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'query' => new external_value(PARAM_TEXT, 'The search query string'),
            'realuserid' => new external_value(PARAM_INT, 'The configured real user ID', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Returns the target accounts matching a search, as offered by the creation form.
     *
     * @param string $query Active search query.
     * @param int $realuserid Authed user ID to consider.
     * @return array Array of user items.
     */
    public static function execute(string $query, int $realuserid): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'query' => $query,
            'realuserid' => $realuserid,
        ]);

        $query = $params['query'];
        $realuserid = $params['realuserid'];

        $syscontext = \context_system::instance();
        self::validate_context($syscontext);

        permission::require_action(permission::CREATE);

        $options = manager::get_delegated_account_options($realuserid, $query, 30);

        $results = [];
        foreach ($options as $id => $fullname) {
            $results[] = [
                'id' => (int)$id,
                'name' => $fullname,
            ];
        }

        return $results;
    }

    /**
     * Describes the execute return value.
     *
     * @return external_multiple_structure
     */
    public static function execute_returns(): external_multiple_structure {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'User ID'),
                'name' => new external_value(PARAM_TEXT, 'User full name'),
            ])
        );
    }
}
