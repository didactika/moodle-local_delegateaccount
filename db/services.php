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
        'classname' => 'local_delegateaccount\external\delegation_api',
        'methodname' => 'get_delegations',
        'description' => 'Returns a paginated delegation inventory.',
        'type' => 'read',
        'capabilities' => 'local/delegateaccount:view',
    ],
    'local_delegateaccount_get_user_delegations' => [
        'classname' => 'local_delegateaccount\external\delegation_api',
        'methodname' => 'get_user_delegations',
        'description' => 'Returns one authorised user\'s paginated delegation history.',
        'type' => 'read',
        'capabilities' => 'local/delegateaccount:view',
    ],
    'local_delegateaccount_create_delegation' => [
        'classname' => 'local_delegateaccount\external\delegation_api',
        'methodname' => 'create_delegation',
        'description' => 'Creates one idempotent delegated-account relationship.',
        'type' => 'write',
        'capabilities' => 'local/delegateaccount:create',
    ],
    'local_delegateaccount_create_delegations' => [
        'classname' => 'local_delegateaccount\external\delegation_api',
        'methodname' => 'create_delegations',
        'description' => 'Creates an idempotent matrix of delegated-account relationships.',
        'type' => 'write',
        'capabilities' => 'local/delegateaccount:create',
    ],
    'local_delegateaccount_update_delegations' => [
        'classname' => 'local_delegateaccount\external\delegation_api',
        'methodname' => 'update_delegations',
        'description' => 'Updates the lifecycle configuration of selected delegations.',
        'type' => 'write',
        'capabilities' => 'local/delegateaccount:update',
    ],
    'local_delegateaccount_revoke_delegations' => [
        'classname' => 'local_delegateaccount\external\delegation_api',
        'methodname' => 'revoke_delegations',
        'description' => 'Revokes selected delegations after explicit confirmation.',
        'type' => 'write',
        'capabilities' => 'local/delegateaccount:revoke',
    ],
    'local_delegateaccount_get_delegation_activity' => [
        'classname' => 'local_delegateaccount\external\delegation_api',
        'methodname' => 'get_delegation_activity',
        'description' => 'Returns paginated standard-log activity for one delegation period.',
        'type' => 'read',
        'capabilities' => 'local/delegateaccount:viewactivity',
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
