<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_delegateaccount\event;

/**
 * Event emitted when the permission overrides of delegated sessions change.
 *
 * The object is the delegation when exactly one delegation changed. For the site and for changes of many delegations at
 * once there is no object, and 'other' says how many delegations were reached.
 *
 * @property-read array $other {
 *      - string scope: site, delegation, bulk or copy.
 *      - int count: number of delegations reached.
 *      - int sourceid: delegation the overrides were copied from, or zero.
 *      - array changes: permission by capability, at most 50 of them.
 *      - int realuserid: (optional) authorised user of the delegation.
 * }
 *
 * @package    local_delegateaccount
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class session_permissions_updated extends \core\event\base {
    /**
     * Initialises the event metadata.
     */
    protected function init(): void {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_delegateaccount';
    }

    /**
     * Gets the event name.
     *
     * @return string Localised event name.
     */
    public static function get_name(): string {
        return get_string('eventsessionpermissionsupdated', 'local_delegateaccount');
    }

    /**
     * Gets the event description for the log store.
     *
     * @return string Event description.
     */
    public function get_description(): string {
        $count = count($this->other['changes'] ?? []);
        if (($this->other['scope'] ?? '') === 'site') {
            return "The user with id '{$this->userid}' changed $count permission overrides of every delegated session.";
        }
        if (!empty($this->objectid)) {
            return "The user with id '{$this->userid}' changed $count permission overrides of the delegation with id " .
                "'{$this->objectid}'.";
        }

        return "The user with id '{$this->userid}' changed $count permission overrides of {$this->other['count']} delegations.";
    }

    /**
     * Gets the URL of what changed.
     *
     * @return \moodle_url Event URL.
     */
    public function get_url(): \moodle_url {
        if (($this->other['scope'] ?? '') === 'site') {
            return new \moodle_url('/admin/settings.php', ['section' => 'local_delegateaccount_settings']);
        }
        if (!empty($this->objectid) && !empty($this->other['realuserid'])) {
            return new \moodle_url('/local/delegateaccount/pages/delegation.php', [
                'realuserid' => $this->other['realuserid'],
                'delegationid' => $this->objectid,
                'tab' => 'permissions',
            ]);
        }

        return new \moodle_url('/local/delegateaccount/pages/manage.php');
    }

    /**
     * Checks the data the event needs.
     */
    protected function validate_data(): void {
        parent::validate_data();
        if (!isset($this->other['scope'], $this->other['count'], $this->other['changes'])) {
            throw new \coding_exception('The scope, count and changes of the event must be set.');
        }
    }

    /**
     * The delegation, when there is one, is restored through its table.
     *
     * @return array Mapping of the object.
     */
    public static function get_objectid_mapping(): array {
        return ['db' => 'local_delegateaccount', 'restore' => \core\event\base::NOT_MAPPED];
    }

    /**
     * Nothing in 'other' needs mapping.
     *
     * @return bool
     */
    public static function get_other_mapping(): bool {
        return false;
    }
}
