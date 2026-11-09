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
 * The accounts the current person may open now.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

require_login();
require_capability('local/delegateaccount:use', context_system::instance());

$url = new moodle_url('/local/delegateaccount/pages/accounts.php');
$title = get_string('my_delegated_accounts', 'local_delegateaccount');
$PAGE->set_context(context_system::instance());
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->requires->js_call_amd('local_delegateaccount/accounts', 'init', ['#lda-accounts']);

// The first page is drawn here, so the cards are there when the page opens; the script takes over from it.
$first = \local_delegateaccount\local\listing::my_accounts((int)$USER->id, '', 0, 12);
$rows = \core\session\manager::is_loggedinas() ? [] : array_map(
    fn(\stdClass $row): array => \local_delegateaccount\output\exporter::account_row($row),
    $first['rows']
);
$total = \core\session\manager::is_loggedinas() ? 0 : $first['total'];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_delegateaccount/accounts/page', [
    'id' => 'lda-accounts',
    'searchlabel' => get_string('search_accounts', 'local_delegateaccount'),
    'initial' => json_encode(['total' => $total, 'rows' => $rows]),
    'first' => ['rows' => array_map(fn(array $row): array => ['user' => $row['user'] + ['size' => 50]] + $row, $rows)],
    'empty' => $total === 0,
]);
echo $OUTPUT->footer();
