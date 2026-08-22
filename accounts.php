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
 * Lists every active delegated account available to the current user.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/tablelib.php');

use local_delegateaccount\table\my_delegated_accounts_table;

require_login();
require_capability('local/delegateaccount:use', context_system::instance());

$url = new moodle_url('/local/delegateaccount/accounts.php');
$title = get_string('my_delegated_accounts', 'local_delegateaccount');
$PAGE->set_context(context_system::instance());
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
echo $OUTPUT->render_from_template('local_delegateaccount/report/description', [
    'description' => get_string('my_delegated_accounts_description', 'local_delegateaccount'),
]);
$table = new my_delegated_accounts_table($url, (int)$USER->id);
$table->out(25, true);
echo $OUTPUT->footer();
