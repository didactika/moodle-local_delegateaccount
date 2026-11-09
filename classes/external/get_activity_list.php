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
use local_delegateaccount\manager;
use local_delegateaccount\output\exporter;
use local_delegateaccount\permission;

/**
 * One page of what was done through a delegation, ready to be drawn.
 *
 * @package    local_delegateaccount
 * @category   external
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class get_activity_list extends list_service {
    /**
     * Describes the function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'delegationid' => new external_value(PARAM_INT, 'Delegation identifier.'),
            'datefrom' => new external_value(PARAM_ALPHANUMEXT, 'First day, as YYYY-MM-DD, or empty.', VALUE_DEFAULT, ''),
            'dateto' => new external_value(PARAM_ALPHANUMEXT, 'Last day, as YYYY-MM-DD, or empty.', VALUE_DEFAULT, ''),
            'component' => new external_value(PARAM_TEXT, 'Component fragment.', VALUE_DEFAULT, ''),
            'action' => new external_value(PARAM_TEXT, 'Action fragment.', VALUE_DEFAULT, ''),
            'page' => new external_value(PARAM_INT, 'Zero-based page.', VALUE_DEFAULT, 0),
            'perpage' => new external_value(PARAM_INT, 'Rows per page, from 1 to 100, or 0 for all.', VALUE_DEFAULT, 12),
        ]);
    }

    /**
     * Executes the function.
     *
     * @param int $delegationid Delegation identifier.
     * @param string $datefrom First day.
     * @param string $dateto Last day.
     * @param string $component Component fragment.
     * @param string $action Action fragment.
     * @param int $page Zero-based page.
     * @param int $perpage Rows per page, 0 for all.
     * @return array The page.
     */
    public static function execute(
        int $delegationid,
        string $datefrom = '',
        string $dateto = '',
        string $component = '',
        string $action = '',
        int $page = 0,
        int $perpage = 12
    ): array {
        $params = self::validate_parameters(self::execute_parameters(), compact(
            'delegationid',
            'datefrom',
            'dateto',
            'component',
            'action',
            'page',
            'perpage'
        ));
        self::require_permission(permission::VIEWACTIVITY);
        self::validate_page($params['page'], $params['perpage']);

        // The days are days of the person who reads the report, as in the date fields of Moodle.
        $from = self::day($params['datefrom']);
        $until = self::day($params['dateto']);
        $result = manager::get_delegation_activity_page(
            $params['delegationid'],
            $params['page'],
            $params['perpage'],
            $from ? $from->getTimestamp() : 0,
            $until ? $until->modify('+1 day')->getTimestamp() : 0,
            trim($params['component']),
            trim($params['action'])
        );

        return [
            'total' => $result['total'],
            'haslog' => manager::get_log_reader() !== null,
            'rows' => array_map(fn(\stdClass $event): array => exporter::activity_row($event), $result['events']),
        ];
    }

    /**
     * Reads a day in the timezone of the person.
     *
     * @param string $date YYYY-MM-DD, or empty.
     * @return \DateTimeImmutable|null Midnight of the day, or null.
     */
    private static function day(string $date): ?\DateTimeImmutable {
        if ($date === '') {
            return null;
        }
        $day = \DateTimeImmutable::createFromFormat('!Y-m-d', $date, \core_date::get_user_timezone_object());
        if (!$day || $day->format('Y-m-d') !== $date) {
            throw new \invalid_parameter_exception('Dates must be written as YYYY-MM-DD.');
        }

        return $day;
    }

    /**
     * Describes the function result.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'total' => new external_value(PARAM_INT, 'Events that match the filters.'),
            'haslog' => new external_value(PARAM_BOOL, 'Whether the site has a log store the report can read.'),
            'rows' => new external_multiple_structure(new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Log record identifier.'),
                'time' => new external_value(PARAM_TEXT, 'When it happened.'),
                'eventname' => new external_value(PARAM_TEXT, 'Name of the event.'),
                'eventurl' => new external_value(PARAM_URL, 'Address of what the event is about, or empty.'),
                'description' => new external_value(PARAM_TEXT, 'Description of the event.'),
                'component' => new external_value(PARAM_TEXT, 'Name of the component.'),
                'contextname' => new external_value(PARAM_TEXT, 'Where it happened.'),
                'contexturl' => new external_value(PARAM_URL, 'Address of where it happened, or empty.'),
                'relateduser' => new external_value(PARAM_TEXT, 'Person the event was about, or empty.'),
                'ip' => new external_value(PARAM_TEXT, 'IP address.'),
                'origin' => new external_value(PARAM_TEXT, 'Origin of the request.'),
            ])),
        ]);
    }
}
