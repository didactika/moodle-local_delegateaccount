/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The people of the management page: who may use delegated accounts, or who keeps delegations without that permission. Each
 * row says which accounts the person is using now and what changes next in their delegations, the end of one in use or the start
 * of one that is scheduled. The list is searched, narrowed by the status of the delegations of each person, sorted and paged.
 *
 * @module     local_delegateaccount/PeopleList
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useRef, useState} from 'react';
import {Avatar, Empty, Icon, ListBody, Pager, Person, SearchBox, SkeletonRows, SortHeader, useFitHeight, useStableColumns} from './components/ui';
import StatusMenu from './components/StatusMenu';
import {useList, useDebounced} from './hooks/useList';
import {useText} from './engine/text';
import {DEFAULT_PAGE_SIZE} from './engine/paging';
import {openCreate} from './CreateButton';
import {getUserList} from './services/api';
import type {PeopleListProps, PersonRow, Text} from './types';

/** The statuses a person can be narrowed to, by the status of their delegations. */
const STATUSES = [
    {key: 'active', label: 'delegation_status_active'},
    {key: 'scheduled', label: 'delegation_status_scheduled'},
    {key: 'expired', label: 'delegation_status_expired'},
    {key: 'revoked', label: 'delegation_status_revoked'},
    {key: 'none', label: 'no_delegations'},
];

/**
 * The accounts a person is using now: their pictures, overlapped, and how many more there are.
 *
 * @param props The row and the text.
 * @returns The accounts.
 */
function Accounts({row, text}: {row: PersonRow; text: Text}) {
    if (row.accounts.length === 0) {
        return <span className="text-muted">{text('current_accounts_none')}</span>;
    }
    return <span className="d-inline-flex align-items-center" data-region="accounts" title={row.accountnames}>
        {row.accounts.map((account) => <Avatar key={account.id} user={account} size={30} />)}
        {row.moreaccounts > 0 && <span className="userinitials size-30">{text('current_accounts_more', row.moreaccounts)}</span>}
        <span className="visually-hidden">{row.accountnames}</span>
    </span>;
}

/**
 * The list.
 *
 * @param props Which people and the texts of the page.
 * @returns The list.
 */
export default function PeopleList({strings, scope, initial}: PeopleListProps) {
    const text = useText(strings);
    const list = useList<PersonRow & {id?: number}>(
        {search: '', status: '', sort: 'name', direction: 'asc', page: 0, perpage: DEFAULT_PAGE_SIZE},
        (state) => getUserList(scope, String(state.search), String(state.status), {
            sort: String(state.sort),
            direction: String(state.direction),
            page: Number(state.page),
            perpage: Number(state.perpage),
        }),
        initial,
    );
    const {state, page} = list;
    const table = useRef<HTMLTableElement>(null);
    const fitbox = useRef<HTMLDivElement>(null);
    useFitHeight(fitbox);
    useStableColumns(table, Boolean(page && page.rows.length > 0));
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

    const chosen = String(state.status).split(',').filter(Boolean);
    const filtered = Boolean(state.search) || chosen.length > 0;
    const reset = (): void => {
        setSearch('');
        list.update({search: '', status: ''});
    };
    const sort = (column: string, direction: string): void => list.update({sort: column, direction, page: 0});
    const headers = {sort: String(state.sort), direction: String(state.direction), onSort: sort};
    const prefix = scope === 'historical' ? 'empty_historical_' : 'empty_authorised_';
    const empty = <Empty filtered={filtered} icon="i/users" text={text} strings={strings} title={text(`${prefix}title`)}
        message={text(`${prefix}text`)} onClear={reset} />;

    return <div className="card">
        <div className="d-flex align-items-center justify-content-between gap-3 px-3 py-2 border-bottom" data-region="filters">
            <div className="d-flex align-items-center gap-2 flex-grow-1">
                <SearchBox strings={strings} value={search} onChange={setSearch} label={text('search_people')} />
                <StatusMenu label={text('core:status')} chosen={chosen} text={text} strings={strings}
                    options={STATUSES.map(({key, label}) => ({key, label: text(label), count: page?.counts?.[key]}))}
                    onChange={(statuses) => list.update({status: statuses.join(','), page: 0})} />
            </div>
            <div className="d-flex align-items-center gap-2 flex-shrink-0">
                <button type="button" className="btn btn-link btn-icon" disabled={!filtered} title={text('clear_filters')}
                    aria-label={text('clear_filters')} onClick={reset}><Icon name="i/reload" strings={strings} /></button>
            </div>
        </div>
        <ListBody error={list.error} onRetry={list.refresh} text={text} empty={empty}
            isEmpty={Boolean(page && page.total === 0 && !list.skeleton)}>
            <div ref={fitbox} className="table-responsive" data-region="tablewrap" aria-busy={list.busy || undefined}>
                <table className="table" ref={table}>
                    <caption className="visually-hidden">{text('list_caption_people')}</caption>
                    <thead>
                        <tr>
                            <SortHeader column="name" label={text('authoriseduser')} {...headers} />
                            <SortHeader column="accounts" label={text('current_accounts')} {...headers} />
                            <SortHeader column="next" label={text('next_change')} {...headers} />
                            <th scope="col" className="text-end text-nowrap" data-column="actions">
                                <span className="visually-hidden">{text('core:actions')}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {list.skeleton || !page ? <SkeletonRows columns={4} /> : page.rows.map((row) => <tr key={row.user.id}>
                            <th scope="row"><Person user={row.user} /></th>
                            <td><Accounts row={row} text={text} /></td>
                            <td>
                                {row.next.kind
                                    ? <span className="d-flex flex-column">
                                        <span className={`badge rounded-pill text-bg-${row.next.tone} align-self-start`}>
                                            {row.next.label}
                                        </span>
                                        <span className="small text-muted">{row.next.relative}</span>
                                    </span>
                                    : <span className="text-muted">{row.next.label}</span>}
                            </td>
                            <td className="text-end text-nowrap" data-column="actions">
                                {row.cancreate && <button type="button" className="btn btn-link btn-icon btn-sm text-primary"
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
    </div>;
}
