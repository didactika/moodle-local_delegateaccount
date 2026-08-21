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
 * Form to update the lifecycle boundaries of one account delegation.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit_form extends \moodleform {
    /**
     * Defines the lifecycle fields that may be changed for one delegation.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('edit_delegation', 'local_delegateaccount'));
        $mform->addElement(
            'date_time_selector',
            'timestart',
            get_string('delegation_start', 'local_delegateaccount')
        );

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
            $mform->addHelpButton('notificationmode', 'delegationnotificationmode', 'local_delegateaccount');
        }

        $this->add_action_buttons(true, get_string('savechanges'));
    }

    /**
     * Validates the requested period against the configured site boundaries.
     *
     * @param array $data Submitted form data.
     * @param array $files Uploaded files.
     * @return array Validation errors indexed by field name.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        $timestart = (int)$data['timestart'];
        $timeend = (int)$data['timeend'];

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
