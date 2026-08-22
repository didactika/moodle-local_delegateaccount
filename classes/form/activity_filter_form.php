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
 * Native filters for the delegated activity report.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class activity_filter_form extends \moodleform {
    /**
     * Defines period and event metadata filters.
     */
    public function definition() {
        $mform = $this->_form;
        $mform->disable_form_change_checker();
        $mform->updateAttributes(['method' => 'get']);
        $mform->updateAttributes(['class' => 'full-width-labels']);

        $mform->addElement(
            'date_time_selector',
            'datefrom',
            get_string('activity_filter_datefrom', 'local_delegateaccount'),
            ['optional' => true]
        );
        $mform->addElement(
            'date_time_selector',
            'dateto',
            get_string('activity_filter_dateto', 'local_delegateaccount'),
            ['optional' => true]
        );
        $mform->addElement(
            'text',
            'component',
            get_string('activity_filter_component', 'local_delegateaccount'),
            ['size' => 40]
        );
        $mform->setType('component', PARAM_TEXT);
        $mform->addElement(
            'text',
            'action',
            get_string('activity_filter_action', 'local_delegateaccount'),
            ['size' => 40]
        );
        $mform->setType('action', PARAM_TEXT);

        $mform->addElement('hidden', 'realuserid', $this->_customdata['realuserid']);
        $mform->setType('realuserid', PARAM_INT);
        $mform->addElement('hidden', 'delegationid', $this->_customdata['delegationid']);
        $mform->setType('delegationid', PARAM_INT);

        $applybutton = $mform->createElement('submit', 'submitbutton', get_string('apply'));
        $mform->addGroup([$applybutton], 'buttonar', '', ' ', false);
    }

    /**
     * Ensures the requested report period is ordered.
     *
     * @param array $data Submitted values.
     * @param array $files Submitted files.
     * @return array Validation errors.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (!empty($data['datefrom']) && !empty($data['dateto']) && $data['dateto'] <= $data['datefrom']) {
            $errors['dateto'] = get_string('activity_filter_invalidperiod', 'local_delegateaccount');
        }

        return $errors;
    }
}
