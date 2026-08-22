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
 * Creates a matrix of delegated-account relationships.
 *
 * @package    local_delegateaccount
 * @category   external
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class create_delegations extends delegation_service {
    /**
     * Describes the function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'realuserids' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Authorised user identifier.')
            ),
            'delegateduserids' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Target account identifier.')
            ),
            'timestart' => new external_value(PARAM_INT, 'Access start timestamp.'),
            'timeend' => new external_value(PARAM_INT, 'Access end timestamp, or zero.', VALUE_DEFAULT, 0),
            'notificationmode' => new external_value(
                PARAM_ALPHA,
                'Site, always, or never.',
                VALUE_DEFAULT,
                manager::NOTIFICATION_SITE
            ),
        ]);
    }

    /**
     * Executes the function idempotently.
     *
     * @param array $realuserids Authorised user identifiers.
     * @param array $delegateduserids Target account identifiers.
     * @param int $timestart Access start timestamp.
     * @param int $timeend Access end timestamp, or zero.
     * @param string $notificationmode Notification decision.
     * @return array Per-pair outcomes and created count.
     */
    public static function execute(
        array $realuserids,
        array $delegateduserids,
        int $timestart,
        int $timeend = 0,
        string $notificationmode = manager::NOTIFICATION_SITE
    ): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'realuserids' => $realuserids,
            'delegateduserids' => $delegateduserids,
            'timestart' => $timestart,
            'timeend' => $timeend,
            'notificationmode' => $notificationmode,
        ]);
        self::require_granular_capability('local/delegateaccount:create');

        return self::create_delegation_matrix(
            $params['realuserids'],
            $params['delegateduserids'],
            $params['timestart'],
            $params['timeend'],
            $params['notificationmode']
        );
    }

    /**
     * Describes the function result.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'createdcount' => new external_value(PARAM_INT, 'Number of newly created delegations.'),
            'results' => new external_multiple_structure(self::delegation_creation_result_structure()),
        ]);
    }
}
