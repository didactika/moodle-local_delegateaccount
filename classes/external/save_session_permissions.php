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
 * Changes some permission overrides of the site or of one delegation.
 *
 * @package    local_delegateaccount
 * @category   external
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class save_session_permissions extends list_service {
    /**
     * Describes the function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'delegationid' => new external_value(PARAM_INT, 'Delegation identifier, or zero for the site.'),
            'changes' => new external_multiple_structure(self::change_structure()),
        ]);
    }

    /**
     * Executes the function.
     *
     * @param int $delegationid Delegation identifier, or zero for the site.
     * @param array $changes Changes of the overrides.
     * @return array How many changed, and the overrides the delegation has now.
     */
    public static function execute(int $delegationid, array $changes): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), compact('delegationid', 'changes'));
        self::require_manage_permissions();
        if ($params['delegationid'] === session_capabilities::SITE) {
            require_capability('moodle/site:config', \context_system::instance());
        } else {
            $DB->get_record('local_delegateaccount', ['id' => $params['delegationid']], 'id', MUST_EXIST);
        }

        $changed = session_capabilities::update($params['delegationid'], self::changes_to_map($params['changes']));
        $overrides = session_capabilities::get_overrides($params['delegationid']);

        return [
            'changed' => $changed,
            'overrides' => array_map(
                fn(string $capability, int $permission): array => ['capability' => $capability, 'permission' => $permission],
                array_keys($overrides),
                array_values($overrides)
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
            'changed' => new external_value(PARAM_INT, 'How many capabilities changed.'),
            'overrides' => new external_multiple_structure(self::change_structure()),
        ]);
    }
}
