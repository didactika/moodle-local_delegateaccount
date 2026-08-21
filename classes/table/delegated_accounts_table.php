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

    /**
     * Creates the delegated-account table for one authorised user.
     *
     * @param \moodle_url $baseurl URL retaining table state.
     * @param int $realuserid Authorised user ID.
     * @param \context_system $context System context for capabilities.
     */
    public function __construct(\moodle_url $baseurl, int $realuserid, \context_system $context) {
        parent::__construct('local_delegateaccount_delegated_accounts');
        $this->context = $context;
        $this->realuserid = $realuserid;

        $this->define_columns([
            'lastname',
            'email',
            'status',
            'timestart',
            'timeend',
            'lastaccess',
            'actions',
        ]);
        $this->define_headers([
            get_string('delegateduser', 'local_delegateaccount'),
            get_string('email'),
            get_string('delegation_status', 'local_delegateaccount'),
            get_string('delegation_start', 'local_delegateaccount'),
            get_string('delegation_end', 'local_delegateaccount'),
            get_string('last_delegated_access', 'local_delegateaccount'),
            get_string('actions'),
        ]);
        $this->sortable(true, 'lastname', SORT_ASC);
        $this->no_sorting('status');
        $this->no_sorting('actions');
        $this->collapsible(false);
        $this->is_downloadable(false);
        $this->pageable(true);
        $this->define_baseurl($baseurl);
        $this->set_attribute('id', 'local-delegateaccount-delegations');

        $fields = 'da.id, da.realuserid, da.delegateduserid, da.timestart, da.timeend,
                   da.timerevoked, da.activekey, u.firstname, u.lastname, u.middlename,
                   u.alternatename, u.firstnamephonetic, u.lastnamephonetic, u.email,
                   COALESCE(MAX(log.timecreated), 0) AS lastaccess';
        $from = '{local_delegateaccount} da
                 JOIN {user} u ON u.id = da.delegateduserid
                 LEFT JOIN {logstore_standard_log} log ON log.userid = da.delegateduserid
                    AND log.realuserid = da.realuserid';
        $where = 'da.realuserid = :realuserid';
        $groupby = 'da.id, da.realuserid, da.delegateduserid, da.timestart, da.timeend,
                    da.timerevoked, da.activekey, u.firstname, u.lastname, u.middlename,
                    u.alternatename, u.firstnamephonetic, u.lastnamephonetic, u.email';
        $countsql = 'SELECT COUNT(da.id) FROM {local_delegateaccount} da
                      WHERE da.realuserid = :realuserid';

        $this->set_count_sql($countsql, ['realuserid' => $realuserid]);
        $this->set_sql($fields, $from, $where . ' GROUP BY ' . $groupby, ['realuserid' => $realuserid]);
    }

    /**
     * Renders the delegated account's full name.
     *
     * @param \stdClass $row Delegated-account row.
     * @return string Escaped full name.
     */
    public function col_lastname($row): string {
        return fullname($row);
    }

    /**
     * Renders the derived lifecycle status.
     *
     * @param \stdClass $row Delegated-account row.
     * @return string Localised status.
     */
    public function col_status($row): string {
        $status = manager::get_delegation_status($row);
        return get_string('delegation_status_' . $status, 'local_delegateaccount');
    }

    /**
     * Renders the start of the delegation period.
     *
     * @param \stdClass $row Delegated-account row.
     * @return string Formatted start date.
     */
    public function col_timestart($row): string {
        return userdate((int)$row->timestart);
    }

    /**
     * Renders a delegation timestamp and handles open-ended access.
     *
     * @param \stdClass $row Delegated-account row.
     * @return string Formatted end date or the open-ended label.
     */
    public function col_timeend($row): string {
        if ((int)$row->timeend === 0) {
            return get_string('delegation_no_end', 'local_delegateaccount');
        }

        return userdate((int)$row->timeend);
    }

    /**
     * Renders the most recent recorded activity while using the delegated account.
     *
     * @param \stdClass $row Delegated-account row.
     * @return string Formatted timestamp or the no-access label.
     */
    public function col_lastaccess($row): string {
        if ((int)$row->lastaccess === 0) {
            return get_string('no_delegated_access', 'local_delegateaccount');
        }

        return userdate((int)$row->lastaccess);
    }

    /**
     * Renders actions permitted for this delegation.
     *
     * @param \stdClass $row Delegated-account row.
     * @return string Action controls.
     */
    public function col_actions($row): string {
        global $OUTPUT;

        if (!has_capability('local/delegateaccount:revoke', $this->context)) {
            return '';
        }

        if (manager::get_delegation_status($row) === manager::STATUS_REVOKED) {
            return '';
        }

        $url = new \moodle_url('/local/delegateaccount/delegations.php', [
            'realuserid' => $this->realuserid,
            'action' => 'revoke',
            'delegationid' => $row->id,
            'sesskey' => sesskey(),
        ]);
        return $OUTPUT->single_button(
            $url,
            get_string('revoke_delegation', 'local_delegateaccount'),
            'post',
            ['class' => 'btn btn-link btn-sm']
        );
    }
}
