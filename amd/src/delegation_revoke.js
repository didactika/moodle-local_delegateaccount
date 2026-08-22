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
 * Confirms and submits individual or bulk delegation revocations.
 *
 * @module     local_delegateaccount/delegation_revoke
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Notification from 'core/notification';

const CONFIG = '[data-region="local-delegateaccount-revoke-config"]';
const SELECT_ALL = '[data-action="local-delegateaccount-select-all"]';
const SELECT_ONE = '[data-action="local-delegateaccount-select-delegation"]';
const REVOKE_ONE = '[data-action="local-delegateaccount-revoke-one"]';
const REVOKE_SELECTED = '[data-action="local-delegateaccount-revoke-selected"]';

/**
 * Submits a POST request without introducing nested forms into the table.
 *
 * @param {string} url Submission URL.
 * @param {Object<string, string|string[]>} values Form values.
 */
const submitPost = (url, values) => {
    const form = document.createElement('form');
    form.method = 'post';
    form.action = url;
    form.classList.add('d-none');

    Object.entries(values).forEach(([name, value]) => {
        const entries = Array.isArray(value) ? value : [value];
        entries.forEach(entry => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = Array.isArray(value) ? `${name}[]` : name;
            input.value = entry;
            form.append(input);
        });
    });

    document.body.append(form);
    form.submit();
};

/**
 * Returns the currently selected delegation identifiers.
 *
 * @return {string[]} Selected identifiers.
 */
const getSelected = () => Array.from(document.querySelectorAll(`${SELECT_ONE}:checked`))
    .map(checkbox => checkbox.value);

/**
 * Synchronises the bulk controls with the row selections.
 */
const updateSelectionState = () => {
    const checkboxes = Array.from(document.querySelectorAll(SELECT_ONE));
    const selected = checkboxes.filter(checkbox => checkbox.checked);
    const selectAll = document.querySelector(SELECT_ALL);
    const revokeSelected = document.querySelector(REVOKE_SELECTED);

    if (selectAll) {
        selectAll.checked = checkboxes.length > 0 && selected.length === checkboxes.length;
        selectAll.indeterminate = selected.length > 0 && selected.length < checkboxes.length;
    }
    if (revokeSelected) {
        revokeSelected.disabled = selected.length === 0;
    }
};

/**
 * Shows Moodle's destructive confirmation modal.
 *
 * @param {HTMLElement} trigger Triggering button.
 * @param {string} message Confirmation question.
 * @param {DOMStringMap} config Revocation configuration.
 * @return {Promise<boolean>} Whether the action was confirmed.
 */
const confirm = async(trigger, message, config) => {
    try {
        await Notification.deleteCancelPromise(
            config.confirmTitle,
            message,
            config.confirmButton,
            {triggerElement: trigger}
        );
        return true;
    } catch {
        return false;
    }
};

/**
 * Initialises selection and confirmed revocation actions.
 */
export const init = () => {
    const configElement = document.querySelector(CONFIG);
    if (!configElement) {
        return;
    }
    const config = configElement.dataset;

    document.addEventListener('change', event => {
        if (event.target.matches(SELECT_ALL)) {
            document.querySelectorAll(SELECT_ONE).forEach(checkbox => {
                checkbox.checked = event.target.checked;
            });
            updateSelectionState();
        } else if (event.target.matches(SELECT_ONE)) {
            updateSelectionState();
        }
    });

    document.addEventListener('click', async event => {
        const singleTrigger = event.target.closest(REVOKE_ONE);
        if (singleTrigger) {
            event.preventDefault();
            if (await confirm(singleTrigger, config.confirmSingle, config)) {
                submitPost(config.postUrl, {
                    action: 'revoke',
                    delegationid: singleTrigger.dataset.delegationId,
                    sesskey: config.sesskey,
                });
            }
            return;
        }

        const bulkTrigger = event.target.closest(REVOKE_SELECTED);
        if (bulkTrigger) {
            event.preventDefault();
            const selected = getSelected();
            if (selected.length > 0 && await confirm(bulkTrigger, config.confirmBulk, config)) {
                submitPost(config.postUrl, {
                    action: 'bulk_revoke',
                    selecteddelegations: selected,
                    sesskey: config.sesskey,
                });
            }
        }
    });

    updateSelectionState();
};
