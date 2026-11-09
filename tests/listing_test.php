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

use local_delegateaccount\local\listing;

/**
 * Tests for the queries behind the lists of the plugin.
 *
 * @package    local_delegateaccount
 * @category   test
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\local_delegateaccount\local\listing::class)]
final class listing_test extends \advanced_testcase {
    /** @var \stdClass[] People who may use delegations. */
    private array $authorised = [];

    /** @var \stdClass[] Accounts that are delegated. */
    private array $accounts = [];

    /** @var int[] Delegation identifiers by status. */
    private array $delegations = [];

    /**
     * Two authorised people and four delegations of the first one, one in each status.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $roleid = $generator->create_role();
        assign_capability('local/delegateaccount:use', CAP_ALLOW, $roleid, \context_system::instance()->id);
        foreach (['Alice', 'Bruno'] as $name) {
            $user = $generator->create_user(['firstname' => $name, 'lastname' => 'Person']);
            role_assign($roleid, $user->id, \context_system::instance()->id);
            $this->authorised[] = $user;
        }
        foreach (['Carla', 'Dario', 'Elena', 'Fabio'] as $name) {
            $this->accounts[] = $generator->create_user(['firstname' => $name, 'lastname' => 'Account']);
        }
        $now = time();
        $real = (int)$this->authorised[0]->id;
        $periods = [
            manager::STATUS_ACTIVE => [$now - DAYSECS, 0],
            manager::STATUS_SCHEDULED => [$now + DAYSECS, 0],
            manager::STATUS_EXPIRED => [$now - 3 * DAYSECS, $now - DAYSECS],
            manager::STATUS_REVOKED => [$now - DAYSECS, 0],
        ];
        $index = 0;
        foreach ($periods as $status => [$start, $end]) {
            $target = (int)$this->accounts[$index++]->id;
            manager::create_delegations([$real], [$target], [
                'timestart' => $start,
                'timeend' => $end,
                'notificationmode' => 'never',
            ]);
            $this->delegations[$status] = manager::get_current_delegation_id($real, $target);
        }
        manager::revoke_delegations([$this->delegations[manager::STATUS_REVOKED]]);
    }

    /**
     * The people list counts each status and filters by it.
     */
    public function test_users(): void {
        $all = listing::users(listing::SCOPE_AUTHORISED, '', '', 'name', 'asc', 0, 20);
        // Both people and the administrator may use delegations.
        $this->assertSame(3, $all['total']);
        $this->assertSame(1, $all['counts'][manager::STATUS_ACTIVE]);
        $this->assertSame(2, $all['counts']['none']);

        $active = listing::users(listing::SCOPE_AUTHORISED, '', manager::STATUS_ACTIVE, 'name', 'asc', 0, 20);
        $this->assertSame(1, $active['total']);
        $this->assertSame((int)$this->authorised[0]->id, (int)$active['rows'][0]->id);
        $this->assertSame(1, (int)$active['rows'][0]->activecount);
        $this->assertSame(1, (int)$active['rows'][0]->scheduledcount);

        $search = listing::users(listing::SCOPE_AUTHORISED, 'bruno', '', 'name', 'asc', 0, 20);
        $this->assertSame([(int)$this->authorised[1]->id], array_map(fn($row) => (int)$row->id, $search['rows']));
    }

    /**
     * A person who lost the permission moves to the historical list.
     */
    public function test_historical_users(): void {
        $this->assertSame(0, listing::users(listing::SCOPE_HISTORICAL, '', '', 'name', 'asc', 0, 20)['total']);

        role_unassign_all(['userid' => $this->authorised[0]->id]);
        accesslib_clear_all_caches_for_unit_testing();

        $historical = listing::users(listing::SCOPE_HISTORICAL, '', '', 'name', 'asc', 0, 20);
        $this->assertSame(1, $historical['total']);
        $this->assertSame((int)$this->authorised[0]->id, (int)$historical['rows'][0]->id);
    }

    /**
     * The list of delegations counts and filters by status, searches and pages.
     */
    public function test_delegations(): void {
        $filters = ['realuserid' => (int)$this->authorised[0]->id, 'status' => '', 'search' => ''];
        $all = listing::delegations($filters, 'account', 'asc', 0, 20);
        $this->assertSame(4, $all['total']);
        $this->assertSame(['all' => 4, 'active' => 1, 'scheduled' => 1, 'expired' => 1, 'revoked' => 1], $all['counts']);
        $this->assertSame('Carla', $all['rows'][0]->delegated_firstname);

        $expired = listing::delegations(['status' => manager::STATUS_EXPIRED] + $filters, 'account', 'asc', 0, 20);
        $this->assertSame([$this->delegations[manager::STATUS_EXPIRED]], array_map(fn($row) => (int)$row->id, $expired['rows']));

        $search = listing::delegations(['search' => 'fabio'] + $filters, 'account', 'asc', 0, 20);
        $this->assertSame([$this->delegations[manager::STATUS_REVOKED]], array_map(fn($row) => (int)$row->id, $search['rows']));

        $page = listing::delegations($filters, 'account', 'desc', 1, 3);
        $this->assertCount(1, $page['rows']);
        $this->assertSame('Carla', $page['rows'][0]->delegated_firstname);

        $other = ['realuserid' => (int)$this->authorised[1]->id] + $filters;
        $this->assertSame(0, listing::delegations($other, 'account', 'asc', 0, 20)['total']);
    }

    /**
     * Searching the list of everyone also finds the authorised person.
     */
    public function test_delegations_of_everyone(): void {
        $result = listing::delegations(['realuserid' => 0, 'status' => '', 'search' => 'alice'], 'user', 'asc', 0, 20);

        $this->assertSame(4, $result['total']);
    }

    /**
     * Every delegation that matches the filters, for an action on all of them.
     */
    public function test_delegation_ids(): void {
        $ids = listing::delegation_ids(['realuserid' => 0, 'status' => manager::STATUS_ACTIVE, 'search' => '']);

        $this->assertSame([$this->delegations[manager::STATUS_ACTIVE]], $ids);
    }

    /**
     * A person sees the accounts they may open now, and only those.
     */
    public function test_my_accounts(): void {
        $result = listing::my_accounts((int)$this->authorised[0]->id, '', 0, 20);

        $this->assertSame(1, $result['total']);
        $this->assertSame((int)$this->accounts[0]->id, $result['rows'][0]->id);
        $this->assertSame($this->delegations[manager::STATUS_ACTIVE], $result['rows'][0]->delegationid);
        $this->assertSame(0, listing::my_accounts((int)$this->authorised[0]->id, 'nobody', 0, 20)['total']);
    }
}
