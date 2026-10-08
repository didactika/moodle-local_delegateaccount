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

/**
 * Behat data generator for delegated accounts.
 *
 * @package    local_delegateaccount
 * @category   test
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_delegateaccount_generator extends behat_generator_base {
    /**
     * Lists the entities that scenarios can create.
     *
     * @return array Entity definitions.
     */
    protected function get_creatable_entities(): array {
        $userids = ['user' => 'realuserid', 'account' => 'delegateduserid'];

        return [
            'delegations' => [
                'singular' => 'delegation',
                'datagenerator' => 'delegation',
                'required' => ['user', 'account'],
                'switchids' => $userids,
            ],
            'revocations' => [
                'singular' => 'revocation',
                'datagenerator' => 'revocation',
                'required' => ['user', 'account'],
                'switchids' => $userids,
            ],
        ];
    }

    /**
     * Returns the ID of the delegated account, looked up by username.
     *
     * @param string $username Username of the delegated account.
     * @return int User ID.
     */
    protected function get_account_id(string $username): int {
        return (int)$this->get_user_id($username);
    }
}
