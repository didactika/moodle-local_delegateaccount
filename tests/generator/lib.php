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

use local_delegateaccount\manager;

/**
 * Data generator for delegated accounts.
 *
 * @package    local_delegateaccount
 * @category   test
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_delegateaccount_generator extends component_generator_base {
    /**
     * Creates an active delegation that sends no notification.
     *
     * @param array $record realuserid and delegateduserid, and optionally timestart and timeend.
     * @return stdClass Delegation record.
     */
    public function create_delegation(array $record): stdClass {
        global $DB;

        $realuserid = (int)$record['realuserid'];
        $delegateduserid = (int)$record['delegateduserid'];
        manager::create_delegations([$realuserid], [$delegateduserid], [
            'timestart' => (int)($record['timestart'] ?? time() - MINSECS),
            'timeend' => (int)($record['timeend'] ?? 0),
            'notificationmode' => manager::NOTIFICATION_NEVER,
        ]);

        return $DB->get_record('local_delegateaccount', [
            'id' => manager::get_current_delegation_id($realuserid, $delegateduserid),
        ], '*', MUST_EXIST);
    }

    /**
     * Revokes the current delegation between two users.
     *
     * @param array $record realuserid and delegateduserid.
     */
    public function create_revocation(array $record): void {
        manager::revoke_delegations([
            manager::get_current_delegation_id((int)$record['realuserid'], (int)$record['delegateduserid']),
        ]);
    }
}
