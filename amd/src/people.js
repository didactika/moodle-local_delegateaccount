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
 * The people of the management page: the ones who may use delegated accounts, or the ones who lost the permission but keep
 * delegations, with the delegations each one has.
 *
 * @module     local_delegateaccount/people
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getString} from 'core/str';
import List from 'local_delegateaccount/local/list';
import {openCreate} from 'local_delegateaccount/create';

/**
 * Starts the list of people.
 *
 * @param {string} selector Selector of the element of the list.
 * @returns {Promise<List>}
 */
export const init = async(selector) => {
    const root = document.querySelector(selector);
    const config = JSON.parse(root.dataset.config);
    const prefix = config.scope === 'historical' ? 'empty_historical_' : 'empty_authorised_';
    const [emptytitle, emptytext] = await Promise.all([
        getString(prefix + 'title', 'local_delegateaccount'),
        getString(prefix + 'text', 'local_delegateaccount'),
    ]);
    const list = new List(root, {
        method: 'local_delegateaccount_get_user_list',
        template: 'local_delegateaccount/list/people_table',
        defaults: {search: '', status: '', sort: 'name', direction: 'asc', page: 0, perpage: 12},
        args: (state) => ({
            scope: config.scope,
            search: state.search,
            status: state.status,
            sort: state.sort,
            direction: state.direction,
            page: state.page,
            perpage: state.perpage,
        }),
        empty: () => ({icon: 'i/users', title: emptytitle, text: emptytext}),
    });
    root.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action="create"]');
        if (button) {
            openCreate(Number(button.dataset.id), button);
        }
    });
    document.addEventListener('local_delegateaccount:created', () => list.load());
    await list.init();
    return list;
};
