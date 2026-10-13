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

use local_delegateaccount\notification_manager;

/**
 * Chooses which saved notification to send to oneself as a test.
 *
 * @package    local_delegateaccount
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class test_notification_form extends \moodleform {
    /**
     * Defines the language and action choices.
     */
    public function definition() {
        $mform = $this->_form;

        $languages = get_string_manager()->get_list_of_translations();
        $mform->addElement('select', 'language', get_string('testnotification_language', 'local_delegateaccount'), $languages);
        $mform->setDefault('language', current_language());

        $mform->addElement('select', 'action', get_string('testnotification_action', 'local_delegateaccount'), [
            notification_manager::ACTION_CREATED => get_string('notification_subject_granted', 'local_delegateaccount'),
            notification_manager::ACTION_REVOKED => get_string('notification_subject_revoked', 'local_delegateaccount'),
        ]);

        $this->add_action_buttons(false, get_string('testnotification', 'local_delegateaccount'));
    }
}
