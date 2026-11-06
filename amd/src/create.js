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
 * Creates delegations with the Moodle form of the plugin, for several people or for one, and tells the lists of the page.
 *
 * @module     local_delegateaccount/create
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getString} from 'core/str';
import {openForm, toast} from 'local_delegateaccount/local/ui';

/**
 * Opens the form that creates delegations.
 *
 * @param {number} realuserid The authorised user they are for, or zero to choose several.
 * @param {HTMLElement} trigger Element that gets the focus back.
 */
export const openCreate = (realuserid, trigger) => {
    openForm({
        formClass: 'local_delegateaccount\\form\\assign_dynamic_form',
        args: {realuserid},
        title: getString('create_delegations', 'local_delegateaccount'),
        trigger,
        onSubmitted: async(result) => {
            document.dispatchEvent(new CustomEvent('local_delegateaccount:created', {detail: result}));
            if (result.message) {
                await toast(result.message, result.type || 'success');
            }
        },
    });
};

/**
 * Wires the buttons of a page that create delegations.
 */
export const init = () => {
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action="local-delegateaccount-create"]');
        if (button) {
            event.preventDefault();
            openCreate(Number(button.dataset.realuserid || 0), button);
        }
    });
};
