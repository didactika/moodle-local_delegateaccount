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
 * Reports activity performed through one delegated account relationship.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');

use local_delegateaccount\table\delegated_activity_table;
use local_delegateaccount\form\activity_filter_form;

admin_externalpage_setup('local_delegateaccount_manage');
$context = context_system::instance();
require_capability('local/delegateaccount:viewactivity', $context);

$realuserid = required_param('realuserid', PARAM_INT);
$delegationid = required_param('delegationid', PARAM_INT);
$delegation = $DB->get_record('local_delegateaccount', [
    'id' => $delegationid,
    'realuserid' => $realuserid,
], '*', MUST_EXIST);
$realuser = $DB->get_record('user', ['id' => $realuserid, 'deleted' => 0], '*', MUST_EXIST);
$delegateduser = $DB->get_record('user', ['id' => $delegation->delegateduserid, 'deleted' => 0], '*', MUST_EXIST);

$accessend = \local_delegateaccount\manager::get_delegation_access_end($delegation);
$filterform = new activity_filter_form(new moodle_url('/local/delegateaccount/activity.php'), [
    'realuserid' => $realuserid,
    'delegationid' => $delegationid,
    'periodstart' => (int)$delegation->timestart,
    'periodend' => $accessend > 0 ? $accessend : time(),
]);
$submittedfilters = $filterform->get_data();
$datefromparam = isset($_GET['datefrom']) && !is_array($_GET['datefrom'])
    ? optional_param('datefrom', 0, PARAM_INT)
    : 0;
$datetoparam = isset($_GET['dateto']) && !is_array($_GET['dateto'])
    ? optional_param('dateto', 0, PARAM_INT)
    : 0;
$filters = [
    'datefrom' => $submittedfilters ? (int)$submittedfilters->datefrom : $datefromparam,
    'dateto' => $submittedfilters ? (int)$submittedfilters->dateto : $datetoparam,
    'component' => $submittedfilters ? trim($submittedfilters->component) : optional_param('component', '', PARAM_TEXT),
    'action' => $submittedfilters ? trim($submittedfilters->action) : optional_param('action', '', PARAM_TEXT),
];

$title = get_string('delegated_activity_for', 'local_delegateaccount', (object)[
    'authoriseduser' => fullname($realuser),
    'delegateduser' => fullname($delegateduser),
]);
$url = new moodle_url('/local/delegateaccount/activity.php', [
    'realuserid' => $realuserid,
    'delegationid' => $delegationid,
]);
foreach ($filters as $name => $value) {
    if ($value !== '' && $value !== 0) {
        $url->param($name, $value);
    }
}
$periodend = $accessend > 0
    ? userdate($accessend)
    : get_string('delegation_no_end', 'local_delegateaccount');

$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->requires->js_call_amd('local_delegateaccount/filter_toggle', 'init');

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
echo $OUTPUT->render_from_template('local_delegateaccount/report/description', [
    'description' => get_string('delegated_activity_description', 'local_delegateaccount', (object)[
        'authoriseduser' => fullname($realuser),
        'delegateduser' => fullname($delegateduser),
        'timestart' => userdate((int)$delegation->timestart),
        'timeend' => $periodend,
    ]),
]);
if ($submittedfilters || array_filter($filters, static fn($value): bool => $value !== '' && $value !== 0)) {
    $filterform->set_data($filters);
}
ob_start();
$filterform->display();
$filterformhtml = ob_get_clean();
$hasfilters = array_filter($filters, static fn($value): bool => $value !== '' && $value !== 0) !== [];
echo $OUTPUT->render_from_template('local_delegateaccount/report/activity_toolbar', [
    'backurl' => (new moodle_url('/local/delegateaccount/delegations.php', [
        'realuserid' => $realuserid,
    ]))->out(false),
    'backlabel' => get_string('back'),
    'filterid' => 'local-delegateaccount-activity-filters',
    'filterlabel' => get_string('filters'),
    'filterform' => $filterformhtml,
    'hasfilters' => $hasfilters,
    'showfilters' => $hasfilters,
    'reseturl' => (new moodle_url('/local/delegateaccount/activity.php', [
        'realuserid' => $realuserid,
        'delegationid' => $delegationid,
    ]))->out(false),
    'resetlabel' => get_string('reset'),
]);

$table = new delegated_activity_table($url, $delegation, $filters);
$table->out(25, true);
echo $OUTPUT->footer();
