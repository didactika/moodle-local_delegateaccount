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
 * Management page of delegated accounts: the people who may use them, the ones who lost the permission, and every delegation.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_delegateaccount\local\listing;
use local_delegateaccount\manager;
use local_delegateaccount\output\page;
use local_delegateaccount\permission;

// The page is the same node of the administration tree whatever tab a link opens, so the breadcrumb is always there.
navigation_node::override_active_url(new moodle_url('/local/delegateaccount/pages/manage.php'));
admin_externalpage_setup('local_delegateaccount_manage', '', null, new moodle_url('/local/delegateaccount/pages/manage.php'));
permission::require_action(permission::VIEW);

$tab = optional_param('tab', listing::SCOPE_AUTHORISED, PARAM_ALPHA);
if (!in_array($tab, [listing::SCOPE_AUTHORISED, listing::SCOPE_HISTORICAL, 'delegations'], true)) {
    $tab = listing::SCOPE_AUTHORISED;
}

// The page keeps the address of the administration tree whatever tab is open, so the breadcrumb is always the same; the tab
// only chooses which one is open first, and the tabs change the list without loading the page again.
$PAGE->set_url(new moodle_url('/local/delegateaccount/pages/manage.php'));
$PAGE->set_title(get_string('manage_accounts', 'local_delegateaccount'));
$PAGE->set_heading(get_string('manage_accounts', 'local_delegateaccount'));

$tabs = [];
foreach (
    [
        listing::SCOPE_AUTHORISED => 'manage_authorised_users',
        listing::SCOPE_HISTORICAL => 'manage_historical_users',
        'delegations' => 'manage_delegations_tab',
    ] as $key => $label
) {
    $tabs[] = [
        'key' => $key,
        'label' => get_string($label, 'local_delegateaccount'),
        'url' => (new moodle_url(
            '/local/delegateaccount/pages/manage.php',
            $key === listing::SCOPE_AUTHORISED ? [] : ['tab' => $key]
        ))
            ->out(false),
        'active' => $key === $tab,
        'description' => $key === 'delegations'
            ? get_string('all_delegations_description', 'local_delegateaccount')
            : get_string('manage_' . $key . '_users_description', 'local_delegateaccount'),
        'helpicon' => $key === listing::SCOPE_AUTHORISED ? manager::get_role_setup_help_icon() : '',
        'people' => $key === 'delegations' ? null : page::people_list($key, 'lda-people', false),
        'delegations' => $key === 'delegations' ? page::delegation_list(0, 'lda-delegations', false) : null,
    ];
}

$context = [
    'cancreate' => permission::has(permission::CREATE),
    'settingsurl' => has_capability('moodle/site:config', context_system::instance())
        ? (new moodle_url('/admin/settings.php', ['section' => 'local_delegateaccount_settings']))->out(false)
        : '',
    'tabs' => $tabs,
];
page::require_create();

$PAGE->requires->js_call_amd('local_delegateaccount/manage_tabs', 'init');

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_delegateaccount/manage/page', $context);
echo $OUTPUT->footer();
