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

namespace local_delegateaccount\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

use local_delegateaccount\manager;

/**
 * Form to create new delegations between users.
 *
 * @package    local_delegateaccount
 * @author     Miguel Rivas Morantes <miguelrivasmorantes@gmail.com>
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_form extends \moodleform {
    /**
     * Defines the fields used to create account delegations.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('create_delegations', 'local_delegateaccount'));

        $authorisedusers = manager::get_authorised_users();

        $mform->addElement('autocomplete', 'realuserids', get_string('realusers', 'local_delegateaccount'), $authorisedusers, [
            'multiple' => true,
            'placeholder' => get_string('search', 'core'),
        ]);
        $mform->addRule('realuserids', null, 'required', null, 'client');
        $mform->addHelpButton('realuserids', 'realusers', 'local_delegateaccount');

        $realuserid = (int)($this->_customdata['realuserid'] ?? 0);
        if ($realuserid > 0) {
            $mform->setDefault('realuserids', [$realuserid]);
            $mform->hardFreeze('realuserids');
            $mform->addElement('hidden', 'lockedrealuserid', $realuserid);
            $mform->setType('lockedrealuserid', PARAM_INT);
        }

        $mform->addElement(
            'autocomplete',
            'delegateduserids',
            get_string('delegatedusers', 'local_delegateaccount'),
            self::get_delegated_account_options($realuserid),
            [
                'multiple' => true,
                'placeholder' => get_string('search', 'core'),
            ]
        );
        $mform->addRule('delegateduserids', null, 'required', null, 'client');
        $mform->addHelpButton('delegateduserids', 'delegatedusers', 'local_delegateaccount');

        $mform->addElement(
            'date_time_selector',
            'timestart',
            get_string('delegation_start', 'local_delegateaccount')
        );
        $mform->setDefault('timestart', time());

        $allowopenendedsetting = get_config('local_delegateaccount', 'allowopenended');
        $allowopenended = $allowopenendedsetting === false ? true : (bool)$allowopenendedsetting;
        $mform->addElement(
            'date_time_selector',
            'timeend',
            get_string('delegation_end', 'local_delegateaccount'),
            ['optional' => $allowopenended]
        );

        $policy = get_config('local_delegateaccount', 'notificationpolicy') ?: manager::NOTIFICATION_OPTIONAL;
        if ($policy === manager::NOTIFICATION_OPTIONAL) {
            $mform->addElement(
                'select',
                'notificationmode',
                get_string('delegationnotificationmode', 'local_delegateaccount'),
                [
                    manager::NOTIFICATION_ALWAYS =>
                        get_string('delegationnotificationmode_always', 'local_delegateaccount'),
                    manager::NOTIFICATION_NEVER =>
                        get_string('delegationnotificationmode_never', 'local_delegateaccount'),
                ]
            );
            $mform->setDefault('notificationmode', manager::NOTIFICATION_ALWAYS);
            $mform->addHelpButton('notificationmode', 'delegationnotificationmode', 'local_delegateaccount');
        }

        $this->add_action_buttons(true, get_string('savechanges'));
    }

    /**
     * Returns active accounts that can safely be selected as delegation targets.
     *
     * @param int $realuserid Optional authorised user whose existing targets must be excluded.
     * @return array<int, string> User IDs mapped to display names.
     */
    public static function get_delegated_account_options(int $realuserid = 0): array {
        global $DB;

        $users = $DB->get_records(
            'user',
            ['deleted' => 0, 'suspended' => 0],
            'lastname ASC, firstname ASC',
            'id, firstname, lastname, middlename, alternatename, firstnamephonetic, lastnamephonetic'
        );
        $excludeduserids = [];
        if ($realuserid > 0) {
            $excludeduserids = array_fill_keys($DB->get_fieldset_select(
                'local_delegateaccount',
                'delegateduserid',
                'realuserid = :realuserid AND activekey = 0',
                ['realuserid' => $realuserid]
            ), true);
            $excludeduserids[$realuserid] = true;
        }
        $options = [];
        $protectprivilegedtargets = manager::protect_privileged_targets();
        foreach ($users as $user) {
            if (
                !isset($excludeduserids[(int)$user->id]) &&
                (!$protectprivilegedtargets || !is_siteadmin($user->id))
            ) {
                $options[(int)$user->id] = fullname($user);
            }
        }

        return $options;
    }

    /**
     * Validates the requested delegation period before it reaches the manager.
     *
     * @param array $data Submitted form data.
     * @param array $files Uploaded files.
     * @return array Validation errors indexed by field name.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files) + self::validate_period_values(
            (int)$data['timestart'],
            (int)$data['timeend']
        );
        $lockedrealuserid = (int)($this->_customdata['realuserid'] ?? 0);
        if ($lockedrealuserid > 0 && (int)($data['lockedrealuserid'] ?? 0) !== $lockedrealuserid) {
            $errors['realuserids'] = get_string('error_invalidlockeduser', 'local_delegateaccount');
        }

        return $errors;
    }

    /**
     * Validates a delegation period for regular and dynamic forms.
     *
     * @param int $timestart Requested start timestamp.
     * @param int $timeend Requested end timestamp, or zero.
     * @return array Validation errors indexed by field name.
     */
    public static function validate_period_values(int $timestart, int $timeend): array {
        $errors = [];

        $allowopenendedsetting = get_config('local_delegateaccount', 'allowopenended');
        $allowopenended = $allowopenendedsetting === false ? true : (bool)$allowopenendedsetting;
        if ($timeend === 0 && !$allowopenended) {
            $errors['timeend'] = get_string('error_openendednotallowed', 'local_delegateaccount');
        } else if ($timeend > 0 && $timeend <= $timestart) {
            $errors['timeend'] = get_string('error_invalidperiod', 'local_delegateaccount');
        }

        $maximumdurationdays = (int)get_config('local_delegateaccount', 'maximumdurationdays');
        if ($maximumdurationdays > 0 && $timeend > $timestart + ($maximumdurationdays * DAYSECS)) {
            $errors['timeend'] = get_string(
                'error_maximumduration',
                'local_delegateaccount',
                $maximumdurationdays
            );
        }

        return $errors;
    }
}
