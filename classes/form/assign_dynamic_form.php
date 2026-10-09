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
use local_delegateaccount\permission;
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
    use lifecycle_fields;

    /**
     * Defines the assignment controls shown inside the core modal.
     */
    protected function definition() {
        global $OUTPUT;

        $mform = $this->_form;
        $realuserid = $this->optional_param('realuserid', 0, PARAM_INT);
        if ($realuserid === 0) {
            $realuserid = $this->optional_param('lockedrealuserid', 0, PARAM_INT);
        }

        if (($hint = manager::get_role_setup_hint()) !== null) {
            $mform->addElement('html', $OUTPUT->notification($hint, \core\output\notification::NOTIFY_INFO, false));
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
            manager::get_delegated_account_options($realuserid, '', 30),
            [
                'multiple' => true,
                'placeholder' => get_string('search', 'core'),
                'ajax' => 'local_delegateaccount/form_user_selector',
                'data-realuserid' => $realuserid,
            ]
        );
        $mform->addRule('delegateduserids', null, 'required', null, 'client');
        $mform->addHelpButton('delegateduserids', 'delegatedusers', 'local_delegateaccount');

        $this->add_lifecycle_fields($mform);
    }

    /**
     * Validates the selected lifecycle period.
     *
     * @param array $data Submitted values.
     * @param array $files Submitted files.
     * @return array Validation errors.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files) + $this->get_lifecycle_errors($data);
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
        permission::require_action(permission::CREATE);
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
        $notificationmode = $this->get_notification_mode($data);
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
     * Returns the page the form is opened from.
     *
     * @return moodle_url The authorised user's delegations, or the management overview.
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $realuserid = $this->optional_param('realuserid', 0, PARAM_INT);
        if ($realuserid === 0) {
            $realuserid = $this->optional_param('lockedrealuserid', 0, PARAM_INT);
        }
        if ($realuserid > 0) {
            return new moodle_url('/local/delegateaccount/pages/delegations.php', ['realuserid' => $realuserid]);
        }

        return new moodle_url('/local/delegateaccount/pages/manage.php');
    }
}
