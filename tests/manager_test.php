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

/**
 * Tests for delegated account management.
 *
 * @package    local_delegateaccount
 * @category   test
 * @author     Miguel Rivas Morantes <miguelrivasmorantes@gmail.com>
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_delegateaccount\manager
 */
final class manager_test extends \advanced_testcase {
    /**
     * Creates non-duplicate delegations and excludes self-delegation.
     */
    public function test_create_delegations_is_idempotent_and_excludes_self_delegation(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $sourceuser = $generator->create_user();
        $targetuser = $generator->create_user();
        $this->grant_delegated_account_use($sourceuser);

        $created = manager::create_delegations(
            [(int) $sourceuser->id],
            [(int) $sourceuser->id, (int) $targetuser->id]
        );

        $this->assertSame(1, $created);
        $this->assertTrue(manager::delegation_exists((int) $sourceuser->id, (int) $targetuser->id));
        $this->assertFalse(manager::delegation_exists((int) $sourceuser->id, (int) $sourceuser->id));
        $this->assertSame(1, $DB->count_records('local_delegateaccount'));

        $this->assertSame(
            0,
            manager::create_delegations([(int) $sourceuser->id], [(int) $targetuser->id])
        );
    }

    /**
     * Deletes the selected delegations without affecting other records.
     */
    public function test_delete_delegations_only_removes_selected_records(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $sourceuser = $generator->create_user();
        $firsttarget = $generator->create_user();
        $secondtarget = $generator->create_user();
        $this->grant_delegated_account_use($sourceuser);

        manager::create_delegations(
            [(int) $sourceuser->id],
            [(int) $firsttarget->id, (int) $secondtarget->id]
        );

        $first = $DB->get_record('local_delegateaccount', [
            'realuserid' => $sourceuser->id,
            'delegateduserid' => $firsttarget->id,
        ], '*', MUST_EXIST);

        manager::delete_delegations([(int) $first->id]);

        $this->assertFalse(manager::delegation_exists((int) $sourceuser->id, (int) $firsttarget->id));
        $this->assertTrue(manager::delegation_exists((int) $sourceuser->id, (int) $secondtarget->id));
    }

    /**
     * Records the configurable lifecycle of a delegation and its audit events.
     */
    public function test_delegation_lifecycle_preserves_audit_evidence(): void {
        global $DB, $USER;

        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $authoriseduser = $generator->create_user();
        $targetuser = $generator->create_user();
        $this->grant_delegated_account_use($authoriseduser);
        $now = time();

        $eventsink = $this->redirectEvents();
        $created = manager::create_delegations(
            [(int) $authoriseduser->id],
            [(int) $targetuser->id],
            [
                'timestart' => $now + 60,
                'timeend' => $now + 3600,
                'notificationmode' => manager::NOTIFICATION_NEVER,
            ]
        );
        $events = $eventsink->get_events();

        $this->assertSame(1, $created);
        $this->assertCount(1, $events);
        $this->assertInstanceOf(\local_delegateaccount\event\delegation_created::class, $events[0]);
        $this->assertSame((int) $USER->id, (int) $events[0]->userid);
        $this->assertSame((int) $authoriseduser->id, (int) $events[0]->relateduserid);
        $this->assertSame((int) $targetuser->id, (int) $events[0]->other['delegateduserid']);

        $delegation = $DB->get_record('local_delegateaccount', [
            'realuserid' => $authoriseduser->id,
            'delegateduserid' => $targetuser->id,
        ], '*', MUST_EXIST);
        $this->assertSame(manager::STATUS_SCHEDULED, manager::get_delegation_status($delegation, $now));

        $eventsink->clear();
        $this->assertTrue(manager::update_delegation(
            (int) $delegation->id,
            $now - 60,
            0,
            manager::NOTIFICATION_ALWAYS
        ));
        $events = $eventsink->get_events();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(\local_delegateaccount\event\delegation_updated::class, $events[0]);
        $delegation = $DB->get_record('local_delegateaccount', ['id' => $delegation->id], '*', MUST_EXIST);
        $this->assertSame(manager::STATUS_ACTIVE, manager::get_delegation_status($delegation, $now));

        $eventsink->clear();
        manager::revoke_delegations([(int) $delegation->id]);
        $events = $eventsink->get_events();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(\local_delegateaccount\event\delegation_revoked::class, $events[0]);
        $delegation = $DB->get_record('local_delegateaccount', ['id' => $delegation->id], '*', MUST_EXIST);
        $this->assertSame((int) $delegation->id, (int) $delegation->activekey);
        $this->assertGreaterThan(0, (int) $delegation->timerevoked);
        $this->assertFalse(manager::delegation_exists((int) $authoriseduser->id, (int) $targetuser->id));
    }

    /**
     * Revokes every selected delegation while preserving unselected records.
     */
    public function test_bulk_revoke_only_updates_selected_delegations(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $authoriseduser = $generator->create_user();
        $firsttarget = $generator->create_user();
        $secondtarget = $generator->create_user();
        $thirdtarget = $generator->create_user();
        $this->grant_delegated_account_use($authoriseduser);

        manager::create_delegations(
            [(int) $authoriseduser->id],
            [(int) $firsttarget->id, (int) $secondtarget->id, (int) $thirdtarget->id]
        );
        $delegations = $DB->get_records('local_delegateaccount', [], 'delegateduserid ASC');
        $delegationsbyuser = [];
        foreach ($delegations as $delegation) {
            $delegationsbyuser[(int) $delegation->delegateduserid] = $delegation;
        }

        $eventsink = $this->redirectEvents();
        manager::revoke_delegations([
            (int) $delegationsbyuser[$firsttarget->id]->id,
            (int) $delegationsbyuser[$secondtarget->id]->id,
        ]);
        $events = $eventsink->get_events();

        $this->assertCount(2, $events);
        $this->assertContainsOnlyInstancesOf(\local_delegateaccount\event\delegation_revoked::class, $events);
        $this->assertFalse(manager::delegation_exists((int) $authoriseduser->id, (int) $firsttarget->id));
        $this->assertFalse(manager::delegation_exists((int) $authoriseduser->id, (int) $secondtarget->id));
        $this->assertTrue(manager::delegation_exists((int) $authoriseduser->id, (int) $thirdtarget->id));
    }

    /**
     * Limits the active delegated accounts returned for the user menu.
     */
    public function test_user_menu_query_honours_configured_limit(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('notificationpolicy', manager::NOTIFICATION_NEVER, 'local_delegateaccount');
        $generator = $this->getDataGenerator();
        $authoriseduser = $generator->create_user();
        $firsttarget = $generator->create_user();
        $secondtarget = $generator->create_user();
        $thirdtarget = $generator->create_user();
        $this->grant_delegated_account_use($authoriseduser);

        manager::create_delegations(
            [(int) $authoriseduser->id],
            [(int) $firsttarget->id, (int) $secondtarget->id, (int) $thirdtarget->id]
        );

        $this->assertCount(2, manager::get_delegated_accounts_for_user((int) $authoriseduser->id, 2));
        $this->assertCount(3, manager::get_delegated_accounts_for_user((int) $authoriseduser->id));
    }

    /**
     * Uses the earliest lifecycle boundary for activity and revocation for display.
     */
    public function test_delegation_end_boundaries_are_unambiguous(): void {
        $delegation = (object) [
            'timeend' => 2_000,
            'timerevoked' => 1_500,
        ];

        $this->assertSame(1_500, manager::get_delegation_access_end($delegation));
        $this->assertSame(1_500, manager::get_delegation_display_end($delegation));

        $delegation->timeend = 1_000;
        $this->assertSame(1_000, manager::get_delegation_access_end($delegation));
        $this->assertSame(1_500, manager::get_delegation_display_end($delegation));

        $delegation->timeend = 0;
        $delegation->timerevoked = 0;
        $this->assertSame(0, manager::get_delegation_access_end($delegation));
        $this->assertSame(0, manager::get_delegation_display_end($delegation));
    }

    /**
     * Applies the site notification policy to a requested delegation choice.
     */
    public function test_notification_policy_overrides_requested_choice(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('notificationpolicy', manager::NOTIFICATION_ALWAYS, 'local_delegateaccount');
        $generator = $this->getDataGenerator();
        $authoriseduser = $generator->create_user();
        $targetuser = $generator->create_user();
        $this->grant_delegated_account_use($authoriseduser);

        manager::create_delegations(
            [(int) $authoriseduser->id],
            [(int) $targetuser->id],
            ['notificationmode' => manager::NOTIFICATION_NEVER]
        );

        $delegation = $DB->get_record('local_delegateaccount', [
            'realuserid' => $authoriseduser->id,
            'delegateduserid' => $targetuser->id,
        ], '*', MUST_EXIST);
        $this->assertSame(manager::NOTIFICATION_ALWAYS, $delegation->notificationmode);
    }

    /**
     * Rejects an open-ended delegation when the site requires an end date.
     */
    public function test_open_ended_delegation_can_be_disabled(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('allowopenended', 0, 'local_delegateaccount');
        $generator = $this->getDataGenerator();
        $authoriseduser = $generator->create_user();
        $targetuser = $generator->create_user();
        $this->grant_delegated_account_use($authoriseduser);

        $this->expectException(\moodle_exception::class);
        manager::create_delegations([(int) $authoriseduser->id], [(int) $targetuser->id]);
    }

    /**
     * Limits the number of current or scheduled targets for each authorised user.
     */
    public function test_delegation_limit_is_enforced_per_authorised_user(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('maxdelegationsperuser', 1, 'local_delegateaccount');
        $generator = $this->getDataGenerator();
        $authoriseduser = $generator->create_user();
        $firsttarget = $generator->create_user();
        $secondtarget = $generator->create_user();
        $this->grant_delegated_account_use($authoriseduser);

        manager::create_delegations([(int) $authoriseduser->id], [(int) $firsttarget->id]);

        $this->expectException(\moodle_exception::class);
        manager::create_delegations([(int) $authoriseduser->id], [(int) $secondtarget->id]);
    }

    /**
     * Rejects suspended users and protects site administrator target accounts.
     */
    public function test_ineligible_and_privileged_targets_are_rejected(): void {
        global $DB, $USER;

        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $authoriseduser = $generator->create_user();
        $suspendedtarget = $generator->create_user();
        $this->grant_delegated_account_use($authoriseduser);
        $suspendedtarget->suspended = 1;
        $DB->update_record('user', $suspendedtarget);

        try {
            manager::create_delegations([(int) $authoriseduser->id], [(int) $suspendedtarget->id]);
            $this->fail('A suspended target must be rejected.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('error_ineligibleuser', $exception->errorcode);
        }

        $this->expectException(\moodle_exception::class);
        manager::create_delegations([(int) $authoriseduser->id], [(int) $USER->id]);
    }

    /**
     * Rejects a source account that is not allowed to use delegated accounts.
     */
    public function test_delegation_requires_authorised_source_user(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $sourceuser = $generator->create_user();
        $targetuser = $generator->create_user();

        try {
            manager::create_delegations([(int)$sourceuser->id], [(int)$targetuser->id]);
            $this->fail('A source user without the use capability must be rejected.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('error_unauthorised_realuser', $exception->errorcode);
        }
    }

    /**
     * Grants the test user the system capability required to use delegations.
     *
     * @param \stdClass $user Test user.
     */
    private function grant_delegated_account_use(\stdClass $user): void {
        global $DB;

        $context = \context_system::instance();
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'manager'], MUST_EXIST);
        assign_capability('local/delegateaccount:use', CAP_ALLOW, $roleid, $context->id, true);
        role_assign($roleid, $user->id, $context->id);
        accesslib_clear_all_caches_for_unit_testing();
    }
}
