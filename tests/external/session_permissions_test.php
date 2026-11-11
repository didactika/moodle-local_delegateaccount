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

namespace local_delegateaccount\external;

use core_external\external_api;
use local_delegateaccount\manager;
use local_delegateaccount\session_capabilities;

/**
 * Tests for the web services of the session permissions.
 *
 * @package    local_delegateaccount
 * @category   test
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_delegateaccount\external\get_session_permissions
 * @covers     \local_delegateaccount\external\save_session_permissions
 * @covers     \local_delegateaccount\external\update_session_permissions
 */
final class session_permissions_test extends \advanced_testcase {
    /**
     * Creates delegations from the administrator to some new accounts.
     *
     * @param int $count How many.
     * @return int[] Delegation identifiers.
     */
    private function create_delegations(int $count): array {
        $this->resetAfterTest();
        $this->setAdminUser();
        $targets = [];
        for ($i = 0; $i < $count; $i++) {
            $targets[] = (int)$this->getDataGenerator()->create_user()->id;
        }
        manager::create_delegations([get_admin()->id], $targets, ['notificationmode' => 'never']);

        return array_values(manager::get_current_delegation_ids([(int)get_admin()->id], $targets));
    }

    /**
     * A manager who may change session permissions but holds only some capabilities.
     *
     * @return \stdClass The user.
     */
    private function create_permissions_manager(): \stdClass {
        $user = $this->getDataGenerator()->create_user();
        $roleid = $this->getDataGenerator()->create_role();
        $system = \context_system::instance();
        foreach (['local/delegateaccount:managepermissions', 'local/delegateaccount:view'] as $capability) {
            assign_capability($capability, CAP_ALLOW, $roleid, $system->id);
        }
        role_assign($roleid, $user->id, $system->id);

        return $user;
    }

    /**
     * The editor gets every capability and the overrides of the site and of the delegation.
     */
    public function test_get(): void {
        [$id] = $this->create_delegations(1);
        session_capabilities::update(0, ['moodle/user:update' => CAP_PROHIBIT]);
        session_capabilities::update($id, ['moodle/user:editownprofile' => CAP_PREVENT]);

        $result = external_api::clean_returnvalue(
            get_session_permissions::execute_returns(),
            get_session_permissions::execute($id)
        );

        $this->assertGreaterThan(100, count($result['capabilities']));
        $this->assertSame([['capability' => 'moodle/user:editownprofile', 'permission' => CAP_PREVENT]], $result['overrides']);
        $this->assertSame([['capability' => 'moodle/user:update', 'permission' => CAP_PROHIBIT]], $result['site']);
        $this->assertTrue($result['canedit']);
    }

    /**
     * Saving changes some overrides and says which the delegation has now.
     */
    public function test_save(): void {
        [$id] = $this->create_delegations(1);

        $returns = save_session_permissions::execute_returns();
        $result = external_api::clean_returnvalue($returns, save_session_permissions::execute($id, [
            ['capability' => 'moodle/user:editownprofile', 'permission' => CAP_PROHIBIT],
            ['capability' => 'moodle/user:update', 'permission' => CAP_INHERIT],
        ]));

        $this->assertSame(1, $result['changed']);
        $this->assertSame(['moodle/user:editownprofile' => CAP_PROHIBIT], session_capabilities::get_overrides($id));
    }

    /**
     * Without local/delegateaccount:managepermissions nothing can be changed, even by someone who manages delegations.
     */
    public function test_save_requires_capability(): void {
        [$id] = $this->create_delegations(1);
        $user = $this->getDataGenerator()->create_user();
        global $DB;

        $managerrole = (int)$DB->get_field('role', 'id', ['shortname' => 'manager']);
        role_assign($managerrole, $user->id, \context_system::instance()->id);
        $this->setUser($user);

        $this->expectException(\required_capability_exception::class);
        save_session_permissions::execute($id, [['capability' => 'moodle/user:update', 'permission' => CAP_PROHIBIT]]);
    }

    /**
     * Allowing gives the capability to the session, so a person may only allow what they have; reducing is always possible.
     */
    public function test_only_own_capabilities_can_be_allowed(): void {
        [$id] = $this->create_delegations(1);
        $this->setUser($this->create_permissions_manager());

        save_session_permissions::execute($id, [['capability' => 'moodle/site:config', 'permission' => CAP_PROHIBIT]]);
        $this->assertSame(['moodle/site:config' => CAP_PROHIBIT], session_capabilities::get_overrides($id));

        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage('moodle/site:config');
        save_session_permissions::execute($id, [['capability' => 'moodle/site:config', 'permission' => CAP_ALLOW]]);
    }

    /**
     * Allowing gives a session a capability its account may not have, so only site administrators can do it.
     */
    public function test_only_administrators_can_allow(): void {
        [$id] = $this->create_delegations(1);
        $user = $this->create_permissions_manager();
        $roleid = (int)$this->getDataGenerator()->create_role();
        assign_capability('moodle/user:update', CAP_ALLOW, $roleid, \context_system::instance()->id);
        role_assign($roleid, $user->id, \context_system::instance()->id);
        $this->setUser($user);

        try {
            save_session_permissions::execute($id, [['capability' => 'moodle/user:update', 'permission' => CAP_ALLOW]]);
            $this->fail('A person who is not a site administrator allowed a capability.');
        } catch (moodle_exception $exception) {
            $this->assertStringContainsString('moodle/user:update', $exception->getMessage());
        }

        $this->setAdminUser();
        save_session_permissions::execute($id, [['capability' => 'moodle/user:update', 'permission' => CAP_ALLOW]]);
        $this->assertSame(['moodle/user:update' => CAP_ALLOW], session_capabilities::get_overrides($id));
    }

    /**
     * The site overrides are only for who configures the site.
     */
    public function test_site_requires_config(): void {
        $this->create_delegations(1);
        $this->setUser($this->create_permissions_manager());

        $this->expectException(\required_capability_exception::class);
        save_session_permissions::execute(0, [['capability' => 'moodle/user:update', 'permission' => CAP_PROHIBIT]]);
    }

    /**
     * Many delegations change in one call, chosen one by one or as every one that matches the filters of a list.
     */
    public function test_bulk(): void {
        $ids = $this->create_delegations(4);

        $result = update_session_permissions::execute(
            ['delegationids' => [$ids[0], $ids[1]], 'all' => false, 'filters' => []],
            [['capability' => 'moodle/user:update', 'permission' => CAP_PREVENT]]
        );
        $this->assertSame(2, $result['count']);
        $this->assertSame([$ids[0] => 1, $ids[1] => 1], session_capabilities::count_overrides($ids));

        $result = update_session_permissions::execute(
            ['delegationids' => [], 'all' => true, 'filters' => ['realuserid' => 0, 'status' => '', 'search' => '']],
            [['capability' => 'moodle/user:update', 'permission' => CAP_INHERIT],
                ['capability' => 'moodle/user:editownprofile', 'permission' => CAP_PROHIBIT]]
        );
        $this->assertSame(4, $result['count']);
        $this->assertSame(array_fill_keys($ids, 1), session_capabilities::count_overrides($ids));
    }

    /**
     * Copying from one delegation to many follows the same rules.
     */
    public function test_bulk_copy(): void {
        $ids = $this->create_delegations(3);
        session_capabilities::update($ids[0], ['moodle/user:update' => CAP_ALLOW]);

        $result = update_session_permissions::execute(
            ['delegationids' => [$ids[1], $ids[2]], 'all' => false, 'filters' => []],
            [],
            $ids[0],
            true
        );

        $this->assertSame(2, $result['count']);
        $this->assertSame(['moodle/user:update' => CAP_ALLOW], session_capabilities::get_overrides($ids[2]));

        // Someone who does not have the capability cannot copy an Allow of it.
        $this->setUser($this->create_permissions_manager());
        $this->expectException(\moodle_exception::class);
        update_session_permissions::execute(['delegationids' => [$ids[1]], 'all' => false, 'filters' => []], [], $ids[0], false);
    }
}
