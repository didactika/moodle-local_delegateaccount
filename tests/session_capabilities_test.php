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

namespace local_delegateaccount;

/**
 * Tests for the permission overrides of delegated sessions.
 *
 * @package    local_delegateaccount
 * @category   test
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_delegateaccount\session_capabilities
 */
final class session_capabilities_test extends \advanced_testcase {
    /**
     * Creates a delegation from the administrator to a new account.
     *
     * @return array The account and the delegation identifier.
     */
    private function create_delegation(): array {
        $this->resetAfterTest();
        $this->setAdminUser();
        $target = $this->getDataGenerator()->create_user();
        manager::create_delegations([get_admin()->id], [$target->id], ['notificationmode' => 'never']);

        return [$target, manager::get_current_delegation_id((int)get_admin()->id, (int)$target->id)];
    }

    /**
     * Opens a delegated session as a person who is not an administrator, so capabilities are really checked.
     *
     * @return array The account, the delegation and the authorised user.
     */
    private function open_session(): array {
        global $DB;

        $this->resetAfterTest();
        $real = $this->getDataGenerator()->create_user();
        $target = $this->getDataGenerator()->create_user();
        $roleid = $this->getDataGenerator()->create_role();
        assign_capability('local/delegateaccount:use', CAP_ALLOW, $roleid, \context_system::instance()->id);
        role_assign($roleid, $real->id, \context_system::instance()->id);
        $this->setAdminUser();
        manager::create_delegations([$real->id], [$target->id], ['notificationmode' => 'never']);
        $delegationid = manager::get_current_delegation_id((int)$real->id, (int)$target->id);

        $this->setUser($real);
        manager::start_delegated_session((int)$target->id);
        $this->assertSame((int)$target->id, (int)$DB->get_field('user', 'id', ['id' => $target->id]));

        return [$target, $delegationid, $real];
    }

    /**
     * Nothing is overridden until someone sets an override.
     */
    public function test_default_has_no_overrides(): void {
        $this->resetAfterTest();

        $this->assertSame([], session_capabilities::get_overrides(session_capabilities::SITE));
        $this->assertSame([], session_capabilities::get_effective_permissions(0));
    }

    /**
     * The override of a delegation wins over the one of the site, and Not set removes an override.
     */
    public function test_delegation_overrides_win_over_site(): void {
        [, $delegationid] = $this->create_delegation();

        session_capabilities::update(0, [
            'moodle/user:editownprofile' => CAP_PROHIBIT,
            'moodle/user:changeownpassword' => CAP_PREVENT,
        ]);
        session_capabilities::update($delegationid, ['moodle/user:editownprofile' => CAP_ALLOW]);

        $this->assertSame([
            'moodle/user:changeownpassword' => CAP_PREVENT,
            'moodle/user:editownprofile' => CAP_ALLOW,
        ], session_capabilities::get_effective_permissions($delegationid));

        $this->assertSame(1, session_capabilities::update($delegationid, ['moodle/user:editownprofile' => CAP_INHERIT]));
        $this->assertSame(
            CAP_PROHIBIT,
            session_capabilities::get_effective_permissions($delegationid)['moodle/user:editownprofile']
        );
    }

    /**
     * Unknown capabilities and permissions cannot be set, but an override of a capability that went away can be removed.
     */
    public function test_validation(): void {
        $this->resetAfterTest();

        try {
            session_capabilities::update(0, ['local/nothing:here' => CAP_ALLOW]);
            $this->fail('An unknown capability was accepted.');
        } catch (\invalid_parameter_exception $exception) {
            $this->assertStringContainsString('local/nothing:here', $exception->getMessage() . $exception->debuginfo);
        }
        try {
            session_capabilities::update(0, ['moodle/user:update' => 5]);
            $this->fail('An unknown permission was accepted.');
        } catch (\invalid_parameter_exception $exception) {
            $this->assertNotEmpty($exception->getMessage());
        }
        $this->assertSame(
            ['local/nothing:here' => CAP_INHERIT],
            session_capabilities::validate_changes(['local/nothing:here' => 0])
        );
    }

    /**
     * Replacing the overrides of the site keeps only the new list.
     */
    public function test_replace(): void {
        $this->resetAfterTest();
        session_capabilities::update(0, ['moodle/user:update' => CAP_PROHIBIT]);

        session_capabilities::replace(0, ['moodle/user:editownprofile' => CAP_PREVENT, 'moodle/user:delete' => CAP_INHERIT]);

        $this->assertSame(['moodle/user:editownprofile' => CAP_PREVENT], session_capabilities::get_overrides(0));
    }

    /**
     * Prohibit takes a capability away from the session even though a role of the account allows it.
     */
    public function test_prohibit_in_session(): void {
        [$target, $delegationid] = $this->open_session();
        $context = \context_system::instance();
        $this->assertTrue(has_capability('moodle/user:editownprofile', $context));

        session_capabilities::update($delegationid, ['moodle/user:editownprofile' => CAP_PROHIBIT]);
        session_capabilities::apply();

        $this->assertFalse(has_capability('moodle/user:editownprofile', $context));
        // The account itself, read outside the session, keeps it.
        $this->assertTrue(has_capability('moodle/user:editownprofile', $context, $this->getDataGenerator()->create_user()));
        $this->assertNotEmpty($target);
    }

    /**
     * Prevent takes away what the roles of the account give at site level, without a role prohibiting it.
     */
    public function test_prevent_in_session(): void {
        [, $delegationid] = $this->open_session();
        $context = \context_system::instance();

        session_capabilities::update($delegationid, ['moodle/user:changeownpassword' => CAP_PREVENT]);
        session_capabilities::apply();

        $this->assertFalse(has_capability('moodle/user:changeownpassword', $context));
    }

    /**
     * Prevent leaves an override of a role in a course in place, as Prevent in a role definition does.
     */
    public function test_prevent_keeps_course_overrides(): void {
        global $DB;

        [$target, $delegationid] = $this->open_session();
        $course = $this->getDataGenerator()->create_course();
        $coursecontext = \context_course::instance($course->id);
        $studentrole = (int)$DB->get_field('role', 'id', ['shortname' => 'student']);
        $this->getDataGenerator()->enrol_user($target->id, $course->id, 'student');
        // The course gives its students this capability in an override of their role.
        assign_capability('moodle/course:viewparticipants', CAP_ALLOW, $studentrole, $coursecontext->id, true);
        session_capabilities::update($delegationid, [
            'moodle/course:viewparticipants' => CAP_PREVENT,
            'mod/forum:replypost' => CAP_PREVENT,
        ]);
        reload_all_capabilities();
        session_capabilities::apply();

        $this->assertTrue(has_capability('moodle/course:viewparticipants', $coursecontext));
        $this->assertFalse(has_capability('mod/forum:replypost', $coursecontext));
    }

    /**
     * Allow gives a capability the account does not have.
     */
    public function test_allow_in_session(): void {
        [, $delegationid] = $this->open_session();
        $context = \context_system::instance();
        $this->assertFalse(has_capability('moodle/user:viewalldetails', $context));

        session_capabilities::update($delegationid, ['moodle/user:viewalldetails' => CAP_ALLOW]);
        session_capabilities::apply();

        $this->assertTrue(has_capability('moodle/user:viewalldetails', $context));
    }

    /**
     * Applying again on the next request starts from the roles of the account, so a removed override is gone.
     */
    public function test_apply_restores_between_requests(): void {
        global $USER;

        [, $delegationid] = $this->open_session();
        $context = \context_system::instance();
        $roles = $USER->access['ra'];
        session_capabilities::update($delegationid, [
            'moodle/user:changeownpassword' => CAP_PREVENT,
            'moodle/user:update' => CAP_ALLOW,
        ]);
        session_capabilities::apply();
        session_capabilities::apply();
        $this->assertFalse(has_capability('moodle/user:changeownpassword', $context));

        session_capabilities::update($delegationid, [
            'moodle/user:changeownpassword' => CAP_INHERIT,
            'moodle/user:update' => CAP_INHERIT,
        ]);
        session_capabilities::apply();

        $this->assertTrue(has_capability('moodle/user:changeownpassword', $context));
        $this->assertFalse(has_capability('moodle/user:update', $context));
        $this->assertEquals($roles, $USER->access['ra']);
    }

    /**
     * A normal login is never changed.
     */
    public function test_normal_session_is_not_changed(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        session_capabilities::update(0, ['moodle/user:editownprofile' => CAP_PROHIBIT]);
        $this->setUser($user);

        session_capabilities::apply();

        $this->assertTrue(has_capability('moodle/user:editownprofile', \context_system::instance()));
    }

    /**
     * The session knows the delegation that opened it.
     */
    public function test_session_delegation(): void {
        [, $delegationid] = $this->open_session();

        $this->assertTrue(session_capabilities::is_delegated_session());
        $this->assertSame($delegationid, session_capabilities::get_session_delegation_id());
    }

    /**
     * Many delegations change at once, and the counts follow.
     */
    public function test_update_many_and_count(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $targets = [];
        for ($i = 0; $i < 3; $i++) {
            $targets[] = (int)$this->getDataGenerator()->create_user()->id;
        }
        manager::create_delegations([get_admin()->id], $targets, ['notificationmode' => 'never']);
        $ids = array_values(manager::get_current_delegation_ids([(int)get_admin()->id], $targets));

        $this->assertSame(3, session_capabilities::update_many(array_merge($ids, [999999]), [
            'moodle/user:editownprofile' => CAP_PROHIBIT,
            'moodle/user:update' => CAP_PREVENT,
        ]));
        $this->assertSame(array_fill_keys($ids, 2), session_capabilities::count_overrides($ids));

        session_capabilities::update_many([$ids[0]], ['moodle/user:update' => CAP_INHERIT]);
        $this->assertSame(['moodle/user:editownprofile' => CAP_PROHIBIT], session_capabilities::get_overrides($ids[0]));
    }

    /**
     * Copying adds to the overrides of the targets, or replaces them.
     */
    public function test_copy(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $targets = [];
        for ($i = 0; $i < 3; $i++) {
            $targets[] = (int)$this->getDataGenerator()->create_user()->id;
        }
        manager::create_delegations([get_admin()->id], $targets, ['notificationmode' => 'never']);
        [$source, $merge, $replace] = array_values(manager::get_current_delegation_ids([(int)get_admin()->id], $targets));
        session_capabilities::update($source, ['moodle/user:editownprofile' => CAP_PROHIBIT]);
        session_capabilities::update_many([$merge, $replace], ['moodle/user:update' => CAP_PREVENT]);

        session_capabilities::copy($source, [$merge], false);
        session_capabilities::copy($source, [$replace, $source], true);

        $this->assertSame([
            'moodle/user:editownprofile' => CAP_PROHIBIT,
            'moodle/user:update' => CAP_PREVENT,
        ], session_capabilities::get_overrides($merge));
        $this->assertSame(['moodle/user:editownprofile' => CAP_PROHIBIT], session_capabilities::get_overrides($replace));
    }

    /**
     * Every change is logged.
     */
    public function test_events(): void {
        [, $delegationid] = $this->create_delegation();
        $sink = $this->redirectEvents();

        session_capabilities::update(0, ['moodle/user:update' => CAP_PROHIBIT]);
        session_capabilities::update($delegationid, ['moodle/user:update' => CAP_ALLOW]);
        session_capabilities::update($delegationid, ['moodle/user:update' => CAP_ALLOW]);

        $events = array_values(array_filter(
            $sink->get_events(),
            fn($event) => $event instanceof event\session_permissions_updated
        ));
        $this->assertCount(2, $events);
        $this->assertSame('site', $events[0]->other['scope']);
        $this->assertSame($delegationid, (int)$events[1]->objectid);
    }
}
