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
 * Opens delegated-account lifecycle details in a core Moodle modal.
 *
 * @module     local_delegateaccount/delegation_info
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import ModalFactory from 'core/modal_factory';
import Notification from 'core/notification';

const SELECTOR = '[data-action="local_delegateaccount-show-delegation-info"]';

/**
 * Enables progressive modal presentation for delegation detail links.
 */
export const init = () => {
    $(document).on('click', SELECTOR, event => {
        event.preventDefault();

        const trigger = $(event.currentTarget);
        const content = document.querySelector(trigger.attr('data-content-selector'));
        if (!content) {
            return;
        }

        ModalFactory.create({
            type: ModalFactory.types.DEFAULT,
            title: trigger.attr('aria-label'),
            body: content.innerHTML,
        }).then(modal => {
            modal.show();
            return modal;
        }).catch(Notification.exception);
    });
};
