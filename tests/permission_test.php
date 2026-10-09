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

namespace local_delegateaccount;

use local_delegateaccount\external\get_delegations;

/**
 * Tests for management permissions and the local/delegateaccount:manage alias.
 *
 * @package    local_delegateaccount
 * @category   test
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_delegateaccount\permission
 * @covers     \local_delegateaccount\external\get_delegations
 */
final class permission_test extends \advanced_testcase {
    /** @var string[] Every management action. */
    private const ACTIONS = [
        permission::VIEW,
        permission::CREATE,
        permission::UPDATE,
        permission::REVOKE,
        permission::VIEWACTIVITY,
    ];

    /**
     * A specific capability grants only its own action.
     */
    public function test_specific_capability_grants_only_its_action(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->assign_role($user, ['local/delegateaccount:view' => CAP_ALLOW]);

        $this->assertTrue(permission::has(permission::VIEW, (int)$user->id));
        $this->assertFalse(permission::has(permission::CREATE, (int)$user->id));
        $this->assertFalse(permission::has(permission::REVOKE, (int)$user->id));
    }

    /**
     * The manage capability grants every management action, but never the use capability.
     */
    public function test_manage_grants_every_management_action(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->assign_role($user, [permission::MANAGE_CAPABILITY => CAP_ALLOW]);

        foreach (self::ACTIONS as $action) {
            $this->assertTrue(permission::has($action, (int)$user->id), $action);
        }
        $this->assertFalse(has_capability('local/delegateaccount:use', \context_system::instance(), $user));
    }

    /**
     * Prevent on a specific capability in the same role overrides manage.
     */
    public function test_prevent_overrides_manage(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->assign_role($user, [
            permission::MANAGE_CAPABILITY => CAP_ALLOW,
            'local/delegateaccount:update' => CAP_PREVENT,
        ]);

        $this->assertFalse(permission::has(permission::UPDATE, (int)$user->id));
        $this->assertTrue(permission::has(permission::REVOKE, (int)$user->id));
    }

    /**
     * Prohibit on a specific capability in another role overrides manage.
     */
    public function test_prohibit_in_another_role_overrides_manage(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->assign_role($user, [permission::MANAGE_CAPABILITY => CAP_ALLOW]);
        $this->assign_role($user, ['local/delegateaccount:revoke' => CAP_PROHIBIT]);

        $this->assertFalse(permission::has(permission::REVOKE, (int)$user->id));
        $this->assertTrue(permission::has(permission::UPDATE, (int)$user->id));
    }

    /**
     * Prevent on the default authenticated-user role also overrides manage.
     */
    public function test_prevent_on_default_user_role_overrides_manage(): void {
        global $CFG;

        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->assign_role($user, [permission::MANAGE_CAPABILITY => CAP_ALLOW]);
        assign_capability(
            'local/delegateaccount:viewactivity',
            CAP_PREVENT,
            (int)$CFG->defaultuserroleid,
            \context_system::instance()->id,
            true
        );
        accesslib_clear_all_caches_for_unit_testing();

        $this->assertFalse(permission::has(permission::VIEWACTIVITY, (int)$user->id));
        $this->assertTrue(permission::has(permission::VIEW, (int)$user->id));
    }

    /**
     * A specific capability allowed by another role still grants the action.
     */
    public function test_specific_allow_in_another_role_wins_over_prevent(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->assign_role($user, [
            permission::MANAGE_CAPABILITY => CAP_ALLOW,
            'local/delegateaccount:create' => CAP_PREVENT,
        ]);
        $this->assign_role($user, ['local/delegateaccount:create' => CAP_ALLOW]);

        $this->assertTrue(permission::has(permission::CREATE, (int)$user->id));
    }

    /**
     * Web services accept manage as well, and honour an explicit refusal.
     */
    public function test_web_services_apply_the_same_rule(): void {
        $this->resetAfterTest();
        $manager = $this->getDataGenerator()->create_user();
        $this->assign_role($manager, [permission::MANAGE_CAPABILITY => CAP_ALLOW]);
        $this->setUser($manager);

        $result = get_delegations::execute(0, 25, '', '');
        $this->assertSame(0, $result['total']);

        $restricted = $this->getDataGenerator()->create_user();
        $this->assign_role($restricted, [
            permission::MANAGE_CAPABILITY => CAP_ALLOW,
            'local/delegateaccount:view' => CAP_PROHIBIT,
        ]);
        $this->setUser($restricted);

        $this->expectException(\required_capability_exception::class);
        get_delegations::execute(0, 25, '', '');
    }

    /**
     * Unknown actions are rejected.
     */
    public function test_unknown_action_is_rejected(): void {
        $this->expectException(\coding_exception::class);
        permission::has('use');
    }

    /**
     * Creates a role with the given system permissions and assigns it to a user.
     *
     * @param \stdClass $user User receiving the role.
     * @param array $permissions Permission values indexed by capability name.
     */
    private function assign_role(\stdClass $user, array $permissions): void {
        $context = \context_system::instance();
        $roleid = create_role('Test role ' . random_string(), 'testrole' . random_string(), '');
        set_role_contextlevels($roleid, [CONTEXT_SYSTEM]);
        foreach ($permissions as $capability => $permission) {
            assign_capability($capability, $permission, $roleid, $context->id, true);
        }
        role_assign($roleid, $user->id, $context->id);
        accesslib_clear_all_caches_for_unit_testing();
    }
}
