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
 * Provides an accessible, version-neutral toggle for the management filters.
 *
 * @module     local_delegateaccount/filter_toggle
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';

const SELECTOR = '[data-action="local_delegateaccount-toggle-manage-filters"]';

/**
 * Registers the management-filter toggle.
 *
 * @returns {void}
 */
export const init = () => {
    $(document).on('click', SELECTOR, event => {
        const trigger = $(event.currentTarget);
        const panel = document.querySelector(trigger.attr('data-target'));

        if (!panel) {
            return;
        }

        const expanded = panel.classList.toggle('show');
        trigger.attr('aria-expanded', expanded ? 'true' : 'false');
    });
};
