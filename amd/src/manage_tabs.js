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
 * The tabs of the management page: each one puts its list in the page without loading it again, and only the list of the open tab
 * lives in the page, so the lists never read the filters of one another from the address.
 *
 * @module     local_delegateaccount/manage_tabs
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {init as people} from 'local_delegateaccount/people';
import {init as delegations} from 'local_delegateaccount/delegations';

/**
 * Starts the list that is in the panel.
 *
 * @param {HTMLElement} panel The panel of the open tab.
 */
const start = (panel) => {
    if (panel.querySelector('#lda-people')) {
        people('#lda-people');
    } else if (panel.querySelector('#lda-delegations')) {
        delegations('#lda-delegations');
    }
};

/**
 * Makes the tabs work and starts the list of the open one.
 */
export const init = () => {
    const root = document.querySelector('[data-region="lda-manage"]');
    const panel = root.querySelector('[data-region="tabpanel"]');
    // What the lists keep in the address: the list of the tab that is left takes them with it.
    const keys = ['search', 'status', 'sort', 'direction', 'page', 'perpage', 'datefrom', 'dateto', 'component', 'action'];
    root.querySelectorAll('[data-tab]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            if (link.getAttribute('aria-selected') === 'true') {
                return;
            }
            const key = link.getAttribute('data-tab');
            const template = root.querySelector('template[data-tab-panel="' + key + '"]');
            root.querySelectorAll('[data-tab]').forEach((other) => {
                const on = other === link;
                other.classList.toggle('active', on);
                other.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            panel.replaceChildren(template.content.cloneNode(true));
            const url = new URL(window.location.href);
            keys.forEach((name) => {
                url.searchParams.delete(name);
            });
            if (key === 'authorised') {
                url.searchParams.delete('tab');
            } else {
                url.searchParams.set('tab', key);
            }
            window.history.replaceState(window.history.state, '', url);
            start(panel);
        });
    });
    start(panel);
};
