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

use local_delegateaccount\manager;

/**
 * Validity period and notification fields shared by the delegation forms.
 *
 * @package    local_delegateaccount
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait lifecycle_fields {
    /**
     * Adds the start date, the end date and, when the site policy allows a choice, the notification choice.
     *
     * @param \MoodleQuickForm $mform Form being defined.
     */
    protected function add_lifecycle_fields(\MoodleQuickForm $mform): void {
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
                    manager::NOTIFICATION_ALWAYS => get_string('delegationnotificationmode_always', 'local_delegateaccount'),
                    manager::NOTIFICATION_NEVER => get_string('delegationnotificationmode_never', 'local_delegateaccount'),
                ]
            );
            $mform->setDefault('notificationmode', manager::NOTIFICATION_ALWAYS);
            $mform->addHelpButton('notificationmode', 'delegationnotificationmode', 'local_delegateaccount');
        }
    }

    /**
     * Validates the submitted period against the site settings.
     *
     * @param array $data Submitted values.
     * @return array Validation errors indexed by field name.
     */
    protected function get_lifecycle_errors(array $data): array {
        $error = manager::get_period_error((int)$data['timestart'], (int)$data['timeend']);

        return $error === null ? [] : ['timeend' => $error];
    }

    /**
     * Returns the submitted notification choice, or the site decision when the form offers none.
     *
     * @param \stdClass $data Submitted values.
     * @return string Notification mode for the manager.
     */
    protected function get_notification_mode(\stdClass $data): string {
        return $data->notificationmode ?? manager::NOTIFICATION_SITE;
    }
}
