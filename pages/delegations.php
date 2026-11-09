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
 * The delegations of one authorised user, with what can be done to one or to many of them.
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
use local_delegateaccount\output\page;
use local_delegateaccount\permission;

admin_externalpage_setup('local_delegateaccount_manage');
permission::require_action(permission::VIEW);

$realuserid = required_param('realuserid', PARAM_INT);
$realuser = $DB->get_record('user', ['id' => $realuserid, 'deleted' => 0], '*', MUST_EXIST);
$url = new moodle_url('/local/delegateaccount/pages/delegations.php', ['realuserid' => $realuserid]);
$title = get_string('delegated_accounts_for', 'local_delegateaccount', fullname($realuser));

$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$manageurl = new moodle_url('/local/delegateaccount/pages/manage.php');
$PAGE->navbar->add(get_string('manage_accounts', 'local_delegateaccount'), $manageurl);
$PAGE->navbar->add(fullname($realuser), $url);

$authorised = manager::can_use_delegated_accounts($realuserid);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_delegateaccount/delegations/page', [
    'backurl' => (new moodle_url('/local/delegateaccount/pages/manage.php'))->out(false),
    'user' => exporter::user($realuser) + ['size' => 50],
    'authorised' => $authorised,
    'cancreate' => $authorised && permission::has(permission::CREATE),
    'create' => $authorised && permission::has(permission::CREATE) ? page::create_button($realuserid) : '',
    'realuserid' => $realuserid,
    'list' => page::delegation_list($realuserid, 'lda-delegations'),
]);
echo $OUTPUT->footer();
