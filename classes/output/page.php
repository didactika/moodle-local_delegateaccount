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

use local_delegateaccount\external\list_service;
use local_delegateaccount\permission;

/**
 * The parts the pages of the plugin are made of: each list with what it may do, and the scripts that draw them.
 *
 * @package    local_delegateaccount
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class page {
    /**
     * The list of people of the management page.
     *
     * @param string $scope authorised or historical.
     * @param string $id Identifier of the element of the list.
     * @return array Context of local_delegateaccount/list/people.
     */
    public static function people_list(string $scope, string $id): array {
        global $PAGE;

        $PAGE->requires->js_call_amd('local_delegateaccount/people', 'init', ['#' . $id]);

        return [
            'id' => $id,
            'config' => json_encode(['scope' => $scope]),
            'searchlabel' => get_string('search_people', 'local_delegateaccount'),
        ];
    }

    /**
     * A list of delegations, of one person or of everyone.
     *
     * @param int $realuserid The authorised user, or zero for everyone.
     * @param string $id Identifier of the element of the list.
     * @return array Context of local_delegateaccount/list/delegations.
     */
    public static function delegation_list(int $realuserid, string $id): array {
        global $PAGE;

        $canupdate = permission::has(permission::UPDATE);
        $canrevoke = permission::has(permission::REVOKE);
        $canpermissions = has_capability(list_service::MANAGE_PERMISSIONS, \context_system::instance());
        $canbulk = $canupdate || $canrevoke || $canpermissions;
        $PAGE->requires->js_call_amd('local_delegateaccount/delegations', 'init', ['#' . $id]);

        return [
            'id' => $id,
            'config' => json_encode([
                'realuserid' => $realuserid,
                'canbulk' => $canbulk,
                'canpermissions' => $canpermissions,
                // On the page of one person the list opens with every status, so nothing of theirs is hidden at first.
                'status' => $realuserid > 0 ? '' : 'active',
            ]),
            'searchlabel' => get_string($realuserid > 0 ? 'search_accounts' : 'search_delegations', 'local_delegateaccount'),
            'canbulk' => $canbulk,
            'canupdate' => $canupdate,
            'canrevoke' => $canrevoke,
            'canpermissions' => $canpermissions,
        ];
    }

    /**
     * Starts the buttons that create delegations.
     */
    public static function require_create(): void {
        global $PAGE;

        $PAGE->requires->js_call_amd('local_delegateaccount/create', 'init');
    }
}
