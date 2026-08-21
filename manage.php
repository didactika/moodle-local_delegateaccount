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
 * Management dashboard for delegated accounts.
 *
 * @package    local_delegateaccount
 * @author     Miguel Rivas Morantes <miguelrivasmorantes@gmail.com>
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');

use local_delegateaccount\form\manage_filter_form;
use local_delegateaccount\table\delegated_users_table;

admin_externalpage_setup('local_delegateaccount_manage');
$context = context_system::instance();
if (!has_any_capability([
    'local/delegateaccount:view',
    'local/delegateaccount:manage',
], $context)) {
    require_capability('local/delegateaccount:view', $context);
}

$search = optional_param('search', '', PARAM_TEXT);

$dashboardurl = new moodle_url('/local/delegateaccount/manage.php', [
    'search' => $search,
]);

$PAGE->set_url($dashboardurl);
$PAGE->set_title(get_string('manage_accounts', 'local_delegateaccount'));
$PAGE->set_heading(get_string('manage_accounts', 'local_delegateaccount'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage_accounts', 'local_delegateaccount'));

if (has_capability('local/delegateaccount:create', $context) ||
        has_capability('local/delegateaccount:manage', $context)) {
    echo $OUTPUT->single_button(
        new moodle_url('/local/delegateaccount/assign.php'),
        get_string('create_delegations', 'local_delegateaccount'),
        'get',
        ['class' => 'mb-3']
    );
}

$filterform = new manage_filter_form(new moodle_url('/local/delegateaccount/manage.php'));
$filterform->set_data(['search' => $search]);
$filterform->display();
if ($search !== '') {
    echo $OUTPUT->single_button(
        new moodle_url('/local/delegateaccount/manage.php'),
        get_string('clear', 'core'),
        'get',
        ['class' => 'mb-3']
    );
}

$table = new delegated_users_table($dashboardurl, $search, $context);
$table->out(25, true);
echo $OUTPUT->footer();
