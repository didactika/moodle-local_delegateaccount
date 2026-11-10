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

namespace local_delegateaccount\output;

use local_delegateaccount\local\listing;
use local_delegateaccount\manager;
use local_delegateaccount\permission;

/**
 * Turns the records of the plugin into what its screens draw, with every text already in the language of the person.
 *
 * The web services of the lists return these arrays and the pages pass them to the templates, so every screen reads the same
 * fields whichever way it was drawn.
 *
 * @package    local_delegateaccount
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class exporter {
    /** Colour of the badge of each status. */
    private const STATUS_TONES = [
        manager::STATUS_ACTIVE => 'success',
        manager::STATUS_SCHEDULED => 'info',
        manager::STATUS_EXPIRED => 'warning',
        manager::STATUS_REVOKED => 'secondary',
    ];

    /** @var string[] Names of contexts already resolved, by identifier. */
    private static array $contexts = [];

    /**
     * A person, with what is needed to draw their picture or initials and their name.
     *
     * @param \stdClass $user User record with the name and picture fields.
     * @return array The person.
     */
    public static function user(\stdClass $user): array {
        global $PAGE;

        $hasimage = !empty($user->picture);
        $initials = \core_text::strtoupper(
            \core_text::substr((string)($user->firstname ?? ''), 0, 1) . \core_text::substr((string)($user->lastname ?? ''), 0, 1)
        );

        return [
            'id' => (int)$user->id,
            'fullname' => fullname($user),
            'email' => (string)($user->email ?? ''),
            'hasimage' => $hasimage,
            'imageurl' => $hasimage ? (new \user_picture($user))->get_url($PAGE)->out(false) : '',
            'initials' => $initials,
            'profileurl' => (new \moodle_url('/user/profile.php', ['id' => (int)$user->id]))->out(false),
            'url' => '',
            'linklabel' => '',
        ];
    }

    /**
     * A row of the management list of people.
     *
     * @param \stdClass $row Row of listing::users().
     * @param bool $cancreate Whether delegations can be created for the person.
     * @return array The row.
     */
    public static function user_row(\stdClass $row, bool $cancreate): array {
        $user = self::user($row);
        $user['url'] = (new \moodle_url('/local/delegateaccount/pages/delegations.php', ['realuserid' => $user['id']]))->out(false);
        $user['linklabel'] = get_string('manage_user_delegations_for', 'local_delegateaccount', $user['fullname']);

        return [
            'user' => $user,
            'activecount' => (int)$row->activecount,
            'scheduledcount' => (int)$row->scheduledcount,
            'url' => (new \moodle_url('/local/delegateaccount/pages/delegations.php', ['realuserid' => $user['id']]))->out(false),
            'cancreate' => $cancreate,
            'openlabel' => get_string('manage_user_delegations_for', 'local_delegateaccount', $user['fullname']),
            'addlabel' => get_string('add_delegation_for', 'local_delegateaccount', $user['fullname']),
        ];
    }

    /**
     * A row of a list of delegations.
     *
     * @param \stdClass $row Row of listing::delegations().
     * @param int $overrides Number of permission overrides of the delegation.
     * @return array The row.
     */
    public static function delegation_row(\stdClass $row, int $overrides): array {
        $real = self::user(listing::user_from_row($row, 'real'));
        $delegated = self::user(listing::user_from_row($row, 'delegated'));
        $status = manager::get_delegation_status($row);
        $open = $status !== manager::STATUS_REVOKED;
        $end = manager::get_delegation_display_end($row);
        $lastaccess = manager::get_last_delegated_access($row);
        $params = ['realuserid' => (int)$row->realuserid, 'delegationid' => (int)$row->id];
        $pair = (object)['authoriseduser' => $real['fullname'], 'delegateduser' => $delegated['fullname']];
        // The account opens the delegation; the authorised user opens the list of their delegations.
        $details = (new \moodle_url('/local/delegateaccount/pages/delegation.php', $params))->out(false);
        $delegated['url'] = $details;
        $delegated['linklabel'] = get_string(
            'view_details_of',
            'local_delegateaccount',
            get_string('delegation_pair', 'local_delegateaccount', $pair)
        );
        $real['url'] = (new \moodle_url('/local/delegateaccount/pages/delegations.php', [
            'realuserid' => (int)$row->realuserid,
        ]))->out(false);
        $real['linklabel'] = get_string('manage_user_delegations_for', 'local_delegateaccount', $real['fullname']);

        return [
            'id' => (int)$row->id,
            'realuser' => $real,
            'delegateduser' => $delegated,
            'status' => $status,
            'statuslabel' => get_string('delegation_status_' . $status, 'local_delegateaccount'),
            'statustone' => self::STATUS_TONES[$status],
            'timestart' => (int)$row->timestart,
            'start' => self::date((int)$row->timestart),
            'timeend' => $end,
            'end' => $end > 0 ? self::date($end) : get_string('delegation_no_end', 'local_delegateaccount'),
            'lastaccess' => $lastaccess > 0 ? self::date($lastaccess) : get_string('no_delegated_access', 'local_delegateaccount'),
            'hasaccess' => $lastaccess > 0,
            'overrides' => $overrides,
            'overrideslabel' => get_string('overridecount', 'local_delegateaccount', $overrides),
            'detailsurl' => $details,
            'permissionsurl' => (new \moodle_url('/local/delegateaccount/pages/delegation.php', $params + ['tab' => 'permissions']))
                ->out(false),
            'activityurl' => (new \moodle_url('/local/delegateaccount/pages/activity.php', $params))->out(false),
            'canupdate' => $open && permission::has(permission::UPDATE),
            'canrevoke' => $open && permission::has(permission::REVOKE),
            'canviewactivity' => permission::has(permission::VIEWACTIVITY),
            'selectable' => $open,
            'label' => get_string('delegation_pair', 'local_delegateaccount', $pair),
        ];
    }

    /**
     * A row of the list of accounts a person may open.
     *
     * @param \stdClass $row Row of listing::my_accounts().
     * @return array The row.
     */
    public static function account_row(\stdClass $row): array {
        $user = self::user($row);
        $end = (int)$row->timeend;

        return [
            'user' => $user,
            'delegationid' => (int)$row->delegationid,
            'start' => self::date((int)$row->timestart),
            'end' => $end > 0 ? self::date($end) : get_string('delegation_no_end', 'local_delegateaccount'),
            'hasend' => $end > 0,
            'loginurl' => (new \moodle_url('/local/delegateaccount/pages/loginas.php', [
                'id' => $user['id'],
                'sesskey' => sesskey(),
            ]))->out(false),
            'loginlabel' => get_string('use_delegated_account', 'local_delegateaccount', $user['fullname']),
        ];
    }

    /**
     * A row of the activity of a delegation.
     *
     * @param \stdClass $event Record of manager::get_delegation_activity_page().
     * @return array The row.
     */
    public static function activity_row(\stdClass $event): array {
        global $DB;

        $restored = ($event->event ?? null) instanceof \core\event\base ? $event->event : null;
        $context = self::context((int)$event->contextid);
        $related = !empty($event->relateduserid)
            ? $DB->get_record('user', ['id' => (int)$event->relateduserid], implode(',', \core_user\fields::get_picture_fields()))
            : false;
        $component = (string)$event->component;
        if ($component === 'core' || $component === 'legacy') {
            $componentname = get_string('coresystem');
        } else if (get_string_manager()->string_exists('pluginname', $component)) {
            $componentname = get_string('pluginname', $component);
        } else {
            $componentname = $component;
        }
        $url = $restored ? $restored->get_url() : null;

        return [
            'id' => (int)$event->id,
            'time' => userdate((int)$event->timecreated, get_string('strftimedatetimeaccurate', 'core_langconfig')),
            'eventname' => $restored ? $restored->get_name() : (string)$event->eventname,
            'eventurl' => $url ? $url->out(false) : '',
            'description' => $restored ? $restored->get_description() : '',
            'component' => $componentname,
            'contextname' => $context['name'],
            'contexturl' => $context['url'],
            'relateduser' => $related ? fullname($related) : '',
            'ip' => (string)($event->ip ?? ''),
            'origin' => (string)($event->origin ?? ''),
        ];
    }

    /**
     * A date in the short format of the language.
     *
     * @param int $time Timestamp.
     * @return string The date.
     */
    public static function date(int $time): string {
        return userdate($time, get_string('strftimedatetimeshort', 'core_langconfig'));
    }

    /**
     * The name and the address of a context, read once for every page.
     *
     * @param int $contextid Context identifier.
     * @return array{name: string, url: string} The context.
     */
    private static function context(int $contextid): array {
        if (!isset(self::$contexts[$contextid])) {
            $context = $contextid ? \context::instance_by_id($contextid, IGNORE_MISSING) : false;
            $url = $context ? $context->get_url() : null;
            self::$contexts[$contextid] = [
                'name' => $context ? $context->get_context_name(true) : get_string('other'),
                'url' => $url ? $url->out(false) : '',
            ];
        }

        return self::$contexts[$contextid];
    }
}
