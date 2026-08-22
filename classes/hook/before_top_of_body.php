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

namespace local_delegateaccount\hook;

use local_delegateaccount\manager;

/**
 * Prepares the delegated-account user-menu submenu before body output.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class before_top_of_body {
    /**
     * Adds an inert template that AMD moves into Moodle's user-menu carousel.
     *
     * @param \core\hook\output\before_standard_top_of_body_html_generation $hook Output hook.
     */
    public static function execute(
        \core\hook\output\before_standard_top_of_body_html_generation $hook
    ): void {
        global $PAGE, $USER;

        if (
            !isloggedin() ||
            isguestuser() ||
            \core\session\manager::is_loggedinas() ||
            !has_capability('local/delegateaccount:use', \context_system::instance())
        ) {
            return;
        }

        $configuredlimit = get_config('local_delegateaccount', 'usermenulimit');
        $limit = $configuredlimit === false ? 10 : max(0, (int)$configuredlimit);
        $querylimit = $limit > 0 ? $limit + 1 : 0;
        $accounts = array_values(manager::get_delegated_accounts_for_user((int)$USER->id, $querylimit));
        if (!$accounts) {
            return;
        }

        $hasmore = $limit > 0 && count($accounts) > $limit;
        if ($hasmore) {
            $accounts = array_slice($accounts, 0, $limit);
        }
        $items = array_map(static function (\stdClass $account): array {
            return [
                'url' => (new \moodle_url('/local/delegateaccount/pages/loginas.php', [
                    'id' => (int)$account->delegateduserid,
                    'sesskey' => sesskey(),
                ]))->out(false),
                'title' => get_string(
                    'use_delegated_account',
                    'local_delegateaccount',
                    fullname($account)
                ),
                'text' => fullname($account),
            ];
        }, $accounts);

        $html = $hook->renderer->render_from_template('local_delegateaccount/usermenu/panel', [
            'title' => get_string('delegated_accounts_menu', 'local_delegateaccount'),
            'items' => $items,
            'hasmore' => $hasmore,
            'viewallurl' => (new \moodle_url('/local/delegateaccount/pages/accounts.php'))->out(false),
            'viewalllabel' => get_string('view_all_delegated_accounts', 'local_delegateaccount'),
        ]);
        $hook->add_html('<template data-region="local-delegateaccount-usermenu-source">' . $html . '</template>');
        $PAGE->requires->js_call_amd('local_delegateaccount/usermenu', 'init');
    }
}
