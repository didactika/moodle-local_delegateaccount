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

namespace local_delegateaccount\event;

/**
 * Event emitted when a delegated-account authorisation is revoked.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class delegation_revoked extends \core\event\base {
    /**
     * Initialises the event metadata.
     */
    protected function init(): void {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_delegateaccount';
    }

    /**
     * Gets the event name.
     *
     * @return string Localised event name.
     */
    public static function get_name(): string {
        return get_string('eventdelegationrevoked', 'local_delegateaccount');
    }

    /**
     * Gets the event description for the log store.
     *
     * @return string Event description.
     */
    public function get_description(): string {
        return "The user with id '{$this->userid}' revoked a delegation for the user with id " .
            "'{$this->relateduserid}' to access the account with id '{$this->other['delegateduserid']}'.";
    }

    /**
     * Gets a URL for the management page.
     *
     * @return \moodle_url Event URL.
     */
    public function get_url(): \moodle_url {
        return new \moodle_url('/local/delegateaccount/manage.php');
    }
}
