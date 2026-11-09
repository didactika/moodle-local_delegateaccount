/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Turns the native "Delegated accounts" link of the user menu into a submenu of Moodle's user-menu carousel, and keeps the
 * guided tour of delegated accounts open until it is finished.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes before its body; it draws nothing of its
 * own.
 *
 * @module     local_delegateaccount/UserMenu
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect} from 'react';
import type {UserMenuProps} from './types';

/** The native link of the user menu, written by the hook of the plugin. */
const FALLBACK_LINK = 'a[href*="/local/delegateaccount/pages/accounts.php"]';

/** The submenu, once in the carousel. */
const PANEL = '#carousel-item-delegatedaccounts';

// Controls the guided tour highlights while it waits for the person to click them. The submenu link is found by the attribute
// the submenu gives it, because its href is replaced with "#" once the submenu is built.
const TOUR_TARGETS = [
    '#user-menu-toggle',
    'a[data-carousel-target-id="carousel-item-delegatedaccounts"]',
    '#user-action-menu',
].map((selector) => `${selector}[data-flexitour="highlight"]`).join(', ');

// The only places that stay clickable while the tour runs: the control of the current step, the delegated accounts in the
// last step, and the buttons of the tour popover.
const TOUR_CLICKABLE = [
    '#user-menu-toggle[data-flexitour="highlight"]',
    'a[data-carousel-target-id="carousel-item-delegatedaccounts"][data-flexitour="highlight"]',
    `#user-action-menu[data-flexitour="highlight"] ${PANEL} .items a:not(${FALLBACK_LINK})`,
    '[data-flexitour="container"] button[data-role]',
].join(', ');


/**
 * The submenu.
 *
 * @param props The submenu, as the server drew it.
 * @returns Nothing: the submenu goes into the user menu.
 */
export default function UserMenu({html}: UserMenuProps) {
    useEffect(() => {
        const carousel = document.querySelector<HTMLElement>('.usermenu #usermenu-carousel .carousel-inner');
        const main = document.querySelector('.usermenu #carousel-item-main');
        const trigger = main?.querySelector<HTMLAnchorElement>(FALLBACK_LINK);
        if (!carousel || !trigger || carousel.querySelector(PANEL)) {
            return undefined;
        }
        const template = document.createElement('template');
        template.innerHTML = html;
        carousel.append(template.content.cloneNode(true));
        trigger.href = '#';
        trigger.classList.add('carousel-navigation-link');
        trigger.dataset.carouselTargetId = 'carousel-item-delegatedaccounts';

        // The user menu of Moodle opens the submenu of a link marked as carousel navigation when the click lands on the link
        // itself; a click on its icon is passed on to the link.
        const open = (event: Event): void => {
            if (event.target !== trigger) {
                event.preventDefault();
                event.stopPropagation();
                trigger.click();
            }
        };
        trigger.addEventListener('click', open);

        // The guided tour cannot be closed by clicking elsewhere or by Escape while it waits for the user menu.
        const active = (): boolean => document.querySelector(TOUR_TARGETS) !== null && document.querySelector(PANEL) !== null;
        const click = (event: MouseEvent): void => {
            if (active() && !(event.target as Element).closest(TOUR_CLICKABLE)) {
                event.preventDefault();
                event.stopPropagation();
            }
        };
        const escape = (event: KeyboardEvent): void => {
            if (event.key === 'Escape' && active()) {
                event.stopPropagation();
            }
        };
        document.addEventListener('click', click, true);
        document.addEventListener('keydown', escape, true);
        return () => {
            trigger.removeEventListener('click', open);
            document.removeEventListener('click', click, true);
            document.removeEventListener('keydown', escape, true);
        };
    }, [html]);
    return null;
}
