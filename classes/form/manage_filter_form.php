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
 * Native Moodle filter form for the delegated-account management table.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manage_filter_form extends \moodleform {
    /**
     * Defines the user filters and their standard Moodle submit control.
     */
    public function definition() {
        $mform = $this->_form;
        $mform->updateAttributes(['method' => 'get']);

        foreach ([
            'fullname' => get_string('fullname'),
            'firstname' => get_string('firstname'),
            'lastname' => get_string('lastname'),
            'username' => get_string('username'),
            'idnumber' => get_string('idnumber'),
            'email' => get_string('email'),
        ] as $name => $label) {
            $mform->addElement('text', $name, $label, ['size' => 40]);
            $mform->setType($name, PARAM_TEXT);
        }

        $mform->addElement('submit', 'submitbutton', get_string('apply'));
    }
}
