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

use local_delegateaccount\table\delegated_accounts_table;

/**
 * Tests for the actions offered on one authorised user's delegations.
 *
 * @package    local_delegateaccount
 * @category   test
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_delegateaccount\table\delegated_accounts_table
 */
final class delegated_accounts_table_test extends \advanced_testcase {
    /** @var \stdClass Delegation shown in the table. */
    private \stdClass $delegation;

    /**
     * Creates one active delegation.
     */
    protected function setUp(): void {
        global $DB;

        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $realuser = $generator->create_user();
        $target = $generator->create_user();
        $roleid = (int)$DB->get_field('role', 'id', ['shortname' => 'manager'], MUST_EXIST);
        assign_capability('local/delegateaccount:use', CAP_ALLOW, $roleid, \context_system::instance()->id, true);
        role_assign($roleid, $realuser->id, \context_system::instance()->id);
        accesslib_clear_all_caches_for_unit_testing();

        $this->delegation = $generator->get_plugin_generator('local_delegateaccount')->create_delegation([
            'realuserid' => (int)$realuser->id,
            'delegateduserid' => (int)$target->id,
        ]);
    }

    /**
     * Rows can be selected by users who may either edit or revoke delegations.
     */
    public function test_rows_are_selectable_with_update_or_revoke(): void {
        $this->setUser($this->create_user_with(['local/delegateaccount:update']));
        $this->assertNotSame('', $this->make_table()->col_select($this->delegation));

        $this->setUser($this->create_user_with(['local/delegateaccount:revoke']));
        $this->assertNotSame('', $this->make_table()->col_select($this->delegation));

        $this->setUser($this->create_user_with(['local/delegateaccount:view']));
        $this->assertSame('', $this->make_table()->col_select($this->delegation));
    }

    /**
     * The information modal links to the full details page.
     */
    public function test_information_modal_links_to_full_details(): void {
        $this->setUser($this->create_user_with(['local/delegateaccount:view']));
        $actions = $this->make_table()->col_actions($this->delegation);

        $this->assertStringContainsString(get_string('view_full_details', 'local_delegateaccount'), $actions);
        $this->assertStringContainsString('/local/delegateaccount/pages/delegation.php', $actions);
    }

    /**
     * Creates the table for the delegation's authorised user.
     *
     * @return delegated_accounts_table Table.
     */
    private function make_table(): delegated_accounts_table {
        return new delegated_accounts_table(new \moodle_url('/'), (int)$this->delegation->realuserid);
    }

    /**
     * Creates a user with a system role that allows the given capabilities.
     *
     * @param string[] $capabilities Capabilities to allow.
     * @return \stdClass User.
     */
    private function create_user_with(array $capabilities): \stdClass {
        $user = $this->getDataGenerator()->create_user();
        $roleid = create_role('Test role ' . random_string(), 'testrole' . random_string(), '');
        foreach ($capabilities as $capability) {
            assign_capability($capability, CAP_ALLOW, $roleid, \context_system::instance()->id);
        }
        role_assign($roleid, $user->id, \context_system::instance()->id);
        accesslib_clear_all_caches_for_unit_testing();

        return $user;
    }
}
