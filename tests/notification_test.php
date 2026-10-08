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

use local_delegateaccount\external\create_delegations;

/**
 * Tests for delegation notifications and notification settings.
 *
 * @package    local_delegateaccount
 * @category   test
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_delegateaccount\notification_manager
 * @covers     \local_delegateaccount\manager
 */
final class notification_test extends \advanced_testcase {
    /** @var \stdClass Authorised user. */
    private \stdClass $realuser;

    /** @var \stdClass Delegated account. */
    private \stdClass $target;

    /**
     * Creates an authorised user and a target account, and notifies both of every change.
     */
    protected function setUp(): void {
        global $DB;

        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->preventResetByRollback();

        $generator = $this->getDataGenerator();
        $this->realuser = $generator->create_user(['firstname' => 'Ada', 'lastname' => 'Authorised']);
        $this->target = $generator->create_user(['firstname' => 'Tom', 'lastname' => 'Target']);

        $context = \context_system::instance();
        $roleid = (int)$DB->get_field('role', 'id', ['shortname' => 'manager'], MUST_EXIST);
        assign_capability('local/delegateaccount:use', CAP_ALLOW, $roleid, $context->id, true);
        role_assign($roleid, $this->realuser->id, $context->id);
        accesslib_clear_all_caches_for_unit_testing();

        set_config('notificationrecipients', 'both', 'local_delegateaccount');
        set_config('notifyonrevocation', 1, 'local_delegateaccount');
    }

    /**
     * Each recipient of a granted-access notification reads about their own side of the delegation.
     */
    public function test_granted_message_is_worded_for_each_recipient(): void {
        $sink = $this->redirectMessages();
        $this->create_delegation();
        $messages = $this->index_by_recipient($sink->get_messages());

        $this->assertCount(2, $messages);
        foreach ($messages as $message) {
            $this->assertSame('Delegated account access granted', $message->subject);
        }

        $authorised = $messages[(int)$this->realuser->id]->fullmessagehtml;
        $this->assertStringContainsString('Hello Ada Authorised,', $authorised);
        $this->assertStringContainsString('You can now access the account of Tom Target', $authorised);

        $target = $messages[(int)$this->target->id]->fullmessagehtml;
        $this->assertStringContainsString('Hello Tom Target,', $target);
        $this->assertStringContainsString('Ada Authorised can now access your account', $target);
    }

    /**
     * A revocation notification says that access was revoked.
     */
    public function test_revoked_message_says_access_was_revoked(): void {
        $id = $this->create_delegation();
        $sink = $this->redirectMessages();
        manager::revoke_delegations([$id]);
        $messages = $this->index_by_recipient($sink->get_messages());

        $this->assertCount(2, $messages);
        foreach ($messages as $message) {
            $this->assertSame('Delegated account access revoked', $message->subject);
            $this->assertStringNotContainsString('granted', $message->fullmessagehtml);
        }
        $this->assertStringContainsString(
            'You can no longer access the account of Tom Target',
            $messages[(int)$this->realuser->id]->fullmessagehtml
        );
        $this->assertStringContainsString(
            'Ada Authorised can no longer access your account',
            $messages[(int)$this->target->id]->fullmessagehtml
        );
    }

    /**
     * The subject and message configured for granted access in the recipient's language replace the built-in ones.
     */
    public function test_custom_granted_subject_and_message_for_the_recipient_language(): void {
        set_config('notificationsubject_granted_en', 'Custom subject', 'local_delegateaccount');
        set_config('notificationtemplate_granted_en', '<p>Custom message for {$a->delegateduser}</p>', 'local_delegateaccount');

        $sink = $this->redirectMessages();
        $id = $this->create_delegation();
        $this->assertCount(2, $sink->get_messages());
        foreach ($sink->get_messages() as $message) {
            $this->assertSame('Custom subject', $message->subject);
            $this->assertStringContainsString('Custom message for Tom Target', $message->fullmessagehtml);
        }

        $sink->clear();
        manager::revoke_delegations([$id]);
        foreach ($sink->get_messages() as $message) {
            $this->assertSame('Delegated account access revoked', $message->subject);
            $this->assertStringNotContainsString('Custom message', $message->fullmessagehtml);
        }
    }

    /**
     * Revocation has its own configurable subject and message, used only for recipients in that language.
     */
    public function test_custom_revoked_subject_and_message_for_the_recipient_language(): void {
        global $DB;

        set_config('notificationsubject_revoked_es', 'Acceso revocado', 'local_delegateaccount');
        set_config('notificationtemplate_revoked_es', '<p>{$a->authoriseduser} ya no accede</p>', 'local_delegateaccount');
        $DB->set_field('user', 'lang', 'es', ['id' => $this->target->id]);

        $id = $this->create_delegation();
        $sink = $this->redirectMessages();
        manager::revoke_delegations([$id]);
        $messages = $this->index_by_recipient($sink->get_messages());

        $this->assertSame('Acceso revocado', $messages[(int)$this->target->id]->subject);
        $this->assertStringContainsString('Ada Authorised ya no accede', $messages[(int)$this->target->id]->fullmessagehtml);
        $this->assertSame('Delegated account access revoked', $messages[(int)$this->realuser->id]->subject);
        $this->assertStringContainsString(
            'You can no longer access the account of Tom Target',
            $messages[(int)$this->realuser->id]->fullmessagehtml
        );
    }

    /**
     * A web-service request without a notification decision is stored as the decision that was applied.
     */
    public function test_web_service_default_is_stored_as_always(): void {
        global $DB;

        $sink = $this->redirectMessages();
        create_delegations::execute([(int)$this->realuser->id], [(int)$this->target->id], time());

        $this->assertSame('always', $DB->get_field('local_delegateaccount', 'notificationmode', [
            'realuserid' => $this->realuser->id,
        ]));
        $this->assertCount(2, $sink->get_messages());
    }

    /**
     * Creates the test delegation with notifications enabled.
     *
     * @return int Delegation identifier.
     */
    private function create_delegation(): int {
        manager::create_delegations([(int)$this->realuser->id], [(int)$this->target->id], [
            'notificationmode' => manager::NOTIFICATION_ALWAYS,
        ]);

        return manager::get_current_delegation_id((int)$this->realuser->id, (int)$this->target->id);
    }

    /**
     * Indexes captured messages by recipient.
     *
     * @param \stdClass[] $messages Captured messages.
     * @return \stdClass[] Messages indexed by recipient user ID.
     */
    private function index_by_recipient(array $messages): array {
        $indexed = [];
        foreach ($messages as $message) {
            $indexed[(int)$message->useridto] = $message;
        }

        return $indexed;
    }
}
