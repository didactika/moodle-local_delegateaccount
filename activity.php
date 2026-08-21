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

admin_externalpage_setup('local_delegateaccount_manage');
$context = context_system::instance();
require_capability('local/delegateaccount:viewactivity', $context);

$realuserid = required_param('realuserid', PARAM_INT);
$delegateduserid = required_param('delegateduserid', PARAM_INT);
$realuser = $DB->get_record('user', ['id' => $realuserid, 'deleted' => 0], '*', MUST_EXIST);
$delegateduser = $DB->get_record('user', ['id' => $delegateduserid, 'deleted' => 0], '*', MUST_EXIST);
if (!$DB->record_exists('local_delegateaccount', [
    'realuserid' => $realuserid,
    'delegateduserid' => $delegateduserid,
])) {
    throw new moodle_exception('error_unauthorized', 'local_delegateaccount');
}

$title = get_string('delegated_activity_for', 'local_delegateaccount', (object)[
    'authoriseduser' => fullname($realuser),
    'delegateduser' => fullname($delegateduser),
]);
$url = new moodle_url('/local/delegateaccount/activity.php', [
    'realuserid' => $realuserid,
    'delegateduserid' => $delegateduserid,
]);

$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
echo $OUTPUT->render_from_template('local_delegateaccount/report_description', [
    'description' => get_string('delegated_activity_description', 'local_delegateaccount', (object)[
        'authoriseduser' => fullname($realuser),
        'delegateduser' => fullname($delegateduser),
    ]),
]);
echo $OUTPUT->action_link(
    new moodle_url('/local/delegateaccount/delegations.php', ['realuserid' => $realuserid]),
    get_string('back'),
    null,
    ['class' => 'btn btn-secondary mb-3'],
    new \pix_icon('t/left', '', 'core', ['class' => 'mr-1'])
);

$table = new delegated_activity_table($url, $realuserid, $delegateduserid);
$table->out(50, true);
echo $OUTPUT->footer();
