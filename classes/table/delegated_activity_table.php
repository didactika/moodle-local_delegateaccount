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

use local_delegateaccount\manager;

/**
 * Renders activity that Moodle recorded while an authorised user acted as a target account.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delegated_activity_table extends \table_sql {
    /** @var array<int, string|false> User names keyed by user id. */
    private array $usernames = [];

    /** @var array<int, string> Context labels keyed by context id. */
    private array $contextnames = [];

    /**
     * Creates a report table constrained to one immutable delegation period.
     *
     * @param \moodle_url $baseurl URL retaining table state.
     * @param \stdClass $delegation Delegation database record.
     * @param array $filters Optional date and event metadata filters.
     */
    public function __construct(\moodle_url $baseurl, \stdClass $delegation, array $filters = []) {
        global $DB;

        parent::__construct('local_delegateaccount_delegated_activity');

        $this->define_columns([
            'timecreated',
            'fullnameuser',
            'relatedfullnameuser',
            'context',
            'component',
            'eventname',
            'description',
            'origin',
            'ip',
        ]);
        $this->define_headers([
            get_string('time'),
            get_string('fullnameuser'),
            get_string('eventrelatedfullnameuser', 'report_log'),
            get_string('eventcontext', 'report_log'),
            get_string('eventcomponent', 'report_log'),
            get_string('eventname'),
            get_string('description'),
            get_string('eventorigin', 'report_log'),
            get_string('ip_address'),
        ]);
        $this->sortable(true, 'timecreated', SORT_DESC);
        $nonsortablecolumns = [
            'fullnameuser',
            'relatedfullnameuser',
            'context',
            'component',
            'eventname',
            'description',
            'origin',
            'ip',
        ];
        foreach ($nonsortablecolumns as $column) {
            $this->no_sorting($column);
        }
        $this->collapsible(false);
        $this->is_downloadable(false);
        $this->pageable(true);
        $this->define_baseurl($baseurl);
        $this->set_attribute('id', 'local-delegateaccount-activity');
        $this->set_attribute('class', 'reportlog generaltable generalbox table-sm');

        [$where, $params] = manager::get_delegation_log_selector($delegation);
        $accessend = manager::get_delegation_access_end($delegation);
        if (!empty($filters['datefrom'])) {
            $where .= ' AND timecreated >= :filterdatefrom';
            $params['filterdatefrom'] = max((int)$delegation->timestart, (int)$filters['datefrom']);
        }
        if (!empty($filters['dateto'])) {
            $selecteddate = (new \DateTimeImmutable('@' . (int)$filters['dateto']))
                ->setTimezone(\core_date::get_user_timezone_object());
            $requestedend = $selecteddate->setTime(0, 0)->modify('+1 day')->getTimestamp();
            $params['filterdateto'] = $accessend > 0 ? min($accessend, $requestedend) : $requestedend;
            $where .= ' AND timecreated < :filterdateto';
        }
        if (!empty($filters['component'])) {
            $where .= ' AND ' . $DB->sql_like('component', ':filtercomponent', false);
            $params['filtercomponent'] = '%' . $DB->sql_like_escape($filters['component']) . '%';
        }
        if (!empty($filters['action'])) {
            $where .= ' AND ' . $DB->sql_like('action', ':filteraction', false);
            $params['filteraction'] = '%' . $DB->sql_like_escape($filters['action']) . '%';
        }

        // The rows come from the site's log reader in query_db(), not from a table of this plugin.
        $this->sql = (object)['where' => $where, 'params' => $params];
    }

    /**
     * Reads one page of events through the site's log reader, like Moodle's log report.
     *
     * @param int $pagesize Number of rows per page.
     * @param bool $useinitialsbar Unused: this report has no initials bar.
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        $this->rawdata = [];
        $reader = manager::get_log_reader();
        if (!$reader) {
            $this->pagesize($pagesize, 0);
            return;
        }

        $this->pagesize($pagesize, $reader->get_events_select_count($this->sql->where, $this->sql->params));
        $sort = $this->get_sql_sort() ?: 'timecreated DESC';
        $events = $reader->get_events_select(
            $this->sql->where,
            $this->sql->params,
            $sort . ', id DESC',
            $this->get_page_start(),
            $this->get_page_size()
        );
        foreach ($events as $event) {
            $this->rawdata[] = self::event_to_row($event);
        }
    }

    /**
     * Converts a logged event into a table row.
     *
     * @param \core\event\base $event Event restored by the log reader.
     * @return \stdClass Row with the event data, its log details and the event itself.
     */
    public static function event_to_row(\core\event\base $event): \stdClass {
        $row = (object)($event->get_data() + $event->get_logextra());
        $row->event = $event;

        return $row;
    }

    /**
     * Renders the activity time in the viewer's preferred format.
     *
     * @param \stdClass $row Standard-log record.
     * @return string Formatted timestamp.
     */
    public function col_timecreated($row): string {
        return userdate(
            (int)$row->timecreated,
            get_string('strftimedatetimeaccurate', 'core_langconfig')
        );
    }

    /**
     * Renders the user who performed the event, retaining Moodle's "logged in as" wording.
     *
     * @param \stdClass $row Standard-log record.
     * @return string User link or fallback marker.
     */
    public function col_fullnameuser($row): string {
        $username = $this->get_user_name((int)$row->userid);
        if (!empty($row->realuserid)) {
            $actorname = $this->get_user_name((int)$row->realuserid) ?: '-';
            $accountname = $username ?: '-';
            $actorname = $this->user_link((int)$row->realuserid, $actorname, (int)$row->courseid);
            $accountname = $this->user_link((int)$row->userid, $accountname, (int)$row->courseid);

            return get_string('eventloggedas', 'report_log', (object)[
                'realusername' => $actorname,
                'asusername' => $accountname,
            ]);
        }

        return $username
            ? $this->user_link((int)$row->userid, $username, (int)$row->courseid)
            : '-';
    }

    /**
     * Renders the user affected by the event.
     *
     * @param \stdClass $row Standard-log record.
     * @return string User link or fallback marker.
     */
    public function col_relatedfullnameuser($row): string {
        $userid = (int)$row->relateduserid;
        $username = $this->get_user_name($userid);

        return $username ? $this->user_link($userid, $username, (int)$row->courseid) : '-';
    }

    /**
     * Renders the event context using the same convention as Moodle's standard log report.
     *
     * @param \stdClass $row Standard-log record.
     * @return string Context name, optionally linked.
     */
    public function col_context($row): string {
        $contextid = (int)$row->contextid;
        if (isset($this->contextnames[$contextid])) {
            return $this->contextnames[$contextid];
        }

        $context = $contextid ? \context::instance_by_id($contextid, IGNORE_MISSING) : false;
        if (!$context) {
            return $this->contextnames[$contextid] = get_string('other');
        }

        $name = $context->get_context_name(true);
        if ($url = $context->get_url()) {
            $name = \html_writer::link($url, $name);
        }

        return $this->contextnames[$contextid] = $name;
    }

    /**
     * Renders a localised component name where one is available.
     *
     * @param \stdClass $row Standard-log record.
     * @return string Component name.
     */
    public function col_component($row): string {
        if ($row->component === 'core' || $row->component === 'legacy') {
            return get_string('coresystem');
        }
        if (get_string_manager()->string_exists('pluginname', $row->component)) {
            return get_string('pluginname', $row->component);
        }

        return s($row->component);
    }

    /**
     * Renders a localised event name where Moodle can resolve the event class.
     *
     * @param \stdClass $row Standard-log record.
     * @return string Localised or escaped technical event name.
     */
    public function col_eventname($row): string {
        $event = $this->get_event($row);
        if (!$event) {
            return s($row->eventname);
        }

        $name = $event->get_name();
        if ($url = $event->get_url()) {
            return \html_writer::link($url, $name);
        }

        return $name;
    }

    /**
     * Renders the event description supplied by the event class.
     *
     * @param \stdClass $row Standard-log record.
     * @return string Formatted description or fallback marker.
     */
    public function col_description($row): string {
        $event = $this->get_event($row);

        return $event ? format_text($event->get_description(), FORMAT_PLAIN) : '-';
    }

    /**
     * Renders the event origin.
     *
     * @param \stdClass $row Standard-log record.
     * @return string Event origin.
     */
    public function col_origin($row): string {
        return s($row->origin ?: '-');
    }

    /**
     * Renders the IP address using Moodle's standard lookup action.
     *
     * @param \stdClass $row Standard-log record.
     * @return string IP lookup link or fallback marker.
     */
    public function col_ip($row): string {
        if (empty($row->ip)) {
            return '-';
        }

        return \html_writer::link(new \moodle_url('/iplookup/index.php', [
            'popup' => 1,
            'ip' => $row->ip,
            'user' => (int)$row->userid,
        ]), s($row->ip));
    }

    /**
     * Returns the event represented by a row.
     *
     * @param \stdClass $row Activity row.
     * @return \core\event\base|null Event, or null when it could not be restored.
     */
    private function get_event(\stdClass $row): ?\core\event\base {
        return $row->event ?? null;
    }

    /**
     * Gets and caches a user's display name.
     *
     * @param int $userid User id.
     * @return string|false Display name, or false when unavailable.
     */
    private function get_user_name(int $userid) {
        if ($userid <= 0) {
            return false;
        }
        if (array_key_exists($userid, $this->usernames)) {
            return $this->usernames[$userid];
        }

        $fields = \core_user\fields::for_name()->get_sql('', false, '', '', false)->selects;
        $user = \core_user::get_user($userid, $fields);
        $this->usernames[$userid] = $user ? fullname($user) : false;

        return $this->usernames[$userid];
    }

    /**
     * Builds a profile link while retaining the event course where possible.
     *
     * @param int $userid User id.
     * @param string $name Display name.
     * @param int $courseid Course id recorded on the event.
     * @return string Profile link.
     */
    private function user_link(int $userid, string $name, int $courseid): string {
        $params = ['id' => $userid];
        if ($courseid > 0) {
            $params['course'] = $courseid;
        }

        return \html_writer::link(new \moodle_url('/user/view.php', $params), $name);
    }
}
