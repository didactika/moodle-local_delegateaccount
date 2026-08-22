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
        $mform->addElement(
            'autocomplete',
            'realuserids',
            get_string('realusers', 'local_delegateaccount'),
            manager::get_authorised_users(),
            ['multiple' => true, 'placeholder' => get_string('search', 'core')]
        );
        $mform->addRule('realuserids', null, 'required', null, 'client');
        $mform->addHelpButton('realuserids', 'realusers', 'local_delegateaccount');

        $mform->addElement(
            'autocomplete',
            'delegateduserids',
            get_string('delegatedusers', 'local_delegateaccount'),
            assign_form::get_delegated_account_options(),
            ['multiple' => true, 'placeholder' => get_string('search', 'core')]
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
        return parent::validation($data, $files) + assign_form::validate_period_values(
            (int)$data['timestart'],
            (int)$data['timeend']
        );
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
        $policy = get_config('local_delegateaccount', 'notificationpolicy') ?: manager::NOTIFICATION_OPTIONAL;
        $notificationmode = $policy === manager::NOTIFICATION_OPTIONAL
            ? $data->notificationmode
            : $policy;
        $createdcount = manager::create_delegations(
            $data->realuserids,
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
        return new moodle_url('/local/delegateaccount/assign.php', [
            'realuserid' => $this->optional_param('realuserid', 0, PARAM_INT),
        ]);
    }
}
