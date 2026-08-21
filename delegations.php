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
$action = optional_param('action', '', PARAM_ALPHA);
$delegationid = optional_param('delegationid', 0, PARAM_INT);
$realuser = $DB->get_record('user', ['id' => $realuserid, 'deleted' => 0], '*', MUST_EXIST);
$url = new moodle_url('/local/delegateaccount/delegations.php', ['realuserid' => $realuserid]);

if ($action === 'revoke' && data_submitted()) {
    require_capability('local/delegateaccount:revoke', $context);
    require_sesskey();

    $delegation = $DB->get_record('local_delegateaccount', [
        'id' => $delegationid,
        'realuserid' => $realuserid,
        'activekey' => 0,
    ], '*', MUST_EXIST);
    manager::revoke_delegations([(int)$delegation->id]);

    // The page may already be in its body state after the external-page setup.
    // Queue the notification explicitly so redirect() can still send its HTTP header.
    if (!isset($SESSION->notifications) || !is_array($SESSION->notifications)) {
        $SESSION->notifications = [];
    }
    $SESSION->notifications[] = (object) [
        'message' => get_string('deleted_success', 'local_delegateaccount'),
        'type' => \core\notification::SUCCESS,
    ];
    redirect($url);
}

$PAGE->set_url($url);
$PAGE->set_title(get_string('delegated_accounts_for', 'local_delegateaccount', fullname($realuser)));
$PAGE->set_heading(get_string('delegated_accounts_for', 'local_delegateaccount', fullname($realuser)));
$PAGE->requires->js_call_amd('local_delegateaccount/delegation_info', 'init');

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
echo $OUTPUT->render_from_template('local_delegateaccount/delegations_actions', [
    'backurl' => (new moodle_url('/local/delegateaccount/manage.php'))->out(false),
    'backlabel' => get_string('back'),
    'cancreate' => $cancreate,
    'assignurl' => (new moodle_url('/local/delegateaccount/assign.php', ['realuserid' => $realuserid]))->out(false),
    'addlabel' => get_string('add_delegation', 'local_delegateaccount'),
]);

$table = new delegated_accounts_table($url, $realuserid, $context);
$table->out(25, true);
echo $OUTPUT->footer();
