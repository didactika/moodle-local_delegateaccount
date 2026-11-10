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

/**
 * One delegation in depth: its details and history, and the permission overrides of the sessions it opens.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_delegateaccount\manager;
use local_delegateaccount\output\exporter;
use local_delegateaccount\output\permission_editor;
use local_delegateaccount\permission;
use local_delegateaccount\session_capabilities;

admin_externalpage_setup('local_delegateaccount_manage');
permission::require_action(permission::VIEW);

$realuserid = required_param('realuserid', PARAM_INT);
$delegationid = required_param('delegationid', PARAM_INT);
$tab = optional_param('tab', 'summary', PARAM_ALPHA) === 'permissions' ? 'permissions' : 'summary';
$delegation = $DB->get_record('local_delegateaccount', ['id' => $delegationid, 'realuserid' => $realuserid], '*', MUST_EXIST);
$realuser = $DB->get_record('user', ['id' => $delegation->realuserid, 'deleted' => 0], '*', MUST_EXIST);
$delegateduser = $DB->get_record('user', ['id' => $delegation->delegateduserid, 'deleted' => 0], '*', MUST_EXIST);

$params = ['realuserid' => $realuserid, 'delegationid' => $delegationid];
$url = new moodle_url('/local/delegateaccount/pages/delegation.php', $params + ($tab === 'summary' ? [] : ['tab' => $tab]));
$label = get_string('delegation_pair', 'local_delegateaccount', (object)[
    'authoriseduser' => fullname($realuser),
    'delegateduser' => fullname($delegateduser),
]);
$PAGE->set_url($url);
$PAGE->set_title($label);
$PAGE->set_heading(get_string('delegation_details', 'local_delegateaccount'));
$manageurl = new moodle_url('/local/delegateaccount/pages/manage.php');
$PAGE->navbar->add(get_string('manage_accounts', 'local_delegateaccount'), $manageurl);
$PAGE->navbar->add(fullname($realuser), new moodle_url('/local/delegateaccount/pages/delegations.php', [
    'realuserid' => $realuserid,
]));
$PAGE->navbar->add($label, $url);

$status = manager::get_delegation_status($delegation);
$open = $status !== manager::STATUS_REVOKED;
$displayend = manager::get_delegation_display_end($delegation);
$lastaccess = manager::get_last_delegated_access($delegation);
$notificationkey = 'delegationnotificationmode_' . $delegation->notificationmode;

// The people of the history, read at once.
$userids = array_filter([(int)$delegation->usercreated, (int)$delegation->usermodified, (int)$delegation->userrevoked]);
$namefields = 'id,' . implode(',', \core_user\fields::get_name_fields());
$people = $userids ? $DB->get_records_list('user', 'id', $userids, '', $namefields) : [];
$name = fn(int $userid): string => isset($people[$userid])
    ? fullname($people[$userid])
    : get_string('delegation_unknown_user', 'local_delegateaccount');
$history = [];
if ((int)$delegation->timerevoked > 0) {
    $history[] = [
        'title' => get_string('delegation_history_revoked', 'local_delegateaccount', $name((int)$delegation->userrevoked)),
        'meta' => userdate((int)$delegation->timerevoked),
        'tone' => 'danger',
    ];
}
if ((int)$delegation->timemodified > (int)$delegation->timecreated) {
    $history[] = [
        'title' => get_string('delegation_history_modified', 'local_delegateaccount', $name((int)$delegation->usermodified)),
        'meta' => userdate((int)$delegation->timemodified),
        'tone' => '',
    ];
}
$history[] = [
    'title' => get_string('delegation_history_created', 'local_delegateaccount', $name((int)$delegation->usercreated)),
    'meta' => userdate((int)$delegation->timecreated),
    'tone' => 'success',
];

$overrides = count(session_capabilities::get_overrides($delegationid));
$tabs = [];
foreach (['summary' => 'delegation_summary', 'permissions' => 'session_permissions'] as $key => $string) {
    $tabs[] = [
        'label' => get_string($string, 'local_delegateaccount'),
        'url' => (new moodle_url(
            '/local/delegateaccount/pages/delegation.php',
            $params + ($key === 'summary' ? [] : ['tab' => $key])
        ))
            ->out(false),
        'active' => $key === $tab,
        'count' => $key === 'permissions' ? $overrides : 0,
    ];
}

$real = exporter::user($realuser);
$real['url'] = (new moodle_url('/local/delegateaccount/pages/delegations.php', ['realuserid' => $realuserid]))->out(false);
$delegated = exporter::user($delegateduser);
$delegated['url'] = $delegated['profileurl'];
$tones = [
    manager::STATUS_ACTIVE => 'success',
    manager::STATUS_SCHEDULED => 'info',
    manager::STATUS_EXPIRED => 'warning',
    manager::STATUS_REVOKED => 'secondary',
];

$PAGE->requires->js_call_amd('local_delegateaccount/delegation', 'init', ['[data-region="delegation"]']);
$editor = $tab === 'permissions' ? $OUTPUT->render(new permission_editor($delegationid)) : '';

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_delegateaccount/delegation/page', [
    'id' => $delegationid,
    'realuserid' => $realuserid,
    'backurl' => (new moodle_url('/local/delegateaccount/pages/delegations.php', ['realuserid' => $realuserid]))->out(false),
    'label' => $label,
    'statuslabel' => get_string('delegation_status_' . $status, 'local_delegateaccount'),
    'statustone' => $tones[$status],
    'realuser' => $real,
    'delegateduser' => $delegated + ['size' => 50],
    'canupdate' => $open && permission::has(permission::UPDATE),
    'canrevoke' => $open && permission::has(permission::REVOKE),
    'activityurl' => permission::has(permission::VIEWACTIVITY)
        ? (new moodle_url('/local/delegateaccount/pages/activity.php', $params))->out(false)
        : '',
    'tabs' => $tabs,
    'summary' => $tab === 'summary',
    'details' => [
        ['label' => get_string('delegation_status', 'local_delegateaccount'),
            'value' => get_string('delegation_status_' . $status, 'local_delegateaccount')],
        ['label' => get_string('delegation_start', 'local_delegateaccount'), 'value' => userdate((int)$delegation->timestart)],
        ['label' => get_string('delegation_end', 'local_delegateaccount'), 'value' => $displayend === 0
            ? get_string('delegation_no_end', 'local_delegateaccount')
            : userdate($displayend)],
        ['label' => get_string('last_access', 'local_delegateaccount'), 'value' => $lastaccess > 0
            ? userdate($lastaccess)
            : get_string('no_delegated_access', 'local_delegateaccount')],
        ['label' => get_string('delegationnotificationmode', 'local_delegateaccount'),
            'value' => get_string_manager()->string_exists($notificationkey, 'local_delegateaccount')
                ? get_string($notificationkey, 'local_delegateaccount')
                : get_string('delegationnotificationmode_never', 'local_delegateaccount')],
        ['label' => get_string('session_permissions', 'local_delegateaccount'),
            'value' => get_string('overridecount', 'local_delegateaccount', $overrides)],
    ],
    'history' => $history,
    'editor' => $editor,
]);
echo $OUTPUT->footer();
