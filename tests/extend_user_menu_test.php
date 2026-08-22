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
 * Tests the supported Moodle user-menu extension.
 *
 * @package    local_delegateaccount
 * @category   test
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_delegateaccount\hook\extend_user_menu
 */
final class extend_user_menu_test extends \advanced_testcase {
    /**
     * Adds one native fallback entry when an active delegated account exists.
     */
    public function test_active_delegations_add_one_native_fallback_link(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('notificationpolicy', manager::NOTIFICATION_NEVER, 'local_delegateaccount');
        $generator = $this->getDataGenerator();
        $authoriseduser = $generator->create_user();
        $activetarget = $generator->create_user();
        $futuretarget = $generator->create_user();

        $context = \context_system::instance();
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'manager'], MUST_EXIST);
        assign_capability('local/delegateaccount:use', CAP_ALLOW, $roleid, $context->id, true);
        role_assign($roleid, $authoriseduser->id, $context->id);
        accesslib_clear_all_caches_for_unit_testing();

        manager::create_delegations([(int)$authoriseduser->id], [(int)$activetarget->id]);
        manager::create_delegations(
            [(int)$authoriseduser->id],
            [(int)$futuretarget->id],
            ['timestart' => time() + HOURSECS]
        );

        $this->setUser($authoriseduser);
        $hook = new \core_user\hook\extend_user_menu();
        \core\di::get(\core\hook\manager::class)->dispatch($hook);
        $items = $hook->get_navitems();

        $this->assertCount(1, $items);
        $this->assertSame('link', $items[0]->itemtype);
        $this->assertSame('i/switch', $items[0]->pix);
        $this->assertStringContainsString('/local/delegateaccount/accounts.php', $items[0]->url->out(false));
        $this->assertStringNotContainsString('id=' . $futuretarget->id, $items[0]->url->out(false));
    }

    /**
     * Does not add a fallback link when the user has no active delegation.
     */
    public function test_user_without_active_delegation_has_no_fallback_link(): void {
        global $DB;

        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $authoriseduser = $generator->create_user();
        $context = \context_system::instance();
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'manager'], MUST_EXIST);
        assign_capability('local/delegateaccount:use', CAP_ALLOW, $roleid, $context->id, true);
        role_assign($roleid, $authoriseduser->id, $context->id);
        accesslib_clear_all_caches_for_unit_testing();

        $this->setUser($authoriseduser);
        $hook = new \core_user\hook\extend_user_menu();
        \local_delegateaccount\hook\extend_user_menu::execute($hook);

        $this->assertSame([], $hook->get_navitems());
    }

    /**
     * Keeps one fallback link if the hook callback is invoked more than once.
     */
    public function test_repeated_callback_does_not_duplicate_fallback_link(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('notificationpolicy', manager::NOTIFICATION_NEVER, 'local_delegateaccount');
        $generator = $this->getDataGenerator();
        $authoriseduser = $generator->create_user();
        $targetuser = $generator->create_user();
        $context = \context_system::instance();
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'manager'], MUST_EXIST);
        assign_capability('local/delegateaccount:use', CAP_ALLOW, $roleid, $context->id, true);
        role_assign($roleid, $authoriseduser->id, $context->id);
        accesslib_clear_all_caches_for_unit_testing();
        manager::create_delegations([(int)$authoriseduser->id], [(int)$targetuser->id]);

        $this->setUser($authoriseduser);
        $hook = new \core_user\hook\extend_user_menu();
        \local_delegateaccount\hook\extend_user_menu::execute($hook);
        \local_delegateaccount\hook\extend_user_menu::execute($hook);

        $this->assertCount(1, $hook->get_navitems());
    }
}
