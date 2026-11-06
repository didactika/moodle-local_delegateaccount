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

/**
 * External service definitions for delegated-account management.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_delegateaccount_get_delegations' => [
        'classname' => 'local_delegateaccount\external\get_delegations',
        'methodname' => 'execute',
        'description' => 'Returns a paginated delegation inventory.',
        'type' => 'read',
        'capabilities' => 'local/delegateaccount:view',
    ],
    'local_delegateaccount_get_user_delegations' => [
        'classname' => 'local_delegateaccount\external\get_user_delegations',
        'methodname' => 'execute',
        'description' => 'Returns one authorised user\'s paginated delegation history.',
        'type' => 'read',
        'capabilities' => 'local/delegateaccount:view',
    ],
    'local_delegateaccount_create_delegation' => [
        'classname' => 'local_delegateaccount\external\create_delegation',
        'methodname' => 'execute',
        'description' => 'Creates one idempotent delegated-account relationship.',
        'type' => 'write',
        'capabilities' => 'local/delegateaccount:create',
    ],
    'local_delegateaccount_create_delegations' => [
        'classname' => 'local_delegateaccount\external\create_delegations',
        'methodname' => 'execute',
        'description' => 'Creates an idempotent matrix of delegated-account relationships.',
        'type' => 'write',
        'capabilities' => 'local/delegateaccount:create',
    ],
    'local_delegateaccount_update_delegations' => [
        'classname' => 'local_delegateaccount\external\update_delegations',
        'methodname' => 'execute',
        'description' => 'Updates the lifecycle configuration of selected delegations.',
        'type' => 'write',
        'capabilities' => 'local/delegateaccount:update',
    ],
    'local_delegateaccount_revoke_delegations' => [
        'classname' => 'local_delegateaccount\external\revoke_delegations',
        'methodname' => 'execute',
        'description' => 'Revokes selected delegations after explicit confirmation.',
        'type' => 'write',
        'capabilities' => 'local/delegateaccount:revoke',
        'ajax' => true,
    ],
    'local_delegateaccount_get_delegation_activity' => [
        'classname' => 'local_delegateaccount\external\get_delegation_activity',
        'methodname' => 'execute',
        'description' => 'Returns paginated standard-log activity for one delegation period.',
        'type' => 'read',
        'capabilities' => 'local/delegateaccount:viewactivity',
    ],
    'local_delegateaccount_get_delegated_account_options' => [
        'classname' => 'local_delegateaccount\external\get_delegated_account_options',
        'methodname' => 'execute',
        'description' => 'Returns a list of accounts that can safely be selected as delegation targets.',
        'type' => 'read',
        'capabilities' => 'local/delegateaccount:create',
        'ajax' => true,
    ],
    'local_delegateaccount_get_authorised_user_options' => [
        'classname' => 'local_delegateaccount\external\get_authorised_user_options',
        'methodname' => 'execute',
        'description' => 'Returns a list of accounts that can safely be selected as authorised users.',
        'type' => 'read',
        'capabilities' => 'local/delegateaccount:create',
        'ajax' => true,
    ],
    'local_delegateaccount_get_user_list' => [
        'classname' => 'local_delegateaccount\external\get_user_list',
        'methodname' => 'execute',
        'description' => 'Returns one page of the people of the management page, with the counts of each status.',
        'type' => 'read',
        'capabilities' => 'local/delegateaccount:view',
        'ajax' => true,
    ],
    'local_delegateaccount_get_delegation_list' => [
        'classname' => 'local_delegateaccount\external\get_delegation_list',
        'methodname' => 'execute',
        'description' => 'Returns one page of delegations, of one person or of everyone, ready to be drawn.',
        'type' => 'read',
        'capabilities' => 'local/delegateaccount:view',
        'ajax' => true,
    ],
    'local_delegateaccount_get_delegation_ids' => [
        'classname' => 'local_delegateaccount\external\get_delegation_ids',
        'methodname' => 'execute',
        'description' => 'Returns the delegations that match the filters of a list, for an action on all of them.',
        'type' => 'read',
        'capabilities' => 'local/delegateaccount:view',
        'ajax' => true,
    ],
    'local_delegateaccount_get_activity_list' => [
        'classname' => 'local_delegateaccount\external\get_activity_list',
        'methodname' => 'execute',
        'description' => 'Returns one page of the activity of a delegation, ready to be drawn.',
        'type' => 'read',
        'capabilities' => 'local/delegateaccount:viewactivity',
        'ajax' => true,
    ],
    'local_delegateaccount_get_my_accounts' => [
        'classname' => 'local_delegateaccount\external\get_my_accounts',
        'methodname' => 'execute',
        'description' => 'Returns one page of the accounts the current person may open.',
        'type' => 'read',
        'capabilities' => 'local/delegateaccount:use',
        'ajax' => true,
    ],
    'local_delegateaccount_get_session_permissions' => [
        'classname' => 'local_delegateaccount\external\get_session_permissions',
        'methodname' => 'execute',
        'description' => 'Returns the capabilities of the site and the permission overrides of the site or of a delegation.',
        'type' => 'read',
        'capabilities' => 'local/delegateaccount:view',
        'ajax' => true,
    ],
    'local_delegateaccount_save_session_permissions' => [
        'classname' => 'local_delegateaccount\external\save_session_permissions',
        'methodname' => 'execute',
        'description' => 'Changes permission overrides of the site or of one delegation.',
        'type' => 'write',
        'capabilities' => 'local/delegateaccount:managepermissions',
        'ajax' => true,
    ],
    'local_delegateaccount_update_session_permissions' => [
        'classname' => 'local_delegateaccount\external\update_session_permissions',
        'methodname' => 'execute',
        'description' => 'Changes or copies the permission overrides of many delegations at once.',
        'type' => 'write',
        'capabilities' => 'local/delegateaccount:managepermissions',
        'ajax' => true,
    ],
];

$services = [
    'Delegated account management' => [
        'functions' => array_keys($functions),
        'restrictedusers' => 1,
        'enabled' => 0,
        'shortname' => 'local_delegateaccount_management',
        'downloadfiles' => 0,
        'uploadfiles' => 0,
    ],
];
