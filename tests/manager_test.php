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
}
