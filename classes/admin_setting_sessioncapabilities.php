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

namespace local_delegateaccount;

use local_delegateaccount\output\permission_editor;

/**
 * The permission overrides of every delegated session of the site, in their tab of the settings page.
 *
 * The permission editor draws every capability with Not set, Allow, Prevent and Prohibit and saves what changes itself, from
 * its floating bar, so the list refreshes in place and the page does not reload. The setting therefore posts nothing: the
 * overrides are kept in the table of overrides as the ones of delegation zero, never in the configuration.
 *
 * @package    local_delegateaccount
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_sessioncapabilities extends \admin_setting {
    /**
     * Creates the setting.
     */
    public function __construct() {
        parent::__construct(
            'local_delegateaccount/sessioncapabilities',
            get_string('sessioncapabilities', 'local_delegateaccount'),
            get_string('sessioncapabilities_desc', 'local_delegateaccount'),
            ''
        );
    }

    /**
     * There is nothing to read: the editor reads the overrides itself.
     *
     * @return string Always empty, so the setting never counts as new.
     */
    public function get_setting() {
        return '';
    }

    /**
     * There is nothing to write: the editor saves the overrides itself.
     *
     * @param mixed $data Ignored.
     * @return string Always empty: no error.
     */
    public function write_setting($data) {
        return '';
    }

    /**
     * Draws the permission editor across the whole width of the tab.
     *
     * @param mixed $data Ignored.
     * @param string $query Admin search text.
     * @return string HTML.
     */
    public function output_html($data, $query = '') {
        global $OUTPUT;

        return \html_writer::div($OUTPUT->render(new permission_editor(session_capabilities::SITE)), '', [
            'id' => 'admin-' . $this->name,
        ]);
    }
}
