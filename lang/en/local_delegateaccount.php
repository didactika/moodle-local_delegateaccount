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
$string['active_delegations'] = 'Active delegations';
$string['add_delegation'] = 'Add delegated account';
$string['allowopenended'] = 'Allow delegations with no end date';
$string['allowopenended_desc'] = 'When disabled, every delegation must have an end date.';
$string['bulk_deleted_success'] = 'Selected delegations have been removed successfully.';
$string['confirm_bulk_delete'] = 'Are you sure you want to delete all selected delegations?';
$string['confirm_delete'] = 'Are you sure you want to delete this delegation?';
$string['create_delegations'] = 'Create New Delegations';
$string['delegateaccount:create'] = 'Create account delegations';
$string['delegateaccount:manage'] = 'Manage account delegations';
$string['delegateaccount:revoke'] = 'Revoke account delegations';
$string['delegateaccount:update'] = 'Update account delegations';
$string['delegateaccount:use'] = 'Log in as a delegated account';
$string['delegateaccount:view'] = 'View account delegations';
$string['delegateaccount:viewactivity'] = 'View delegated account activity';
$string['delegated_accounts_for'] = 'Delegated accounts for {$a}';
$string['delegateduser'] = 'Delegated Account (Target)';
$string['delegatedusers'] = 'Delegated Accounts (Targets)';
$string['delegatedusers_help'] = 'Select the destination accounts. The users selected above will be able to log in and act as these accounts.';
$string['delegation_count'] = 'Current or scheduled accounts';
$string['delegation_end'] = 'Access ends';
$string['delegation_no_end'] = 'No end date';
$string['delegation_start'] = 'Access starts';
$string['delegation_status'] = 'Status';
$string['delegation_status_active'] = 'Active';
$string['delegation_status_expired'] = 'Expired';
$string['delegation_status_revoked'] = 'Revoked';
$string['delegation_status_scheduled'] = 'Scheduled';
$string['delegationnotificationsubject'] = 'Delegated account access granted';
$string['delegations_created_success'] = 'Delegations created successfully.';
$string['delete_selected'] = 'Delete selected';
$string['deleted_success'] = 'The delegation has been removed successfully.';
$string['error_already_exists'] = 'This delegation already exists.';
$string['error_alreadyloggedinas'] = 'You must return to your original account before using a delegated account.';
$string['error_ineligibleuser'] = 'Deleted or suspended users cannot participate in a delegation.';
$string['error_invalidtemplateplaceholder'] = 'The notification template contains an unsupported placeholder: {$a}.';
$string['error_invaliduser'] = 'One or more selected users no longer exist.';
$string['error_maxbulkoperations'] = 'A bulk action cannot affect more than {$a} delegation records.';
$string['error_maxdelegations'] = 'An authorised user cannot have more than {$a} current or scheduled delegated accounts.';
$string['error_maximumduration'] = 'A delegation cannot last longer than {$a} days.';
$string['error_openendednotallowed'] = 'Delegations without an end date are not permitted.';
$string['error_privilegedtarget'] = 'Site administrator accounts cannot be delegated.';
$string['error_unauthorized'] = 'You are not allowed to access this delegated account.';
$string['eventdelegationcreated'] = 'Account delegation created';
$string['eventdelegationrevoked'] = 'Account delegation revoked';
$string['eventdelegationupdated'] = 'Account delegation updated';
$string['last_delegated_access'] = 'Last delegated access';
$string['manage_accounts'] = 'Manage Delegated Accounts';
$string['manage_user_delegations'] = 'Manage this user\'s delegated accounts';
$string['maxbulkoperations'] = 'Maximum records per bulk action';
$string['maxbulkoperations_desc'] = 'Maximum number of delegation records that one bulk action may create or revoke. Set to 0 for no limit.';
$string['maxdelegationsperuser'] = 'Maximum delegated accounts per user';
$string['maxdelegationsperuser_desc'] = 'Maximum number of current or scheduled accounts that one authorised user may access. Set to 0 for no limit.';
$string['maximumdurationdays'] = 'Maximum delegation duration';
$string['maximumdurationdays_desc'] = 'Maximum duration in days. Set to 0 for no duration limit.';
$string['messageprovider:delegationnotification'] = 'Delegated account notifications';
$string['no_delegated_access'] = 'No delegated access recorded';
$string['no_delegations'] = 'No account delegations have been created yet.';
$string['no_delegations_created'] = 'No new delegations were created (duplicates ignored).';
$string['notificationaccessends'] = 'Access ends:';
$string['notificationaccessgranted'] = 'Delegated account access has been granted.';
$string['notificationaccessstarts'] = 'Access starts:';
$string['notificationaccountaccess'] = 'You can now access {$a->delegateduser} at {$a->sitefullname}.';
$string['notificationgreeting'] = 'Hello {$a},';
$string['notificationpolicy'] = 'Notification policy';
$string['notificationpolicy_always'] = 'Always notify';
$string['notificationpolicy_desc'] = 'Controls whether the delegation form can choose to send a notification.';
$string['notificationpolicy_never'] = 'Never notify';
$string['notificationpolicy_optional'] = 'Allow the person creating the delegation to choose';
$string['notificationrecipients'] = 'Notification recipients';
$string['notificationrecipients_authorised'] = 'Authorised user only';
$string['notificationrecipients_both'] = 'Both users';
$string['notificationrecipients_desc'] = 'Choose which affected users receive a delegation notification.';
$string['notificationrecipients_target'] = 'Delegated account only';
$string['notificationsubject'] = 'Notification subject ({$a})';
$string['notificationsubject_desc'] = 'Plain-text subject used for this language when a delegated account notification is sent.';
$string['notificationsupportmessage'] = 'If you do not expect this access, contact your site administrator.';
$string['notificationtemplate'] = 'Notification template ({$a})';
$string['notificationtemplate_desc'] = 'Optional HTML content that replaces the built-in Moodle Mustache message for this language. Leave empty to use the built-in message. Available placeholders: {$a->authoriseduser}, {$a->delegateduser}, {$a->actor}, {$a->timestart}, {$a->timeend}, {$a->sitefullname}.';
$string['notifyonrevocation'] = 'Notify when a delegation is revoked';
$string['notifyonrevocation_desc'] = 'Send the configured notification to the selected recipients when access is revoked.';
$string['pluginname'] = 'Delegate account';
$string['privacy:metadata:local_delegateaccount'] = 'Stores the account delegations configured by site administrators.';
$string['privacy:metadata:local_delegateaccount:delegateduserid'] = 'The account a user may access.';
$string['privacy:metadata:local_delegateaccount:notificationmode'] = 'The notification decision selected for the delegation.';
$string['privacy:metadata:local_delegateaccount:realuserid'] = 'The user who may access a delegated account.';
$string['privacy:metadata:local_delegateaccount:timecreated'] = 'The time when the delegation was created.';
$string['privacy:metadata:local_delegateaccount:timeend'] = 'The time when the delegation expires.';
$string['privacy:metadata:local_delegateaccount:timemodified'] = 'The time when the delegation was last changed.';
$string['privacy:metadata:local_delegateaccount:timerevoked'] = 'The time when the delegation was revoked.';
$string['privacy:metadata:local_delegateaccount:timestart'] = 'The time when the delegation becomes active.';
$string['privacy:metadata:local_delegateaccount:usercreated'] = 'The administrator who created the delegation.';
$string['privacy:metadata:local_delegateaccount:usermodified'] = 'The user who last changed the delegation.';
$string['privacy:metadata:local_delegateaccount:userrevoked'] = 'The user who revoked the delegation.';
$string['privacy:path:delegations'] = 'Account delegations';
$string['privacy:role:creator'] = 'Delegation creator';
$string['privacy:role:delegateduser'] = 'Delegated account';
$string['privacy:role:modifier'] = 'Delegation modifier';
$string['privacy:role:realuser'] = 'Authorised user';
$string['privacy:role:revoker'] = 'Delegation revoker';
$string['protectprivilegedtargets'] = 'Protect site administrator accounts';
$string['protectprivilegedtargets_desc'] = 'Prevent site administrator accounts from being delegated to another user.';
$string['realuser'] = 'Real User (Main Account)';
$string['realusers'] = 'Real Users (Main Accounts)';
$string['realusers_help'] = 'Select the users who will be granted permission to log in as someone else. You can search and select multiple users.';
$string['revoke_delegation'] = 'Revoke delegation';
$string['scheduled_delegations'] = 'Scheduled delegations';
$string['search_authorised_users'] = 'Search authorised users';
$string['settings'] = 'Delegated account settings';
$string['settings_delegations'] = 'Delegation controls';
$string['settings_delegations_desc'] = 'Set the boundaries that apply whenever delegated account access is created or revoked.';
$string['settings_notifications'] = 'Notifications';
$string['settings_notifications_desc'] = 'Choose when affected users are notified and provide a template for each installed language.';
$string['timecreated'] = 'Date Created';
$string['usermenulimit'] = 'Delegated accounts shown in the user menu';
$string['usermenulimit_desc'] = 'Maximum number of active delegated accounts shown in the user menu. Set to 0 to show all active accounts.';
