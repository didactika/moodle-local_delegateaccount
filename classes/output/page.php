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

use core_external\external_api;
use local_delegateaccount\external\get_delegation_list;
use local_delegateaccount\external\get_user_list;
use local_delegateaccount\external\list_service;
use local_delegateaccount\permission;

/**
 * The parts the pages of the plugin are made of: each list, drawn by its React component with what it may do.
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
     * @return string HTML.
     */
    public static function people_list(string $scope, string $id): string {
        return self::render('PeopleList', [
            'id' => $id,
            'scope' => $scope,
            'initial' => self::first(get_user_list::class, ['scope' => $scope]),
        ]);
    }

    /**
     * A list of delegations, of one person or of everyone.
     *
     * @param int $realuserid The authorised user, or zero for everyone.
     * @param string $id Identifier of the element of the list.
     * @return string HTML.
     */
    public static function delegation_list(int $realuserid, string $id): string {
        $canupdate = permission::has(permission::UPDATE);
        $canrevoke = permission::has(permission::REVOKE);
        $canpermissions = has_capability(list_service::MANAGE_PERMISSIONS, \context_system::instance());

        return self::render('DelegationList', [
            'id' => $id,
            'realuserid' => $realuserid,
            // On the page of one person the list opens with every status, so nothing of theirs is hidden at first.
            'status' => $realuserid > 0 ? '' : 'active',
            'canbulk' => $canupdate || $canrevoke || $canpermissions,
            'canupdate' => $canupdate,
            'canrevoke' => $canrevoke,
            'canpermissions' => $canpermissions,
            'initial' => self::first(get_delegation_list::class, ['filters' => [
                'realuserid' => $realuserid,
                'status' => $realuserid > 0 ? '' : 'active',
                'search' => '',
            ]]),
        ]);
    }

    /**
     * The first page of a list, as its web service returns it, so the page shows its rows without waiting for a request.
     *
     * @param string $service Class of the web service of the list.
     * @param array $args Its arguments for the first page; the rest take their defaults.
     * @return array|null The response, or null when it cannot be had here and the list asks for its rows itself.
     */
    private static function first(string $service, array $args): ?array {
        try {
            return external_api::clean_returnvalue($service::execute_returns(), $service::execute(...$args));
        } catch (\moodle_exception $e) {
            return null;
        }
    }

    /**
     * The button that creates delegations, for several people or for one.
     *
     * @param int $realuserid The authorised user, or zero to choose several.
     * @return string HTML.
     */
    public static function create_button(int $realuserid): string {
        return self::render('CreateButton', ['realuserid' => $realuserid]);
    }

    /**
     * Draws one React component of the plugin.
     *
     * @param string $name Name of the component.
     * @param array $props Its properties.
     * @return string HTML.
     */
    public static function render(string $name, array $props): string {
        global $OUTPUT;

        return $OUTPUT->render(new component($name, $props));
    }
}
