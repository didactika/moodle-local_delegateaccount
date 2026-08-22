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
 * Injects the delegated accounts menu into the user menu carousel.
 *
 * @module     local_delegateaccount/usermenu
 * @author     Miguel Rivas Morantes <miguelrivasmorantes@gmail.com>
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';

const SOURCE = '[data-region="local-delegateaccount-usermenu-source"]';
const TRIGGER = '[data-region="local-delegateaccount-usermenu-trigger"]';
const PANEL = '[data-region="local-delegateaccount-usermenu-panel"]';

/**
 * Initialize the user menu injection.
 */
export const init = () => {
    $(document).ready(() => {
        const carouselInner = $('#usermenu-carousel .carousel-inner');
        const mainItem = $('#carousel-item-main');
        const source = $(SOURCE);

        if (
            carouselInner.length === 0 ||
            mainItem.length === 0 ||
            source.length === 0 ||
            $('#carousel-item-delegatedaccounts').length > 0
        ) {
            source.remove();
            return;
        }

        const trigger = source.find(TRIGGER).children().first().detach();
        const panel = source.find(PANEL).children().first().detach();
        const logout = mainItem.find('a[href*="logout.php"]');

        if (trigger.length === 0 || panel.length === 0) {
            source.remove();
            return;
        }

        if (logout.length > 0) {
            const divider = logout.prev('.dropdown-divider');
            if (divider.length > 0) {
                trigger.insertBefore(divider);
            } else {
                trigger.insertBefore(logout);
            }
        } else {
            mainItem.append(trigger);
        }

        carouselInner.append(panel);
        source.remove();
    });
};
