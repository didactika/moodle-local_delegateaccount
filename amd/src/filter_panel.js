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
 * Keeps Moodle-native dropdown filters open while their fields are edited.
 *
 * @module     local_delegateaccount/filter_panel
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {normalise} from './form_layout';

const ROOT = '[data-region="local-delegateaccount-filters"]';
const MENU = '[data-region="local-delegateaccount-filter-menu"]';
const DISMISSING = 'a, button';

/**
 * Initialises all delegated-account filter panels on the current page.
 */
export const init = () => {
    const roots = Array.from(document.querySelectorAll(ROOT));

    roots.forEach(root => {
        normalise(root);
        root.querySelector(MENU)?.addEventListener('click', event => {
            if (!event.target.closest(DISMISSING)) {
                event.stopPropagation();
            }
        });
    });

    window.addEventListener('resize', () => roots.forEach(normalise), {passive: true});
};
