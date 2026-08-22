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

/**
 * Paginated Moodle standard-log report for one delegation period.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_delegateaccount\table;

/**
 * Renders activity that Moodle recorded while an authorised user acted as a target account.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delegated_activity_table extends \table_sql {
    /**
     * Creates a report table constrained to one immutable delegation period.
     *
     * @param \moodle_url $baseurl URL retaining table state.
     * @param \stdClass $delegation Delegation database record.
     */
    public function __construct(\moodle_url $baseurl, \stdClass $delegation) {
        parent::__construct('local_delegateaccount_delegated_activity');

        $this->define_columns([
            'timecreated',
            'eventname',
            'component',
            'action',
            'target',
        ]);
        $this->define_headers([
            get_string('activity_time', 'local_delegateaccount'),
            get_string('activity_event', 'local_delegateaccount'),
            get_string('activity_component', 'local_delegateaccount'),
            get_string('activity_action', 'local_delegateaccount'),
            get_string('activity_target', 'local_delegateaccount'),
        ]);
        $this->sortable(true, 'timecreated', SORT_DESC);
        $this->collapsible(false);
        $this->is_downloadable(false);
        $this->pageable(true);
        $this->define_baseurl($baseurl);
        $this->set_attribute('id', 'local-delegateaccount-activity');

        $where = 'log.userid = :delegateduserid
                  AND log.realuserid = :realuserid
                  AND log.timecreated >= :timestart';
        $params = [
            'realuserid' => (int)$delegation->realuserid,
            'delegateduserid' => (int)$delegation->delegateduserid,
            'timestart' => (int)$delegation->timestart,
        ];
        $accessend = \local_delegateaccount\manager::get_delegation_access_end($delegation);
        if ($accessend > 0) {
            $where .= ' AND log.timecreated < :timeend';
            $params['timeend'] = $accessend;
        }
        $countsql = 'SELECT COUNT(log.id) FROM {logstore_standard_log} log WHERE ' . $where;

        $this->set_count_sql($countsql, $params);
        $this->set_sql(
            'log.id, log.timecreated, log.eventname, log.component, log.action, log.target',
            '{logstore_standard_log} log',
            $where,
            $params
        );
    }

    /**
     * Renders the activity time in the viewer's preferred format.
     *
     * @param \stdClass $row Standard-log record.
     * @return string Formatted timestamp.
     */
    public function col_timecreated($row): string {
        return userdate((int)$row->timecreated);
    }

    /**
     * Renders a localised event name where Moodle can resolve the event class.
     *
     * @param \stdClass $row Standard-log record.
     * @return string Localised or escaped technical event name.
     */
    public function col_eventname($row): string {
        if (class_exists($row->eventname) && is_subclass_of($row->eventname, '\\core\\event\\base')) {
            return call_user_func([$row->eventname, 'get_name']);
        }

        return s($row->eventname);
    }
}
