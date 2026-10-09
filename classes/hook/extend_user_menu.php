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
     * Adds one native entry that remains useful when JavaScript is unavailable.
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

        if (!\local_delegateaccount\manager::get_delegated_accounts_for_user((int)$USER->id, 1)) {
            return;
        }

        $url = new \moodle_url('/local/delegateaccount/pages/accounts.php');
        $title = get_string('delegated_accounts_menu', 'local_delegateaccount');
        if (in_array($url->out(false), self::get_link_urls($hook), true)) {
            return;
        }

        if (method_exists($hook, 'add_menu_item')) {
            // Moodle 5.3 and later replaced add_navitem() with typed menu items.
            $hook->add_menu_item(new \core_user\output\user_action_menu\link($url, $title, null, new \pix_icon('i/switch', '')));
        } else {
            $hook->add_navitem((object) [
                'itemtype' => 'link',
                'url' => $url,
                'title' => $title,
                'pix' => 'i/switch',
            ]);
        }
    }

    /**
     * Returns the URLs of the links already in the user menu.
     *
     * @param \core_user\hook\extend_user_menu $hook User-menu extension hook.
     * @return string[] Link URLs.
     */
    public static function get_link_urls(\core_user\hook\extend_user_menu $hook): array {
        global $PAGE;

        $urls = [];
        if (method_exists($hook, 'get_menu_items')) {
            $renderer = $PAGE->get_renderer('core');
            foreach ($hook->get_menu_items() as $item) {
                if ($item instanceof \core_user\output\user_action_menu\link) {
                    $urls[] = $item->export_for_template($renderer)['url'];
                }
            }
            return $urls;
        }

        foreach ($hook->get_navitems() as $navitem) {
            if (($navitem->itemtype ?? '') === 'link' && ($navitem->url ?? null) instanceof \moodle_url) {
                $urls[] = $navitem->url->out(false);
            }
        }

        return $urls;
    }
}
