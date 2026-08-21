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
 * Displays the audit details of one delegated-account relationship.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_delegateaccount\manager;

admin_externalpage_setup('local_delegateaccount_manage');
$context = context_system::instance();
if (!has_any_capability([
    'local/delegateaccount:view',
    'local/delegateaccount:manage',
], $context)) {
    require_capability('local/delegateaccount:view', $context);
}

$realuserid = required_param('realuserid', PARAM_INT);
$delegationid = required_param('delegationid', PARAM_INT);
$delegation = $DB->get_record('local_delegateaccount', [
    'id' => $delegationid,
    'realuserid' => $realuserid,
], '*', MUST_EXIST);
$realuser = $DB->get_record('user', ['id' => $delegation->realuserid, 'deleted' => 0], '*', MUST_EXIST);
$delegateduser = $DB->get_record('user', ['id' => $delegation->delegateduserid, 'deleted' => 0], '*', MUST_EXIST);
$url = new moodle_url('/local/delegateaccount/delegation.php', [
    'realuserid' => $realuserid,
    'delegationid' => $delegationid,
]);
$backurl = new moodle_url('/local/delegateaccount/delegations.php', ['realuserid' => $realuserid]);
$title = get_string('delegation_details', 'local_delegateaccount');

$notificationkey = 'delegationnotificationmode_' . $delegation->notificationmode;
$notificationmode = get_string_manager()->string_exists($notificationkey, 'local_delegateaccount')
    ? get_string($notificationkey, 'local_delegateaccount')
    : get_string('delegationnotificationmode_never', 'local_delegateaccount');
$audituserids = array_filter([
    (int)$delegation->usercreated,
    (int)$delegation->usermodified,
    (int)$delegation->userrevoked,
]);
$auditusers = empty($audituserids) ? [] : $DB->get_records_list(
    'user',
    'id',
    $audituserids,
    '',
    'id, firstname, lastname, middlename, alternatename, firstnamephonetic, lastnamephonetic'
);
$getfullname = static function(int $userid) use ($auditusers): string {
    return isset($auditusers[$userid])
        ? fullname($auditusers[$userid])
        : get_string('delegation_unknown_user', 'local_delegateaccount');
};

$templatecontext = [
    'statuslabel' => get_string('delegation_status', 'local_delegateaccount'),
    'status' => get_string('delegation_status_' . manager::get_delegation_status($delegation), 'local_delegateaccount'),
    'authoriseduserlabel' => get_string('realuser', 'local_delegateaccount'),
    'authoriseduser' => fullname($realuser),
    'delegateduserlabel' => get_string('delegateduser', 'local_delegateaccount'),
    'delegateduser' => fullname($delegateduser),
    'startlabel' => get_string('delegation_start', 'local_delegateaccount'),
    'start' => userdate((int)$delegation->timestart),
    'endlabel' => get_string('delegation_end', 'local_delegateaccount'),
    'end' => (int)$delegation->timeend === 0
        ? get_string('delegation_no_end', 'local_delegateaccount')
        : userdate((int)$delegation->timeend),
    'notificationmodelabel' => get_string('delegationnotificationmode', 'local_delegateaccount'),
    'notificationmode' => $notificationmode,
    'createdlabel' => get_string('delegation_created', 'local_delegateaccount'),
    'created' => userdate((int)$delegation->timecreated),
    'createdbylabel' => get_string('delegation_created_by', 'local_delegateaccount'),
    'createdby' => $getfullname((int)$delegation->usercreated),
    'modifiedlabel' => get_string('delegation_modified', 'local_delegateaccount'),
    'modified' => userdate((int)$delegation->timemodified),
    'modifiedbylabel' => get_string('delegation_modified_by', 'local_delegateaccount'),
    'modifiedby' => $getfullname((int)$delegation->usermodified),
    'isrevoked' => (int)$delegation->timerevoked > 0,
    'revokedlabel' => get_string('delegation_revoked', 'local_delegateaccount'),
    'revoked' => userdate((int)$delegation->timerevoked),
    'revokedbylabel' => get_string('delegation_revoked_by', 'local_delegateaccount'),
    'revokedby' => $getfullname((int)$delegation->userrevoked),
];

$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
echo $OUTPUT->action_link(
    $backurl,
    get_string('back'),
    null,
    ['class' => 'btn btn-secondary mb-3'],
    new \pix_icon('t/left', '', 'core', ['class' => 'mr-1'])
);
echo $OUTPUT->render_from_template('local_delegateaccount/delegation_info', $templatecontext);
echo $OUTPUT->footer();
