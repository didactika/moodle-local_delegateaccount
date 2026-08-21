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
 * Language strings for the local_delegateaccount plugin.
 *
 * @package    local_delegateaccount
 * @author     Miguel Rivas Morantes <miguelrivasmorantes@gmail.com>
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['actions'] = 'Actions';
$string['bulk_deleted_success'] = 'Selected delegations have been removed successfully.';
$string['confirm_bulk_delete'] = 'Are you sure you want to delete all selected delegations?';
$string['confirm_delete'] = 'Are you sure you want to delete this delegation?';
$string['create_delegations'] = 'Create New Delegations';
$string['delegateaccount:manage'] = 'Manage account delegations';
$string['delegateaccount:use'] = 'Log in as a delegated account';
$string['delegateduser'] = 'Delegated Account (Target)';
$string['delegatedusers'] = 'Delegated Accounts (Targets)';
$string['delegatedusers_help'] = 'Select the destination accounts. The users selected above will be able to log in and act as these accounts.';
$string['delegations_created_success'] = 'Delegations created successfully.';
$string['delete_selected'] = 'Delete selected';
$string['deleted_success'] = 'The delegation has been removed successfully.';
$string['error_already_exists'] = 'This delegation already exists.';
$string['error_alreadyloggedinas'] = 'You must return to your original account before using a delegated account.';
$string['error_unauthorized'] = 'You are not allowed to access this delegated account.';
$string['manage_accounts'] = 'Manage Delegated Accounts';
$string['no_delegations'] = 'No account delegations have been created yet.';
$string['no_delegations_created'] = 'No new delegations were created (duplicates ignored).';
$string['pluginname'] = 'Delegate account';
$string['privacy:metadata:local_delegateaccount'] = 'Stores the account delegations configured by site administrators.';
$string['privacy:metadata:local_delegateaccount:delegateduserid'] = 'The account a user may access.';
$string['privacy:metadata:local_delegateaccount:realuserid'] = 'The user who may access a delegated account.';
$string['privacy:metadata:local_delegateaccount:timecreated'] = 'The time when the delegation was created.';
$string['privacy:metadata:local_delegateaccount:usercreated'] = 'The administrator who created the delegation.';
$string['privacy:path:delegations'] = 'Account delegations';
$string['privacy:role:creator'] = 'Delegation creator';
$string['privacy:role:delegateduser'] = 'Delegated account';
$string['privacy:role:realuser'] = 'Authorised user';
$string['realuser'] = 'Real User (Main Account)';
$string['realusers'] = 'Real Users (Main Accounts)';
$string['realusers_help'] = 'Select the users who will be granted permission to log in as someone else. You can search and select multiple users.';
$string['timecreated'] = 'Date Created';
