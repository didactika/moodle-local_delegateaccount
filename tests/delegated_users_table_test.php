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

use local_delegateaccount\table\delegated_users_table;

/**
 * Tests for the authorised users and users without permission tabs.
 *
 * @package    local_delegateaccount
 * @category   test
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_delegateaccount\table\delegated_users_table
 * @covers     \local_delegateaccount\manager::get_authorised_users_condition
 */
final class delegated_users_table_test extends \advanced_testcase {
    /**
     * Each user appears in exactly the tab that matches their permission.
     */
    public function test_users_are_split_between_the_tabs(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $authorised = $generator->create_user();
        $suspended = $generator->create_user();
        $former = $generator->create_user();
        $unrelated = $generator->create_user();
        $target = $generator->create_user();

        $context = \context_system::instance();
        $roleid = create_role('Delegated account user', 'delegatedaccountuser', '');
        assign_capability('local/delegateaccount:use', CAP_ALLOW, $roleid, $context->id);
        foreach ([$authorised, $suspended, $former] as $user) {
            role_assign($roleid, $user->id, $context->id);
        }
        accesslib_clear_all_caches_for_unit_testing();
        manager::create_delegations([(int)$suspended->id, (int)$former->id], [(int)$target->id], [
            'notificationmode' => manager::NOTIFICATION_NEVER,
        ]);
        $DB->set_field('user', 'suspended', 1, ['id' => $suspended->id]);
        role_unassign($roleid, $former->id, $context->id);
        accesslib_clear_all_caches_for_unit_testing();

        $authorisedtab = $this->get_user_ids(true);
        $this->assertContains((int)$authorised->id, $authorisedtab);
        $this->assertContains((int)get_admin()->id, $authorisedtab);
        $this->assertNotContains((int)$suspended->id, $authorisedtab);
        $this->assertNotContains((int)$former->id, $authorisedtab);
        $this->assertNotContains((int)$unrelated->id, $authorisedtab);

        $historicaltab = $this->get_user_ids(false);
        $this->assertEqualsCanonicalizing([(int)$suspended->id, (int)$former->id], $historicaltab);
    }

    /**
     * Returns the user IDs listed by one tab.
     *
     * @param bool $authorised Whether to read the authorised users tab.
     * @return int[] User IDs.
     */
    private function get_user_ids(bool $authorised): array {
        $table = new delegated_users_table(new \moodle_url('/'), $authorised, [
            'search' => '',
            'delegationstatus' => '',
        ]);
        $table->setup();
        $table->query_db(100);

        return array_map(static fn(\stdClass $row): int => (int)$row->id, array_values($table->rawdata));
    }
}
