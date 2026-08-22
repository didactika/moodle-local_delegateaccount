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
 * Controls the report-style filter dropdown consistently across supported Moodle versions.
 *
 * @module     local_delegateaccount/filter_toggle
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {normalise} from './form_layout';

const ROOT = '[data-region="local-delegateaccount-filters"]';
const TOGGLE = '[data-action="local-delegateaccount-toggle-filters"]';
const MENU = '[data-region="local-delegateaccount-filter-menu"]';

/**
 * Sets a filter dropdown's open state.
 *
 * @param {HTMLElement} root Filter dropdown root.
 * @param {boolean} open Whether the menu should be open.
 */
const setOpen = (root, open) => {
    root.querySelector(TOGGLE)?.setAttribute('aria-expanded', open ? 'true' : 'false');
    root.querySelector(MENU)?.classList.toggle('show', open);
};

/**
 * Initialises every delegated-account filter dropdown on the page.
 */
export const init = () => {
    const roots = Array.from(document.querySelectorAll(ROOT));

    roots.forEach(root => {
        normalise(root);
        root.querySelector(TOGGLE)?.addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();
            const menu = root.querySelector(MENU);
            const willOpen = !menu?.classList.contains('show');
            roots.forEach(otherRoot => setOpen(otherRoot, false));
            setOpen(root, willOpen);
        });

        root.querySelector(MENU)?.addEventListener('click', event => event.stopPropagation());
    });

    document.addEventListener('click', () => roots.forEach(root => setOpen(root, false)));
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            roots.forEach(root => setOpen(root, false));
        }
    });
    window.addEventListener('resize', () => roots.forEach(normalise), {passive: true});
};
