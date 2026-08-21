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
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import Templates from 'core/templates';

/**
 * Initialize the user menu injection.
 *
 * @param {Object} data Data to render the templates.
 */
export const init = (data) => {
    $(document).ready(() => {
        const carouselInner = $('#usermenu-carousel .carousel-inner');
        const mainItem = $('#carousel-item-main');

        if (carouselInner.length === 0 || mainItem.length === 0 || $('#carousel-item-delegatedaccounts').length > 0) {
            return;
        }

        Templates.render('local_delegateaccount/usermenu_trigger', data)
            .then((html, js) => {
                const trigger = $(html);
                const logout = mainItem.find('a[href*="logout.php"]');

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

                Templates.runTemplateJS(js);
                return;
            })
            .catch(Templates.errorHandler);

        Templates.render('local_delegateaccount/usermenu_panel', data)
            .then((html, js) => {
                carouselInner.append(html);
                Templates.runTemplateJS(js);
                return;
            })
            .catch(Templates.errorHandler);
    });
};
