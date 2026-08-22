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
 * Search form for one authorised user's delegated accounts.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delegations_filter_form extends \moodleform {
    /**
     * Defines the filters available on every delegation-status tab.
     */
    public function definition() {
        $mform = $this->_form;
        $mform->disable_form_change_checker();
        $mform->updateAttributes(['method' => 'get']);
        $mform->updateAttributes(['class' => 'full-width-labels']);

        $mform->addElement(
            'text',
            'search',
            get_string('delegation_search', 'local_delegateaccount'),
            ['size' => 40]
        );
        $mform->setType('search', PARAM_TEXT);

        $mform->addElement('hidden', 'realuserid', $this->_customdata['realuserid']);
        $mform->setType('realuserid', PARAM_INT);
        $mform->addElement('hidden', 'status', $this->_customdata['status']);
        $mform->setType('status', PARAM_ALPHA);

        $applybutton = $mform->createElement('submit', 'submitbutton', get_string('apply'));
        $mform->addGroup([$applybutton], 'buttonar', '', ' ', false);
    }
}
