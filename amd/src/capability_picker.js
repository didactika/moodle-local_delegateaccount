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
 * Adds a row to the capability list for each capability picked in the search field.
 *
 * @module     local_delegateaccount/capability_picker
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const PENDING_CLASS = 'local-delegateaccount-capability-pending';

/**
 * Builds the row of a capability that has been picked but not saved yet.
 *
 * @param {HTMLOptionElement} option Picked capability.
 * @param {string} fieldName Name prefix of the permission fields, one per capability.
 * @param {Array<{value: string, label: string}>} permissions Permissions that can be chosen.
 * @param {string} permission Permission to preselect.
 * @param {string} unsavedLabel Text of the badge that marks the row as not saved.
 * @param {string} removeLabel Text of the button that drops the row.
 * @return {HTMLLIElement} The row.
 */
const buildRow = (option, fieldName, permissions, permission, unsavedLabel, removeLabel) => {
    const [label, code] = option.textContent.split(/ \((?=[^(]*\)$)/);

    const name = document.createElement('div');
    name.className = 'local-delegateaccount-capability-name';
    const title = document.createElement('span');
    title.className = 'font-weight-bold fw-bold';
    title.textContent = label;
    const badge = document.createElement('span');
    badge.className = 'badge badge-info bg-info ml-2 ms-2';
    badge.textContent = unsavedLabel;
    title.append(badge);
    const identifier = document.createElement('code');
    identifier.className = 'small text-muted';
    identifier.textContent = code ? code.slice(0, -1) : option.value;
    name.append(title, identifier);

    const dropdown = document.createElement('select');
    dropdown.className = 'custom-select form-select';
    dropdown.name = `${fieldName}[${option.value}]`;
    dropdown.dataset.capability = option.value;
    dropdown.setAttribute('aria-label', label);
    permissions.forEach((item) => dropdown.add(new Option(item.label, item.value)));
    dropdown.value = permission;

    const remove = document.createElement('button');
    remove.type = 'button';
    remove.className = 'btn btn-link text-danger local-delegateaccount-capability-remove';
    remove.textContent = '\u00d7';
    remove.title = removeLabel;
    remove.setAttribute('aria-label', `${removeLabel}: ${label}`);
    remove.addEventListener('click', () => {
        option.selected = false;
        option.parentElement.dispatchEvent(new Event('change', {bubbles: true}));
    });

    const row = document.createElement('li');
    row.className = `list-group-item local-delegateaccount-capability-row ${PENDING_CLASS}`;
    row.append(name, dropdown, remove);

    return row;
};

/**
 * Keeps one row per picked capability in the list, keeping the permission already chosen for each.
 *
 * @param {HTMLSelectElement} select Multiple select that holds the picked capabilities.
 * @param {HTMLElement} list Capability list that receives the rows.
 * @param {Function} createRow Builds the row of a picked capability for a given permission.
 * @param {string} defaultPermission Permission preselected for a newly picked capability.
 */
const renderRows = (select, list, createRow, defaultPermission) => {
    const chosen = new Map();
    list.querySelectorAll(`.${PENDING_CLASS} select`).forEach((dropdown) => {
        chosen.set(dropdown.dataset.capability, dropdown.value);
    });

    list.querySelectorAll(`.${PENDING_CLASS}`).forEach((row) => row.remove());
    const picked = Array.from(select.selectedOptions).filter((option) => option.value !== '');
    picked.forEach((option) => list.append(createRow(option, chosen.get(option.value) ?? defaultPermission)));
    if (picked.length > chosen.size) {
        // Show the capability that was just added, since the list scrolls when it is long.
        list.lastElementChild.scrollIntoView({block: 'nearest'});
    }

    const empty = list.querySelector('.local-delegateaccount-capabilities-empty');
    if (empty) {
        const configured = list.querySelectorAll(`.local-delegateaccount-capability-row:not(.${PENDING_CLASS})`).length;
        empty.hidden = configured + picked.length > 0;
    }
};

/**
 * Lists every capability picked in the search field as a row of the capability list.
 *
 * @param {string} selectId Identifier of the multiple select.
 * @param {string} listId Identifier of the capability list.
 * @param {string} fieldName Name prefix of the permission fields, one per capability.
 * @param {Array<{value: string, label: string}>} permissions Permissions that can be chosen.
 * @param {string} defaultPermission Permission preselected for a newly picked capability.
 * @param {string} unsavedLabel Text of the badge that marks a row as not saved.
 * @param {string} removeLabel Text of the button that drops a row.
 */
export const init = (selectId, listId, fieldName, permissions, defaultPermission, unsavedLabel, removeLabel) => {
    const select = document.getElementById(selectId);
    const list = document.getElementById(listId);
    if (!select || !list) {
        return;
    }

    const createRow = (option, permission) => buildRow(option, fieldName, permissions, permission, unsavedLabel, removeLabel);
    select.addEventListener('change', () => renderRows(select, list, createRow, defaultPermission));
};
