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
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use local_delegateaccount\manager;

/**
 * Revokes selected delegated-account relationships.
 *
 * @package    local_delegateaccount
 * @category   external
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class revoke_delegations extends delegation_service {
    /**
     * Describes the function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'delegationids' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Delegation identifier.')
            ),
            'confirm' => new external_value(PARAM_BOOL, 'Explicit confirmation of the destructive operation.'),
        ]);
    }

    /**
     * Executes the function.
     *
     * @param array $delegationids Delegation identifiers.
     * @param bool $confirm Explicit destructive-action confirmation.
     * @return array Revocation count.
     */
    public static function execute(array $delegationids, bool $confirm): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'delegationids' => $delegationids,
            'confirm' => $confirm,
        ]);
        self::require_granular_capability('local/delegateaccount:revoke');
        if (!$params['confirm']) {
            throw new \invalid_parameter_exception('Delegation revocation requires explicit confirmation.');
        }

        return ['revokedcount' => manager::revoke_delegations($params['delegationids'])];
    }

    /**
     * Describes the function result.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'revokedcount' => new external_value(PARAM_INT, 'Number of revoked delegations.'),
        ]);
    }
}
