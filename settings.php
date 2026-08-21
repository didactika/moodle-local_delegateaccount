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
 * Site settings and administration navigation for delegated accounts.
 *
 * @package    local_delegateaccount
 * @author     Miguel Rivas Morantes <miguelrivasmorantes@gmail.com>
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_delegateaccount_settings',
        get_string('settings', 'local_delegateaccount')
    );

    if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_heading(
            'local_delegateaccount/delegationsheading',
            get_string('settings_delegations', 'local_delegateaccount'),
            get_string('settings_delegations_desc', 'local_delegateaccount')
        ));
        $settings->add(new admin_setting_configtext(
            'local_delegateaccount/maxdelegationsperuser',
            get_string('maxdelegationsperuser', 'local_delegateaccount'),
            get_string('maxdelegationsperuser_desc', 'local_delegateaccount'),
            10,
            PARAM_INT
        ));
        $settings->add(new admin_setting_configcheckbox(
            'local_delegateaccount/allowopenended',
            get_string('allowopenended', 'local_delegateaccount'),
            get_string('allowopenended_desc', 'local_delegateaccount'),
            1
        ));
        $settings->add(new admin_setting_configtext(
            'local_delegateaccount/maximumdurationdays',
            get_string('maximumdurationdays', 'local_delegateaccount'),
            get_string('maximumdurationdays_desc', 'local_delegateaccount'),
            0,
            PARAM_INT
        ));
        $settings->add(new admin_setting_configcheckbox(
            'local_delegateaccount/protectprivilegedtargets',
            get_string('protectprivilegedtargets', 'local_delegateaccount'),
            get_string('protectprivilegedtargets_desc', 'local_delegateaccount'),
            1
        ));
        $settings->add(new admin_setting_configtext(
            'local_delegateaccount/maxbulkoperations',
            get_string('maxbulkoperations', 'local_delegateaccount'),
            get_string('maxbulkoperations_desc', 'local_delegateaccount'),
            100,
            PARAM_INT
        ));
        $settings->add(new admin_setting_configtext(
            'local_delegateaccount/usermenulimit',
            get_string('usermenulimit', 'local_delegateaccount'),
            get_string('usermenulimit_desc', 'local_delegateaccount'),
            10,
            PARAM_INT
        ));

        $settings->add(new admin_setting_heading(
            'local_delegateaccount/notificationsheading',
            get_string('settings_notifications', 'local_delegateaccount'),
            get_string('settings_notifications_desc', 'local_delegateaccount')
        ));
        $settings->add(new admin_setting_configselect(
            'local_delegateaccount/notificationpolicy',
            get_string('notificationpolicy', 'local_delegateaccount'),
            get_string('notificationpolicy_desc', 'local_delegateaccount'),
            'optional',
            [
                'optional' => get_string('notificationpolicy_optional', 'local_delegateaccount'),
                'always' => get_string('notificationpolicy_always', 'local_delegateaccount'),
                'never' => get_string('notificationpolicy_never', 'local_delegateaccount'),
            ]
        ));
        $settings->add(new admin_setting_configselect(
            'local_delegateaccount/notificationrecipients',
            get_string('notificationrecipients', 'local_delegateaccount'),
            get_string('notificationrecipients_desc', 'local_delegateaccount'),
            'both',
            [
                'authorised' => get_string('notificationrecipients_authorised', 'local_delegateaccount'),
                'target' => get_string('notificationrecipients_target', 'local_delegateaccount'),
                'both' => get_string('notificationrecipients_both', 'local_delegateaccount'),
            ]
        ));
        $settings->add(new admin_setting_configcheckbox(
            'local_delegateaccount/notifyonrevocation',
            get_string('notifyonrevocation', 'local_delegateaccount'),
            get_string('notifyonrevocation_desc', 'local_delegateaccount'),
            1
        ));

        $stringmanager = get_string_manager();
        $languages = $stringmanager->get_list_of_translations();
        $languages['en'] = $languages['en'] ?? 'English';
        ksort($languages);
        foreach ($languages as $languagecode => $languagename) {
            if (!preg_match('/^[a-z0-9_]+$/', $languagecode)) {
                continue;
            }

            $settings->add(new \local_delegateaccount\admin_setting_notificationtemplate(
                'local_delegateaccount/notificationtemplate_' . $languagecode,
                get_string('notificationtemplate', 'local_delegateaccount', $languagename),
                get_string('notificationtemplate_desc', 'local_delegateaccount'),
                $stringmanager->get_string(
                    'notificationtemplatedefault',
                    'local_delegateaccount',
                    null,
                    $languagecode
                ),
                PARAM_TEXT
            ));
        }
    }

    $ADMIN->add('localplugins', $settings);
}

if ($hassiteconfig || has_capability('local/delegateaccount:manage', context_system::instance())) {
    $managepage = new admin_externalpage(
        'local_delegateaccount_manage',
        get_string('manage_accounts', 'local_delegateaccount'),
        new moodle_url('/local/delegateaccount/manage.php'),
        'local/delegateaccount:manage'
    );
    $ADMIN->add('accounts', $managepage);
}
