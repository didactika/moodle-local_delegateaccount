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
 * Paginated table of delegated accounts belonging to one authorised user.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_delegateaccount\table;

use local_delegateaccount\manager;

/**
 * Renders the lifecycle and use history of one user's delegated accounts.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delegated_accounts_table extends \table_sql {
    /** @var \context_system Context used to evaluate action capabilities. */
    private \context_system $context;

    /** @var int ID of the authorised user represented by this table. */
    private int $realuserid;

    /** @var string Lifecycle status represented by the current tab. */
    private string $status;

    /**
     * Creates the delegated-account table for one authorised user.
     *
     * @param \moodle_url $baseurl URL retaining table state.
     * @param int $realuserid Authorised user ID.
     * @param \context_system $context System context for capabilities.
     * @param string $status Lifecycle status represented by the current tab.
     * @param string $search Optional delegated-user search query.
     */
    public function __construct(
        \moodle_url $baseurl,
        int $realuserid,
        \context_system $context,
        string $status = manager::STATUS_ACTIVE,
        string $search = ''
    ) {
        global $DB;

        parent::__construct('local_delegateaccount_delegated_accounts');
        $this->context = $context;
        $this->realuserid = $realuserid;
        $this->status = $status;

        $this->define_columns([
            'select',
            'lastname',
            'email',
            'status',
            'timestart',
            'timeend',
            'lastaccess',
            'actions',
        ]);
        $this->define_headers([
            $this->render_select_all(),
            get_string('delegateduser', 'local_delegateaccount'),
            get_string('email'),
            get_string('delegation_status', 'local_delegateaccount'),
            get_string('delegation_start', 'local_delegateaccount'),
            get_string('delegation_end', 'local_delegateaccount'),
            get_string('last_delegated_access', 'local_delegateaccount'),
            get_string('actions'),
        ]);
        $this->sortable(true, 'lastname', SORT_ASC);
        $this->no_sorting('select');
        $this->no_sorting('status');
        $this->no_sorting('actions');
        $this->collapsible(false);
        $this->is_downloadable(false);
        $this->pageable(true);
        $this->define_baseurl($baseurl);
        $this->set_attribute('id', 'local-delegateaccount-delegations');

        $fields = 'da.id, da.realuserid, da.delegateduserid, da.timestart, da.timeend,
                   da.timerevoked, da.activekey, da.notificationmode, u.firstname, u.lastname, u.middlename,
                   u.alternatename, u.firstnamephonetic, u.lastnamephonetic, u.email,
                   u.picture, u.imagealt,
                   COALESCE(MAX(log.timecreated), 0) AS lastaccess';
        $from = '{local_delegateaccount} da
                 JOIN {user} u ON u.id = da.delegateduserid
                 LEFT JOIN {logstore_standard_log} log ON log.userid = da.delegateduserid
                    AND log.realuserid = da.realuserid
                    AND log.timecreated >= da.timestart
                    AND (da.timeend = 0 OR log.timecreated < da.timeend)
                    AND (da.timerevoked = 0 OR log.timecreated < da.timerevoked)';
        $where = 'da.realuserid = :realuserid';
        $params = ['realuserid' => $realuserid];
        if ($search !== '') {
            $fullname = $DB->sql_concat('u.firstname', "' '", 'u.lastname');
            $searchvalue = '%' . $DB->sql_like_escape($search) . '%';
            $where .= ' AND (' . $DB->sql_like($fullname, ':searchfullname', false)
                . ' OR ' . $DB->sql_like('u.username', ':searchusername', false)
                . ' OR ' . $DB->sql_like('u.email', ':searchemail', false) . ')';
            $params['searchfullname'] = $searchvalue;
            $params['searchusername'] = $searchvalue;
            $params['searchemail'] = $searchvalue;
        }

        $now = time();
        if ($status === manager::STATUS_ACTIVE) {
            $where .= ' AND da.activekey = 0 AND da.timestart <= :statusstart
                        AND (da.timeend = 0 OR da.timeend > :statusend)';
            $params['statusstart'] = $now;
            $params['statusend'] = $now;
        } else if ($status === manager::STATUS_SCHEDULED) {
            $where .= ' AND da.activekey = 0 AND da.timestart > :statusscheduled';
            $params['statusscheduled'] = $now;
        } else if ($status === manager::STATUS_EXPIRED) {
            $where .= ' AND da.activekey = 0 AND da.timeend > 0 AND da.timeend <= :statusexpired';
            $params['statusexpired'] = $now;
        } else if ($status === manager::STATUS_REVOKED) {
            $where .= ' AND (da.activekey <> 0 OR da.timerevoked > 0)';
        }
        $groupby = 'da.id, da.realuserid, da.delegateduserid, da.timestart, da.timeend,
                    da.timerevoked, da.activekey, da.notificationmode, u.firstname, u.lastname, u.middlename,
                    u.alternatename, u.firstnamephonetic, u.lastnamephonetic, u.email,
                    u.picture, u.imagealt';
        $countsql = 'SELECT COUNT(da.id)
                       FROM {local_delegateaccount} da
                       JOIN {user} u ON u.id = da.delegateduserid
                      WHERE ' . $where;

        $this->set_count_sql($countsql, $params);
        $this->set_sql($fields, $from, $where . ' GROUP BY ' . $groupby, $params);
    }

    /**
     * Renders the select-all control when the current user can revoke delegations.
     *
     * @return string Select-all checkbox or an empty header.
     */
    private function render_select_all(): string {
        global $OUTPUT;

        if (
            $this->status === manager::STATUS_REVOKED ||
            !has_capability('local/delegateaccount:revoke', $this->context)
        ) {
            return '';
        }

        return $OUTPUT->render_from_template('local_delegateaccount/delegation/select_all', [
            'label' => get_string('select_all_delegations', 'local_delegateaccount'),
        ]);
    }

    /**
     * Renders a selection checkbox for delegations that can still be revoked.
     *
     * @param \stdClass $row Delegated-account row.
     * @return string Selection control or an empty value.
     */
    public function col_select($row): string {
        global $OUTPUT;

        if (
            !has_capability('local/delegateaccount:revoke', $this->context) ||
            manager::get_delegation_status($row) === manager::STATUS_REVOKED
        ) {
            return '';
        }

        return $OUTPUT->render_from_template('local_delegateaccount/delegation/select', [
            'id' => (int)$row->id,
            'label' => get_string('select_delegation', 'local_delegateaccount', fullname($row)),
        ]);
    }

    /**
     * Renders the delegated account's full name.
     *
     * @param \stdClass $row Delegated-account row.
     * @return string Escaped full name.
     */
    public function col_lastname($row): string {
        global $OUTPUT;

        return $OUTPUT->render_from_template('local_delegateaccount/shared/user_identity', [
            'userpicture' => $OUTPUT->user_picture($row, ['size' => 35, 'link' => false]),
            'fullname' => fullname($row),
            'profileurl' => (new \moodle_url('/user/profile.php', [
                'id' => (int)$row->delegateduserid,
            ]))->out(false),
        ]);
    }

    /**
     * Renders the derived lifecycle status.
     *
     * @param \stdClass $row Delegated-account row.
     * @return string Localised status.
     */
    public function col_status($row): string {
        $status = manager::get_delegation_status($row);
        $classes = [
            manager::STATUS_ACTIVE => 'badge badge-success',
            manager::STATUS_SCHEDULED => 'badge badge-info',
            manager::STATUS_EXPIRED => 'badge badge-warning',
            manager::STATUS_REVOKED => 'badge badge-secondary',
        ];

        return $this->render_badge(
            get_string('delegation_status_' . $status, 'local_delegateaccount'),
            $classes[$status] ?? 'badge badge-secondary'
        );
    }

    /**
     * Renders the start of the delegation period.
     *
     * @param \stdClass $row Delegated-account row.
     * @return string Formatted start date.
     */
    public function col_timestart($row): string {
        return $this->render_badge(userdate((int)$row->timestart), 'badge badge-info font-weight-normal');
    }

    /**
     * Renders a delegation timestamp and handles open-ended access.
     *
     * @param \stdClass $row Delegated-account row.
     * @return string Formatted end date or the open-ended label.
     */
    public function col_timeend($row): string {
        $displayend = manager::get_delegation_display_end($row);
        if ($displayend === 0) {
            return $this->render_badge(
                get_string('delegation_no_end', 'local_delegateaccount'),
                'badge badge-info font-weight-normal'
            );
        }

        return $this->render_badge(userdate($displayend), 'badge badge-info font-weight-normal');
    }

    /**
     * Renders the most recent recorded activity while using the delegated account.
     *
     * @param \stdClass $row Delegated-account row.
     * @return string Formatted timestamp or the no-access label.
     */
    public function col_lastaccess($row): string {
        if ((int)$row->lastaccess === 0) {
            return $this->render_badge(get_string('no_delegated_access', 'local_delegateaccount'), 'badge badge-secondary');
        }

        return $this->render_badge(userdate((int)$row->lastaccess), 'badge badge-info font-weight-normal');
    }

    /**
     * Renders a compact, accessible table value through the plugin template.
     *
     * @param string $label Human-readable lifecycle value.
     * @param string $class Moodle Bootstrap classes selected by this table.
     * @return string Rendered badge.
     */
    private function render_badge(string $label, string $class): string {
        global $OUTPUT;

        return $OUTPUT->render_from_template('local_delegateaccount/delegation/badge', [
            'class' => $class,
            'label' => $label,
        ]);
    }

    /**
     * Renders actions permitted for this delegation.
     *
     * @param \stdClass $row Delegated-account row.
     * @return string Action controls.
     */
    public function col_actions($row): string {
        global $OUTPUT;

        $actions = [];
        $title = get_string('delegation_details', 'local_delegateaccount');
        $notificationkey = 'delegationnotificationmode_' . $row->notificationmode;
        $notificationmode = get_string_manager()->string_exists($notificationkey, 'local_delegateaccount')
            ? get_string($notificationkey, 'local_delegateaccount')
            : get_string('delegationnotificationmode_never', 'local_delegateaccount');
        $displayend = manager::get_delegation_display_end($row);
        $content = $OUTPUT->render_from_template('local_delegateaccount/delegation/modal_body', [
            'statuslabel' => get_string('delegation_status', 'local_delegateaccount'),
            'status' => get_string('delegation_status_' . manager::get_delegation_status($row), 'local_delegateaccount'),
            'startlabel' => get_string('delegation_start', 'local_delegateaccount'),
            'start' => userdate((int)$row->timestart),
            'endlabel' => get_string('delegation_end', 'local_delegateaccount'),
            'end' => $displayend === 0
                ? get_string('delegation_no_end', 'local_delegateaccount')
                : userdate($displayend),
            'notificationmodelabel' => get_string('delegationnotificationmode', 'local_delegateaccount'),
            'notificationmode' => $notificationmode,
        ]);
        $actions[] = $OUTPUT->render_from_template('local_delegateaccount/delegation/info_action', [
            'url' => (new \moodle_url('/local/delegateaccount/pages/delegation.php', [
                'realuserid' => $this->realuserid,
                'delegationid' => $row->id,
            ]))->out(false),
            'title' => $title,
            'contentid' => 'local-delegateaccount-delegation-info-' . $row->id,
            'content' => $content,
        ]);

        $canupdate = has_capability('local/delegateaccount:update', $this->context) ||
            has_capability('local/delegateaccount:manage', $this->context);
        if (manager::get_delegation_status($row) !== manager::STATUS_REVOKED && $canupdate) {
            $actions[] = $OUTPUT->action_icon(
                new \moodle_url('/local/delegateaccount/pages/edit.php', [
                    'realuserid' => $this->realuserid,
                    'delegationid' => $row->id,
                ]),
                new \pix_icon('t/edit', get_string('edit_delegation', 'local_delegateaccount'), 'core'),
                null,
                [
                    'data-action' => 'local-delegateaccount-edit-one',
                    'data-real-user-id' => $this->realuserid,
                    'data-delegation-id' => (int)$row->id,
                ]
            );
        }

        if (has_capability('local/delegateaccount:viewactivity', $this->context)) {
            $actions[] = $OUTPUT->action_icon(
                new \moodle_url('/local/delegateaccount/pages/activity.php', [
                    'realuserid' => $this->realuserid,
                    'delegationid' => $row->id,
                ]),
                new \pix_icon('i/report', get_string('view_delegated_activity', 'local_delegateaccount'), 'core')
            );
        }

        if (
            !has_capability('local/delegateaccount:revoke', $this->context) ||
            manager::get_delegation_status($row) === manager::STATUS_REVOKED
        ) {
            return implode('', $actions);
        }

        $actions[] = $OUTPUT->render_from_template('local_delegateaccount/delegation/revoke_action', [
            'delegationid' => (int)$row->id,
            'label' => get_string('revoke_delegation', 'local_delegateaccount'),
        ]);

        return implode('', $actions);
    }
}
