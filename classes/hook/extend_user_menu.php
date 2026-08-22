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
 * Adds active delegated accounts through Moodle's supported user-menu hook.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class extend_user_menu {
    /**
     * Adds one native user-menu link for every visible active delegation.
     *
     * @param \core_user\hook\extend_user_menu $hook User-menu extension hook.
     */
    public static function execute(\core_user\hook\extend_user_menu $hook): void {
        global $USER;

        if (!isloggedin() || isguestuser() || \core\session\manager::is_loggedinas()) {
            return;
        }

        if (!has_capability('local/delegateaccount:use', \context_system::instance())) {
            return;
        }

        $configuredlimit = get_config('local_delegateaccount', 'usermenulimit');
        $limit = $configuredlimit === false ? 10 : max(0, (int)$configuredlimit);
        $accounts = manager::get_delegated_accounts_for_user((int)$USER->id, $limit);

        foreach ($accounts as $account) {
            $item = (object) [
                'itemtype' => 'link',
                'url' => new \moodle_url('/local/delegateaccount/loginas.php', [
                    'id' => (int)$account->delegateduserid,
                    'sesskey' => sesskey(),
                ]),
                'title' => get_string(
                    'use_delegated_account',
                    'local_delegateaccount',
                    fullname($account)
                ),
                'pix' => 'i/switch',
            ];
            $hook->add_navitem($item);
        }
    }
}
