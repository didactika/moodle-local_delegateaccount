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
 * Updates the lifecycle period and notification decision of one delegation.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_delegateaccount\form\edit_form;
use local_delegateaccount\manager;

admin_externalpage_setup('local_delegateaccount_manage');
$context = context_system::instance();
if (
    !has_any_capability(
        [
            'local/delegateaccount:update',
            'local/delegateaccount:manage',
        ],
        $context
    )
) {
    require_capability('local/delegateaccount:update', $context);
}

$realuserid = required_param('realuserid', PARAM_INT);
$delegationid = required_param('delegationid', PARAM_INT);
$delegation = $DB->get_record('local_delegateaccount', [
    'id' => $delegationid,
    'realuserid' => $realuserid,
    'activekey' => 0,
], '*', MUST_EXIST);
$delegateduser = $DB->get_record(
    'user',
    ['id' => $delegation->delegateduserid, 'deleted' => 0],
    '*',
    MUST_EXIST
);
$url = new moodle_url('/local/delegateaccount/pages/edit.php', [
    'realuserid' => $realuserid,
    'delegationid' => $delegationid,
]);
$backurl = new moodle_url('/local/delegateaccount/pages/delegations.php', ['realuserid' => $realuserid]);
$title = get_string('edit_delegation', 'local_delegateaccount');
$mform = new edit_form($url);

if ($mform->is_cancelled()) {
    redirect($backurl);
} else if ($data = $mform->get_data()) {
    $policy = get_config('local_delegateaccount', 'notificationpolicy') ?: manager::NOTIFICATION_OPTIONAL;
    $notificationmode = $policy === manager::NOTIFICATION_OPTIONAL
        ? $data->notificationmode
        : $policy;
    manager::update_delegation(
        $delegationid,
        (int)$data->timestart,
        (int)$data->timeend,
        $notificationmode
    );

    // The external-page setup can place the page in its body state. Store a
    // session notification instead of emitting output before the redirect.
    if (!isset($SESSION->notifications) || !is_array($SESSION->notifications)) {
        $SESSION->notifications = [];
    }
    $SESSION->notifications[] = (object) [
        'message' => get_string('delegation_updated_success', 'local_delegateaccount'),
        'type' => \core\notification::SUCCESS,
    ];
    redirect($backurl);
}

$mform->set_data([
    'timestart' => (int)$delegation->timestart,
    'timeend' => (int)$delegation->timeend,
    'notificationmode' => $delegation->notificationmode,
]);

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
echo $OUTPUT->heading(fullname($delegateduser), 3);
$mform->display();
echo $OUTPUT->footer();
