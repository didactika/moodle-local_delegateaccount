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

use local_delegateaccount\form\assign_form;

/**
 * Tests contextual delegation target selection.
 *
 * @package    local_delegateaccount
 * @category   test
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_delegateaccount\form\assign_form
 */
final class assign_form_test extends \advanced_testcase {
    /**
     * Excludes self and every current non-revoked target from a contextual form.
     */
    public function test_contextual_targets_exclude_existing_delegations(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('notificationpolicy', manager::NOTIFICATION_NEVER, 'local_delegateaccount');
        $generator = $this->getDataGenerator();
        $authoriseduser = $generator->create_user();
        $existingtarget = $generator->create_user();
        $availabletarget = $generator->create_user();
        $context = \context_system::instance();
        $roleid = create_role('Delegation use', 'delegationuse', '');
        assign_capability('local/delegateaccount:use', CAP_ALLOW, $roleid, $context->id, true);
        role_assign($roleid, $authoriseduser->id, $context->id);
        accesslib_clear_all_caches_for_unit_testing();
        manager::create_delegations([(int)$authoriseduser->id], [(int)$existingtarget->id]);

        $options = assign_form::get_delegated_account_options((int)$authoriseduser->id);

        $this->assertArrayNotHasKey((int)$authoriseduser->id, $options);
        $this->assertArrayNotHasKey((int)$existingtarget->id, $options);
        $this->assertArrayHasKey((int)$availabletarget->id, $options);
        $this->assertSame(1, $DB->count_records('local_delegateaccount'));
    }
}
