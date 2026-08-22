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
 * Lists and manages the delegated accounts assigned to one authorised user.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');

use local_delegateaccount\manager;
use local_delegateaccount\form\delegations_filter_form;
use local_delegateaccount\table\delegated_accounts_table;

admin_externalpage_setup('local_delegateaccount_manage');
$context = context_system::instance();
if (
    !has_any_capability(
        [
            'local/delegateaccount:view',
            'local/delegateaccount:manage',
        ],
        $context
    )
) {
    require_capability('local/delegateaccount:view', $context);
}

$realuserid = required_param('realuserid', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$delegationid = optional_param('delegationid', 0, PARAM_INT);
$status = optional_param('status', manager::STATUS_ACTIVE, PARAM_ALPHA);
$search = optional_param('search', '', PARAM_TEXT);
if (
    !in_array(
        $status,
        [
            manager::STATUS_ACTIVE,
            manager::STATUS_SCHEDULED,
            manager::STATUS_EXPIRED,
            manager::STATUS_REVOKED,
        ],
        true
    )
) {
    $status = manager::STATUS_ACTIVE;
}
$realuser = $DB->get_record('user', ['id' => $realuserid, 'deleted' => 0], '*', MUST_EXIST);
$urlparams = ['realuserid' => $realuserid, 'status' => $status];
if ($search !== '') {
    $urlparams['search'] = $search;
}
$url = new moodle_url('/local/delegateaccount/delegations.php', $urlparams);

if (in_array($action, ['revoke', 'bulk_revoke'], true) && data_submitted()) {
    require_capability('local/delegateaccount:revoke', $context);
    require_sesskey();

    if ($action === 'revoke') {
        $delegation = $DB->get_record('local_delegateaccount', [
            'id' => $delegationid,
            'realuserid' => $realuserid,
            'activekey' => 0,
        ], '*', MUST_EXIST);
        $delegationids = [(int)$delegation->id];
    } else {
        $requestedids = optional_param_array('selecteddelegations', [], PARAM_INT);
        $requestedids = array_values(array_unique(array_map('intval', $requestedids)));
        if (empty($requestedids)) {
            $delegationids = [];
        } else {
            [$insql, $inparams] = $DB->get_in_or_equal($requestedids, SQL_PARAMS_NAMED, 'selected');
            $inparams['realuserid'] = $realuserid;
            $delegations = $DB->get_records_select(
                'local_delegateaccount',
                "realuserid = :realuserid AND activekey = 0 AND id $insql",
                $inparams,
                '',
                'id'
            );
            $delegationids = array_map('intval', array_keys($delegations));
        }
    }

    if (!empty($delegationids)) {
        manager::revoke_delegations($delegationids);
    }

    // The page may already be in its body state after the external-page setup.
    // Queue the notification explicitly so redirect() can still send its HTTP header.
    if (!isset($SESSION->notifications) || !is_array($SESSION->notifications)) {
        $SESSION->notifications = [];
    }
    $SESSION->notifications[] = (object) [
        'message' => get_string('delegations_revoked_success', 'local_delegateaccount', count($delegationids)),
        'type' => empty($delegationids) ? \core\notification::WARNING : \core\notification::SUCCESS,
    ];
    redirect($url);
}

$PAGE->set_url($url);
$PAGE->set_title(get_string('delegated_accounts_for', 'local_delegateaccount', fullname($realuser)));
$PAGE->set_heading(get_string('delegated_accounts_for', 'local_delegateaccount', fullname($realuser)));
$PAGE->requires->js_call_amd('local_delegateaccount/delegation_info', 'init');
$PAGE->requires->js_call_amd('local_delegateaccount/delegation_revoke', 'init');
$PAGE->requires->js_call_amd('local_delegateaccount/filter_toggle', 'init');
$PAGE->requires->js_call_amd('local_delegateaccount/management_modals', 'init');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('delegated_accounts_for', 'local_delegateaccount', fullname($realuser)));
$isauthorised = manager::can_use_delegated_accounts($realuserid);
if (!$isauthorised) {
    echo $OUTPUT->notification(
        get_string('delegations_user_not_authorised', 'local_delegateaccount'),
        \core\output\notification::NOTIFY_INFO
    );
}

$cancreate = $isauthorised &&
    (
        has_capability('local/delegateaccount:create', $context) ||
        has_capability('local/delegateaccount:manage', $context)
    );
$statuslabels = [
    manager::STATUS_ACTIVE => get_string('delegation_status_active', 'local_delegateaccount'),
    manager::STATUS_SCHEDULED => get_string('delegation_status_scheduled', 'local_delegateaccount'),
    manager::STATUS_EXPIRED => get_string('delegation_status_expired', 'local_delegateaccount'),
    manager::STATUS_REVOKED => get_string('delegation_status_revoked', 'local_delegateaccount'),
];
$tabs = [];
foreach ($statuslabels as $statuskey => $statuslabel) {
    $tabparams = ['realuserid' => $realuserid, 'status' => $statuskey];
    if ($search !== '') {
        $tabparams['search'] = $search;
    }
    $tabs[] = new tabobject(
        'delegation-status-' . $statuskey,
        new moodle_url('/local/delegateaccount/delegations.php', $tabparams),
        $statuslabel
    );
}
echo $OUTPUT->tabtree($tabs, 'delegation-status-' . $status);

$filterform = new delegations_filter_form(
    new moodle_url('/local/delegateaccount/delegations.php'),
    ['realuserid' => $realuserid, 'status' => $status]
);
$filterform->set_data(['search' => $search]);
ob_start();
$filterform->display();
$filterformhtml = ob_get_clean();
$canrevoke = $status !== manager::STATUS_REVOKED &&
    has_capability('local/delegateaccount:revoke', $context);
$canupdate = $status !== manager::STATUS_REVOKED &&
    (
        has_capability('local/delegateaccount:update', $context) ||
        has_capability('local/delegateaccount:manage', $context)
    );
echo $OUTPUT->render_from_template('local_delegateaccount/delegation/toolbar', [
    'backurl' => (new moodle_url('/local/delegateaccount/manage.php'))->out(false),
    'backlabel' => get_string('back'),
    'canrevoke' => $canrevoke,
    'canupdate' => $canupdate,
    'realuserid' => $realuserid,
    'editselectedlabel' => get_string('edit_selected_delegations', 'local_delegateaccount'),
    'revokeselectedlabel' => get_string('revoke_selected', 'local_delegateaccount'),
    'cancreate' => $cancreate,
    'assignurl' => (new moodle_url('/local/delegateaccount/assign.php', ['realuserid' => $realuserid]))->out(false),
    'addlabel' => get_string('add_delegation', 'local_delegateaccount'),
    'filterid' => 'local-delegateaccount-delegations-filters',
    'filterlabel' => get_string('filters'),
    'filterform' => $filterformhtml,
    'hasfilters' => $search !== '',
    'showfilters' => $search !== '',
    'reseturl' => (new moodle_url('/local/delegateaccount/delegations.php', [
        'realuserid' => $realuserid,
        'status' => $status,
    ]))->out(false),
    'resetlabel' => get_string('reset'),
    'posturl' => $url->out(false),
    'sesskey' => sesskey(),
    'confirmtitle' => get_string('confirm_revoke_title', 'local_delegateaccount'),
    'confirmsingle' => get_string('confirm_revoke_single', 'local_delegateaccount'),
    'confirmbulk' => get_string('confirm_revoke_bulk', 'local_delegateaccount'),
    'confirmbutton' => get_string('revoke_delegation', 'local_delegateaccount'),
]);

$table = new delegated_accounts_table($url, $realuserid, $context, $status, $search);
$table->out(25, true);
echo $OUTPUT->footer();
