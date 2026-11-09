/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Typed adapters for the parts of Moodle that have no native ESM module yet: its toasts and the dialogs of its forms. The
 * forms that create and edit delegations stay Moodle forms, as the design system asks.
 *
 * @module     local_delegateaccount/engine/moodle
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {requireAsync} from '@moodle/lms/core/amd';

/** The tone of a toast. */
export type Tone = 'success' | 'info' | 'warning' | 'danger';

interface ToastModule {
    add(message: string, options?: {type?: Tone; closeButton?: boolean}): Promise<unknown>;
}

interface ModalFormEvents {
    LOADED: string;
    FORM_SUBMITTED: string;
}

interface ModalFormInstance {
    events: ModalFormEvents;
    modal: {getRoot(): ArrayLike<HTMLElement>};
    addEventListener(name: string, callback: (event: CustomEvent) => void): void;
    show(): Promise<void>;
}

type ModalFormClass = new (config: Record<string, unknown>) => ModalFormInstance;

/**
 * The default export of an AMD module, whichever way it was written.
 *
 * @param id Name of the module.
 * @returns What it exports.
 */
const load = async<T>(id: string): Promise<T> => {
    const module = await requireAsync<T | {default: T}>(id);
    return typeof module === 'object' && module !== null && 'default' in module ? (module as {default: T}).default : module as T;
};

/**
 * Show the result of an action in a toast.
 *
 * @param message What happened.
 * @param type Tone of the toast.
 * @returns Resolves when it is shown.
 */
export const toast = async(message: string, type: Tone = 'success'): Promise<void> => {
    const module = await load<ToastModule>('core/toast');
    await module.add(message, {type, closeButton: true});
};

/**
 * What a failed call says, whether it came as an error or as the object that the web service gives.
 *
 * @param error What the call failed with.
 * @returns The message.
 */
export const describeError = (error: unknown): string => {
    if (error instanceof Error) {
        return error.message;
    }
    if (error !== null && typeof error === 'object' && 'message' in error) {
        return String((error as {message: unknown}).message);
    }
    return String(error);
};

/**
 * The date fields of Moodle forms fit a dialog: they wrap on a narrow screen and lose their inline margins.
 *
 * @param root The dialog.
 */
const normalise = (root: HTMLElement): void => {
    const narrow = window.matchMedia('(max-width: 575.98px)').matches;
    root.querySelectorAll<HTMLElement>('.fdate_selector, .fdate_time_selector').forEach((controls) => {
        controls.classList.toggle('flex-wrap', narrow);
        controls.classList.toggle('flex-nowrap', !narrow);
        Array.from(controls.children).forEach((control) => {
            control.classList.remove('mx-1', 'mx-2', 'ms-1', 'ms-2', 'me-1', 'me-2');
        });
    });
};

/** What a form of the plugin answers once it is saved. */
export interface FormResult {
    type?: Tone;
    message?: string;
}

/**
 * Open one of the Moodle forms of the plugin in a dialog.
 *
 * @param formClass Class of the dynamic form.
 * @param args Arguments of the form.
 * @param title Title of the dialog, the same words as the button that opens it.
 * @param trigger Element that gets the focus back.
 * @param onSubmitted Called with what the form answered.
 * @returns Resolves when the dialog is open.
 */
export const openForm = async(
    formClass: string,
    args: Record<string, string | number>,
    title: string,
    trigger: HTMLElement | null,
    onSubmitted: (result: FormResult) => void,
): Promise<void> => {
    const ModalForm = await load<ModalFormClass>('core_form/modalform');
    const form = new ModalForm({modalConfig: {title, large: true}, formClass, args, returnFocus: trigger});
    form.addEventListener(form.events.LOADED, () => {
        const root = form.modal.getRoot()[0];
        root.classList.add('lda-modal');
        normalise(root);
    });
    form.addEventListener(form.events.FORM_SUBMITTED, (event) => onSubmitted((event.detail || {}) as FormResult));
    await form.show();
};
