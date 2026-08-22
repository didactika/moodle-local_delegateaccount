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

namespace local_delegateaccount\table;

/**
 * Paginated self-service list of the current user's active delegated accounts.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class my_delegated_accounts_table extends \table_sql {
    /**
     * Defines the active-account query and presentation.
     *
     * @param \moodle_url $baseurl Stable list URL.
     * @param int $realuserid Current authorised user ID.
     */
    public function __construct(\moodle_url $baseurl, int $realuserid) {
        parent::__construct('local_delegateaccount_my_delegated_accounts');

        $this->define_columns(['lastname', 'email', 'timestart', 'timeend', 'actions']);
        $this->define_headers([
            get_string('delegateduser', 'local_delegateaccount'),
            get_string('email'),
            get_string('delegation_start', 'local_delegateaccount'),
            get_string('delegation_end', 'local_delegateaccount'),
            get_string('actions'),
        ]);
        $this->sortable(true, 'lastname', SORT_ASC);
        $this->no_sorting('actions');
        $this->collapsible(false);
        $this->is_downloadable(false);
        $this->pageable(true);
        $this->define_baseurl($baseurl);
        $this->set_attribute('id', 'local-delegateaccount-my-accounts');

        $now = time();
        $where = 'da.realuserid = :realuserid
                   AND da.activekey = 0
                   AND da.timestart <= :startnow
                   AND (da.timeend = 0 OR da.timeend > :endnow)
                   AND u.deleted = 0
                   AND u.suspended = 0';
        $params = [
            'realuserid' => $realuserid,
            'startnow' => $now,
            'endnow' => $now,
        ];
        $fields = 'da.id AS delegationid, da.delegateduserid, da.timestart, da.timeend,
                   u.id, u.firstname, u.lastname, u.middlename, u.alternatename,
                   u.firstnamephonetic, u.lastnamephonetic, u.email, u.picture, u.imagealt';
        $from = '{local_delegateaccount} da JOIN {user} u ON u.id = da.delegateduserid';

        $this->set_count_sql('SELECT COUNT(da.id) FROM ' . $from . ' WHERE ' . $where, $params);
        $this->set_sql($fields, $from, $where, $params);
    }

    /**
     * Renders the target user's identity with their Moodle profile image.
     *
     * @param \stdClass $row Delegated account row.
     * @return string Rendered identity.
     */
    public function col_lastname($row): string {
        global $OUTPUT;

        return $OUTPUT->render_from_template('local_delegateaccount/shared/user_identity', [
            'userpicture' => $OUTPUT->user_picture($row, ['size' => 35, 'link' => false]),
            'fullname' => fullname($row),
        ]);
    }

    /**
     * Renders the access start.
     *
     * @param \stdClass $row Delegated account row.
     * @return string Formatted date.
     */
    public function col_timestart($row): string {
        return userdate((int)$row->timestart);
    }

    /**
     * Renders the configured end or the open-ended label.
     *
     * @param \stdClass $row Delegated account row.
     * @return string Formatted date.
     */
    public function col_timeend($row): string {
        return (int)$row->timeend > 0
            ? userdate((int)$row->timeend)
            : get_string('delegation_no_end', 'local_delegateaccount');
    }

    /**
     * Renders the protected switch-account action.
     *
     * @param \stdClass $row Delegated account row.
     * @return string Action link.
     */
    public function col_actions($row): string {
        global $OUTPUT;

        return $OUTPUT->action_link(
            new \moodle_url('/local/delegateaccount/loginas.php', [
                'id' => (int)$row->delegateduserid,
                'sesskey' => sesskey(),
            ]),
            get_string('use_delegated_account', 'local_delegateaccount', fullname($row)),
            null,
            ['class' => 'btn btn-primary btn-sm']
        );
    }
}
