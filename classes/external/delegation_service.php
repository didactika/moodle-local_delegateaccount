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
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use local_delegateaccount\manager;

/**
 * Shared implementation for delegated-account external functions.
 *
 * This class is internal infrastructure and is not registered as a web-service function.
 *
 * @package    local_delegateaccount
 * @category   external
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class delegation_service extends external_api {
    /** Maximum page size exposed by read endpoints. */
    protected const MAX_PAGE_SIZE = 100;

    /**
     * Requires one exact capability for an external operation.
     *
     * The transitional manage capability is intentionally not accepted here;
     * integration users receive only the operations explicitly assigned to them.
     *
     * @param string $capability Required system capability.
     */
    protected static function require_granular_capability(string $capability): void {
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
    protected static function validate_page(int $page, int $perpage): void {
        if ($page < 0 || $perpage < 1 || $perpage > self::MAX_PAGE_SIZE) {
            throw new \invalid_parameter_exception('Page must be non-negative and perpage must be between 1 and 100.');
        }
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
    protected static function create_delegation_matrix(
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
    protected static function delegation_creation_result_structure(): external_single_structure {
        return new external_single_structure([
            'realuserid' => new external_value(PARAM_INT, 'Authorised user identifier.'),
            'delegateduserid' => new external_value(PARAM_INT, 'Target account identifier.'),
            'delegationid' => new external_value(PARAM_INT, 'Current delegation identifier, or zero.'),
            'outcome' => new external_value(PARAM_ALPHA, 'Created, unchanged, or skipped.'),
        ]);
    }

    /**
     * Returns the shared external representation of a delegation page.
     *
     * @return external_single_structure
     */
    protected static function delegation_page_structure(): external_single_structure {
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
    protected static function serialise_delegation_page(array $page): array {
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
