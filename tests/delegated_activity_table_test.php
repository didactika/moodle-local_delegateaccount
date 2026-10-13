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

use local_delegateaccount\external\get_delegation_activity;
use local_delegateaccount\form\activity_filter_form;
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
 * @covers     \local_delegateaccount\manager
 * @covers     \local_delegateaccount\form\activity_filter_form
 */
final class delegated_activity_table_test extends \advanced_testcase {
    /**
     * Constrains every log query to the selected delegation's effective period.
     */
    public function test_query_is_scoped_to_one_delegation_period(): void {
        $this->resetAfterTest();
        $table = new delegated_activity_table(new \moodle_url('/'), $this->make_delegation());

        $this->assertStringContainsString('timecreated >= :delegationstart', $table->sql->where);
        $this->assertStringContainsString('timecreated < :delegationend', $table->sql->where);
        $this->assertSame(1_000, $table->sql->params['delegationstart']);
        $this->assertSame(2_000, $table->sql->params['delegationend']);
    }

    /**
     * Applies report filters without allowing dates outside the delegation period.
     */
    public function test_filters_are_clamped_to_the_delegation_period(): void {
        $this->resetAfterTest();
        $table = new delegated_activity_table(new \moodle_url('/'), $this->make_delegation(), [
            'datefrom' => 500,
            'dateto' => 4_000,
            'component' => 'forum',
            'action' => 'viewed',
        ]);

        $this->assertSame(1_000, $table->sql->params['filterdatefrom']);
        $this->assertSame(2_000, $table->sql->params['filterdateto']);
        $this->assertStringContainsString('component', $table->sql->where);
        $this->assertStringContainsString('action', $table->sql->where);
    }

    /**
     * Treats the upper date filter as an inclusive calendar day.
     */
    public function test_upper_date_filter_includes_the_selected_day(): void {
        $this->resetAfterTest();
        set_config('timezone', 'UTC');
        $selectedday = gmmktime(0, 0, 0, 8, 22, 2026);
        $delegation = $this->make_delegation($selectedday - DAYSECS, $selectedday + (2 * DAYSECS), 0);
        $table = new delegated_activity_table(new \moodle_url('/'), $delegation, [
            'dateto' => $selectedday,
        ]);

        $this->assertSame($selectedday + DAYSECS, $table->sql->params['filterdateto']);
    }

    /**
     * The date filters submitted with the GET form reach the report.
     */
    public function test_date_filters_are_read_from_the_get_form(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $_GET = [
            '_qf__local_delegateaccount_form_activity_filter_form' => 1,
            'sesskey' => sesskey(),
            'datefrom' => ['day' => 1, 'month' => 8, 'year' => 2026],
            'dateto' => ['day' => 20, 'month' => 8, 'year' => 2026],
            'component' => 'forum',
            'action' => '',
            'realuserid' => 11,
            'delegationid' => 7,
        ];

        try {
            $form = new activity_filter_form(new \moodle_url('/local/delegateaccount/pages/activity.php'), [
                'realuserid' => 11,
                'delegationid' => 7,
                'periodstart' => 1,
                'periodend' => time(),
            ]);
            $data = $form->get_data();
        } finally {
            $_GET = [];
        }

        $this->assertNotNull($data);
        $this->assertSame(make_timestamp(2026, 8, 1), (int)$data->datefrom);
        $this->assertSame(make_timestamp(2026, 8, 20), (int)$data->dateto);
        $this->assertSame('forum', $data->component);
    }

    /**
     * Renders the standard report identity, context and network columns from a Moodle event.
     */
    public function test_standard_log_columns_render_from_event_data(): void {
        $this->resetAfterTest();
        $actor = $this->getDataGenerator()->create_user();
        $account = $this->getDataGenerator()->create_user();
        $created = \core\event\user_loggedin::create([
            'objectid' => $account->id,
            'relateduserid' => $account->id,
            'other' => ['username' => $account->username],
        ]);
        $event = \core\event\base::restore($created->get_data(), [
            'origin' => 'web',
            'ip' => '127.0.0.1',
            'realuserid' => $actor->id,
        ]);
        $row = delegated_activity_table::event_to_row($event);

        $table = new delegated_activity_table(
            new \moodle_url('/'),
            $this->make_delegation(1, 0, 0, (int)$actor->id, (int)$account->id)
        );

        $this->assertStringContainsString(fullname($actor), $table->col_fullnameuser($row));
        $this->assertStringContainsString(fullname($account), $table->col_relatedfullnameuser($row));
        $this->assertStringContainsString(get_string('coresystem'), $table->col_context($row));
        $this->assertSame(get_string('coresystem'), $table->col_component($row));
        $this->assertSame('web', $table->col_origin($row));
        $this->assertStringContainsString('127.0.0.1', $table->col_ip($row));
        $this->assertNotSame('-', $table->col_eventname($row));
        $this->assertNotSame('-', $table->col_description($row));
    }

    /**
     * The report, the last-access column and the web service read the site's log reader.
     */
    public function test_activity_is_read_through_the_log_reader(): void {
        global $DB;

        [$delegation, $realuser, $target] = $this->log_delegated_activity();
        $this->setAdminUser();

        $logged = $DB->count_records('logstore_standard_log', ['realuserid' => $realuser->id, 'userid' => $target->id]);
        $this->assertGreaterThanOrEqual(2, $logged);

        $table = new delegated_activity_table(new \moodle_url('/'), $delegation);
        $table->setup();
        $table->query_db(100);
        $this->assertCount($logged, $table->rawdata);

        $this->assertSame(
            (int)$DB->get_field_sql('SELECT MAX(timecreated) FROM {logstore_standard_log} WHERE realuserid = ?', [$realuser->id]),
            manager::get_last_delegated_access($delegation)
        );

        $result = get_delegation_activity::execute((int)$delegation->id, 0, 100);
        $this->assertSame($logged, $result['total']);
        $logids = $DB->get_fieldset_select('logstore_standard_log', 'id', 'realuserid = ?', [$realuser->id]);
        foreach ($result['events'] as $event) {
            $this->assertContains($event['id'], array_map('intval', $logids));
        }
    }

    /**
     * Anonymous events are hidden from viewers who may not see anonymous events.
     */
    public function test_anonymous_events_require_permission(): void {
        global $DB;

        [$delegation, $realuser] = $this->log_delegated_activity();
        $anonymous = $DB->count_records('logstore_standard_log', ['realuserid' => $realuser->id, 'anonymous' => 1]);
        $this->assertSame(1, $anonymous);
        $roleid = create_role('Delegation auditor', 'delegationauditor', '');
        $auditor = $this->getDataGenerator()->create_user();
        assign_capability('local/delegateaccount:viewactivity', CAP_ALLOW, $roleid, \context_system::instance()->id);
        role_assign($roleid, $auditor->id, \context_system::instance()->id);
        accesslib_clear_all_caches_for_unit_testing();

        $this->setAdminUser();
        $all = get_delegation_activity::execute((int)$delegation->id, 0, 100)['total'];

        $this->setUser($auditor);
        $result = get_delegation_activity::execute((int)$delegation->id, 0, 100);
        $this->assertSame($all - 1, $result['total']);
        foreach ($result['events'] as $event) {
            $this->assertSame(0, (int)$DB->get_field('logstore_standard_log', 'anonymous', ['id' => $event['id']]));
        }
    }

    /**
     * Logs one ordinary and one anonymous event during a delegated session.
     *
     * @return array The delegation record, the authorised user and the target account.
     */
    private function log_delegated_activity(): array {
        global $DB;

        $this->resetAfterTest();
        $this->preventResetByRollback();
        set_config('enabled_stores', 'logstore_standard', 'tool_log');
        set_config('buffersize', 0, 'logstore_standard');
        get_log_manager(true);

        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $realuser = $generator->create_user();
        $target = $generator->create_user();
        $roleid = (int)$DB->get_field('role', 'id', ['shortname' => 'manager'], MUST_EXIST);
        assign_capability('local/delegateaccount:use', CAP_ALLOW, $roleid, \context_system::instance()->id, true);
        role_assign($roleid, $realuser->id, \context_system::instance()->id);
        accesslib_clear_all_caches_for_unit_testing();
        manager::create_delegations([(int)$realuser->id], [(int)$target->id], [
            'timestart' => time() - 60,
            'notificationmode' => manager::NOTIFICATION_NEVER,
        ]);
        $delegation = $DB->get_record('local_delegateaccount', ['realuserid' => $realuser->id], '*', MUST_EXIST);

        $this->setUser($realuser);
        manager::start_delegated_session((int)$target->id);
        $context = \context_user::instance($target->id);
        \core\event\dashboard_viewed::create(['context' => $context])->trigger();
        \core\event\dashboard_viewed::create(['context' => $context, 'anonymous' => 1])->trigger();

        return [$delegation, $realuser, $target];
    }

    /**
     * Creates an in-memory delegation record.
     *
     * @param int $timestart Start of access.
     * @param int $timeend Configured end of access, or zero.
     * @param int $timerevoked Revocation time, or zero.
     * @param int $realuserid Authorised user.
     * @param int $delegateduserid Target account.
     * @return \stdClass Delegation record.
     */
    private function make_delegation(
        int $timestart = 1_000,
        int $timeend = 3_000,
        int $timerevoked = 2_000,
        int $realuserid = 11,
        int $delegateduserid = 22
    ): \stdClass {
        return (object)[
            'realuserid' => $realuserid,
            'delegateduserid' => $delegateduserid,
            'timestart' => $timestart,
            'timeend' => $timeend,
            'timerevoked' => $timerevoked,
        ];
    }
}
