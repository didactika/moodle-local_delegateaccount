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
 * The accounts a person may open now, as cards, searched as they type.
 *
 * @module     local_delegateaccount/accounts
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getString} from 'core/str';
import List from 'local_delegateaccount/local/list';
import {init as fitTables} from 'local_delegateaccount/fit_tables';

/**
 * Starts the list of accounts.
 *
 * @param {string} selector Selector of the element of the list.
 * @returns {Promise<List>}
 */
export const init = async(selector) => {
    // The box of the table takes the height the window leaves it.
    fitTables();
    const root = document.querySelector(selector);
    const [emptytitle, emptytext] = await Promise.all([
        getString('empty_mine_title', 'local_delegateaccount'),
        getString('empty_mine_text', 'local_delegateaccount'),
    ]);
    const list = new List(root, {
        method: 'local_delegateaccount_get_my_accounts',
        template: 'local_delegateaccount/accounts/cards',
        defaults: {search: '', page: 0, perpage: 12},
        args: (state) => ({search: state.search, page: state.page, perpage: state.perpage}),
        context: (response) => ({rows: response.rows.map((row) => ({...row, user: {...row.user, size: 50}}))}),
        empty: () => ({icon: 'i/switch', title: emptytitle, text: emptytext}),
    });
    await list.init();
    return list;
};
