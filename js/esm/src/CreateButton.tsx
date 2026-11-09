/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The main action of the management pages: create delegations with the Moodle form of the plugin, for several people or for
 * one, and tell the lists of the page so they show them at once.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_delegateaccount/CreateButton
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {Icon} from './components/ui';
import {useText} from './engine/text';
import {openForm, toast} from './engine/moodle';
import type {CreateButtonProps, Text} from './types';

/**
 * Open the form that creates delegations.
 *
 * @param realuserid The authorised user they are for, or zero to choose several.
 * @param trigger Element that gets the focus back.
 * @param text The texts of the page.
 * @returns Resolves when the form is open.
 */
export const openCreate = (realuserid: number, trigger: HTMLElement | null, text: Text): Promise<void> => openForm(
    'local_delegateaccount\\form\\assign_dynamic_form',
    {realuserid},
    text('create_delegations'),
    trigger,
    (result) => {
        document.dispatchEvent(new CustomEvent('local_delegateaccount:created', {detail: result}));
        if (result.message) {
            void toast(result.message, result.type ?? 'success');
        }
    },
);

/**
 * The button.
 *
 * @param props The authorised user, or zero, and the texts of the page.
 * @returns The button.
 */
export default function CreateButton({strings, realuserid}: CreateButtonProps) {
    const text = useText(strings);
    return <button type="button" className="btn btn-primary"
        onClick={(event) => void openCreate(realuserid, event.currentTarget, text)}>
        <Icon name="t/add" strings={strings} /> {text('create_delegations')}
    </button>;
}
