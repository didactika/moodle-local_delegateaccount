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

/**
 * Form to create new delegations between users.
 *
 * @package    local_delegateaccount
 * @copyright  2026, Miguel Rivas Morantes <miguelrivasmorantes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_form extends \moodleform {
    public function definition() {
        global $DB;
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('create_delegations', 'local_delegateaccount'));

        $users = $DB->get_records_menu('user', ['deleted' => 0, 'suspended' => 0], 'lastname ASC', 'id, ' . $DB->sql_fullname());

        $mform->addElement('autocomplete', 'realuserids', get_string('realusers', 'local_delegateaccount'), $users, [
            'multiple' => true,
            'placeholder' => get_string('search', 'core'),
        ]);
        $mform->addRule('realuserids', null, 'required', null, 'client');
        $mform->addHelpButton('realuserids', 'realusers', 'local_delegateaccount');

        $mform->addElement('autocomplete', 'delegateduserids', get_string('delegatedusers', 'local_delegateaccount'), $users, [
            'multiple' => true,
            'placeholder' => get_string('search', 'core'),
        ]);
        $mform->addRule('delegateduserids', null, 'required', null, 'client');
        $mform->addHelpButton('delegateduserids', 'delegatedusers', 'local_delegateaccount');

        $this->add_action_buttons(true, get_string('savechanges'));
    }
}
