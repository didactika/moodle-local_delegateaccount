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
 * Paginated overview of users eligible for delegated accounts or retaining history.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_delegateaccount\table;

use local_delegateaccount\manager;

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

    /** @var bool Whether rows in this table can receive new delegations. */
    private bool $allowsdelegationcreation;

    /**
     * Creates the user overview table.
     *
     * @param \moodle_url $baseurl URL retaining table and filter state.
     * @param int[] $userids Users included by the selected management tab.
     * @param array $filters User filters indexed by field name.
     * @param bool $allowsdelegationcreation Whether this tab contains authorised users.
     * @param \context_system $context System context for capabilities.
     */
    public function __construct(
        \moodle_url $baseurl,
        array $userids,
        array $filters,
        bool $allowsdelegationcreation,
        \context_system $context
    ) {
        global $DB;

        parent::__construct('local_delegateaccount_delegated_users');
        $this->context = $context;
        $this->allowsdelegationcreation = $allowsdelegationcreation;

        $this->define_columns([
            'lastname',
            'email',
            'activecount',
            'scheduledcount',
            'actions',
        ]);
        $this->define_headers([
            get_string('authoriseduser', 'local_delegateaccount'),
            get_string('email'),
            get_string('active_delegations', 'local_delegateaccount'),
            get_string('scheduled_delegations', 'local_delegateaccount'),
            get_string('actions'),
        ]);
        $this->sortable(true, 'lastname', SORT_ASC);
        $this->no_sorting('actions');
        $this->collapsible(false);
        $this->is_downloadable(false);
        $this->pageable(true);
        $this->define_baseurl($baseurl);
        $this->set_attribute('id', 'local-delegateaccount-users');

        $now = time();
        [$where, $filterparams] = self::get_filter_sql($filters, $now);
        if (empty($userids)) {
            $where .= ' AND u.id = 0';
        } else {
            [$useridsql, $userparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'manageduser');
            $where .= ' AND u.id ' . $useridsql;
            $filterparams = array_merge($filterparams, $userparams);
        }
        $fields = 'u.id, u.firstname, u.lastname, u.middlename, u.alternatename,
                   u.firstnamephonetic, u.lastnamephonetic, u.email, u.picture, u.imagealt,
                   SUM(CASE WHEN da.activekey = 0 AND da.timestart <= :activefrom
                         AND (da.timeend = 0 OR da.timeend > :activeto) THEN 1 ELSE 0 END) AS activecount,
                   SUM(CASE WHEN da.activekey = 0 AND da.timestart > :scheduledfrom
                         THEN 1 ELSE 0 END) AS scheduledcount';
        $from = '{user} u LEFT JOIN {local_delegateaccount} da ON da.realuserid = u.id';
        $groupby = 'u.id, u.firstname, u.lastname, u.middlename, u.alternatename,
                    u.firstnamephonetic, u.lastnamephonetic, u.email, u.picture, u.imagealt';
        $dataparams = array_merge($filterparams, [
            'activefrom' => $now,
            'activeto' => $now,
            'scheduledfrom' => $now,
        ]);
        $countsql = 'SELECT COUNT(DISTINCT u.id) FROM {user} u
                       LEFT JOIN {local_delegateaccount} da ON da.realuserid = u.id
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
        global $OUTPUT;

        return $OUTPUT->render_from_template('local_delegateaccount/shared/user_identity', [
            'userpicture' => $OUTPUT->user_picture($row, ['size' => 35, 'link' => false]),
            'fullname' => fullname($row),
            'profileurl' => (new \moodle_url('/user/profile.php', ['id' => (int)$row->id]))->out(false),
        ]);
    }

    /**
     * Renders the actions available for this authorised user.
     *
     * @param \stdClass $row User overview row.
     * @return string Accessible action icon.
     */
    public function col_actions($row): string {
        global $OUTPUT;

        $actions = [];
        $actions[] = $OUTPUT->action_icon(
            new \moodle_url('/local/delegateaccount/delegations.php', ['realuserid' => $row->id]),
            new \pix_icon('t/edit', get_string('manage_user_delegations', 'local_delegateaccount'), 'core')
        );
        if (
            $this->allowsdelegationcreation &&
            (
                has_capability('local/delegateaccount:create', $this->context) ||
                has_capability('local/delegateaccount:manage', $this->context)
            )
        ) {
            $actions[] = $OUTPUT->action_icon(
                new \moodle_url('/local/delegateaccount/assign.php', ['realuserid' => $row->id]),
                new \pix_icon('t/add', get_string('add_delegation', 'local_delegateaccount'), 'core'),
                null,
                [
                    'data-action' => 'local-delegateaccount-open-assign',
                    'data-real-user-id' => (int)$row->id,
                ]
            );
        }

        return implode('', $actions);
    }

    /**
     * Builds portable SQL for the active delegated-account filters.
     *
     * @param array $filters User filters indexed by field name.
     * @param int $now Current Unix timestamp.
     * @return array SQL clause followed by its named parameters.
     */
    private static function get_filter_sql(array $filters, int $now): array {
        global $DB;

        $where = 'u.deleted = 0';
        $params = [];
        if ($filters['search'] !== '') {
            $like = '%' . $DB->sql_like_escape($filters['search']) . '%';
            $where .= ' AND (' . $DB->sql_like(
                $DB->sql_fullname('u.firstname', 'u.lastname'),
                ':filterfullname',
                false
            ) . ' OR ' . $DB->sql_like('u.username', ':filterusername', false)
                . ' OR ' . $DB->sql_like('u.email', ':filteremail', false)
                . ' OR ' . $DB->sql_like('u.idnumber', ':filteridnumber', false) . ')';
            $params['filterfullname'] = $like;
            $params['filterusername'] = $like;
            $params['filteremail'] = $like;
            $params['filteridnumber'] = $like;
        }

        $status = $filters['delegationstatus'];
        if ($status === 'none') {
            $where .= ' AND NOT EXISTS (
                SELECT 1
                  FROM {local_delegateaccount} filterda
                 WHERE filterda.realuserid = u.id
            )';
        } else if ($status !== '') {
            $statuswhere = match ($status) {
                manager::STATUS_ACTIVE => 'filterda.activekey = 0
                    AND filterda.timestart <= :filterstatusstart
                    AND (filterda.timeend = 0 OR filterda.timeend > :filterstatusend)',
                manager::STATUS_SCHEDULED => 'filterda.activekey = 0
                    AND filterda.timestart > :filterstatusscheduled',
                manager::STATUS_EXPIRED => 'filterda.activekey = 0
                    AND filterda.timeend > 0
                    AND filterda.timeend <= :filterstatusexpired',
                manager::STATUS_REVOKED => '(filterda.activekey <> 0 OR filterda.timerevoked > 0)',
            };
            $where .= ' AND EXISTS (
                SELECT 1
                  FROM {local_delegateaccount} filterda
                 WHERE filterda.realuserid = u.id
                   AND ' . $statuswhere . '
            )';
            if ($status === manager::STATUS_ACTIVE) {
                $params['filterstatusstart'] = $now;
                $params['filterstatusend'] = $now;
            } else if ($status === manager::STATUS_SCHEDULED) {
                $params['filterstatusscheduled'] = $now;
            } else if ($status === manager::STATUS_EXPIRED) {
                $params['filterstatusexpired'] = $now;
            }
        }

        return [$where, $params];
    }
}
