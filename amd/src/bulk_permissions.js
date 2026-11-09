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
 * The session permissions of many delegations at once: add, change or remove overrides on all of them, or copy the
 * overrides of one delegation to all of them. The server writes them in bounded batches, so the selection can be every
 * delegation that matches a list.
 *
 * The dialog keeps one size and shows its lists from the start: the capabilities of the site, narrowed as one types, and
 * the delegations to copy from.
 *
 * @module     local_delegateaccount/bulk_permissions
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import ModalSaveCancel from 'core/modal_save_cancel';
import ModalEvents from 'core/modal_events';
import Templates from 'core/templates';
import {getString, getStrings} from 'core/str';
import {debounce, describeError, toast} from 'local_delegateaccount/local/ui';
import {
    ALLOW,
    INHERIT,
    PERMISSIONS,
    PROHIBIT,
    loadPermissions,
    matches,
    permissionNames,
    words,
} from 'local_delegateaccount/local/capabilities';

/** How many options a list shows at once; typing narrows it. */
const LIMIT = 60;

let counter = 0;

/**
 * Builds an element with a class and a text.
 *
 * @param {string} tag Tag name.
 * @param {string} className Classes.
 * @param {string} text Text.
 * @returns {HTMLElement}
 */
const element = (tag, className = '', text = '') => {
    const node = document.createElement(tag);
    node.className = className;
    node.textContent = text;
    return node;
};

/**
 * Draws the options of a list as buttons of a listbox.
 *
 * @param {HTMLElement} list The listbox.
 * @param {Array<{key: *, title: string, detail: string, selected: boolean, mark: string}>} options The options.
 * @param {string} empty Text when there are none.
 * @param {Function} onChoose Called with the key of the option chosen.
 */
const drawOptions = (list, options, empty, onChoose) => {
    list.replaceChildren();
    if (!options.length) {
        list.append(element('li', 'list-group-item text-muted text-center', empty));
        return;
    }
    options.forEach((option) => {
        const item = element('li');
        item.setAttribute('role', 'presentation');
        const button = element('button');
        button.type = 'button';
        button.setAttribute('role', 'option');
        button.setAttribute('aria-selected', String(option.selected));
        const text = element('span', 'd-flex flex-column');
        text.append(element('span', '', option.title), element('small', '', option.detail));
        button.append(text, element('span', 'small font-weight-bold fw-bold text-primary', option.mark));
        button.addEventListener('click', () => onChoose(option.key));
        item.append(button);
        list.append(item);
    });
};

/**
 * Wires the list of delegations to copy from: it shows delegations from the start and searches them as one types.
 *
 * @param {HTMLElement} root The dialog.
 * @param {number} exclude Delegation that cannot be chosen.
 * @param {Function} onChoose Called with {id, label, overrides}.
 * @returns {Promise<void>}
 */
const bindSourcePicker = async(root, exclude, onChoose) => {
    const input = root.querySelector('[data-region="sourcesearch"]');
    const list = root.querySelector('[data-region="sourceresults"]');
    const empty = await getString('empty_search_title', 'local_delegateaccount');
    let chosen = 0;
    let rows = [];
    const draw = () => drawOptions(list, rows.map((row) => ({
        key: row,
        title: row.label,
        detail: `${row.statuslabel} · ${row.overrideslabel}`,
        selected: row.id === chosen,
        mark: '',
    })), empty, (row) => {
        chosen = row.id;
        draw();
        onChoose({id: row.id, label: row.label, overrides: row.overrides});
    });
    const search = async() => {
        try {
            const response = await Ajax.call([{
                methodname: 'local_delegateaccount_get_delegation_list',
                args: {filters: {realuserid: 0, status: '', search: input.value.trim()}, sort: 'account', page: 0, perpage: 30},
            }])[0];
            rows = response.rows.filter((row) => row.id !== exclude);
            draw();
        } catch (error) {
            drawOptions(list, [], describeError(error), () => null);
        }
    };
    input.addEventListener('input', debounce(search, 300));
    input.addEventListener('keydown', (event) => event.key === 'Enter' && event.preventDefault());
    await search();
};

/**
 * Lets one delegation be chosen, for instance to copy its overrides into the editor.
 *
 * @param {HTMLElement} trigger Element that gets the focus back.
 * @param {number} exclude Delegation that cannot be chosen.
 * @returns {Promise<{id: number, label: string}|null>} The delegation chosen, or null.
 */
export const chooseDelegation = async(trigger, exclude) => {
    const id = `lda-copy-${++counter}`;
    const [title, button, body] = await Promise.all([
        getString('copy_from_delegation', 'local_delegateaccount'),
        getString('bulk_mode_copy', 'local_delegateaccount'),
        Templates.render('local_delegateaccount/permissions/bulk', {id, copyonly: true}),
    ]);
    const modal = await ModalSaveCancel.create({title, body, large: true, buttons: {save: button}, removeOnClose: true,
        returnElement: trigger});
    const root = modal.getRoot()[0];
    root.classList.add('lda-modal');
    const save = root.querySelector('[data-action="save"]');
    save.disabled = true;
    let chosen = null;
    await bindSourcePicker(root, exclude, (delegation) => {
        chosen = delegation;
        save.disabled = false;
    });
    return new Promise((resolve) => {
        modal.getRoot().on(ModalEvents.save, () => resolve(chosen));
        modal.getRoot().on(ModalEvents.hidden, () => resolve(null));
        modal.show();
    });
};

/**
 * Opens the dialog that changes the session permissions of many delegations.
 *
 * @param {Object} options What the dialog acts on.
 * @param {Object} options.selection The delegations, as the web services take them.
 * @param {number} options.count How many they are.
 * @param {HTMLElement} options.trigger Element that gets the focus back.
 * @param {Function} options.onApplied Called after the changes are saved.
 * @returns {Promise<void>}
 */
export const openBulkPermissions = async({selection, count, trigger, onApplied}) => {
    const id = `lda-bulk-${++counter}`;
    const [title, apply, body, names, data, strings, cancelicon] = await Promise.all([
        getString('bulk_permissions_title', 'local_delegateaccount', count),
        getString('apply_to_count', 'local_delegateaccount', count),
        Templates.render('local_delegateaccount/permissions/bulk', {id, copyonly: false}),
        permissionNames(),
        loadPermissions(0),
        getStrings(['bulk_remove', 'bulk_add', 'bulk_added', 'bulk_search_noresults'].map(
            (key) => ({key, component: 'local_delegateaccount'})
        )),
        Templates.renderPix('e/cancel', 'core'),
    ]);
    const [removelabel, addlabel, addedlabel, nomatch] = strings;
    const modal = await ModalSaveCancel.create({
        title,
        body,
        large: true,
        buttons: {save: apply},
        removeOnClose: true,
        returnElement: trigger,
    });
    const root = modal.getRoot()[0];
    root.classList.add('lda-modal');
    const region = (name) => root.querySelector(`[data-region="${name}"]`);
    const save = root.querySelector('[data-action="save"]');
    const byname = new Map(data.capabilities.map((capability) => [capability.name, capability]));
    const changes = new Map();
    let mode = 'change';
    let source = null;

    const refresh = () => {
        region('nochanges').hidden = changes.size > 0;
        region('changecount').textContent = changes.size ? `(${changes.size})` : '';
        save.disabled = mode === 'change' ? changes.size === 0 : source === null;
    };

    // The capabilities of the site, the ones chosen marked; typing narrows the list.
    const capsearch = region('capsearch');
    const drawCapabilities = () => {
        const typed = words(capsearch.value);
        const found = data.capabilities.filter((capability) => matches(capability, typed)).slice(0, LIMIT);
        drawOptions(region('capresults'), found.map((capability) => ({
            key: capability,
            title: capability.label,
            detail: `${capability.name} · ${capability.componentlabel}`,
            selected: changes.has(capability.name),
            mark: changes.has(capability.name) ? addedlabel : addlabel,
        })), nomatch, (capability) => toggle(capability));
    };

    const drawChanges = () => {
        const list = region('changes');
        list.replaceChildren();
        changes.forEach((permission, name) => {
            const capability = byname.get(name);
            const row = element('li', 'list-group-item');
            const text = element('span', 'd-flex flex-column');
            const meta = element('span', 'small text-muted');
            meta.append(element('span', 'mr-2 me-2', capability.name));
            text.append(element('span', 'font-weight-bold fw-bold', capability.label), meta);
            const group = element('span', 'dd-segmented');
            group.setAttribute('role', 'radiogroup');
            group.setAttribute('aria-label', capability.label);
            PERMISSIONS.filter(({value}) => value !== INHERIT).concat([{value: INHERIT, tone: 'notset', remove: true}])
                .forEach(({value, tone, remove}) => {
                    const option = element('button', '', remove ? removelabel : names[value]);
                    option.type = 'button';
                    option.dataset.permission = value;
                    option.dataset.tone = tone;
                    option.setAttribute('role', 'radio');
                    option.setAttribute('aria-checked', String(value === permission));
                    option.disabled = value === ALLOW && !capability.canallow;
                    option.addEventListener('click', () => {
                        changes.set(name, value);
                        drawChanges();
                    });
                    group.append(option);
                });
            const discard = element('button', 'btn btn-link btn-icon btn-sm');
            discard.type = 'button';
            discard.title = `${removelabel}: ${capability.label}`;
            discard.setAttribute('aria-label', discard.title);
            discard.innerHTML = cancelicon;
            discard.addEventListener('click', () => toggle(capability));
            row.append(text, group, discard);
            list.append(row);
        });
        refresh();
    };

    const toggle = (capability) => {
        if (changes.has(capability.name)) {
            changes.delete(capability.name);
        } else {
            changes.set(capability.name, PROHIBIT);
        }
        drawCapabilities();
        drawChanges();
    };

    capsearch.addEventListener('input', debounce(drawCapabilities, 150));
    capsearch.addEventListener('keydown', (event) => event.key === 'Enter' && event.preventDefault());
    drawCapabilities();
    drawChanges();

    await bindSourcePicker(root, 0, (delegation) => {
        source = delegation;
        refresh();
    });

    root.querySelectorAll('[data-mode]').forEach((button) => button.addEventListener('click', () => {
        mode = button.dataset.mode;
        root.querySelectorAll('[data-mode]').forEach((other) => {
            other.setAttribute('aria-checked', String(other === button));
            other.tabIndex = other === button ? 0 : -1;
        });
        region('change').hidden = mode !== 'change';
        region('copy').hidden = mode !== 'copy';
        refresh();
    }));

    modal.getRoot().on(ModalEvents.save, async(event) => {
        event.preventDefault();
        save.disabled = true;
        region('error').hidden = true;
        try {
            const replace = root.querySelector(`input[name="${id}-replace"]:checked`).value === '1';
            const args = mode === 'copy'
                ? {selection, copyfrom: source.id, replace}
                : {selection, changes: Array.from(changes, ([capability, permission]) => ({capability, permission}))};
            const result = await Ajax.call([{methodname: 'local_delegateaccount_update_session_permissions', args}])[0];
            modal.destroy();
            await toast(await getString('bulk_applied', 'local_delegateaccount', result.count));
            await onApplied();
        } catch (error) {
            region('error').textContent = describeError(error);
            region('error').hidden = false;
            save.disabled = false;
        }
    });
    refresh();
    modal.show();
};
