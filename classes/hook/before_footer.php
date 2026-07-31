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
 * Hook listener for before_footer_html_generation.
 *
 * This class intercepts the footer generation to inject the JavaScript module
 * that builds the delegated accounts user menu.
 *
 * @package    local_delegateaccount
 * @copyright  2026, Miguel Rivas Morantes <miguelrivasmorantes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class before_footer {

    /**
     * Executes the hook callback.
     *
     * @param \core\hook\output\before_footer_html_generation $hook The hook instance.
     */
    public static function execute(\core\hook\output\before_footer_html_generation $hook): void {
        global $USER, $PAGE;

        if (!isloggedin() || isguestuser() || \core\session\manager::is_loggedinas()) {
            return;
        }

        $syscontext = \context_system::instance();
        if (!has_capability('local/delegateaccount:use', $syscontext)) {
            return;
        }

        $accounts = manager::get_delegated_accounts_for_user($USER->id);

        if (empty($accounts)) {
            return;
        }

        $delegations = [];
        foreach ($accounts as $account) {
            $fakeuser = clone($account);
            $url = new \moodle_url('/local/delegateaccount/loginas.php', [
                'id' => $account->delegateduserid,
                'sesskey' => sesskey()
            ]);

            $delegations[] = [
                'fullname' => s(fullname($fakeuser)),
                'url' => $url->out(false)
            ];
        }

        $templatedata = [
            'title' => get_string('pluginname', 'local_delegateaccount'),
            'back' => get_string('back'),
            'delegations' => $delegations
        ];

        $PAGE->requires->js_call_amd('local_delegateaccount/usermenu', 'init', [$templatedata]);
    }
}
