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
 * Tests for the capabilities prohibited in delegated sessions.
 *
 * @package    local_delegateaccount
 * @category   test
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_delegateaccount\session_capabilities
 */
final class session_capabilities_test extends \advanced_testcase {
    /**
     * Opens a delegated session for a new account and applies the restriction.
     *
     * @return \stdClass The delegated account.
     */
    private function open_delegated_session(): \stdClass {
        $this->resetAfterTest();
        $this->setAdminUser();
        $target = $this->getDataGenerator()->create_user();
        manager::start_delegated_session((int)$target->id);

        return $target;
    }

    /**
     * Until an administrator saves a list, only editing the own profile is prohibited.
     */
    public function test_default_prohibits_only_editownprofile(): void {
        $this->resetAfterTest();

        $this->assertSame(['moodle/user:editownprofile' => CAP_PROHIBIT], session_capabilities::get_permissions());
    }

    /**
     * A saved list replaces the default, and an empty list sets nothing.
     */
    public function test_saved_list_replaces_default(): void {
        $this->resetAfterTest();

        $saved = ['moodle/user:update' => CAP_PROHIBIT, 'moodle/user:changeownpassword' => CAP_ALLOW];
        set_config(session_capabilities::SETTING, json_encode($saved), 'local_delegateaccount');
        $this->assertSame($saved, session_capabilities::get_permissions());

        set_config(session_capabilities::SETTING, '[]', 'local_delegateaccount');
        $this->assertSame([], session_capabilities::get_permissions());
    }

    /**
     * Malformed names and unsupported permissions are dropped.
     */
    public function test_sanitise_drops_invalid_entries(): void {
        $this->assertSame(
            ['moodle/user:update' => CAP_ALLOW],
            session_capabilities::sanitise([
                'moodle/user:update' => CAP_ALLOW,
                'not a capability' => CAP_ALLOW,
                'moodle/user:viewdetails' => CAP_INHERIT,
                'moodle/user:delete' => CAP_PREVENT,
            ])
        );
    }

    /**
     * The own profile cannot be edited inside a delegated session, even though the account normally can.
     */
    public function test_delegated_session_cannot_edit_own_profile(): void {
        $target = $this->open_delegated_session();
        $context = \context_system::instance();

        $this->assertFalse(has_capability('moodle/user:editownprofile', $context));

        // The account owner is not affected.
        $this->assertTrue(has_capability('moodle/user:editownprofile', $context, $target));
    }

    /**
     * Changing the permissions takes effect on the next request.
     */
    public function test_setting_changes_reach_open_session(): void {
        $this->open_delegated_session();
        $context = \context_system::instance();

        set_config(
            session_capabilities::SETTING,
            json_encode(['moodle/user:changeownpassword' => CAP_PROHIBIT]),
            'local_delegateaccount'
        );
        session_capabilities::apply();

        $this->assertTrue(has_capability('moodle/user:editownprofile', $context));
        $this->assertFalse(has_capability('moodle/user:changeownpassword', $context));
    }

    /**
     * A normal login is never restricted.
     */
    public function test_normal_session_is_not_restricted(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        session_capabilities::apply();

        $this->assertTrue(has_capability('moodle/user:editownprofile', \context_system::instance()));
    }
}
