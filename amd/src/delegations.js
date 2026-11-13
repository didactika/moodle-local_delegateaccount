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
 * A list of delegations, of one person or of everyone, and what can be done to one or to many of them: change their period,
 * change or copy their session permissions, and revoke them.
 *
 * @module     local_delegateaccount/delegations
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import {getString} from 'core/str';
import List from 'local_delegateaccount/local/list';
import {confirmDanger, describeError, openForm, toast} from 'local_delegateaccount/local/ui';
import {openBulkPermissions} from 'local_delegateaccount/bulk_permissions';

/**
 * The delegations an action on the selection reaches. When every matching delegation was chosen, the server says which ones
 * can still be changed, within the limit of records per bulk action.
 *
 * @param {List} list The list.
 * @returns {Promise<number[]>} Delegation identifiers.
 */
const resolveIds = async(list) => {
    const selection = list.selection();
    if (!selection.all) {
        return selection.delegationids;
    }
    const result = await Ajax.call([{
        methodname: 'local_delegateaccount_get_delegation_ids',
        args: {filters: selection.filters},
    }])[0];
    if (result.error) {
        throw new Error(result.error);
    }
    return result.ids;
};

/**
 * Asks before revoking and revokes.
 *
 * @param {List} list The list.
 * @param {number[]|null} ids The delegations, or null for the selection.
 * @param {HTMLElement} trigger Element that gets the focus back.
 * @returns {Promise<void>}
 */
const revoke = async(list, ids, trigger) => {
    let delegationids = ids;
    try {
        delegationids = ids || await resolveIds(list);
    } catch (error) {
        await toast(describeError(error), 'danger');
        return;
    }
    const one = delegationids.length === 1;
    const [title, text, button] = await Promise.all([
        one ? getString('revoke_confirm_title_one', 'local_delegateaccount')
            : getString('revoke_confirm_title', 'local_delegateaccount', delegationids.length),
        getString('revoke_confirm_text', 'local_delegateaccount'),
        one ? getString('revoke_confirm_button_one', 'local_delegateaccount')
            : getString('revoke_confirm_button', 'local_delegateaccount', delegationids.length),
    ]);
    await confirmDanger({
        title,
        text,
        button,
        trigger,
        action: async() => {
            const result = await Ajax.call([{
                methodname: 'local_delegateaccount_revoke_delegations',
                args: {delegationids, confirm: true},
            }])[0];
            list.clearSelection();
            await list.load();
            await toast(await getString('revoked_count', 'local_delegateaccount', result.revokedcount ?? delegationids.length));
        },
    });
};

/**
 * Opens the Moodle form that changes the period of one or several delegations.
 *
 * @param {List} list The list.
 * @param {Object} config Configuration of the list.
 * @param {number[]|null} ids The delegations, or null for the selection.
 * @param {HTMLElement} trigger Element that gets the focus back.
 * @returns {Promise<void>}
 */
const editPeriod = async(list, config, ids, trigger) => {
    let delegationids = ids;
    try {
        delegationids = ids || await resolveIds(list);
    } catch (error) {
        await toast(describeError(error), 'danger');
        return;
    }
    openForm({
        formClass: 'local_delegateaccount\\form\\bulk_edit_dynamic_form',
        args: {realuserid: config.realuserid, delegationids: delegationids.join(',')},
        title: getString(delegationids.length === 1 ? 'edit_period' : 'edit_selected_delegations', 'local_delegateaccount'),
        trigger,
        onSubmitted: async(result) => {
            list.clearSelection();
            await list.load();
            if (result.message) {
                await toast(result.message, result.type || 'success');
            }
        },
    });
};

/**
 * Starts a list of delegations.
 *
 * @param {string} selector Selector of the element of the list.
 * @returns {Promise<List>}
 */
export const init = async(selector) => {
    const root = document.querySelector(selector);
    const config = JSON.parse(root.dataset.config);
    const filters = (state) => ({realuserid: config.realuserid, status: state.status, search: state.search});
    const [emptytitle, emptytext] = await Promise.all([
        getString('empty_delegations_title', 'local_delegateaccount'),
        getString('empty_delegations_text', 'local_delegateaccount'),
    ]);
    const list = new List(root, {
        method: 'local_delegateaccount_get_delegation_list',
        template: 'local_delegateaccount/list/delegations_table',
        prefix: config.prefix || '',
        defaults: {search: '', status: config.status ?? 'active', sort: 'account', direction: 'asc', page: 0, perpage: 12},
        args: (state) => ({
            filters: filters(state),
            sort: state.sort,
            direction: state.direction,
            page: state.page,
            perpage: state.perpage,
        }),
        filters,
        context: (response) => ({
            rows: response.rows,
            showrealuser: !config.realuserid,
            canselect: config.canbulk,
            canpermissions: config.canpermissions,
        }),
        empty: () => ({icon: 'i/users', title: emptytitle, text: emptytext}),
    });

    root.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action]');
        if (!button) {
            return;
        }
        const id = Number(button.dataset.id || 0);
        switch (button.dataset.action) {
            case 'edit':
                editPeriod(list, config, [id], button);
                break;
            case 'revoke':
                revoke(list, [id], button);
                break;
            case 'bulk-edit':
                editPeriod(list, config, null, button);
                break;
            case 'bulk-revoke':
                revoke(list, null, button);
                break;
            case 'bulk-permissions':
                openBulkPermissions({
                    selection: list.selection(),
                    count: list.selectionCount(),
                    trigger: button,
                    onApplied: async() => {
                        list.clearSelection();
                        await list.load();
                    },
                });
                break;
        }
    });

    // A delegation created from the header of the page appears at once.
    // A list that the tabs took off the page is not read again.
    document.addEventListener('local_delegateaccount:created', () => root.isConnected && list.load());
    await list.init();
    return list;
};
