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
 * Native Moodle report-style filter form for the delegated-account management table.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manage_filter_form extends \moodleform {
    /**
     * Defines the filters that are relevant to delegated-account administration.
     */
    public function definition() {
        $mform = $this->_form;
        $mform->disable_form_change_checker();
        $mform->updateAttributes(['method' => 'get']);
        $mform->setAttributes(['class' => 'full-width-labels']);

        $mform->addElement('text', 'search', get_string('delegation_filter_user', 'local_delegateaccount'), ['size' => 40]);
        $mform->setType('search', PARAM_TEXT);

        $statusoptions = [
            '' => get_string('isanyvalue', 'filters'),
            manager::STATUS_ACTIVE => get_string('delegation_status_active', 'local_delegateaccount'),
            manager::STATUS_SCHEDULED => get_string('delegation_status_scheduled', 'local_delegateaccount'),
            manager::STATUS_EXPIRED => get_string('delegation_status_expired', 'local_delegateaccount'),
            manager::STATUS_REVOKED => get_string('delegation_status_revoked', 'local_delegateaccount'),
            'none' => get_string('delegation_filter_none', 'local_delegateaccount'),
        ];
        $mform->addElement('select', 'delegationstatus', get_string('delegation_status', 'local_delegateaccount'), $statusoptions);
        $mform->setType('delegationstatus', PARAM_ALPHA);

        $mform->addElement('hidden', 'tab', $this->_customdata['tab']);
        $mform->setType('tab', PARAM_ALPHA);

        $applybutton = $mform->createElement('submit', 'submitbutton', get_string('apply'));
        $mform->addGroup([$applybutton], 'buttonar', '', ' ', false);
    }
}
