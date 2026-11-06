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

namespace local_delegateaccount\output;

use local_delegateaccount\session_capabilities;


/**
 * The permission editor: every capability of the site with Not set, Allow, Prevent and Prohibit, searchable, filtered and
 * paged, and a floating bar that saves what changed.
 *
 * It edits the overrides of the whole site, in its tab of the settings page, or the overrides of one delegation.
 *
 * @package    local_delegateaccount
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class permission_editor implements \renderable, \templatable {
    /**
     * Creates the editor.
     *
     * @param int $delegationid Delegation whose overrides are edited, or session_capabilities::SITE.
     */
    public function __construct(
        /** @var int Delegation whose overrides are edited, or zero for the site. */
        private readonly int $delegationid,
    ) {
    }

    /**
     * What the editor starts from.
     *
     * @return array Configuration of the editor.
     */
    public function get_config(): array {
        return [
            'delegationid' => $this->delegationid,
            'site' => $this->delegationid === session_capabilities::SITE,
        ];
    }

    /**
     * Exports the editor for its template and starts its script.
     *
     * @param \renderer_base $output Renderer.
     * @return array Template context.
     */
    public function export_for_template(\renderer_base $output): array {
        global $PAGE;

        $id = \html_writer::random_id('lda-permissions');
        $PAGE->requires->js_call_amd('local_delegateaccount/permission_editor', 'init', ['#' . $id]);

        return [
            'id' => $id,
            'config' => json_encode($this->get_config()),
            'site' => $this->delegationid === session_capabilities::SITE,
        ];
    }
}
