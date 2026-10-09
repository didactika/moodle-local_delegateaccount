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
 * Explains why a delegated session was closed.
 *
 * Shown without login because the session has just been ended.
 *
 * @package    local_delegateaccount
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:ignore moodle.Files.RequireLogin.Missing -- The session was just ended, so there is nobody to log in.
require_once(__DIR__ . '/../../../config.php');

$title = get_string('delegated_session_ended', 'local_delegateaccount');
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/delegateaccount/pages/sessionended.php'));
$PAGE->set_pagelayout('login');
$PAGE->set_title($title);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
echo $OUTPUT->notification(
    get_string('delegated_session_ended_desc', 'local_delegateaccount'),
    \core\output\notification::NOTIFY_WARNING,
    false
);
echo $OUTPUT->single_button(new moodle_url('/login/index.php'), get_string('login'), 'get');
echo $OUTPUT->footer();
