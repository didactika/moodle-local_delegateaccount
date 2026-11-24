/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The accounts a person may open now, as cards, searched as they type. The first page comes with the page, so a guided tour
 * finds the cards from the start.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_delegateaccount/MyAccounts
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useState} from 'react';
import {Avatar, Empty, Icon, ListBody, Pager, SearchBox} from './components/ui';
import {useList, useDebounced} from './hooks/useList';
import {useText} from './engine/text';
import {DEFAULT_PAGE_SIZE} from './engine/paging';
import {getMyAccounts} from './services/api';
import type {AccountRow, MyAccountsProps} from './types';

/**
 * The accounts.
 *
 * @param props The first page and the texts of the page.
 * @returns The accounts.
 */
export default function MyAccounts({strings, id, initial}: MyAccountsProps) {
    const text = useText(strings);
    const list = useList<AccountRow & {id?: number}>(
        {search: '', page: 0, perpage: DEFAULT_PAGE_SIZE},
        (state) => getMyAccounts(String(state.search), {page: Number(state.page), perpage: Number(state.perpage)}),
        initial,
    );
    const {state, page} = list;
    const [search, setSearch] = useState(String(state.search));
    const typed = useDebounced(search.trim(), 300);
    useEffect(() => {
        list.update({search: typed});
    }, [typed]);

    return <section id={id} className="d-grid gap-3">
        <div className="d-flex align-items-center justify-content-between gap-3 px-3 py-2 border-bottom px-0 border-0">
            <div className="d-flex align-items-center gap-2 flex-grow-1">
                <SearchBox strings={strings} value={search} onChange={setSearch} label={text('search_accounts')} />
            </div>
        </div>
        <ListBody error={list.error} onRetry={list.refresh} text={text} isEmpty={Boolean(page && page.total === 0)}
            empty={<div className="card"><Empty filtered={Boolean(state.search)} icon="i/switch" text={text}
                strings={strings} title={text('empty_mine_title')} message={text('empty_mine_text')}
                onClear={() => setSearch('')} /></div>}>
                <ul className="row list-unstyled mb-0" data-region="account-cards" aria-busy={list.busy || undefined}>
                    {(page?.rows ?? []).map((row) => <li key={row.delegationid} className="col-sm-6 col-xl-4">
                        <div className="card h-100"><div className="card-body d-flex flex-column">
                            <div className="d-flex align-items-center mb-3">
                                <Avatar user={row.user} size={50} />
                                <div className="d-flex flex-column overflow-hidden">
                                    <p className="card-title mb-0 text-truncate" title={row.user.fullname}>{row.user.fullname}</p>
                                    <span className="small text-muted text-truncate" title={row.user.email}>{row.user.email}</span>
                                </div>
                            </div>
                            <span className="d-flex flex-column mb-3">
                                <small className="text-muted">{text('delegation_end')}</small>{row.end}
                            </span>
                            <a className="btn btn-outline-primary mt-auto align-self-start" href={row.loginurl}
                                title={row.loginlabel}>
                                <Icon name="i/switch" strings={strings} /> {text('open_account')}
                            </a>
                        </div></div>
                    </li>)}
                </ul>
        </ListBody>
        {page && page.total > Number(state.perpage) && <div className="card">
            <Pager page={Number(state.page)} perpage={Number(state.perpage)} total={page.total} onChange={list.update}
                text={text} strings={strings} />
        </div>}
    </section>;
}
