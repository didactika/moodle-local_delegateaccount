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

    /** Recipient who can use the delegated account. */
    public const AUDIENCE_AUTHORISED = 'authorised';

    /** Recipient whose account is delegated. */
    public const AUDIENCE_TARGET = 'target';

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

        $sent = false;
        foreach (self::get_recipient_ids($delegation) as $recipientid) {
            if (!isset($users[$recipientid])) {
                continue;
            }

            $recipient = $users[$recipientid];
            $message = self::build_message(
                $action,
                self::get_recipient_language($recipient),
                (int)$recipientid === (int)$delegation->delegateduserid ? self::AUDIENCE_TARGET : self::AUDIENCE_AUTHORISED,
                $delegation,
                $users[$delegation->realuserid],
                $users[$delegation->delegateduserid],
                $users[$actorid],
                $recipient
            );

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
     * Sends a test notification to a user, once as each recipient would receive it.
     *
     * Uses the saved subject and message for the action in the chosen language, with sample
     * names, and adds a test marker to the subject.
     *
     * @param string $action Lifecycle action.
     * @param string $language Language whose subject and message are tested.
     * @param \stdClass $user User who receives the test.
     * @return int Number of messages accepted by Moodle.
     */
    public static function send_test(string $action, string $language, \stdClass $user): int {
        $stringmanager = get_string_manager();
        $sample = static fn(string $person): \stdClass => self::make_sample_user(
            $stringmanager->get_string('test_' . $person . '_firstname', 'local_delegateaccount', null, $language),
            $stringmanager->get_string('test_' . $person . '_lastname', 'local_delegateaccount', null, $language)
        );
        $authorised = $sample('authoriseduser');
        $delegated = $sample('delegateduser');
        $delegation = (object)[
            'id' => 0,
            'realuserid' => 0,
            'delegateduserid' => 0,
            'timestart' => time(),
            'timeend' => time() + WEEKSECS,
        ];

        $sent = 0;
        foreach ([self::AUDIENCE_AUTHORISED, self::AUDIENCE_TARGET] as $audience) {
            $message = self::build_message($action, $language, $audience, $delegation, $authorised, $delegated, $user, $user);
            $message->subject = $stringmanager->get_string('test_subject', 'local_delegateaccount', (object)[
                'audience' => $stringmanager->get_string('test_audience_' . $audience, 'local_delegateaccount', null, $language),
                'subject' => $message->subject,
            ], $language);
            if (message_send($message) !== false) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Builds the notification one recipient receives.
     *
     * @param string $action Lifecycle action.
     * @param string $language Language of the notification.
     * @param string $audience Whether the recipient is the authorised user or the delegated account.
     * @param \stdClass $delegation Delegation database record.
     * @param \stdClass $authoriseduser Authorised user record.
     * @param \stdClass $delegateduser Delegated account record.
     * @param \stdClass $actor User who performed the action.
     * @param \stdClass $recipient Notification recipient.
     * @return \core\message\message Message ready to send.
     */
    private static function build_message(
        string $action,
        string $language,
        string $audience,
        \stdClass $delegation,
        \stdClass $authoriseduser,
        \stdClass $delegateduser,
        \stdClass $actor,
        \stdClass $recipient
    ): \core\message\message {
        // Compose in the recipient's language, so that dates and any other text formatted
        // through the current language follow the recipient rather than whoever acted.
        $previouslanguage = force_current_language($language);
        try {
            $messagehtml = self::render_message(
                $action,
                $language,
                $audience,
                $delegation,
                $authoriseduser,
                $delegateduser,
                $actor,
                $recipient
            );
        } finally {
            force_current_language($previouslanguage);
        }
        $messagebody = html_to_text($messagehtml);

        $message = new \core\message\message();
        $message->component = 'local_delegateaccount';
        $message->name = 'delegationnotification';
        $message->userfrom = \core_user::get_noreply_user();
        $message->userto = $recipient;
        $message->subject = self::get_subject($action, $language);
        $message->fullmessage = $messagebody;
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = format_text($messagehtml, FORMAT_HTML, [
            'context' => \context_system::instance(),
        ]);
        $message->smallmessage = shorten_text($messagebody, 255);
        $message->notification = 1;
        if ($action === self::ACTION_CREATED && $audience === self::AUDIENCE_AUTHORISED) {
            $message->contexturl = (new \moodle_url('/local/delegateaccount/pages/accounts.php'))->out(false);
            $message->contexturlname = get_string_manager()->get_string(
                'my_delegated_accounts',
                'local_delegateaccount',
                null,
                $language
            );
        }

        return $message;
    }

    /**
     * Creates a user record with only a name, for test notifications.
     *
     * @param string $firstname First name.
     * @param string $lastname Last name.
     * @return \stdClass User record.
     */
    private static function make_sample_user(string $firstname, string $lastname): \stdClass {
        $user = (object)array_fill_keys(\core_user\fields::get_name_fields(), '');
        $user->firstname = $firstname;
        $user->lastname = $lastname;

        return $user;
    }

    /**
     * Decides whether the current lifecycle action should produce a notification.
     *
     * @param \stdClass $delegation Delegation database record.
     * @param string $action Lifecycle action.
     * @return bool Whether notification delivery is enabled.
     */
    private static function should_notify(\stdClass $delegation, string $action): bool {
        // Never notify applies from the moment it is set, also to delegations created before.
        if (get_config('local_delegateaccount', 'notificationpolicy') === manager::NOTIFICATION_NEVER) {
            return false;
        }
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
     * account they can use, and the target account is told who can use it. A message configured
     * for the action in the recipient's language replaces the built-in message.
     *
     * @param string $action Lifecycle action.
     * @param string $language Recipient language.
     * @param string $audience Whether the recipient is the authorised user or the delegated account.
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
        string $audience,
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

        $customcontent = (string)get_config(
            'local_delegateaccount',
            'notificationtemplate_' . self::get_setting_action($action) . '_' . $language
        );
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
     * A subject configured for the action in the recipient's language replaces the built-in subject.
     *
     * @param string $action Lifecycle action.
     * @param string $language Recipient language.
     * @return string Notification subject.
     */
    private static function get_subject(string $action, string $language): string {
        $settingaction = self::get_setting_action($action);
        $subject = trim((string)get_config('local_delegateaccount', 'notificationsubject_' . $settingaction . '_' . $language));
        if ($subject !== '') {
            return format_string($subject, true);
        }

        return get_string_manager()->get_string('notification_subject_' . $settingaction, 'local_delegateaccount', null, $language);
    }

    /**
     * Returns the language a recipient's notification is written in.
     *
     * Matches the language Moodle shows the recipient: their profile language when it is
     * installed, otherwise the site language, for example after a language pack is removed.
     *
     * @param \stdClass $recipient Notification recipient.
     * @return string Language code.
     */
    private static function get_recipient_language(\stdClass $recipient): string {
        global $CFG;

        if (!empty($recipient->lang) && get_string_manager()->translation_exists($recipient->lang, false)) {
            return $recipient->lang;
        }

        return $CFG->lang;
    }

    /**
     * Returns the word used for an action in setting and string names.
     *
     * @param string $action Lifecycle action.
     * @return string Either 'granted' or 'revoked'.
     */
    private static function get_setting_action(string $action): string {
        return $action === self::ACTION_CREATED ? 'granted' : 'revoked';
    }
}
