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
        global $DB;

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
            array_unique($userids)
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
            $messagehtml = self::render_message(
                $action,
                $language,
                $delegation,
                $users[$delegation->realuserid],
                $users[$delegation->delegateduserid],
                $users[$actorid],
                $recipient
            );
            $messagebody = html_to_text($messagehtml);
            $message = new \core\message\message();
            $message->component = 'local_delegateaccount';
            $message->name = 'delegationnotification';
            $message->userfrom = $sender;
            $message->userto = $recipient;
            $message->subject = self::get_subject($action, $language);
            $message->fullmessage = $messagebody;
            $message->fullmessageformat = FORMAT_PLAIN;
            $message->fullmessagehtml = format_text($messagehtml, FORMAT_HTML, [
                'context' => \context_system::instance(),
            ]);
            $message->smallmessage = shorten_text($messagebody, 255);
            $message->notification = 1;
            if ($action === self::ACTION_CREATED && (int)$recipientid === (int)$delegation->realuserid) {
                $message->contexturl = (new \moodle_url('/local/delegateaccount/pages/accounts.php'))->out(false);
                $message->contexturlname = get_string_manager()->get_string(
                    'my_delegated_accounts',
                    'local_delegateaccount',
                    null,
                    $language
                );
            }

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
     * Renders one recipient's notification through its Mustache template.
     *
     * The built-in message is worded for the recipient: the authorised user is told which
     * account they can use, and the target account is told who can use it. The configured
     * custom template, when present, replaces the built-in message for granted access only.
     *
     * @param string $action Lifecycle action.
     * @param string $language Recipient language.
     * @param \stdClass $delegation Delegation database record.
     * @param \stdClass $authoriseduser Authorised user record.
     * @param \stdClass $delegateduser Target account record.
     * @param \stdClass $actor User who performed the action.
     * @param \stdClass $recipient Notification recipient.
     * @return string Rendered HTML notification.
     */
    private static function render_message(
        string $action,
        string $language,
        \stdClass $delegation,
        \stdClass $authoriseduser,
        \stdClass $delegateduser,
        \stdClass $actor,
        \stdClass $recipient
    ): string {
        global $OUTPUT, $SITE;

        $stringmanager = get_string_manager();
        $names = (object)[
            'authoriseduser' => fullname($authoriseduser),
            'delegateduser' => fullname($delegateduser),
            'sitefullname' => format_string($SITE->fullname, true),
        ];
        $timestart = userdate((int)$delegation->timestart, '', $recipient->timezone);
        $timeend = (int)$delegation->timeend === 0
            ? $stringmanager->get_string('never', 'moodle', null, $language)
            : userdate((int)$delegation->timeend, '', $recipient->timezone);

        $customcontent = '';
        if ($action === self::ACTION_CREATED) {
            $customcontent = (string)get_config('local_delegateaccount', 'notificationtemplate');
        }
        if (trim($customcontent) !== '') {
            return $OUTPUT->render_from_template('local_delegateaccount/notification/message', [
                'hascustomcontent' => true,
                'customcontent' => self::replace_placeholders($customcontent, [
                    'authoriseduser' => s($names->authoriseduser),
                    'delegateduser' => s($names->delegateduser),
                    'actor' => s(fullname($actor)),
                    'timestart' => s($timestart),
                    'timeend' => s($timeend),
                    'sitefullname' => s($names->sitefullname),
                ]),
            ]);
        }

        $isgranted = $action === self::ACTION_CREATED;
        $audience = (int)$recipient->id === (int)$delegation->delegateduserid ? 'target' : 'authorised';
        $string = static fn(string $identifier, $a = null): string =>
            $stringmanager->get_string($identifier, 'local_delegateaccount', $a, $language);

        return $OUTPUT->render_from_template('local_delegateaccount/notification/message', [
            'hascustomcontent' => false,
            'greeting' => $string('notificationgreeting', fullname($recipient)),
            'heading' => $string($isgranted ? 'notificationaccessgranted' : 'notificationaccessrevoked'),
            'summary' => $string('notification_' . ($isgranted ? 'granted' : 'revoked') . '_' . $audience, $names),
            'isgranted' => $isgranted,
            'accessstarts' => $string('notificationaccessstarts'),
            'timestart' => $timestart,
            'accessends' => $string('notificationaccessends'),
            'timeend' => $timeend,
            'supportmessage' => $string('notificationsupportmessage'),
        ]);
    }

    /**
     * Replaces the supported variables in administrator-supplied notification content.
     *
     * @param string $content Trusted administrator-supplied HTML content.
     * @param array $values Escaped replacement values indexed by placeholder name.
     * @return string Content with placeholders replaced.
     */
    private static function replace_placeholders(string $content, array $values): string {
        return preg_replace_callback(
            '/\{\$a->([^}]+)\}/',
            static function (array $matches) use ($values): string {
                return $values[$matches[1]] ?? '';
            },
            $content
        );
    }

    /**
     * Returns the notification subject in the recipient language.
     *
     * The configured subject, when present, applies to granted access only.
     *
     * @param string $action Lifecycle action.
     * @param string $language Recipient language.
     * @return string Notification subject.
     */
    private static function get_subject(string $action, string $language): string {
        if ($action === self::ACTION_CREATED) {
            $subject = trim((string)get_config('local_delegateaccount', 'notificationsubject'));
            if ($subject !== '') {
                return format_string($subject, true);
            }
        }

        return get_string_manager()->get_string(
            $action === self::ACTION_CREATED ? 'notification_subject_granted' : 'notification_subject_revoked',
            'local_delegateaccount',
            null,
            $language
        );
    }
}
