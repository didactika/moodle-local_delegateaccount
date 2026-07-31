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
 * Bulk action handler for delegated accounts management.
 *
 * @package    local_delegateaccount
 * @copyright  2026, Miguel Rivas Morantes <miguelrivasmorantes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$systemcontext = context_system::instance();
require_login();
require_capability('local/delegateaccount:manage', $systemcontext);
require_sesskey();

$PAGE->set_url(new moodle_url('/local/delegateaccount/bulk_actions.php'));
$PAGE->set_context($systemcontext);

$action = required_param('action', PARAM_ALPHA);
$ids = optional_param_array('ids', [], PARAM_INT);
$dashboardurl = new moodle_url('/local/delegateaccount/manage.php');

if (empty($ids)) {
    redirect(
        $dashboardurl,
        get_string('noselected', 'core'),
        null,
        \core\output\notification::NOTIFY_WARNING
    );
}

if ($action === 'delete') {
    \local_delegateaccount\manager::delete_delegations($ids);
    $message = get_string('deleted', 'core') . ': ' . count($ids);
    redirect($dashboardurl, $message, null, \core\output\notification::NOTIFY_SUCCESS);
}

redirect($dashboardurl);
