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
 * Languages configuration for the local_delegateaccount plugin.
 *
 * @package   local_delegateaccount
 * @copyright 2026, Miguel Rivas Morantes <miguelrivasmorantes@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Delegate Account';

$string['delegateaccount:use'] = 'Log in as a delegated account';
$string['delegateaccount:manage'] = 'Manage delegated accounts links';

$string['manage_accounts'] = 'Manage Delegated Accounts';
$string['link_new_accounts'] = 'Link New Accounts';
$string['no_accounts_linked'] = 'No accounts have been linked yet.';
$string['no_links_created'] = 'No new links were created (duplicates ignored).';

$string['realuser'] = 'Real User (Main Account)';
$string['realusers'] = 'Real Users (Main Accounts)';
$string['delegateduser'] = 'Delegated Account (Target)';
$string['delegatedusers'] = 'Delegated Accounts (Targets)';
$string['timecreated'] = 'Date Linked';
$string['actions'] = 'Actions';

$string['realusers_help'] = 'Select the users who will be granted permission to log in as someone else. You can search and select multiple users.';
$string['delegatedusers_help'] = 'Select the destination accounts. The users selected above will be able to log in and act as these accounts.';

$string['linked_success'] = 'Accounts linked successfully.';
$string['deleted_success'] = 'The link has been removed successfully.';
$string['bulk_deleted_success'] = 'Selected links have been removed successfully.';
$string['error_already_exists'] = 'This link already exists.';
$string['confirm_delete'] = 'Are you sure you want to delete this link?';
$string['confirm_bulk_delete'] = 'Are you sure you want to delete all selected links?';
$string['delete_selected'] = 'Delete selected';
