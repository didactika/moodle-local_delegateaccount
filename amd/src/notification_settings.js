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
 * Shows notification settings only when the notification policy allows them.
 *
 * @module     local_delegateaccount/notification_settings
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Initialise the notification settings visibility controller.
 */
export const init = () => {
    const setup = () => {
        const policy = document.querySelector('#id_s_local_delegateaccount_notificationpolicy');
        if (!policy) {
            return;
        }

        const selectors = [
            '#id_s_local_delegateaccount_notificationrecipients',
            '#id_s_local_delegateaccount_notifyonrevocation',
            '[id^="id_s_local_delegateaccount_notificationsubject_"]',
            '[id^="id_s_local_delegateaccount_notificationtemplate_"]',
        ];
        const inputs = selectors.reduce((elements, selector) => {
            return elements.concat(Array.from(document.querySelectorAll(selector)));
        }, []);
        const update = () => {
            const visible = policy.value !== 'never';
            inputs.forEach((input) => {
                const container = input.closest('.form-item, .form-group') || input.parentElement;
                container.hidden = !visible;
                input.disabled = !visible;
            });
        };

        policy.addEventListener('change', update);
        update();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setup);
    } else {
        setup();
    }
};
