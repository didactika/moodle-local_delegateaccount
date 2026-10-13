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

/**
 * Sends the saved notification to the current administrator as a test.
 *
 * @package    local_delegateaccount
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

use local_delegateaccount\form\test_notification_form;
use local_delegateaccount\notification_manager;

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$url = new moodle_url('/local/delegateaccount/pages/testnotification.php');
$settingsurl = new moodle_url('/admin/settings.php', ['section' => 'local_delegateaccount_settings']);
$title = get_string('testnotification', 'local_delegateaccount');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($title);
$PAGE->set_heading($title);

$form = new test_notification_form($url);
if ($data = $form->get_data()) {
    $languages = get_string_manager()->get_list_of_translations();
    $language = array_key_exists($data->language, $languages) ? $data->language : current_language();
    $action = $data->action === notification_manager::ACTION_REVOKED
        ? notification_manager::ACTION_REVOKED
        : notification_manager::ACTION_CREATED;

    $sent = notification_manager::send_test($action, $language, $USER);
    if ($sent > 0) {
        redirect(
            $url,
            get_string('testnotification_sent', 'local_delegateaccount', $sent),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }
    redirect(
        $url,
        get_string('testnotification_notsent', 'local_delegateaccount'),
        null,
        \core\output\notification::NOTIFY_WARNING
    );
}

echo $OUTPUT->header();
echo $OUTPUT->notification(
    get_string('testnotification_intro', 'local_delegateaccount'),
    \core\output\notification::NOTIFY_INFO,
    false
);
$form->display();
echo html_writer::link($settingsurl, get_string('back'));
echo $OUTPUT->footer();
