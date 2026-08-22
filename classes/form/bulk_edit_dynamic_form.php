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
 * Dynamic form for applying one lifecycle period to selected delegations.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class bulk_edit_dynamic_form extends dynamic_form {
    /**
     * Defines lifecycle fields and protected selection identifiers.
     */
    protected function definition() {
        $mform = $this->_form;
        $mform->addElement('date_time_selector', 'timestart', get_string('delegation_start', 'local_delegateaccount'));
        $allowopenended = get_config('local_delegateaccount', 'allowopenended');
        $mform->addElement(
            'date_time_selector',
            'timeend',
            get_string('delegation_end', 'local_delegateaccount'),
            ['optional' => $allowopenended === false || (bool)$allowopenended]
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
        }

        $mform->addElement('hidden', 'realuserid');
        $mform->setType('realuserid', PARAM_INT);
        $mform->addElement('hidden', 'delegationids');
        $mform->setType('delegationids', PARAM_SEQUENCE);
    }

    /**
     * Validates the common period selected for all records.
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
        if (empty($data['delegationids'])) {
            $errors['delegationids'] = get_string('noselected', 'core');
        }
        return $errors;
    }

    /**
     * Returns the system context.
     *
     * @return context System context.
     */
    protected function get_context_for_dynamic_submission(): context {
        return context_system::instance();
    }

    /**
     * Requires granular update access or the transitional capability.
     */
    protected function check_access_for_dynamic_submission(): void {
        $context = $this->get_context_for_dynamic_submission();
        if (!has_any_capability(['local/delegateaccount:update', 'local/delegateaccount:manage'], $context)) {
            require_capability('local/delegateaccount:update', $context);
        }
    }

    /**
     * Applies the common lifecycle settings atomically.
     *
     * @return array Submission result consumed by AMD.
     */
    public function process_dynamic_submission(): array {
        $data = $this->get_data();
        $policy = get_config('local_delegateaccount', 'notificationpolicy') ?: manager::NOTIFICATION_OPTIONAL;
        $notificationmode = $policy === manager::NOTIFICATION_OPTIONAL
            ? $data->notificationmode
            : $policy;
        $ids = array_map('intval', explode(',', $data->delegationids));
        $updatedcount = manager::update_delegations(
            $ids,
            (int)$data->realuserid,
            (int)$data->timestart,
            (int)$data->timeend,
            $notificationmode
        );

        $message = $updatedcount === 1
            ? get_string('delegation_updated_success', 'local_delegateaccount')
            : get_string('delegations_updated_success', 'local_delegateaccount', $updatedcount);
        \core\notification::success($message);

        return ['updatedcount' => $updatedcount];
    }

    /**
     * Loads defaults from the first selected active delegation.
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;

        $delegationids = $this->optional_param('delegationids', '', PARAM_SEQUENCE);
        $realuserid = $this->optional_param('realuserid', 0, PARAM_INT);
        $ids = array_filter(array_map('intval', explode(',', $delegationids)));
        $firstid = reset($ids) ?: 0;
        $record = $DB->get_record('local_delegateaccount', [
            'id' => $firstid,
            'realuserid' => $realuserid,
            'activekey' => 0,
        ], '*', MUST_EXIST);
        $record->delegationids = implode(',', $ids);
        $this->set_data($record);
    }

    /**
     * Returns the selected user's delegation page.
     *
     * @return moodle_url Delegation page URL.
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new moodle_url('/local/delegateaccount/delegations.php', [
            'realuserid' => $this->optional_param('realuserid', 0, PARAM_INT),
        ]);
    }
}
