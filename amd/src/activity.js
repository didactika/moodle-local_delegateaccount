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
 * What was done through one delegation, newest first, narrowed by days, component and action as they are typed.
 *
 * @module     local_delegateaccount/activity
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getString} from 'core/str';
import List from 'local_delegateaccount/local/list';
import {debounce} from 'local_delegateaccount/local/ui';

/** Filters of the report, as they are named in the address and in the web service. */
const FILTERS = ['datefrom', 'dateto', 'component', 'action'];

/**
 * Starts the activity report.
 *
 * @param {string} selector Selector of the element of the list.
 * @returns {Promise<List>}
 */
export const init = async(selector) => {
    const root = document.querySelector(selector);
    const config = JSON.parse(root.dataset.config);
    const form = root.querySelector('[data-region="filters"]');
    const [emptytitle, emptytext, nolog] = await Promise.all([
        getString('activity_empty_title', 'local_delegateaccount'),
        getString('activity_empty_text', 'local_delegateaccount'),
        getString('activity_nolog', 'local_delegateaccount'),
    ]);
    let haslog = true;
    const list = new List(root, {
        method: 'local_delegateaccount_get_activity_list',
        template: 'local_delegateaccount/activity/table',
        defaults: {datefrom: '', dateto: '', component: '', action: '', page: 0, perpage: 20},
        args: (state) => ({
            delegationid: config.delegationid,
            datefrom: state.datefrom,
            dateto: state.dateto,
            component: state.component,
            action: state.action,
            page: state.page,
            perpage: state.perpage,
        }),
        isFiltered: (state) => FILTERS.some((name) => state[name] !== ''),
        cleared: Object.fromEntries(FILTERS.map((name) => [name, ''])),
        onClear: () => form.reset(),
        context: (response) => {
            haslog = response.haslog;
            return {rows: response.rows};
        },
        empty: () => ({icon: 'i/log', title: emptytitle, text: haslog ? emptytext : nolog}),
    });
    FILTERS.forEach((name) => {
        form.elements[name].value = list.state[name] || '';
    });
    const apply = debounce(() => {
        const changes = {page: 0};
        FILTERS.forEach((name) => {
            changes[name] = form.elements[name].value.trim();
        });
        list.update(changes);
    }, 300);
    form.addEventListener('input', apply);
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        apply();
    });
    await list.init();
    return list;
};
