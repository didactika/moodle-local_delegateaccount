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

use local_delegateaccount\table\delegated_activity_table;

/**
 * Tests period scoping in the delegated activity report.
 *
 * @package    local_delegateaccount
 * @category   test
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_delegateaccount\table\delegated_activity_table
 */
final class delegated_activity_table_test extends \advanced_testcase {
    /**
     * Constrains every log query to the selected delegation's effective period.
     */
    public function test_query_is_scoped_to_one_delegation_period(): void {
        $delegation = (object) [
            'realuserid' => 11,
            'delegateduserid' => 22,
            'timestart' => 1_000,
            'timeend' => 3_000,
            'timerevoked' => 2_000,
        ];
        $table = new delegated_activity_table(new \moodle_url('/'), $delegation);

        $this->assertStringContainsString('log.timecreated >= :timestart', $table->sql->where);
        $this->assertStringContainsString('log.timecreated < :timeend', $table->sql->where);
        $this->assertSame(1_000, $table->sql->params['timestart']);
        $this->assertSame(2_000, $table->sql->params['timeend']);
    }
}
