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
 * Ends delegated sessions whose delegation stopped being valid while they were open.
 *
 * @package    local_delegateaccount
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class after_config {
    /**
     * Verifies the current delegated session on every web request.
     *
     * @param \core\hook\after_config $hook Configuration hook.
     */
    public static function execute(\core\hook\after_config $hook): void {
        global $CFG;

        if (CLI_SCRIPT || during_initial_install() || !empty($CFG->upgraderunning)) {
            return;
        }

        try {
            $ended = manager::end_invalid_delegated_session();
        } catch (\Throwable $exception) {
            // Never break page loading, for example while the plugin is being upgraded.
            debugging('Delegated session check failed: ' . $exception->getMessage(), DEBUG_DEVELOPER);
            return;
        }

        if ($ended && !AJAX_SCRIPT) {
            redirect(new \moodle_url('/local/delegateaccount/pages/sessionended.php'));
        }
    }
}
