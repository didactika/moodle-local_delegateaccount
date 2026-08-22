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
 * Enhances the native delegated-account fallback link into a Moodle carousel submenu.
 *
 * @module     local_delegateaccount/usermenu
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';

const SOURCE = '[data-region="local-delegateaccount-usermenu-source"]';
const FALLBACK_LINK = 'a[href*="/local/delegateaccount/accounts.php"]';

/**
 * Adds the prepared submenu to Moodle's existing user-menu carousel.
 */
export const init = () => {
    const source = document.querySelector(SOURCE);
    const carousel = document.querySelector('.usermenu #usermenu-carousel .carousel-inner');
    const main = document.querySelector('.usermenu #carousel-item-main');
    const trigger = main?.querySelector(FALLBACK_LINK);
    if (!source || !carousel || !trigger || carousel.querySelector('#carousel-item-delegatedaccounts')) {
        return;
    }

    trigger.href = '#';
    trigger.classList.add('carousel-navigation-link');
    trigger.dataset.carouselTargetId = 'carousel-item-delegatedaccounts';
    carousel.append(source.content.cloneNode(true));
    source.remove();

    const openSubmenu = event => {
        event.preventDefault();
        event.stopPropagation();
        const panel = carousel.querySelector('#carousel-item-delegatedaccounts');
        const index = Array.from(carousel.children).indexOf(panel);
        $(carousel.closest('#usermenu-carousel')).carousel(index);
    };
    trigger.addEventListener('click', openSubmenu);
    trigger.addEventListener('keydown', event => {
        if (event.key === 'Enter' || event.key === ' ') {
            openSubmenu(event);
        }
    });
};
