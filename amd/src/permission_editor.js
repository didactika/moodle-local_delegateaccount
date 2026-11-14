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
 * The permission editor: every capability of the site in one flat, paged list with Not set, Allow, Prevent and Prohibit,
 * found by typing or narrowed by component and by chips, never folded away.
 *
 * Changes are kept until they are saved from the floating bar, which saves them through a web service and refreshes the
 * list in place, for the site as for one delegation.
 *
 * @module     local_delegateaccount/permission_editor
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Templates from 'core/templates';
import Pending from 'core/pending';
import {getString, getStrings} from 'core/str';
import {DEFAULT_PAGE_SIZE, debounce, describeError, fitPageSizes, pageRange, toast} from 'local_delegateaccount/local/ui';
import {
    ALLOW,
    INHERIT,
    PERMISSIONS,
    loadPermissions,
    matches,
    permissionNames,
    toMap,
    words,
} from 'local_delegateaccount/local/capabilities';
import {chooseDelegation} from 'local_delegateaccount/bulk_permissions';
import {init as initSelectMenus} from 'local_delegateaccount/local/select_menu';

/** Risks of Moodle, in the order they are shown. */
const RISKS = ['managetrust', 'config', 'xss', 'personal', 'spam', 'dataloss'];

class Editor {
    /**
     * Creates the editor.
     *
     * @param {HTMLElement} root Element of the editor.
     */
    constructor(root) {
        this.root = root;
        this.config = JSON.parse(root.dataset.config);
        this.region = (name) => root.querySelector(`[data-region="${name}"]`);
        this.capabilities = [];
        this.saved = new Map();
        this.current = new Map();
        this.site = new Map();
        this.filter = '';
        this.component = '';
        this.search = '';
        this.page = 0;
        this.perpage = DEFAULT_PAGE_SIZE;
        this.view = '';
        this.canedit = false;
    }

    /**
     * Loads the capabilities and the overrides, and draws the list.
     *
     * @returns {Promise<void>}
     */
    async init() {
        initSelectMenus();
        const pending = new Pending('local_delegateaccount/permission_editor:init');
        try {
            const [data, names, risks] = await Promise.all([
                loadPermissions(this.config.delegationid),
                permissionNames(),
                getStrings(RISKS.map((risk) => ({key: `risk${risk}short`, component: 'admin'}))),
            ]);
            this.names = names;
            this.risknames = Object.fromEntries(RISKS.map((risk, index) => [risk, risks[index]]));
            this.capabilities = data.capabilities;
            this.saved = toMap(data.overrides);
            this.current = new Map(this.saved);
            this.site = toMap(data.site);
            this.canedit = data.canedit;
            this.region('readonly').hidden = this.canedit;
            const copy = this.root.querySelector('[data-action="copyfrom"]');
            if (copy) {
                copy.hidden = !this.canedit;
            }
            this.fillComponents();
            this.bind();
            this.watchSettingsTab();
            await this.render();
        } catch (error) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger m-3';
            alert.setAttribute('role', 'status');
            alert.textContent = await getString('error_loading', 'local_delegateaccount', describeError(error));
            this.region('error').replaceChildren(alert);
            this.region('error').hidden = false;
            this.region('rows').hidden = true;
        } finally {
            this.region('rows').removeAttribute('aria-busy');
            pending.resolve();
        }
    }

    /**
     * In the settings page the editor saves by itself, so the button of the page that saves the other tabs is hidden while
     * its tab is open.
     */
    watchSettingsTab() {
        const pane = this.root.closest('.tab-pane');
        const form = this.root.closest('form');
        const submit = form?.querySelector('[type="submit"]');
        if (!pane || !submit) {
            return;
        }
        const row = submit.closest('.row, .form-buttons, div') || submit;
        const sync = () => {
            row.hidden = pane.classList.contains('active');
        };
        new MutationObserver(sync).observe(pane, {attributes: true, attributeFilter: ['class']});
        sync();
    }

    /**
     * Offers the components that define capabilities, in the order Moodle lists them.
     */
    fillComponents() {
        const select = this.region('component');
        const seen = new Set();
        this.capabilities.forEach((capability) => {
            if (!seen.has(capability.componentlabel)) {
                seen.add(capability.componentlabel);
                select.append(new Option(capability.componentlabel, capability.componentlabel));
            }
        });
    }

    /**
     * Narrows the list to one component, or to none: the select shows it, and its arrow becomes the cross that clears it.
     *
     * @param {string} value The component, empty for all of them.
     */
    setComponent(value) {
        const select = this.region('component');
        this.component = value;
        select.value = value;
        select.classList.toggle('border-primary', value !== '');
        select.classList.toggle('dd-select-set', value !== '');
        this.root.querySelector('[data-action="clearcomponent"]').hidden = value === '';
    }

    /**
     * Wires the controls.
     */
    bind() {
        const search = this.region('search');
        search.addEventListener('input', debounce(() => {
            this.search = search.value;
            this.page = 0;
            this.render();
        }, 200));
        this.region('component').addEventListener('change', (event) => {
            this.setComponent(event.target.value);
            this.page = 0;
            this.render();
        });
        this.region('perpage').addEventListener('change', (event) => {
            this.perpage = Number(event.target.value);
            this.page = 0;
            this.render();
        });
        this.root.addEventListener('click', (event) => this.click(event));
        this.root.addEventListener('keydown', (event) => this.keydown(event));
        // A search field inside a form would send the form on Enter.
        search.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        });
    }

    /**
     * The capabilities that pass the search, the component and the chip.
     *
     * @param {string} filter A chip, or empty for none.
     * @returns {Object[]}
     */
    visible(filter = this.filter) {
        const typed = words(this.search);
        return this.capabilities.filter((capability) => {
            if (this.component && capability.componentlabel !== this.component) {
                return false;
            }
            if (!matches(capability, typed)) {
                return false;
            }
            const permission = this.current.get(capability.name) || INHERIT;
            switch (filter) {
                case 'overridden':
                    return permission !== INHERIT;
                case 'risky':
                    return capability.risks.length > 0;
                case '':
                    return true;
                default:
                    return permission === Number(filter);
            }
        });
    }

    /**
     * Draws the page of rows that pass the filters, the pager, the counts of the chips and the bar of the changes.
     *
     * @returns {Promise<void>}
     */
    async render() {
        const shown = this.visible();
        const {from, to, lastpage, start, end} = pageRange(this.page, this.perpage, shown.length);
        this.page = Math.min(this.page, lastpage);
        const page = shown.slice(start, end);
        const rows = await Promise.all(page.map((capability) => this.row(capability)));
        const groups = [];
        page.forEach((capability, index) => {
            let group = groups[groups.length - 1];
            if (!group || group.label !== capability.componentlabel) {
                group = {label: capability.componentlabel, rows: []};
                groups.push(group);
            }
            group.rows.push(rows[index]);
        });
        const {html, js} = await Templates.renderForPromise('local_delegateaccount/permissions/rows', {groups});
        Templates.replaceNodeContents(this.region('rows'), html, js);
        // Another page or other filters start at the top of the box; a change of permission keeps the place.
        const view = [this.page, this.perpage, this.filter, this.component, this.search].join('|');
        if (view !== this.view) {
            this.view = view;
            this.region('capabilities').scrollTop = 0;
        }
        this.region('rows').hidden = shown.length === 0;
        this.region('empty').hidden = shown.length > 0;
        this.region('foot').hidden = shown.length === 0;
        fitPageSizes(this.region('perpage'), this.perpage, shown.length);
        this.region('range').textContent = await getString('pager_range', 'local_delegateaccount', {
            from,
            to,
            total: shown.length,
        });
        this.root.querySelector('[data-action="prev"]').disabled = this.page === 0;
        this.root.querySelector('[data-action="next"]').disabled = to >= shown.length;
        this.renderChips();
        await this.renderBar();
    }

    /**
     * What the template needs to draw one capability.
     *
     * @param {Object} capability The capability.
     * @returns {Promise<Object>}
     */
    async row(capability) {
        const permission = this.current.get(capability.name) || INHERIT;
        const sitepermission = this.site.get(capability.name);
        const hint = capability.canallow ? '' : await getString('cannotallow_hint', 'local_delegateaccount');
        return {
            name: capability.name,
            label: capability.label,
            risks: capability.risks.map((risk) => ({label: this.risknames[risk]})),
            changed: permission !== (this.saved.get(capability.name) || INHERIT),
            sitelabel: sitepermission && permission === INHERIT
                ? await getString('perm_site_value', 'local_delegateaccount', this.names[sitepermission])
                : '',
            grouplabel: await getString('perm_permission_for', 'local_delegateaccount', capability.label),
            options: PERMISSIONS.map(({value, tone}) => ({
                value,
                tone,
                label: this.names[value],
                checked: value === permission,
                // Allowing gives the capability to the session, so only who has it may allow it.
                disabled: !this.canedit || (value === ALLOW && !capability.canallow && permission !== ALLOW),
                hint: value === ALLOW && !capability.canallow ? hint : '',
            })),
        };
    }

    /**
     * Writes how many capabilities each chip leaves, and which chip is pressed.
     */
    renderChips() {
        this.root.querySelectorAll('[data-filter]').forEach((chip) => {
            const filter = chip.dataset.filter;
            chip.setAttribute('aria-pressed', String(filter === this.filter));
            chip.querySelector('.dd-chip-count').textContent = this.visible(filter).length;
        });
    }

    /**
     * The changes that are not saved, as the web service takes them.
     *
     * @returns {Array<{capability: string, permission: number}>}
     */
    changes() {
        const names = new Set([...this.saved.keys(), ...this.current.keys()]);
        return Array.from(names)
            .filter((name) => (this.saved.get(name) || INHERIT) !== (this.current.get(name) || INHERIT))
            .map((capability) => ({capability, permission: this.current.get(capability) || INHERIT}));
    }

    /**
     * Shows the floating bar while there are unsaved changes.
     *
     * @returns {Promise<void>}
     */
    async renderBar() {
        const count = this.changes().length;
        this.region('savebar').hidden = count === 0;
        if (count > 0) {
            this.region('unsaved').textContent = await getString('perm_unsaved', 'local_delegateaccount', count);
        }
    }

    /**
     * Chooses a permission for a capability.
     *
     * @param {HTMLElement} button The option chosen.
     * @returns {Promise<void>}
     */
    async choose(button) {
        const row = button.closest('[data-capability]');
        const permission = Number(button.dataset.permission);
        this.current.set(row.dataset.capability, permission);
        row.querySelectorAll('[role="radio"]').forEach((option) => {
            const checked = option === button;
            option.setAttribute('aria-checked', String(checked));
            option.tabIndex = checked ? 0 : -1;
        });
        const saved = this.saved.get(row.dataset.capability) || INHERIT;
        row.classList.toggle('is-changed', permission !== saved);
        this.renderChips();
        await this.renderBar();
    }

    /**
     * Answers the clicks inside the editor.
     *
     * @param {Event} event The click.
     * @returns {Promise<void>}
     */
    async click(event) {
        const option = event.target.closest('[role="radio"][data-permission]');
        if (option && !option.disabled) {
            await this.choose(option);
            return;
        }
        const chip = event.target.closest('[data-filter]');
        if (chip) {
            this.filter = chip.dataset.filter;
            this.page = 0;
            await this.render();
            return;
        }
        const action = event.target.closest('[data-action]');
        switch (action?.dataset.action) {
            case 'prev':
                this.page = Math.max(0, this.page - 1);
                await this.render();
                break;
            case 'next':
                this.page += 1;
                await this.render();
                break;
            case 'clearfilters':
                this.filter = '';
                this.component = '';
                this.search = '';
                this.page = 0;
                this.region('search').value = '';
                this.setComponent('');
                await this.render();
                break;
            case 'clearcomponent':
                this.setComponent('');
                this.page = 0;
                this.region('component').focus();
                await this.render();
                break;
            case 'discard':
                this.current = new Map(this.saved);
                this.region('notice').hidden = true;
                await this.render();
                break;
            case 'save':
                await this.save(action);
                break;
            case 'copyfrom':
                await this.copyFrom(action);
                break;
        }
    }

    /**
     * Moves between the options of a capability with the arrow keys, as in any radio group.
     *
     * @param {KeyboardEvent} event The key.
     */
    keydown(event) {
        const option = event.target.closest('[role="radio"]');
        if (!option || !['ArrowRight', 'ArrowDown', 'ArrowLeft', 'ArrowUp'].includes(event.key)) {
            return;
        }
        event.preventDefault();
        const options = Array.from(option.parentElement.querySelectorAll('[role="radio"]:not([disabled])'));
        const step = ['ArrowRight', 'ArrowDown'].includes(event.key) ? 1 : -1;
        const next = options[(options.indexOf(option) + step + options.length) % options.length];
        next.focus();
        this.choose(next);
    }

    /**
     * Saves the changes and refreshes the list in place.
     *
     * @param {HTMLElement} button The save button.
     * @returns {Promise<void>}
     */
    async save(button) {
        const label = button.textContent;
        button.disabled = true;
        button.textContent = await getString('perm_saving', 'local_delegateaccount');
        try {
            const result = await Ajax.call([{
                methodname: 'local_delegateaccount_save_session_permissions',
                args: {delegationid: this.config.delegationid, changes: this.changes()},
            }])[0];
            this.saved = toMap(result.overrides);
            this.current = new Map(this.saved);
            this.region('notice').hidden = true;
            await this.render();
            await toast(await getString('perm_saved', 'local_delegateaccount'));
            this.root.dispatchEvent(new CustomEvent('local_delegateaccount:permissionssaved', {bubbles: true}));
        } catch (error) {
            await toast(describeError(error), 'danger');
        } finally {
            button.disabled = false;
            button.textContent = label;
        }
    }

    /**
     * Loads the overrides of another delegation as changes to review before saving them.
     *
     * @param {HTMLElement} button The button that opened the choice.
     * @returns {Promise<void>}
     */
    async copyFrom(button) {
        const source = await chooseDelegation(button, this.config.delegationid);
        if (!source) {
            return;
        }
        const data = await loadPermissions(source.id);
        this.current = toMap(data.overrides);
        this.page = 0;
        await this.render();
        const notice = this.region('notice');
        notice.textContent = await getString('copy_loaded', 'local_delegateaccount', source.label);
        notice.hidden = false;
    }
}

/**
 * Starts the permission editor.
 *
 * @param {string} selector Selector of the element of the editor.
 * @returns {Promise<void>}
 */
export const init = async(selector) => {
    const root = document.querySelector(selector);
    if (root && !root.dataset.started) {
        root.dataset.started = '1';
        await new Editor(root).init();
    }
};
