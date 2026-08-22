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

use core_external\external_api;
use local_delegateaccount\external\create_delegation;
use local_delegateaccount\external\create_delegations;
use local_delegateaccount\external\get_delegation_activity;
use local_delegateaccount\external\get_delegations;
use local_delegateaccount\external\get_user_delegations;
use local_delegateaccount\external\revoke_delegations;
use local_delegateaccount\external\update_delegations;

/**
 * Tests the granular delegated-account external API.
 *
 * @package    local_delegateaccount
 * @category   test
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_delegateaccount\external\create_delegation
 * @covers     \local_delegateaccount\external\create_delegations
 * @covers     \local_delegateaccount\external\get_delegation_activity
 * @covers     \local_delegateaccount\external\get_delegations
 * @covers     \local_delegateaccount\external\get_user_delegations
 * @covers     \local_delegateaccount\external\revoke_delegations
 * @covers     \local_delegateaccount\external\update_delegations
 */
final class delegation_api_test extends \advanced_testcase {
    /**
     * Verifies that every public function is backed by its own external class.
     */
    public function test_service_registry_uses_one_class_per_function(): void {
        $definitions = (static function (): array {
            require(__DIR__ . '/../db/services.php');

            return $functions;
        })();
        $expectedclasses = [
            'local_delegateaccount_get_delegations' => get_delegations::class,
            'local_delegateaccount_get_user_delegations' => get_user_delegations::class,
            'local_delegateaccount_create_delegation' => create_delegation::class,
            'local_delegateaccount_create_delegations' => create_delegations::class,
            'local_delegateaccount_update_delegations' => update_delegations::class,
            'local_delegateaccount_revoke_delegations' => revoke_delegations::class,
            'local_delegateaccount_get_delegation_activity' => get_delegation_activity::class,
        ];

        $this->assertSame(array_keys($expectedclasses), array_keys($definitions));
        foreach ($expectedclasses as $functionname => $classname) {
            $this->assertSame($classname, $definitions[$functionname]['classname']);
            $this->assertSame('execute', $definitions[$functionname]['methodname']);
            $this->assertTrue(method_exists($classname, 'execute_parameters'));
            $this->assertTrue(method_exists($classname, 'execute'));
            $this->assertTrue(method_exists($classname, 'execute_returns'));
        }
    }

    /**
     * Creates one delegation through the scalar external contract idempotently.
     */
    public function test_create_delegation_supports_single_external_requests(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('notificationpolicy', manager::NOTIFICATION_NEVER, 'local_delegateaccount');
        $generator = $this->getDataGenerator();
        $authoriseduser = $generator->create_user();
        $targetuser = $generator->create_user();
        $this->grant_capability($authoriseduser, 'local/delegateaccount:use');
        $start = time();

        $first = create_delegation::execute(
            (int)$authoriseduser->id,
            (int)$targetuser->id,
            $start,
            0,
            manager::NOTIFICATION_NEVER
        );
        $first = external_api::clean_returnvalue(create_delegation::execute_returns(), $first);
        $second = create_delegation::execute(
            (int)$authoriseduser->id,
            (int)$targetuser->id,
            $start,
            0,
            manager::NOTIFICATION_NEVER
        );
        $second = external_api::clean_returnvalue(create_delegation::execute_returns(), $second);

        $this->assertSame('created', $first['outcome']);
        $this->assertGreaterThan(0, $first['delegationid']);
        $this->assertSame((int)$authoriseduser->id, $first['realuserid']);
        $this->assertSame((int)$targetuser->id, $first['delegateduserid']);
        $this->assertSame('unchanged', $second['outcome']);
        $this->assertSame($first['delegationid'], $second['delegationid']);
        $this->assertSame(1, $DB->count_records('local_delegateaccount'));
    }

    /**
     * Requires the granular create capability for the singular external operation.
     */
    public function test_create_delegation_requires_create_capability(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $integrationuser = $generator->create_user();
        $authoriseduser = $generator->create_user();
        $targetuser = $generator->create_user();
        $this->grant_capability($integrationuser, 'local/delegateaccount:view');
        $this->setUser($integrationuser);

        $this->expectException(\required_capability_exception::class);
        create_delegation::execute(
            (int)$authoriseduser->id,
            (int)$targetuser->id,
            time()
        );
    }

    /**
     * Creates an idempotent matrix and exposes it through stable pagination.
     */
    public function test_create_and_list_delegations_are_idempotent(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('notificationpolicy', manager::NOTIFICATION_NEVER, 'local_delegateaccount');
        $generator = $this->getDataGenerator();
        $authoriseduser = $generator->create_user();
        $targetuser = $generator->create_user();
        $this->grant_capability($authoriseduser, 'local/delegateaccount:use');
        $start = time();

        $first = create_delegations::execute(
            [(int)$authoriseduser->id],
            [(int)$targetuser->id],
            $start,
            0,
            manager::NOTIFICATION_NEVER
        );
        $first = external_api::clean_returnvalue(create_delegations::execute_returns(), $first);
        $second = create_delegations::execute(
            [(int)$authoriseduser->id],
            [(int)$targetuser->id],
            $start,
            0,
            manager::NOTIFICATION_NEVER
        );
        $second = external_api::clean_returnvalue(create_delegations::execute_returns(), $second);

        $this->assertSame(1, $first['createdcount']);
        $this->assertSame('created', $first['results'][0]['outcome']);
        $this->assertSame(0, $second['createdcount']);
        $this->assertSame('unchanged', $second['results'][0]['outcome']);

        $page = get_user_delegations::execute(
            (int)$authoriseduser->id,
            0,
            25,
            manager::STATUS_ACTIVE
        );
        $page = external_api::clean_returnvalue(get_user_delegations::execute_returns(), $page);
        $this->assertSame(1, $page['total']);
        $this->assertSame((int)$targetuser->id, $page['delegations'][0]['delegateduserid']);
        $this->assertSame(manager::STATUS_ACTIVE, $page['delegations'][0]['status']);
    }

    /**
     * Requires the exact operation capability rather than the transitional manage capability.
     */
    public function test_service_functions_enforce_independent_capabilities(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $integrationuser = $generator->create_user();
        $this->grant_capability($integrationuser, 'local/delegateaccount:view');
        $this->setUser($integrationuser);

        $page = get_delegations::execute();
        $this->assertArrayHasKey('total', $page);
        $this->assertArrayHasKey('delegations', $page);

        $this->expectException(\required_capability_exception::class);
        revoke_delegations::execute([1], true);
    }

    /**
     * Requires explicit acknowledgement for a destructive service call.
     */
    public function test_service_revocation_requires_confirmation(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $this->expectException(\invalid_parameter_exception::class);
        revoke_delegations::execute([1], false);
    }

    /**
     * Applies lifecycle updates and logical revocation through independent service functions.
     */
    public function test_service_updates_and_revokes_selected_delegation(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('notificationpolicy', manager::NOTIFICATION_NEVER, 'local_delegateaccount');
        $generator = $this->getDataGenerator();
        $authoriseduser = $generator->create_user();
        $targetuser = $generator->create_user();
        $this->grant_capability($authoriseduser, 'local/delegateaccount:use');
        $start = time() - HOURSECS;

        $created = create_delegations::execute(
            [(int)$authoriseduser->id],
            [(int)$targetuser->id],
            $start,
            0,
            manager::NOTIFICATION_NEVER
        );
        $delegationid = (int)$created['results'][0]['delegationid'];
        $updated = update_delegations::execute(
            [$delegationid],
            (int)$authoriseduser->id,
            $start,
            time() + DAYSECS,
            manager::NOTIFICATION_NEVER
        );
        $updated = external_api::clean_returnvalue(update_delegations::execute_returns(), $updated);

        $this->assertSame(1, $updated['updatedcount']);
        $this->assertGreaterThan(0, (int)$DB->get_field('local_delegateaccount', 'timeend', [
            'id' => $delegationid,
        ]));

        $revoked = revoke_delegations::execute([$delegationid], true);
        $revoked = external_api::clean_returnvalue(revoke_delegations::execute_returns(), $revoked);
        $this->assertSame(1, $revoked['revokedcount']);
        $this->assertGreaterThan(0, (int)$DB->get_field('local_delegateaccount', 'timerevoked', [
            'id' => $delegationid,
        ]));
    }

    /**
     * Rejects pages larger than the public contract permits.
     */
    public function test_service_rejects_oversized_pages(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $this->expectException(\invalid_parameter_exception::class);
        get_delegations::execute(0, 101);
    }

    /**
     * Applies the configured bulk-operation boundary to external creation matrices.
     */
    public function test_service_creation_respects_bulk_limit(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('notificationpolicy', manager::NOTIFICATION_NEVER, 'local_delegateaccount');
        set_config('maxbulkoperations', 1, 'local_delegateaccount');
        $generator = $this->getDataGenerator();
        $authoriseduser = $generator->create_user();
        $firsttarget = $generator->create_user();
        $secondtarget = $generator->create_user();
        $this->grant_capability($authoriseduser, 'local/delegateaccount:use');

        $this->expectException(\moodle_exception::class);
        create_delegations::execute(
            [(int)$authoriseduser->id],
            [(int)$firsttarget->id, (int)$secondtarget->id],
            time(),
            0,
            manager::NOTIFICATION_NEVER
        );
    }

    /**
     * Grants one system capability to a test user through an isolated role.
     *
     * @param \stdClass $user User receiving the capability.
     * @param string $capability Capability name.
     */
    private function grant_capability(\stdClass $user, string $capability): void {
        $context = \context_system::instance();
        $roleid = create_role(
            'Delegated account test role ' . $user->id,
            'delegateaccounttest' . $user->id,
            ''
        );
        assign_capability($capability, CAP_ALLOW, $roleid, $context->id, true);
        role_assign($roleid, $user->id, $context->id);
        accesslib_clear_all_caches_for_unit_testing();
    }
}
