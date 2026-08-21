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
        $now = time();

        $this->redirectEvents();
        $created = manager::create_delegations(
            [(int) $authoriseduser->id],
            [(int) $targetuser->id],
            [
                'timestart' => $now + 60,
                'timeend' => $now + 3600,
                'notificationmode' => manager::NOTIFICATION_NEVER,
            ]
        );
        $events = $this->redirectEvents();

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

        $this->redirectEvents();
        $this->assertTrue(manager::update_delegation(
            (int) $delegation->id,
            $now - 60,
            0,
            manager::NOTIFICATION_ALWAYS
        ));
        $events = $this->redirectEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(\local_delegateaccount\event\delegation_updated::class, $events[0]);
        $delegation = $DB->get_record('local_delegateaccount', ['id' => $delegation->id], '*', MUST_EXIST);
        $this->assertSame(manager::STATUS_ACTIVE, manager::get_delegation_status($delegation, $now));

        $this->redirectEvents();
        manager::revoke_delegations([(int) $delegation->id]);
        $events = $this->redirectEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(\local_delegateaccount\event\delegation_revoked::class, $events[0]);
        $delegation = $DB->get_record('local_delegateaccount', ['id' => $delegation->id], '*', MUST_EXIST);
        $this->assertSame((int) $delegation->id, (int) $delegation->activekey);
        $this->assertGreaterThan(0, (int) $delegation->timerevoked);
        $this->assertFalse(manager::delegation_exists((int) $authoriseduser->id, (int) $targetuser->id));
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
}
