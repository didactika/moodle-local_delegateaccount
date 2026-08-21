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

namespace local_delegateaccount;

/**
 * Delivers configured notifications without retaining message content in plugin data.
 *
 * @package    local_delegateaccount
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notification_manager {
    /** Notification sent when a delegation is created. */
    public const ACTION_CREATED = 'created';

    /** Notification sent when a delegation is revoked. */
    public const ACTION_REVOKED = 'revoked';

    /**
     * Sends notifications for one delegation when the configured policy permits it.
     *
     * @param \stdClass $delegation Delegation database record.
     * @param string $action Lifecycle action.
     * @param int $actorid User who performed the action.
     * @return bool Whether at least one message was accepted by Moodle.
     */
    public static function notify(\stdClass $delegation, string $action, int $actorid): bool {
        global $DB, $SITE;

        if (!self::should_notify($delegation, $action)) {
            return false;
        }

        $userids = self::get_recipient_ids($delegation);
        if (empty($userids)) {
            return false;
        }

        $userids[] = $actorid;
        $users = $DB->get_records_list(
            'user',
            'id',
            array_unique($userids),
            '',
            'id, firstname, lastname, middlename, alternatename, firstnamephonetic, lastnamephonetic, lang, timezone'
        );
        if (!isset($users[$delegation->realuserid], $users[$delegation->delegateduserid], $users[$actorid])) {
            return false;
        }

        $sender = \core_user::get_noreply_user();
        $sent = false;
        foreach (self::get_recipient_ids($delegation) as $recipientid) {
            if (!isset($users[$recipientid])) {
                continue;
            }

            $recipient = $users[$recipientid];
            $language = empty($recipient->lang) ? current_language() : $recipient->lang;
            $messagebody = self::render_template(
                $language,
                $delegation,
                $users[$delegation->realuserid],
                $users[$delegation->delegateduserid],
                $users[$actorid],
                $SITE
            );
            $message = new \core\message\message();
            $message->component = 'local_delegateaccount';
            $message->name = 'delegationnotification';
            $message->userfrom = $sender;
            $message->userto = $recipient;
            $message->subject = get_string_manager()->get_string(
                'delegationnotificationsubject',
                'local_delegateaccount',
                null,
                $language
            );
            $message->fullmessage = $messagebody;
            $message->fullmessageformat = FORMAT_PLAIN;
            $message->fullmessagehtml = nl2br(s($messagebody));
            $message->smallmessage = shorten_text($messagebody, 255);
            $message->notification = 1;
            $message->contexturl = (new \moodle_url('/local/delegateaccount/manage.php'))->out(false);
            $message->contexturlname = get_string('manage_accounts', 'local_delegateaccount');

            try {
                $sent = message_send($message) !== false || $sent;
            } catch (\Throwable $exception) {
                continue;
            }
        }

        if ($sent) {
            $DB->set_field('local_delegateaccount', 'timenotified', time(), ['id' => $delegation->id]);
        }

        return $sent;
    }

    /**
     * Decides whether the current lifecycle action should produce a notification.
     *
     * @param \stdClass $delegation Delegation database record.
     * @param string $action Lifecycle action.
     * @return bool Whether notification delivery is enabled.
     */
    private static function should_notify(\stdClass $delegation, string $action): bool {
        if ($delegation->notificationmode === manager::NOTIFICATION_NEVER) {
            return false;
        }
        if ($action === self::ACTION_REVOKED && !(bool) get_config('local_delegateaccount', 'notifyonrevocation')) {
            return false;
        }

        return $action === self::ACTION_CREATED || $action === self::ACTION_REVOKED;
    }

    /**
     * Returns the distinct selected recipients for a delegation notification.
     *
     * @param \stdClass $delegation Delegation database record.
     * @return int[] Recipient user IDs.
     */
    private static function get_recipient_ids(\stdClass $delegation): array {
        $recipients = get_config('local_delegateaccount', 'notificationrecipients') ?: 'both';
        if ($recipients === 'authorised') {
            return [(int) $delegation->realuserid];
        }
        if ($recipients === 'target') {
            return [(int) $delegation->delegateduserid];
        }

        return array_values(array_unique([
            (int) $delegation->realuserid,
            (int) $delegation->delegateduserid,
        ]));
    }

    /**
     * Expands a configured, language-specific plain text template.
     *
     * @param string $language Recipient language.
     * @param \stdClass $delegation Delegation database record.
     * @param \stdClass $authoriseduser Authorised user record.
     * @param \stdClass $delegateduser Target account record.
     * @param \stdClass $actor User who configured the action.
     * @param \stdClass $site Site record.
     * @return string Rendered plain text notification.
     */
    private static function render_template(
        string $language,
        \stdClass $delegation,
        \stdClass $authoriseduser,
        \stdClass $delegateduser,
        \stdClass $actor,
        \stdClass $site
    ): string {
        $template = get_config('local_delegateaccount', 'notificationtemplate_' . $language);
        if ($template === false || $template === '') {
            $template = get_string_manager()->get_string(
                'notificationtemplatedefault',
                'local_delegateaccount',
                null,
                $language
            );
        }

        $values = [
            'authoriseduser' => fullname($authoriseduser),
            'delegateduser' => fullname($delegateduser),
            'actor' => fullname($actor),
            'timestart' => userdate((int) $delegation->timestart, '', $authoriseduser->timezone),
            'timeend' => (int) $delegation->timeend === 0
                ? get_string_manager()->get_string('never', 'moodle', null, $language)
                : userdate((int) $delegation->timeend, '', $authoriseduser->timezone),
            'sitefullname' => format_string($site->fullname, true),
        ];

        return preg_replace_callback(
            '/\{\$a->([^}]+)\}/',
            static function (array $matches) use ($values): string {
                return $values[$matches[1]] ?? '';
            },
            $template
        );
    }
}
