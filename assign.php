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
 * Page to assign new delegated accounts to real users.
 *
 * @package    local_delegateaccount
 * @copyright  2026, Miguel Rivas Morantes <miguelrivasmorantes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_delegateaccount\manager;
use local_delegateaccount\form\assign_form;

admin_externalpage_setup('local_delegateaccount_manage');
require_capability('local/delegateaccount:manage', context_system::instance());

$url = new moodle_url('/local/delegateaccount/assign.php');
$dashboardurl = new moodle_url('/local/delegateaccount/manage.php');

$PAGE->set_url($url);
$PAGE->set_title(get_string('create_delegations', 'local_delegateaccount'));
$PAGE->set_heading(get_string('create_delegations', 'local_delegateaccount'));

$mform = new assign_form();

if ($mform->is_cancelled()) {
    redirect($dashboardurl);
} else if ($data = $mform->get_data()) {
    $createdcount = manager::create_delegations($data->realuserids, $data->delegateduserids);

    if ($createdcount > 0) {
        \core\notification::success(get_string('delegations_created_success', 'local_delegateaccount'));
    } else {
        \core\notification::warning(get_string('no_delegations_created', 'local_delegateaccount'));
    }
    redirect($dashboardurl);
}

echo $OUTPUT->header();
echo $OUTPUT->action_link(
    $dashboardurl,
    get_string('back'),
    null,
    ['class' => 'btn btn-secondary mb-3'],
    new \pix_icon('t/left', '', 'core', ['class' => 'mr-1'])
);
$mform->display();
echo $OUTPUT->footer();
