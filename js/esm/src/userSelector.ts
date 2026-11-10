/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The source of the user pickers of the forms that create delegations: only the people who may use delegations, and only
 * the accounts that can be delegated to them. Moodle's autocomplete loads it through core/esm, as the transport of the
 * field.
 *
 * @module     local_delegateaccount/userSelector
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {fetchOne} from '@moodle/lms/core/ajax';

/** A user the web service offers. */
interface Option {
    id: number;
    name: string;
    label?: string;
}

/**
 * Escape a text for HTML.
 *
 * @param text The text.
 * @returns The escaped text.
 */
const escape = (text: string): string => text.replace(/[&<>"']/g, (character) => `&#${character.charCodeAt(0)};`);

/**
 * Load the users that match what was typed.
 *
 * @param selector Selector of the field.
 * @param query What was typed.
 * @param callback Called with the users.
 * @param failure Called with the error.
 * @returns Resolves when it is done.
 */
export const transport = async(
    selector: string,
    query: string,
    callback: (results: Option[]) => void,
    failure: (error: unknown) => void,
): Promise<void> => {
    const select = document.querySelector<HTMLSelectElement>(selector);
    const realuserid = Number(select?.dataset.realuserid ?? 0);
    const methodname = select?.dataset.ws || 'local_delegateaccount_get_delegated_account_options';
    try {
        const results = await fetchOne<Option[]>({methodname, args: {query, realuserid}});
        callback(results.map((user) => ({...user, label: `<span>${escape(user.name)}</span>`})));
    } catch (error) {
        failure(error);
    }
};

/**
 * Turn the users into the options of the field.
 *
 * @param selector Selector of the field.
 * @param results The users.
 * @returns The options.
 */
export const processResults = (selector: string, results: Option[] | unknown): unknown =>
    Array.isArray(results) ? results.map((result: Option) => ({value: result.id, label: result.label})) : results;
