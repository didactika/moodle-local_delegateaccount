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
 * Returns standard-log activity for one immutable delegation period.
 *
 * @package    local_delegateaccount
 * @category   external
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class get_delegation_activity extends delegation_service {
    /**
     * Describes the function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'delegationid' => new external_value(PARAM_INT, 'Delegation identifier.'),
            'page' => new external_value(PARAM_INT, 'Zero-based page number.', VALUE_DEFAULT, 0),
            'perpage' => new external_value(PARAM_INT, 'Records per page, from 1 to 100.', VALUE_DEFAULT, 25),
            'timefrom' => new external_value(PARAM_INT, 'Optional inclusive timestamp.', VALUE_DEFAULT, 0),
            'timeuntil' => new external_value(PARAM_INT, 'Optional exclusive timestamp.', VALUE_DEFAULT, 0),
            'component' => new external_value(PARAM_COMPONENT, 'Optional component fragment.', VALUE_DEFAULT, ''),
            'action' => new external_value(PARAM_ALPHANUMEXT, 'Optional action fragment.', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Executes the function.
     *
     * @param int $delegationid Delegation identifier.
     * @param int $page Zero-based page number.
     * @param int $perpage Records per page.
     * @param int $timefrom Optional inclusive timestamp.
     * @param int $timeuntil Optional exclusive timestamp.
     * @param string $component Optional component fragment.
     * @param string $action Optional action fragment.
     * @return array Activity page.
     */
    public static function execute(
        int $delegationid,
        int $page = 0,
        int $perpage = 25,
        int $timefrom = 0,
        int $timeuntil = 0,
        string $component = '',
        string $action = ''
    ): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'delegationid' => $delegationid,
            'page' => $page,
            'perpage' => $perpage,
            'timefrom' => $timefrom,
            'timeuntil' => $timeuntil,
            'component' => $component,
            'action' => $action,
        ]);
        self::require_granular_capability('local/delegateaccount:viewactivity');
        self::validate_page($params['page'], $params['perpage']);
        if ($params['timeuntil'] > 0 && $params['timeuntil'] <= $params['timefrom']) {
            throw new \invalid_parameter_exception('The activity end timestamp must follow its start timestamp.');
        }
        $page = manager::get_delegation_activity_page(
            $params['delegationid'],
            $params['page'],
            $params['perpage'],
            $params['timefrom'],
            $params['timeuntil'],
            $params['component'],
            $params['action']
        );

        return [
            'total' => $page['total'],
            'events' => array_map(static fn(\stdClass $event): array => [
                'id' => (int)$event->id,
                'timecreated' => (int)$event->timecreated,
                'eventname' => $event->eventname,
                'component' => $event->component,
                'action' => $event->action,
                'target' => $event->target,
                'contextid' => (int)$event->contextid,
                'contextlevel' => (int)$event->contextlevel,
            ], $page['events']),
        ];
    }

    /**
     * Describes the function result.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'total' => new external_value(PARAM_INT, 'Total matching event count.'),
            'events' => new external_multiple_structure(new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Standard-log record identifier.'),
                'timecreated' => new external_value(PARAM_INT, 'Event timestamp.'),
                'eventname' => new external_value(PARAM_RAW, 'Event class name.'),
                'component' => new external_value(PARAM_COMPONENT, 'Event component.'),
                'action' => new external_value(PARAM_ALPHANUMEXT, 'Event action.'),
                'target' => new external_value(PARAM_ALPHANUMEXT, 'Event target.'),
                'contextid' => new external_value(PARAM_INT, 'Event context identifier.'),
                'contextlevel' => new external_value(PARAM_INT, 'Event context level.'),
            ])),
        ]);
    }
}
