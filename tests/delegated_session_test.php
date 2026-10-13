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
 * Tests for opening, verifying and changing delegated access.
 *
 * @package    local_delegateaccount
 * @category   test
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_delegateaccount\manager
 */
final class delegated_session_test extends \advanced_testcase {
    /** @var \stdClass Authorised user. */
    private \stdClass $realuser;

    /** @var \stdClass Delegated account. */
    private \stdClass $target;

    /** @var int Role that grants the use capability. */
    private int $useroleid;

    /**
     * Creates an authorised user with one active delegation.
     */
    protected function setUp(): void {
        global $DB;

        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $this->realuser = $generator->create_user();
        $this->target = $generator->create_user();

        $context = \context_system::instance();
        $this->useroleid = (int)$DB->get_field('role', 'id', ['shortname' => 'manager'], MUST_EXIST);
        assign_capability('local/delegateaccount:use', CAP_ALLOW, $this->useroleid, $context->id, true);
        role_assign($this->useroleid, $this->realuser->id, $context->id);
        accesslib_clear_all_caches_for_unit_testing();

        manager::create_delegations([(int)$this->realuser->id], [(int)$this->target->id], [
            'notificationmode' => manager::NOTIFICATION_NEVER,
        ]);
    }

    /**
     * An active delegation to an eligible account can be opened.
     */
    public function test_access_is_allowed_for_valid_delegation(): void {
        $this->assertNull(manager::get_delegated_access_error((int)$this->realuser->id, (int)$this->target->id));
    }

    /**
     * A target promoted to site administrator after the delegation was created cannot be opened.
     */
    public function test_access_is_refused_when_target_becomes_site_admin(): void {
        $this->make_site_admin($this->target);

        $this->assertSame(
            'error_privilegedtarget',
            manager::get_delegated_access_error((int)$this->realuser->id, (int)$this->target->id)
        );

        set_config('protectprivilegedtargets', 0, 'local_delegateaccount');
        $this->assertNull(manager::get_delegated_access_error((int)$this->realuser->id, (int)$this->target->id));
    }

    /**
     * Suspended and deleted targets cannot be opened.
     */
    public function test_access_is_refused_for_unavailable_targets(): void {
        global $DB;

        $DB->set_field('user', 'suspended', 1, ['id' => $this->target->id]);
        $this->assertSame(
            'error_targetunavailable',
            manager::get_delegated_access_error((int)$this->realuser->id, (int)$this->target->id)
        );

        $DB->set_field('user', 'suspended', 0, ['id' => $this->target->id]);
        delete_user($DB->get_record('user', ['id' => $this->target->id]));
        $this->assertSame(
            'error_targetunavailable',
            manager::get_delegated_access_error((int)$this->realuser->id, (int)$this->target->id)
        );
    }

    /**
     * A delegation record pointing at the guest account cannot be opened.
     */
    public function test_access_is_refused_for_guest_target(): void {
        global $DB;

        $guest = guest_user();
        $DB->insert_record('local_delegateaccount', (object)[
            'realuserid' => $this->realuser->id,
            'delegateduserid' => $guest->id,
            'timecreated' => time(),
            'timestart' => time() - 60,
        ]);

        $this->assertSame(
            'error_targetunavailable',
            manager::get_delegated_access_error((int)$this->realuser->id, (int)$guest->id)
        );
    }

    /**
     * The guest account can be neither selected nor submitted as a target.
     */
    public function test_guest_cannot_be_delegated(): void {
        $guest = guest_user();

        $this->assertArrayNotHasKey(
            (int)$guest->id,
            manager::get_delegated_account_options((int)$this->realuser->id, 'guest', 30)
        );

        try {
            manager::create_delegations([(int)$this->realuser->id], [(int)$guest->id]);
            $this->fail('The guest account must not be accepted as a delegated account.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('error_ineligibleuser', $exception->errorcode);
        }
    }

    /**
     * A delegated session survives while its delegation remains valid.
     */
    public function test_valid_delegated_session_is_kept(): void {
        $this->start_session();

        $this->assertFalse(manager::end_invalid_delegated_session());
        $this->assertTrue(\core\session\manager::is_loggedinas());
    }

    /**
     * Revoking a delegation ends the session that is using it.
     */
    public function test_delegated_session_ends_after_revocation(): void {
        global $DB;

        $this->start_session();
        $id = manager::get_current_delegation_id((int)$this->realuser->id, (int)$this->target->id);
        $DB->set_field('local_delegateaccount', 'activekey', $id, ['id' => $id]);
        $DB->set_field('local_delegateaccount', 'timerevoked', time(), ['id' => $id]);

        $this->assertTrue(manager::end_invalid_delegated_session());
        $this->assertFalse(isloggedin());
    }

    /**
     * Reaching the end date ends the session that is using the delegation.
     */
    public function test_delegated_session_ends_after_expiry(): void {
        global $DB;

        $this->start_session();
        $DB->set_field('local_delegateaccount', 'timeend', time() - 1, ['realuserid' => $this->realuser->id]);

        $this->assertTrue(manager::end_invalid_delegated_session());
        $this->assertFalse(isloggedin());
    }

    /**
     * Losing the use capability ends the session that is using the delegation.
     */
    public function test_delegated_session_ends_when_permission_is_removed(): void {
        $this->start_session();
        role_unassign($this->useroleid, $this->realuser->id, \context_system::instance()->id);
        accesslib_clear_all_caches_for_unit_testing();

        $this->assertTrue(manager::end_invalid_delegated_session());
        $this->assertFalse(isloggedin());
    }

    /**
     * A target promoted to site administrator ends the session that is using it.
     */
    public function test_delegated_session_ends_when_target_becomes_site_admin(): void {
        $this->start_session();
        $this->make_site_admin($this->target);

        $this->assertTrue(manager::end_invalid_delegated_session());
        $this->assertFalse(isloggedin());
    }

    /**
     * A core "Log in as" session is not managed by this plugin.
     */
    public function test_core_login_as_session_is_ignored(): void {
        global $DB;

        $this->setUser($this->realuser);
        \core\session\manager::loginas((int)$this->target->id, \context_system::instance());
        $DB->set_field('local_delegateaccount', 'timeend', time() - 1, ['realuserid' => $this->realuser->id]);

        $this->assertFalse(manager::end_invalid_delegated_session());
        $this->assertTrue(\core\session\manager::is_loggedinas());
    }

    /**
     * Reactivating an expired delegation counts towards the authorised user's limit.
     */
    public function test_update_reactivation_respects_delegation_limit(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $expiredtarget = $generator->create_user();
        $now = time();
        manager::create_delegations([(int)$this->realuser->id], [(int)$expiredtarget->id], [
            'timestart' => $now - 7200,
            'timeend' => $now - 3600,
            'notificationmode' => manager::NOTIFICATION_NEVER,
        ]);
        $expiredid = manager::get_current_delegation_id((int)$this->realuser->id, (int)$expiredtarget->id);
        $currentid = manager::get_current_delegation_id((int)$this->realuser->id, (int)$this->target->id);
        set_config('maxdelegationsperuser', 1, 'local_delegateaccount');

        // Changing the dates of the delegation that is already current does not count it twice.
        $this->assertTrue(manager::update_delegation($currentid, $now - 60, $now + 3600, manager::NOTIFICATION_NEVER));

        try {
            manager::update_delegation($expiredid, $now - 60, 0, manager::NOTIFICATION_NEVER);
            $this->fail('Reactivating an expired delegation must respect the per-user limit.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('error_maxdelegations', $exception->errorcode);
        }

        try {
            manager::update_delegations([$expiredid], (int)$this->realuser->id, $now - 60, 0, manager::NOTIFICATION_NEVER);
            $this->fail('Bulk reactivation must respect the per-user limit.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('error_maxdelegations', $exception->errorcode);
        }
        $this->assertSame($now - 3600, (int)$DB->get_field('local_delegateaccount', 'timeend', ['id' => $expiredid]));
    }

    /**
     * Editing a delegation re-checks the target account.
     */
    public function test_update_rejects_target_that_became_site_admin(): void {
        $this->make_site_admin($this->target);
        $id = manager::get_current_delegation_id((int)$this->realuser->id, (int)$this->target->id);

        try {
            manager::update_delegation($id, time() - 60, 0, manager::NOTIFICATION_NEVER);
            $this->fail('A delegation to a site administrator must not be edited back into use.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('error_privilegedtarget', $exception->errorcode);
        }
    }

    /**
     * Editing a delegation re-checks the authorised user's permission.
     */
    public function test_update_requires_authorised_user(): void {
        role_unassign($this->useroleid, $this->realuser->id, \context_system::instance()->id);
        accesslib_clear_all_caches_for_unit_testing();
        $id = manager::get_current_delegation_id((int)$this->realuser->id, (int)$this->target->id);

        try {
            manager::update_delegations([$id], (int)$this->realuser->id, time() - 60, 0, manager::NOTIFICATION_NEVER);
            $this->fail('A user without the use capability must not have delegations edited back into use.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('error_unauthorised_realuser', $exception->errorcode);
        }
    }

    /**
     * Opens the delegated account as the authorised user.
     */
    private function start_session(): void {
        $this->setUser($this->realuser);
        manager::start_delegated_session((int)$this->target->id);
        $this->assertTrue(\core\session\manager::is_loggedinas());
    }

    /**
     * Adds a user to the site administrators.
     *
     * @param \stdClass $user User to promote.
     */
    private function make_site_admin(\stdClass $user): void {
        global $CFG;

        set_config('siteadmins', $CFG->siteadmins . ',' . $user->id);
    }
}
