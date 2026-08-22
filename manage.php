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
use local_delegateaccount\manager;
use local_delegateaccount\table\delegated_users_table;

admin_externalpage_setup('local_delegateaccount_manage');
$context = context_system::instance();
if (
    !has_any_capability(
        [
            'local/delegateaccount:view',
            'local/delegateaccount:manage',
        ],
        $context
    )
) {
    require_capability('local/delegateaccount:view', $context);
}

$tab = optional_param('tab', 'authorised', PARAM_ALPHA);
if (!in_array($tab, ['authorised', 'historical'], true)) {
    $tab = 'authorised';
}
$filters = [
    'search' => optional_param('search', '', PARAM_TEXT),
    'delegationstatus' => optional_param('delegationstatus', '', PARAM_ALPHA),
];
if (
    !in_array(
        $filters['delegationstatus'],
        [
            '',
            manager::STATUS_ACTIVE,
            manager::STATUS_SCHEDULED,
            manager::STATUS_EXPIRED,
            manager::STATUS_REVOKED,
            'none',
        ],
        true
    )
) {
    $filters['delegationstatus'] = '';
}
$filterparams = array_filter($filters, static function (string $value): bool {
    return $value !== '';
});
$dashboardurl = new moodle_url('/local/delegateaccount/manage.php', ['tab' => $tab] + $filterparams);

$PAGE->set_url($dashboardurl);
$PAGE->set_title(get_string('manage_accounts', 'local_delegateaccount'));
$PAGE->set_heading(get_string('manage_accounts', 'local_delegateaccount'));
$PAGE->requires->js_call_amd('local_delegateaccount/filter_toggle', 'init');
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage_accounts', 'local_delegateaccount'));
echo $OUTPUT->render_from_template('local_delegateaccount/report_description', [
    'description' => get_string('manage_' . $tab . '_users_description', 'local_delegateaccount'),
]);

$tabs = [
    new tabobject(
        'authorised',
        new moodle_url('/local/delegateaccount/manage.php', ['tab' => 'authorised'] + $filterparams),
        get_string('manage_authorised_users', 'local_delegateaccount')
    ),
    new tabobject(
        'historical',
        new moodle_url('/local/delegateaccount/manage.php', ['tab' => 'historical'] + $filterparams),
        get_string('manage_historical_users', 'local_delegateaccount')
    ),
];
echo $OUTPUT->tabtree($tabs, $tab);

$filterform = new manage_filter_form(
    new moodle_url('/local/delegateaccount/manage.php', ['tab' => $tab]),
    ['tab' => $tab]
);
$filterform->set_data($filters);
ob_start();
$filterform->display();
$filterformhtml = ob_get_clean();
$cancreate = $tab === 'authorised' && (has_capability('local/delegateaccount:create', $context) ||
    has_capability('local/delegateaccount:manage', $context));
echo $OUTPUT->render_from_template('local_delegateaccount/manage_actions', [
    'cancreate' => $cancreate,
    'assignurl' => (new moodle_url('/local/delegateaccount/assign.php'))->out(false),
    'addlabel' => get_string('create_delegations', 'local_delegateaccount'),
    'filterlabel' => get_string('filters'),
    'filterid' => 'local-delegateaccount-manage-filters',
    'filterform' => $filterformhtml,
    'hasfilters' => !empty($filterparams),
    'showfilters' => !empty($filterparams),
    'reseturl' => (new moodle_url('/local/delegateaccount/manage.php', ['tab' => $tab]))->out(false),
    'resetlabel' => get_string('reset'),
]);

$userids = $tab === 'authorised'
    ? array_keys(manager::get_authorised_users())
    : manager::get_historical_user_ids();
$table = new delegated_users_table($dashboardurl, $userids, $filters, $tab === 'authorised', $context);
$table->out(25, true);
echo $OUTPUT->footer();
