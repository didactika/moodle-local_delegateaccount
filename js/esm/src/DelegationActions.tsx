/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The actions of the page of one delegation: open its activity, change its period and revoke it. The page is drawn again
 * afterwards, so its details and its history say what changed.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_delegateaccount/DelegationActions
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useState} from 'react';
import Pending from '@moodle/lms/core/pending';
import {ConfirmDialog, Icon} from './components/ui';
import {useText} from './engine/text';
import {openForm} from './engine/moodle';
import {revokeDelegations} from './services/api';
import type {DelegationActionsProps} from './types';

/**
 * Draw the page again; anyone waiting for the scripts of the page, such as Behat, waits for the new page too.
 */
const reload = (): void => {
    new Pending('local_delegateaccount/delegation:reload');
    window.location.reload();
};

/**
 * The actions.
 *
 * @param props The delegation, what may be done and the texts of the page.
 * @returns The actions.
 */
export default function DelegationActions(props: DelegationActionsProps) {
    const {strings, delegationid, realuserid, canupdate, canrevoke, activityurl} = props;
    const text = useText(strings);
    const [revoking, setRevoking] = useState(false);
    return <div className="dd-page-actions">
        {activityurl && <a className="btn btn-outline-secondary" href={activityurl}>
            <Icon name="i/log" strings={strings} /> {text('view_delegated_activity')}
        </a>}
        {canupdate && <button type="button" className="btn btn-outline-secondary" onClick={(event) => void openForm(
            'local_delegateaccount\\form\\bulk_edit_dynamic_form',
            {realuserid, delegationids: String(delegationid)},
            text('edit_period'),
            event.currentTarget,
            reload,
        )}>
            <Icon name="t/edit" strings={strings} /> {text('edit_period')}
        </button>}
        {canrevoke && <button type="button" className="btn btn-outline-danger" onClick={() => setRevoking(true)}>
            <Icon name="t/delete" strings={strings} /> {text('revoke')}
        </button>}
        {revoking && <ConfirmDialog text={text} strings={strings} onClose={() => setRevoking(false)}
            title={text('revoke_confirm_title_one')} message={text('revoke_confirm_text')}
            button={text('revoke_confirm_button_one')}
            onConfirm={async() => {
                await revokeDelegations([delegationid]);
                reload();
            }} />}
    </div>;
}
