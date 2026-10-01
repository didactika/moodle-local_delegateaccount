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

use context;
use context_system;
use core_form\dynamic_form;
use local_delegateaccount\manager;
use moodle_url;

/**
 * Dynamic multi-user, multi-account delegation form.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class assign_dynamic_form extends dynamic_form {
    /**
     * Defines the assignment controls shown inside the core modal.
     */
    protected function definition() {
        $mform = $this->_form;
        $realuserid = $this->optional_param('realuserid', 0, PARAM_INT);
        if ($realuserid === 0) {
            $realuserid = $this->optional_param('lockedrealuserid', 0, PARAM_INT);
        }

        $authorisedusers = manager::get_authorised_users('', 30);
        if ($realuserid > 0 && !isset($authorisedusers[$realuserid])) {
            $user = \core_user::get_user($realuserid);
            if ($user && !$user->deleted && !$user->suspended) {
                $authorisedusers[$realuserid] = fullname($user);
            }
        }

        $mform->addElement(
            'autocomplete',
            'realuserids',
            get_string('realusers', 'local_delegateaccount'),
            $authorisedusers,
            [
                'multiple' => true,
                'placeholder' => get_string('search', 'core'),
                'ajax' => 'local_delegateaccount/form_user_selector',
                'data-ws' => 'local_delegateaccount_get_authorised_user_options',
            ]
        );
        $mform->addRule('realuserids', null, 'required', null, 'client');
        $mform->addHelpButton('realuserids', 'realusers', 'local_delegateaccount');
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
            assign_form::get_delegated_account_options($realuserid, '', 30),
            [
                'multiple' => true,
                'placeholder' => get_string('search', 'core'),
                'ajax' => 'local_delegateaccount/form_user_selector',
                'data-realuserid' => $realuserid,
            ]
        );
        $mform->addRule('delegateduserids', null, 'required', null, 'client');
        $mform->addHelpButton('delegateduserids', 'delegatedusers', 'local_delegateaccount');

        $mform->addElement('date_time_selector', 'timestart', get_string('delegation_start', 'local_delegateaccount'));
        $allowopenended = get_config('local_delegateaccount', 'allowopenended');
        $mform->addElement(
            'date_time_selector',
            'timeend',
            get_string('delegation_end', 'local_delegateaccount'),
            ['optional' => $allowopenended === false || (bool)$allowopenended]
        );
        self::add_notification_mode($mform);
    }

    /**
     * Adds the per-operation notification choice when site policy permits it.
     *
     * @param \MoodleQuickForm $mform Form being defined.
     */
    private static function add_notification_mode(\MoodleQuickForm $mform): void {
        $policy = get_config('local_delegateaccount', 'notificationpolicy') ?: manager::NOTIFICATION_OPTIONAL;
        if ($policy !== manager::NOTIFICATION_OPTIONAL) {
            return;
        }
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

    /**
     * Validates the selected lifecycle period.
     *
     * @param array $data Submitted values.
     * @param array $files Submitted files.
     * @return array Validation errors.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files) + assign_form::validate_period_values(
            (int)$data['timestart'],
            (int)$data['timeend']
        );
        $lockedrealuserid = $this->optional_param('realuserid', 0, PARAM_INT);
        if ($lockedrealuserid === 0) {
            $lockedrealuserid = $this->optional_param('lockedrealuserid', 0, PARAM_INT);
        }
        if ($lockedrealuserid > 0 && (int)($data['lockedrealuserid'] ?? 0) !== $lockedrealuserid) {
            $errors['realuserids'] = get_string('error_invalidlockeduser', 'local_delegateaccount');
        }

        if (empty($errors['realuserids']) && empty($errors['delegateduserids'])) {
            $realuserids = $lockedrealuserid > 0
                ? [$lockedrealuserid]
                : (!empty($data['realuserids']) && is_array($data['realuserids']) ? $data['realuserids'] : []);

            $delegateduserids = (!empty($data['delegateduserids']) && is_array($data['delegateduserids']))
                ? $data['delegateduserids']
                : [];

            if (!empty($realuserids) && !empty($delegateduserids)) {
                $bulkcount = count($realuserids) * count($delegateduserids);
                if ($bulkerror = manager::get_bulk_operation_error($bulkcount)) {
                    if (count($realuserids) === 1) {
                        $errors['delegateduserids'] = $bulkerror;
                    } else {
                        $errors['realuserids'] = $bulkerror;
                    }
                } else {
                    $newcounts = array_fill_keys($realuserids, count($delegateduserids));
                    if ($limiterror = manager::get_delegation_limit_error($newcounts)) {
                        if (count($realuserids) === 1) {
                            $errors['delegateduserids'] = $limiterror;
                        } else {
                            $errors['realuserids'] = $limiterror;
                        }
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Returns the system context used by delegation management.
     *
     * @return context System context.
     */
    protected function get_context_for_dynamic_submission(): context {
        return context_system::instance();
    }

    /**
     * Requires granular creation access or the transitional capability.
     */
    protected function check_access_for_dynamic_submission(): void {
        $context = $this->get_context_for_dynamic_submission();
        if (!has_any_capability(['local/delegateaccount:create', 'local/delegateaccount:manage'], $context)) {
            require_capability('local/delegateaccount:create', $context);
        }
    }

    /**
     * Creates the requested delegation matrix.
     *
     * @return array Submission result consumed by AMD.
     */
    public function process_dynamic_submission(): array {
        $data = $this->get_data();
        $lockedrealuserid = $this->optional_param('realuserid', 0, PARAM_INT);
        if ($lockedrealuserid === 0) {
            $lockedrealuserid = $this->optional_param('lockedrealuserid', 0, PARAM_INT);
        }
        $realuserids = $lockedrealuserid > 0 ? [$lockedrealuserid] : $data->realuserids;
        $policy = get_config('local_delegateaccount', 'notificationpolicy') ?: manager::NOTIFICATION_OPTIONAL;
        $notificationmode = $policy === manager::NOTIFICATION_OPTIONAL
            ? $data->notificationmode
            : $policy;
        try {
            $createdcount = manager::create_delegations(
                $realuserids,
                $data->delegateduserids,
                [
                    'timestart' => (int)$data->timestart,
                    'timeend' => (int)$data->timeend,
                    'notificationmode' => $notificationmode,
                ]
            );

            if ($createdcount > 0) {
                \core\notification::success(get_string('delegations_created_success', 'local_delegateaccount'));
            } else {
                \core\notification::warning(get_string('no_delegations_created', 'local_delegateaccount'));
            }

            return ['createdcount' => $createdcount];
        } catch (\moodle_exception $e) {
            \core\notification::error($e->getMessage());
            return ['createdcount' => 0];
        }
    }

    /**
     * Applies defaults and an optional preselected authorised user.
     */
    public function set_data_for_dynamic_submission(): void {
        $realuserid = $this->optional_param('realuserid', 0, PARAM_INT);
        $data = ['timestart' => time()];
        if ($realuserid > 0) {
            $data['realuserids'] = [$realuserid];
        }
        $this->set_data($data);
    }

    /**
     * Returns the stable fallback page for the dynamic form.
     *
     * @return moodle_url Assignment page URL.
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $realuserid = $this->optional_param('realuserid', 0, PARAM_INT);
        if ($realuserid === 0) {
            $realuserid = $this->optional_param('lockedrealuserid', 0, PARAM_INT);
        }
        return new moodle_url('/local/delegateaccount/pages/assign.php', [
            'realuserid' => $realuserid,
        ]);
    }
}
