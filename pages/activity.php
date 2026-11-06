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
 * What was done through one delegation, read from the site's log store.
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
use local_delegateaccount\permission;

admin_externalpage_setup('local_delegateaccount_manage');
permission::require_action(permission::VIEWACTIVITY);

$realuserid = required_param('realuserid', PARAM_INT);
$delegationid = required_param('delegationid', PARAM_INT);
$delegation = $DB->get_record('local_delegateaccount', ['id' => $delegationid, 'realuserid' => $realuserid], '*', MUST_EXIST);
$realuser = $DB->get_record('user', ['id' => $realuserid, 'deleted' => 0], '*', MUST_EXIST);
$delegateduser = $DB->get_record('user', ['id' => $delegation->delegateduserid, 'deleted' => 0], '*', MUST_EXIST);

$params = ['realuserid' => $realuserid, 'delegationid' => $delegationid];
$url = new moodle_url('/local/delegateaccount/pages/activity.php', $params);
$names = (object)['authoriseduser' => fullname($realuser), 'delegateduser' => fullname($delegateduser)];
$title = get_string('delegated_activity_for', 'local_delegateaccount', $names);
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading(get_string('view_delegated_activity', 'local_delegateaccount'));
$manageurl = new moodle_url('/local/delegateaccount/pages/manage.php');
$PAGE->navbar->add(get_string('manage_accounts', 'local_delegateaccount'), $manageurl);
$PAGE->navbar->add(fullname($realuser), new moodle_url('/local/delegateaccount/pages/delegations.php', [
    'realuserid' => $realuserid,
]));
$PAGE->navbar->add(
    get_string('delegation_pair', 'local_delegateaccount', $names),
    new moodle_url('/local/delegateaccount/pages/delegation.php', $params)
);
$PAGE->navbar->add(get_string('view_delegated_activity', 'local_delegateaccount'), $url);
$PAGE->requires->js_call_amd('local_delegateaccount/activity', 'init', ['#lda-activity']);

// The date fields only offer the days of the delegation.
$accessend = manager::get_delegation_access_end($delegation);
$timezone = core_date::get_user_timezone_object();
$day = fn(int $time): string => (new DateTimeImmutable('@' . $time))->setTimezone($timezone)->format('Y-m-d');

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_delegateaccount/activity/page', [
    'id' => 'lda-activity',
    'config' => json_encode(['delegationid' => $delegationid]),
    'backurl' => (new moodle_url('/local/delegateaccount/pages/delegation.php', $params))->out(false),
    'subtitle' => get_string('activity_subtitle', 'local_delegateaccount', $names),
    'period' => get_string('activity_period', 'local_delegateaccount', (object)[
        'timestart' => userdate((int)$delegation->timestart),
        'timeend' => $accessend > 0 ? userdate($accessend) : get_string('delegation_no_end', 'local_delegateaccount'),
    ]),
    'delegateduser' => exporter::user($delegateduser) + ['size' => 50],
    'mindate' => $day((int)$delegation->timestart),
    'maxdate' => $day($accessend > 0 ? min($accessend, time()) : time()),
]);
echo $OUTPUT->footer();
