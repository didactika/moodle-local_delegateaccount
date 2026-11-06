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
 * What the screens of the plugin share: a debounce, toasts, the confirmation of a destructive action and the Moodle forms
 * that create and edit delegations.
 *
 * @module     local_delegateaccount/local/ui
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalForm from 'core_form/modalform';
import ModalSaveCancel from 'core/modal_save_cancel';
import ModalEvents from 'core/modal_events';
import Templates from 'core/templates';
import {add as addToast} from 'core/toast';
import {getString} from 'core/str';
import {normalise} from 'local_delegateaccount/form_layout';

/**
 * Calls a function once the calls to the returned one stop for a while.
 *
 * @param {Function} callback Function to call.
 * @param {number} wait Milliseconds without calls.
 * @returns {Function} The debounced function.
 */
export const debounce = (callback, wait) => {
    let timer = null;
    return (...args) => {
        window.clearTimeout(timer);
        timer = window.setTimeout(() => callback(...args), wait);
    };
};

/**
 * Shows the result of an action in a toast.
 *
 * @param {string} message What happened.
 * @param {string} type success, info, warning or danger.
 * @returns {Promise<void>}
 */
export const toast = async(message, type = 'success') => {
    await addToast(message, {type, closeButton: true});
};

/**
 * What a failed call says.
 *
 * @param {*} error The error of the call.
 * @returns {string} Its message.
 */
export const describeError = (error) => {
    if (error && typeof error === 'object') {
        return error.message || error.error || String(error);
    }
    return String(error);
};

/**
 * Asks before a destructive action, with the focus on Cancel and the action named in the danger button.
 *
 * @param {Object} options The question.
 * @param {string} options.title Question with the quantity.
 * @param {string} options.text What is lost and what is kept.
 * @param {string} options.button Name of the action.
 * @param {HTMLElement} options.trigger Element that gets the focus back.
 * @param {Function} options.action Does the action; a rejection keeps the dialog open with the error.
 * @returns {Promise<void>}
 */
export const confirmDanger = async({title, text, button, trigger, action}) => {
    const body = await Templates.render('local_delegateaccount/dialog/confirm', {text});
    const modal = await ModalSaveCancel.create({
        title,
        body,
        buttons: {save: button},
        removeOnClose: true,
        returnElement: trigger,
    });
    const root = modal.getRoot()[0];
    root.classList.add('lda-modal');
    const save = root.querySelector('[data-action="save"]');
    save.classList.remove('btn-primary');
    save.classList.add('btn-danger');
    modal.getRoot().on(ModalEvents.save, async(event) => {
        event.preventDefault();
        save.disabled = true;
        root.querySelector('[data-region="confirm-error"]').hidden = true;
        try {
            await action();
            modal.destroy();
        } catch (error) {
            const alert = root.querySelector('[data-region="confirm-error"]');
            alert.textContent = describeError(error);
            alert.hidden = false;
            save.disabled = false;
        }
    });
    modal.getRoot().on(ModalEvents.shown, () => root.querySelector('[data-action="cancel"]')?.focus());
    modal.show();
};

/**
 * Opens one of the Moodle forms of the plugin in a dialog.
 *
 * @param {Object} options The form.
 * @param {string} options.formClass Class of the dynamic form.
 * @param {Object} options.args Arguments of the form.
 * @param {string} options.title Title of the dialog, the same words as the button that opens it.
 * @param {HTMLElement} options.trigger Element that gets the focus back.
 * @param {Function} options.onSubmitted Called with the result of the form.
 */
export const openForm = ({formClass, args, title, trigger, onSubmitted}) => {
    const modalForm = new ModalForm({
        modalConfig: {title, large: true},
        formClass,
        args,
        saveButtonText: getString('savechanges'),
        returnFocus: trigger,
    });
    modalForm.addEventListener(modalForm.events.LOADED, () => {
        const root = modalForm.modal.getRoot()[0];
        root.classList.add('lda-modal');
        normalise(root);
    });
    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (event) => onSubmitted(event.detail || {}));
    modalForm.show();
};
