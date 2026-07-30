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
 * @copyright  2026, Miguel Rivas Morantes <miguelrivasmorantes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_delegateaccount\manager;

admin_externalpage_setup('local_delegateaccount_manage');
$context = context_system::instance();
require_capability('local/delegateaccount:manage', $context);

$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 20, PARAM_INT);
$search = optional_param('search', '', PARAM_TEXT);

$dashboardurl = new moodle_url('/local/delegateaccount/manage.php', [
    'page' => $page,
    'perpage' => $perpage,
    'search' => $search
]);

$PAGE->set_url($dashboardurl);
$PAGE->set_title(get_string('manage_accounts', 'local_delegateaccount'));
$PAGE->set_heading(get_string('manage_accounts', 'local_delegateaccount'));

$totalcount = manager::count_links($search);
$links = manager::get_links_paginated($page, $perpage, $search);

$linksdata = [];
foreach ($links as $link) {
    $realuser = new \stdClass();
    $deluser = new \stdClass();

    foreach ($link as $key => $value) {
        if (strpos($key, 'real') === 0 && $key !== 'realemail') {
            $realuser->{substr($key, 4)} = $value;
        } elseif (strpos($key, 'del') === 0 && $key !== 'delemail') {
            $deluser->{substr($key, 3)} = $value;
        }
    }

    $linksdata[] = [
        'id' => $link->id,
        'realname' => fullname($realuser) . " ({$link->realemail})",
        'delname' => fullname($deluser) . " ({$link->delemail})",
        'timecreated' => userdate($link->timecreated),
        'delete_url' => (new moodle_url('/local/delegateaccount/bulk_actions.php', [
            'action' => 'delete',
            'ids[]' => $link->id,
            'sesskey' => sesskey()
        ]))->out(false)
    ];
}

$pagingbar = new \core\output\paging_bar($totalcount, $page, $perpage, $PAGE->url);

$templatedata = [
    'has_links' => count($linksdata) > 0,
    'has_any_links' => manager::count_links('') > 0,
    'links' => $linksdata,
    'assign_url' => (new moodle_url('/local/delegateaccount/assign.php'))->out(false),
    'bulk_action_url' => (new moodle_url('/local/delegateaccount/bulk_actions.php'))->out(false),
    'form_url' => (new moodle_url('/local/delegateaccount/manage.php'))->out(false),
    'sesskey' => sesskey(),
    'can_manage' => true,
    'has_pagination' => $totalcount > $perpage,
    'pagination_html' => $OUTPUT->render($pagingbar),
    'search' => $search,
    'has_filters' => !empty($search),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_delegateaccount/manage', $templatedata);
echo $OUTPUT->footer();
