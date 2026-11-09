/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The people of the management page: the ones who may use delegated accounts, or the ones who lost the permission but keep
 * delegations, with the delegations each one has.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_delegateaccount/PeopleList
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useState} from 'react';
import {Chips, Empty, Icon, ListBody, Pager, Person, SearchBox, SkeletonRows, SortHeader} from './components/ui';
import {useList, useDebounced} from './hooks/useList';
import {useText} from './engine/text';
import {openCreate} from './CreateButton';
import {getUserList} from './services/api';
import type {PeopleListProps, PersonRow} from './types';

/** The chips of the list, by the status of the delegations of each person. */
const CHIPS = [
    {key: '', label: 'perm_filter_all'},
    {key: 'active', label: 'delegation_status_active'},
    {key: 'scheduled', label: 'delegation_status_scheduled'},
    {key: 'expired', label: 'delegation_status_expired'},
    {key: 'revoked', label: 'delegation_status_revoked'},
    {key: 'none', label: 'no_delegations'},
];

/**
 * The list.
 *
 * @param props Which people and the texts of the page.
 * @returns The list.
 */
export default function PeopleList({strings, scope}: PeopleListProps) {
    const text = useText(strings);
    const list = useList<PersonRow & {id?: number}>(
        {search: '', status: '', sort: 'name', direction: 'asc', page: 0, perpage: 20},
        (state) => getUserList(scope, String(state.search), String(state.status), {
            sort: String(state.sort),
            direction: String(state.direction),
            page: Number(state.page),
            perpage: Number(state.perpage),
        }),
    );
    const {state, page} = list;
    const [search, setSearch] = useState(String(state.search));
    const typed = useDebounced(search.trim(), 300);
    useEffect(() => {
        list.update({search: typed});
    }, [typed]);
    useEffect(() => {
        const created = (): void => list.refresh();
        document.addEventListener('local_delegateaccount:created', created);
        return () => document.removeEventListener('local_delegateaccount:created', created);
    }, []);

    const sort = (column: string, direction: string): void => list.update({sort: column, direction, page: 0});
    const headers = {sort: String(state.sort), direction: String(state.direction), onSort: sort};
    const prefix = scope === 'historical' ? 'empty_historical_' : 'empty_authorised_';
    const empty = <Empty filtered={Boolean(state.search) || Boolean(state.status)} icon="i/users" text={text}
        strings={strings} title={text(`${prefix}title`)} message={text(`${prefix}text`)}
        onClear={() => {
            setSearch('');
            list.update({search: '', status: ''});
        }} />;

    return <div className="d-grid gap-3">
        <Chips label={text('filter_by_status')} value={String(state.status)} counts={page?.counts}
            chips={CHIPS.map(({key, label}) => ({key, label: text(label)}))} onChange={(status) => list.update({status})} />
        <div className="card">
            <div className="table-toolbar">
                <div className="table-toolbar-start">
                    <SearchBox strings={strings} value={search} onChange={setSearch} label={text('search_people')} />
                </div>
            </div>
            <ListBody error={list.error} onRetry={list.refresh} text={text} empty={empty}
                isEmpty={Boolean(page && page.total === 0 && !list.skeleton)}>
                <div className="table-responsive" aria-busy={list.busy || undefined}>
                    <table className="table">
                        <caption className="visually-hidden">{text('list_caption_people')}</caption>
                        <thead>
                            <tr>
                                <SortHeader column="name" label={text('authoriseduser')} {...headers} />
                                <SortHeader column="active" label={text('active_delegations')} numeric {...headers} />
                                <SortHeader column="scheduled" label={text('scheduled_delegations')} numeric {...headers} />
                                <th scope="col" className="table-actions">
                                    <span className="visually-hidden">{text('core:actions')}</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {list.skeleton || !page ? <SkeletonRows columns={4} /> : page.rows.map((row) => <tr key={row.user.id}>
                                <th scope="row"><Person user={row.user} /></th>
                                <td className="text-end">{row.activecount}</td>
                                <td className="text-end">{row.scheduledcount}</td>
                                <td className="table-actions">
                                    {row.cancreate && <button type="button" className="btn btn-link btn-icon btn-sm"
                                        title={row.addlabel} aria-label={row.addlabel}
                                        onClick={(event) => void openCreate(row.user.id, event.currentTarget, text)}>
                                        <Icon name="t/add" strings={strings} />
                                    </button>}
                                </td>
                            </tr>)}
                        </tbody>
                    </table>
                </div>
            </ListBody>
            {page && page.total > 0 && !list.error && <Pager page={Number(state.page)} perpage={Number(state.perpage)}
                total={page.total} onChange={list.update} text={text} strings={strings} />}
        </div>
    </div>;
}
