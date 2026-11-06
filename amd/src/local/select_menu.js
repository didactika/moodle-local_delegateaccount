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
 * The list a select opens, as the design system draws it. The browser draws its own and a page cannot style it, so a select
 * of the screens of the plugin opens a dropdown menu of the design system instead, with the chosen option marked and the
 * groups as headers, below the select or above it, wherever it fits. The select underneath keeps its value and fires
 * input and change, so the markup does not change.
 *
 * Keyboard: Enter, Space and the arrows open it; the arrows, Home and End move, Enter and Space choose, a letter jumps to the
 * next option that starts with it, Escape and Tab close it.
 *
 * @module     local_delegateaccount/local/select_menu
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** The selects that open the menu: single selects of the screens and dialogs of the plugin. */
const SELECTOR = ':is(.lda, .lda-modal) select:is(.form-select, .custom-select)';

/** Room kept between the menu and the edge of the window. */
const MARGIN = 8;

let current = null;
let installed = false;

/**
 * Whether a select opens the menu.
 *
 * @param {HTMLSelectElement} select The select.
 * @returns {boolean}
 */
const eligible = (select) => !select.multiple && select.size <= 1 && !select.disabled;

/**
 * Closes the open menu.
 *
 * @param {boolean} restoreFocus Whether the select gets the focus back.
 */
const close = (restoreFocus = false) => {
    if (!current) {
        return;
    }
    const {select, menu} = current;
    menu.remove();
    select.removeAttribute('aria-expanded');
    current = null;
    if (restoreFocus) {
        select.focus();
    }
};

/**
 * Chooses an option and tells the page, as a native select would.
 *
 * @param {number} index Index of the entry.
 */
const choose = (index) => {
    const entry = current?.entries[index];
    if (!entry || entry.option.disabled) {
        return;
    }
    const {select} = current;
    const changed = select.selectedIndex !== entry.option.index;
    select.selectedIndex = entry.option.index;
    close(true);
    if (changed) {
        select.dispatchEvent(new Event('input', {bubbles: true}));
        select.dispatchEvent(new Event('change', {bubbles: true}));
    }
};

/**
 * Moves the focus to an entry, skipping the disabled ones.
 *
 * @param {number} index Index of the entry; -1 is the last.
 */
const activate = (index) => {
    const {entries, menu} = current;
    const count = entries.length;
    for (let step = 0; step < count; step++) {
        const at = (index + step * (index >= 0 ? 1 : -1) + count * 2) % count;
        const entry = entries[at];
        if (entry && !entry.option.disabled) {
            entries.forEach((other) => other.button.classList.toggle('active', other === entry));
            entry.button.focus();
            entry.button.scrollIntoView({block: 'nearest'});
            return;
        }
    }
    menu.focus();
};

/**
 * Places the menu under the select, or over it when there is more room above.
 *
 * @param {HTMLSelectElement} select The select.
 * @param {HTMLElement} menu The menu.
 */
const place = (select, menu) => {
    const box = select.getBoundingClientRect();
    menu.style.minWidth = `${box.width}px`;
    const below = window.innerHeight - box.bottom - MARGIN;
    const above = box.top - MARGIN;
    const height = menu.scrollHeight;
    const up = height > below && above > below;
    menu.style.maxHeight = `${Math.max(120, Math.min(17 * 16, up ? above : below))}px`;
    const top = up ? box.top - Math.min(height, menu.offsetHeight) - 2 : box.bottom + 2;
    menu.style.top = `${Math.max(MARGIN, top)}px`;
    menu.style.left = `${Math.max(MARGIN, Math.min(box.left, window.innerWidth - menu.offsetWidth - MARGIN))}px`;
};

/**
 * Opens the menu of a select.
 *
 * @param {HTMLSelectElement} select The select.
 */
const open = (select) => {
    close();
    const scope = select.closest('.lda, .lda-modal');
    const menu = document.createElement('div');
    menu.className = 'dropdown-menu show dd-select-menu';
    menu.setAttribute('role', 'listbox');
    menu.tabIndex = -1;
    const entries = [];
    const add = (option, parent) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'dropdown-item';
        button.setAttribute('role', 'option');
        button.setAttribute('aria-selected', String(option.selected));
        if (option.disabled) {
            button.classList.add('disabled');
            button.setAttribute('aria-disabled', 'true');
            button.tabIndex = -1;
        }
        button.textContent = option.textContent;
        parent.append(button);
        entries.push({option, button});
    };
    Array.from(select.children).forEach((child) => {
        if (child instanceof HTMLOptGroupElement) {
            const header = document.createElement('h6');
            header.className = 'dropdown-header';
            header.textContent = child.label;
            menu.append(header);
            Array.from(child.children).forEach((option) => add(option, menu));
        } else if (child instanceof HTMLOptionElement) {
            add(child, menu);
        }
    });
    scope.append(menu);
    place(select, menu);
    select.setAttribute('aria-expanded', 'true');
    current = {select, menu, entries};

    menu.addEventListener('click', (event) => {
        const button = event.target.closest('.dropdown-item');
        const at = entries.findIndex((entry) => entry.button === button);
        if (at >= 0) {
            choose(at);
        }
    });
    menu.addEventListener('keydown', (event) => {
        const at = entries.findIndex((entry) => entry.button === document.activeElement);
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            activate(at + 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            activate(at <= 0 ? -1 : at - 1);
        } else if (event.key === 'Home') {
            event.preventDefault();
            activate(0);
        } else if (event.key === 'End') {
            event.preventDefault();
            activate(-1);
        } else if (event.key === 'Escape') {
            event.preventDefault();
            event.stopPropagation();
            close(true);
        } else if (event.key === 'Tab') {
            close();
        } else if (event.key.length === 1 && /\S/.test(event.key)) {
            const letter = event.key.toLowerCase();
            const from = at + 1;
            const found = [...entries.slice(from), ...entries.slice(0, from)].find((entry) => !entry.option.disabled &&
                entry.option.textContent.trim().toLowerCase().startsWith(letter));
            if (found) {
                activate(entries.indexOf(found));
            }
        }
    });
    const selected = entries.findIndex((entry) => entry.option.selected);
    activate(selected >= 0 ? selected : 0);
};

/**
 * Makes the selects of the screens of the plugin open the menu of the design system. It is installed once per page.
 */
export const init = () => {
    if (installed) {
        return;
    }
    installed = true;
    // On mousedown, before the browser opens its own list.
    document.addEventListener('mousedown', (event) => {
        const target = event.target instanceof Element ? event.target : null;
        if (!target) {
            return;
        }
        if (current && !current.menu.contains(target)) {
            const same = target.closest('select') === current.select;
            close();
            if (same) {
                event.preventDefault();
                return;
            }
        }
        const select = target.closest(SELECTOR);
        if (!select || !eligible(select)) {
            return;
        }
        event.preventDefault();
        select.focus();
        open(select);
    });
    document.addEventListener('keydown', (event) => {
        const select = event.target instanceof Element && !current ? event.target.closest(SELECTOR) : null;
        if (select && eligible(select) && ['Enter', ' ', 'ArrowDown', 'ArrowUp'].includes(event.key)) {
            event.preventDefault();
            open(select);
        }
    });
    window.addEventListener('resize', () => close());
    window.addEventListener('scroll', (event) => {
        if (current && !current.menu.contains(event.target)) {
            close();
        }
    }, {capture: true, passive: true});
};
