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

use context_system;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use local_delegateaccount\manager;

/**
 * Granular external API for delegated-account lifecycle management.
 *
 * @package    local_delegateaccount
 * @category   external
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class delegation_api extends external_api {
    /** Maximum page size exposed by read endpoints. */
    private const MAX_PAGE_SIZE = 100;

    /**
     * Describes the paginated delegation-list parameters.
     *
     * @return external_function_parameters
     */
    public static function get_delegations_parameters(): external_function_parameters {
        return new external_function_parameters([
            'page' => new external_value(PARAM_INT, 'Zero-based page number.', VALUE_DEFAULT, 0),
            'perpage' => new external_value(PARAM_INT, 'Records per page, from 1 to 100.', VALUE_DEFAULT, 25),
            'status' => new external_value(PARAM_ALPHA, 'Optional lifecycle status.', VALUE_DEFAULT, ''),
            'search' => new external_value(PARAM_TEXT, 'Optional user identity search.', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Returns a stable page of all visible delegation records.
     *
     * @param int $page Zero-based page number.
     * @param int $perpage Records per page.
     * @param string $status Optional lifecycle status.
     * @param string $search Optional identity search.
     * @return array Delegation page.
     */
    public static function get_delegations(
        int $page = 0,
        int $perpage = 25,
        string $status = '',
        string $search = ''
    ): array {
        $params = self::validate_parameters(self::get_delegations_parameters(), [
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
            0,
            $params['status'],
            trim($params['search'])
        ));
    }

    /**
     * Describes the delegation-list result.
     *
     * @return external_single_structure
     */
    public static function get_delegations_returns(): external_single_structure {
        return self::delegation_page_structure();
    }

    /**
     * Describes the per-user delegation-list parameters.
     *
     * @return external_function_parameters
     */
    public static function get_user_delegations_parameters(): external_function_parameters {
        return new external_function_parameters([
            'realuserid' => new external_value(PARAM_INT, 'Authorised user identifier.'),
            'page' => new external_value(PARAM_INT, 'Zero-based page number.', VALUE_DEFAULT, 0),
            'perpage' => new external_value(PARAM_INT, 'Records per page, from 1 to 100.', VALUE_DEFAULT, 25),
            'status' => new external_value(PARAM_ALPHA, 'Optional lifecycle status.', VALUE_DEFAULT, ''),
            'search' => new external_value(PARAM_TEXT, 'Optional target identity search.', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Returns a stable page of one authorised user's delegation records.
     *
     * @param int $realuserid Authorised user identifier.
     * @param int $page Zero-based page number.
     * @param int $perpage Records per page.
     * @param string $status Optional lifecycle status.
     * @param string $search Optional identity search.
     * @return array Delegation page.
     */
    public static function get_user_delegations(
        int $realuserid,
        int $page = 0,
        int $perpage = 25,
        string $status = '',
        string $search = ''
    ): array {
        $params = self::validate_parameters(self::get_user_delegations_parameters(), [
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
     * Describes the per-user delegation-list result.
     *
     * @return external_single_structure
     */
    public static function get_user_delegations_returns(): external_single_structure {
        return self::delegation_page_structure();
    }

    /**
     * Describes single delegation creation parameters.
     *
     * @return external_function_parameters
     */
    public static function create_delegation_parameters(): external_function_parameters {
        return new external_function_parameters([
            'realuserid' => new external_value(PARAM_INT, 'Authorised user identifier.'),
            'delegateduserid' => new external_value(PARAM_INT, 'Target account identifier.'),
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
     * Creates one idempotent delegation.
     *
     * @param int $realuserid Authorised user identifier.
     * @param int $delegateduserid Target account identifier.
     * @param int $timestart Access start timestamp.
     * @param int $timeend Access end timestamp, or zero.
     * @param string $notificationmode Notification decision.
     * @return array Per-pair creation outcome.
     */
    public static function create_delegation(
        int $realuserid,
        int $delegateduserid,
        int $timestart,
        int $timeend = 0,
        string $notificationmode = manager::NOTIFICATION_SITE
    ): array {
        $params = self::validate_parameters(self::create_delegation_parameters(), [
            'realuserid' => $realuserid,
            'delegateduserid' => $delegateduserid,
            'timestart' => $timestart,
            'timeend' => $timeend,
            'notificationmode' => $notificationmode,
        ]);
        self::require_granular_capability('local/delegateaccount:create');
        $result = self::create_delegation_matrix(
            [$params['realuserid']],
            [$params['delegateduserid']],
            $params['timestart'],
            $params['timeend'],
            $params['notificationmode']
        );

        return $result['results'][0];
    }

    /**
     * Describes a single delegation creation result.
     *
     * @return external_single_structure
     */
    public static function create_delegation_returns(): external_single_structure {
        return self::delegation_creation_result_structure();
    }

    /**
     * Describes bulk delegation creation parameters.
     *
     * @return external_function_parameters
     */
    public static function create_delegations_parameters(): external_function_parameters {
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
     * Creates an idempotent matrix of delegations.
     *
     * @param array $realuserids Authorised user identifiers.
     * @param array $delegateduserids Target account identifiers.
     * @param int $timestart Access start timestamp.
     * @param int $timeend Access end timestamp, or zero.
     * @param string $notificationmode Notification decision.
     * @return array Per-pair outcomes and created count.
     */
    public static function create_delegations(
        array $realuserids,
        array $delegateduserids,
        int $timestart,
        int $timeend = 0,
        string $notificationmode = manager::NOTIFICATION_SITE
    ): array {
        $params = self::validate_parameters(self::create_delegations_parameters(), [
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
     * Describes bulk delegation creation results.
     *
     * @return external_single_structure
     */
    public static function create_delegations_returns(): external_single_structure {
        return new external_single_structure([
            'createdcount' => new external_value(PARAM_INT, 'Number of newly created delegations.'),
            'results' => new external_multiple_structure(self::delegation_creation_result_structure()),
        ]);
    }

    /**
     * Describes bulk delegation update parameters.
     *
     * @return external_function_parameters
     */
    public static function update_delegations_parameters(): external_function_parameters {
        return new external_function_parameters([
            'delegationids' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Delegation identifier.')
            ),
            'realuserid' => new external_value(PARAM_INT, 'Owner of every selected delegation.'),
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
     * Applies one lifecycle configuration to selected delegations.
     *
     * @param array $delegationids Delegation identifiers.
     * @param int $realuserid Owner of all selected delegations.
     * @param int $timestart Access start timestamp.
     * @param int $timeend Access end timestamp, or zero.
     * @param string $notificationmode Notification decision.
     * @return array Update count.
     */
    public static function update_delegations(
        array $delegationids,
        int $realuserid,
        int $timestart,
        int $timeend = 0,
        string $notificationmode = manager::NOTIFICATION_SITE
    ): array {
        $params = self::validate_parameters(self::update_delegations_parameters(), [
            'delegationids' => $delegationids,
            'realuserid' => $realuserid,
            'timestart' => $timestart,
            'timeend' => $timeend,
            'notificationmode' => $notificationmode,
        ]);
        self::require_granular_capability('local/delegateaccount:update');

        return ['updatedcount' => manager::update_delegations(
            $params['delegationids'],
            $params['realuserid'],
            $params['timestart'],
            $params['timeend'],
            $params['notificationmode']
        )];
    }

    /**
     * Describes bulk delegation update results.
     *
     * @return external_single_structure
     */
    public static function update_delegations_returns(): external_single_structure {
        return new external_single_structure([
            'updatedcount' => new external_value(PARAM_INT, 'Number of updated delegations.'),
        ]);
    }

    /**
     * Describes bulk revocation parameters.
     *
     * @return external_function_parameters
     */
    public static function revoke_delegations_parameters(): external_function_parameters {
        return new external_function_parameters([
            'delegationids' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Delegation identifier.')
            ),
            'confirm' => new external_value(PARAM_BOOL, 'Explicit confirmation of the destructive operation.'),
        ]);
    }

    /**
     * Revokes selected delegation records after explicit confirmation.
     *
     * @param array $delegationids Delegation identifiers.
     * @param bool $confirm Explicit destructive-action confirmation.
     * @return array Revocation count.
     */
    public static function revoke_delegations(array $delegationids, bool $confirm): array {
        $params = self::validate_parameters(self::revoke_delegations_parameters(), [
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
     * Describes bulk revocation results.
     *
     * @return external_single_structure
     */
    public static function revoke_delegations_returns(): external_single_structure {
        return new external_single_structure([
            'revokedcount' => new external_value(PARAM_INT, 'Number of revoked delegations.'),
        ]);
    }

    /**
     * Describes delegated-activity query parameters.
     *
     * @return external_function_parameters
     */
    public static function get_delegation_activity_parameters(): external_function_parameters {
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
     * Returns activity attributed to one immutable delegation period.
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
    public static function get_delegation_activity(
        int $delegationid,
        int $page = 0,
        int $perpage = 25,
        int $timefrom = 0,
        int $timeuntil = 0,
        string $component = '',
        string $action = ''
    ): array {
        $params = self::validate_parameters(self::get_delegation_activity_parameters(), [
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
     * Describes delegated-activity query results.
     *
     * @return external_single_structure
     */
    public static function get_delegation_activity_returns(): external_single_structure {
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

    /**
     * Creates a validated matrix and reports one stable result for every requested pair.
     *
     * @param array $realuserids Authorised user identifiers.
     * @param array $delegateduserids Target account identifiers.
     * @param int $timestart Access start timestamp.
     * @param int $timeend Access end timestamp, or zero.
     * @param string $notificationmode Notification decision.
     * @return array Per-pair outcomes and created count.
     */
    private static function create_delegation_matrix(
        array $realuserids,
        array $delegateduserids,
        int $timestart,
        int $timeend,
        string $notificationmode
    ): array {
        $realuserids = array_values(array_unique(array_map('intval', $realuserids)));
        $delegateduserids = array_values(array_unique(array_map('intval', $delegateduserids)));
        $existing = [];
        foreach ($realuserids as $realuserid) {
            foreach ($delegateduserids as $delegateduserid) {
                $existing[$realuserid . ':' . $delegateduserid] = manager::get_current_delegation_id(
                    $realuserid,
                    $delegateduserid
                );
            }
        }
        $createdcount = manager::create_delegations($realuserids, $delegateduserids, [
            'timestart' => $timestart,
            'timeend' => $timeend,
            'notificationmode' => $notificationmode,
        ]);
        $results = [];
        foreach ($realuserids as $realuserid) {
            foreach ($delegateduserids as $delegateduserid) {
                $key = $realuserid . ':' . $delegateduserid;
                $delegationid = manager::get_current_delegation_id($realuserid, $delegateduserid);
                $results[] = [
                    'realuserid' => $realuserid,
                    'delegateduserid' => $delegateduserid,
                    'delegationid' => $delegationid,
                    'outcome' => $existing[$key] > 0
                        ? 'unchanged'
                        : ($delegationid > 0 ? 'created' : 'skipped'),
                ];
            }
        }

        return ['createdcount' => $createdcount, 'results' => $results];
    }

    /**
     * Returns the shared external representation of one creation outcome.
     *
     * @return external_single_structure
     */
    private static function delegation_creation_result_structure(): external_single_structure {
        return new external_single_structure([
            'realuserid' => new external_value(PARAM_INT, 'Authorised user identifier.'),
            'delegateduserid' => new external_value(PARAM_INT, 'Target account identifier.'),
            'delegationid' => new external_value(PARAM_INT, 'Current delegation identifier, or zero.'),
            'outcome' => new external_value(PARAM_ALPHA, 'Created, unchanged, or skipped.'),
        ]);
    }

    /**
     * Requires one exact capability for an external operation.
     *
     * The transitional manage capability is intentionally not accepted here;
     * integration users receive only the operations explicitly assigned to them.
     *
     * @param string $capability Required system capability.
     */
    private static function require_granular_capability(string $capability): void {
        $context = context_system::instance();
        self::validate_context($context);
        require_capability($capability, $context);
    }

    /**
     * Validates stable pagination limits.
     *
     * @param int $page Zero-based page number.
     * @param int $perpage Requested page size.
     */
    private static function validate_page(int $page, int $perpage): void {
        if ($page < 0 || $perpage < 1 || $perpage > self::MAX_PAGE_SIZE) {
            throw new \invalid_parameter_exception('Page must be non-negative and perpage must be between 1 and 100.');
        }
    }

    /**
     * Returns the shared external representation of a delegation page.
     *
     * @return external_single_structure
     */
    private static function delegation_page_structure(): external_single_structure {
        return new external_single_structure([
            'total' => new external_value(PARAM_INT, 'Total matching delegation count.'),
            'delegations' => new external_multiple_structure(new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Delegation identifier.'),
                'realuserid' => new external_value(PARAM_INT, 'Authorised user identifier.'),
                'realuserfullname' => new external_value(PARAM_TEXT, 'Authorised user full name.'),
                'delegateduserid' => new external_value(PARAM_INT, 'Target account identifier.'),
                'delegateduserfullname' => new external_value(PARAM_TEXT, 'Target account full name.'),
                'status' => new external_value(PARAM_ALPHA, 'Derived lifecycle status.'),
                'timestart' => new external_value(PARAM_INT, 'Access start timestamp.'),
                'timeend' => new external_value(PARAM_INT, 'Configured access end timestamp.'),
                'timerevoked' => new external_value(PARAM_INT, 'Revocation timestamp.'),
                'notificationmode' => new external_value(PARAM_ALPHA, 'Effective notification decision.'),
                'timecreated' => new external_value(PARAM_INT, 'Creation timestamp.'),
                'timemodified' => new external_value(PARAM_INT, 'Last modification timestamp.'),
            ])),
        ]);
    }

    /**
     * Converts component records to their public external representation.
     *
     * @param array $page Component page returned by the manager.
     * @return array External page.
     */
    private static function serialise_delegation_page(array $page): array {
        return [
            'total' => $page['total'],
            'delegations' => array_map(static fn(\stdClass $delegation): array => [
                'id' => (int)$delegation->id,
                'realuserid' => (int)$delegation->realuserid,
                'realuserfullname' => $delegation->realuserfullname,
                'delegateduserid' => (int)$delegation->delegateduserid,
                'delegateduserfullname' => $delegation->delegateduserfullname,
                'status' => $delegation->status,
                'timestart' => (int)$delegation->timestart,
                'timeend' => (int)$delegation->timeend,
                'timerevoked' => (int)$delegation->timerevoked,
                'notificationmode' => $delegation->notificationmode,
                'timecreated' => (int)$delegation->timecreated,
                'timemodified' => (int)$delegation->timemodified,
            ], $page['delegations']),
        ];
    }
}
