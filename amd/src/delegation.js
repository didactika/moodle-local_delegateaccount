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

/**
 * The actions of the page of one delegation: change its period and revoke it. The page is drawn again afterwards, so its
 * details and its history say what changed.
 *
 * @module     local_delegateaccount/delegation
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Pending from 'core/pending';
import {getString} from 'core/str';
import {confirmDanger, openForm} from 'local_delegateaccount/local/ui';

/**
 * Draws the page again.
 *
 * @returns {Pending} What anyone waiting for the scripts of the page, such as Behat, waits for until the new page is there.
 */
const reload = () => {
    const pending = new Pending('local_delegateaccount/delegation:reload');
    window.location.reload();
    return pending;
};

/**
 * Starts the actions of the page.
 *
 * @param {string} selector Selector of the element of the page.
 */
export const init = (selector) => {
    const root = document.querySelector(selector);
    const id = Number(root.dataset.id);
    root.addEventListener('click', async(event) => {
        const button = event.target.closest('[data-action]');
        if (button?.dataset.action === 'edit') {
            openForm({
                formClass: 'local_delegateaccount\\form\\bulk_edit_dynamic_form',
                args: {realuserid: Number(root.dataset.realuserid), delegationids: String(id)},
                title: getString('edit_period', 'local_delegateaccount'),
                trigger: button,
                onSubmitted: reload,
            });
        } else if (button?.dataset.action === 'revoke') {
            const [title, text, label] = await Promise.all([
                getString('revoke_confirm_title_one', 'local_delegateaccount'),
                getString('revoke_confirm_text', 'local_delegateaccount'),
                getString('revoke_confirm_button_one', 'local_delegateaccount'),
            ]);
            await confirmDanger({
                title,
                text,
                button: label,
                trigger: button,
                action: async() => {
                    await Ajax.call([{
                        methodname: 'local_delegateaccount_revoke_delegations',
                        args: {delegationids: [id], confirm: true},
                    }])[0];
                    reload();
                },
            });
        }
    });
    // Saving the session permissions changes the count of the tab.
    root.addEventListener('local_delegateaccount:permissionssaved', async() => {
        const badge = root.querySelector('.nav-link.active .badge');
        if (badge) {
            const result = await Ajax.call([{
                methodname: 'local_delegateaccount_get_session_permissions',
                args: {delegationid: id},
            }])[0];
            badge.textContent = result.overrides.length;
        }
    });
};
