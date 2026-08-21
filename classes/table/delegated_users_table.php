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
 * Paginated overview of users who have configured delegated accounts.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_delegateaccount\table;

/**
 * Renders the delegated-account user overview using Moodle's table API.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delegated_users_table extends \table_sql {
    /** @var \context_system Context used to evaluate action capabilities. */
    private \context_system $context;

    /**
     * Creates the user overview table.
     *
     * @param \moodle_url $baseurl URL retaining table and search state.
     * @param string $search Free-text user search.
     * @param \context_system $context System context for capabilities.
     */
    public function __construct(\moodle_url $baseurl, string $search, \context_system $context) {
        global $DB;

        parent::__construct('local_delegateaccount_delegated_users');
        $this->context = $context;

        $this->define_columns([
            'lastname',
            'email',
            'activecount',
            'scheduledcount',
            'delegationcount',
            'actions',
        ]);
        $this->define_headers([
            get_string('authoriseduser', 'local_delegateaccount'),
            get_string('email'),
            get_string('active_delegations', 'local_delegateaccount'),
            get_string('scheduled_delegations', 'local_delegateaccount'),
            get_string('delegation_count', 'local_delegateaccount'),
            get_string('actions'),
        ]);
        $this->sortable(true, 'lastname', SORT_ASC);
        $this->no_sorting('actions');
        $this->collapsible(false);
        $this->is_downloadable(false);
        $this->pageable(true);
        $this->define_baseurl($baseurl);
        $this->set_attribute('id', 'local-delegateaccount-users');

        [$where, $filterparams] = self::get_search_sql($search);
        $now = time();
        $fields = 'u.id, u.firstname, u.lastname, u.middlename, u.alternatename,
                   u.firstnamephonetic, u.lastnamephonetic, u.email,
                   SUM(CASE WHEN da.activekey = 0 AND da.timestart <= :activefrom
                         AND (da.timeend = 0 OR da.timeend > :activeto) THEN 1 ELSE 0 END) AS activecount,
                   SUM(CASE WHEN da.activekey = 0 AND da.timestart > :scheduledfrom
                         THEN 1 ELSE 0 END) AS scheduledcount,
                   SUM(CASE WHEN da.activekey = 0 THEN 1 ELSE 0 END) AS delegationcount';
        $from = '{user} u JOIN {local_delegateaccount} da ON da.realuserid = u.id';
        $groupby = 'u.id, u.firstname, u.lastname, u.middlename, u.alternatename,
                    u.firstnamephonetic, u.lastnamephonetic, u.email';
        $dataparams = array_merge($filterparams, [
            'activefrom' => $now,
            'activeto' => $now,
            'scheduledfrom' => $now,
        ]);
        $countsql = 'SELECT COUNT(DISTINCT u.id) FROM {user} u
                       JOIN {local_delegateaccount} da ON da.realuserid = u.id
                      WHERE ' . $where;

        $this->set_count_sql($countsql, $filterparams);
        $this->set_sql($fields, $from, $where . ' GROUP BY ' . $groupby, $dataparams);
    }

    /**
     * Renders an authorised user's name with the same escaping as core user tables.
     *
     * @param \stdClass $row User overview row.
     * @return string Escaped full name.
     */
    public function col_lastname($row): string {
        return fullname($row);
    }

    /**
     * Renders the actions available for this authorised user.
     *
     * @param \stdClass $row User overview row.
     * @return string Accessible action icon.
     */
    public function col_actions($row): string {
        global $OUTPUT;

        if (!has_capability('local/delegateaccount:create', $this->context) &&
                !has_capability('local/delegateaccount:manage', $this->context)) {
            return '';
        }

        $actions = [];
        $actions[] = $OUTPUT->action_icon(
            new \moodle_url('/local/delegateaccount/delegations.php', ['realuserid' => $row->id]),
            new \pix_icon('t/edit', get_string('manage_user_delegations', 'local_delegateaccount'), 'core')
        );
        if (has_capability('local/delegateaccount:create', $this->context) ||
                has_capability('local/delegateaccount:manage', $this->context)) {
            $actions[] = $OUTPUT->action_icon(
                new \moodle_url('/local/delegateaccount/assign.php', ['realuserid' => $row->id]),
                new \pix_icon('t/add', get_string('add_delegation', 'local_delegateaccount'), 'core')
            );
        }

        return implode('', $actions);
    }

    /**
     * Builds portable SQL for the free-text user filter.
     *
     * @param string $search Free-text user search.
     * @return array{0: string, 1: array<string, string>} SQL and named parameters.
     */
    private static function get_search_sql(string $search): array {
        global $DB;

        $where = 'u.deleted = 0';
        if ($search === '') {
            return [$where, []];
        }

        $searchvalue = '%' . $DB->sql_like_escape(core_text::strtolower($search)) . '%';
        $fields = [
            'firstname',
            'lastname',
            'email',
        ];
        $clauses = [];
        $params = [];
        foreach ($fields as $field) {
            $param = 'search' . $field;
            $clauses[] = $DB->sql_like($DB->sql_lower('u.' . $field), ':' . $param, false);
            $params[$param] = $searchvalue;
        }

        return [$where . ' AND (' . implode(' OR ', $clauses) . ')', $params];
    }
}
