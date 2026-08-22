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
 * Opens core dynamic forms for delegation creation and bulk lifecycle updates.
 *
 * @module     local_delegateaccount/management_modals
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalForm from 'core_form/modalform';
import {getString} from 'core/str';

const ASSIGN = '[data-action="local-delegateaccount-open-assign"]';
const BULK_EDIT = '[data-action="local-delegateaccount-edit-selected"]';
const SELECT_ONE = '[data-action="local-delegateaccount-select-delegation"]';

/**
 * Returns the identifiers selected on the current delegation page.
 *
 * @return {string[]} Selected delegation identifiers.
 */
const getSelected = () => Array.from(document.querySelectorAll(`${SELECT_ONE}:checked`))
    .map(checkbox => checkbox.value);

/**
 * Opens a dynamic form and refreshes the list after successful submission.
 *
 * @param {HTMLElement} trigger Trigger that should regain focus.
 * @param {string} formClass Dynamic form class.
 * @param {Object<string, string|number>} args Form arguments.
 * @param {Promise<string>} title Modal title.
 */
const showForm = (trigger, formClass, args, title) => {
    const modalForm = new ModalForm({
        modalConfig: {title},
        formClass,
        args,
        saveButtonText: getString('savechanges'),
        returnFocus: trigger,
    });
    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, () => window.location.reload());
    modalForm.show();
};

/**
 * Initialises modal triggers and selection-dependent controls.
 */
export const init = () => {
    const updateBulkState = () => {
        const trigger = document.querySelector(BULK_EDIT);
        if (trigger) {
            trigger.disabled = getSelected().length === 0;
        }
    };

    document.addEventListener('change', event => {
        if (event.target.matches(SELECT_ONE) ||
                event.target.matches('[data-action="local-delegateaccount-select-all"]')) {
            updateBulkState();
        }
    });
    document.addEventListener('click', event => {
        const assignTrigger = event.target.closest(ASSIGN);
        if (assignTrigger) {
            event.preventDefault();
            showForm(
                assignTrigger,
                'local_delegateaccount\\form\\assign_dynamic_form',
                {realuserid: Number(assignTrigger.dataset.realUserId || 0)},
                getString('create_delegations', 'local_delegateaccount')
            );
            return;
        }

        const editTrigger = event.target.closest(BULK_EDIT);
        if (editTrigger) {
            event.preventDefault();
            const selected = getSelected();
            if (selected.length > 0) {
                showForm(
                    editTrigger,
                    'local_delegateaccount\\form\\bulk_edit_dynamic_form',
                    {
                        realuserid: Number(editTrigger.dataset.realUserId),
                        delegationids: selected.join(','),
                    },
                    getString('edit_selected_delegations', 'local_delegateaccount')
                );
            }
        }
    });
    updateBulkState();
};
