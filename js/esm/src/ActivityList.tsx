/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What was done through one delegation, newest first, narrowed by a search (words of the component, the action or the event)
 * and by a menu of the date, as every list of the design system.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_delegateaccount/ActivityList
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useRef, useState} from 'react';
import {Empty, Icon, ListBody, Pager, SearchBox, SkeletonRows, useFitHeight, useStableColumns} from './components/ui';
import DateMenu from './components/DateMenu';
import {useList, useDebounced} from './hooks/useList';
import {useText} from './engine/text';
import {DEFAULT_PAGE_SIZE} from './engine/paging';
import {getActivityList} from './services/api';
import type {ActivityListProps, ActivityRow} from './types';

/** Filters of the report, as they are named in the address and in the web service. */
const FILTERS = ['datefrom', 'dateto', 'search'] as const;

/**
 * The report.
 *
 * @param props The delegation, the days it covers and the texts of the page.
 * @returns The report.
 */
export default function ActivityList({strings, delegationid, mindate, maxdate}: ActivityListProps) {
    const text = useText(strings);
    const list = useList<ActivityRow>(
        {datefrom: '', dateto: '', search: '', page: 0, perpage: DEFAULT_PAGE_SIZE},
        (state) => getActivityList({
            delegationid,
            datefrom: String(state.datefrom),
            dateto: String(state.dateto),
            search: String(state.search),
            page: Number(state.page),
            perpage: Number(state.perpage),
        }),
    );
    const {state, page} = list;
    const table = useRef<HTMLTableElement>(null);
    const fitbox = useRef<HTMLDivElement>(null);
    useFitHeight(fitbox);
    useStableColumns(table, Boolean(page && page.rows.length > 0));
    // The search answers as it is typed; the date answers at once.
    const [search, setSearch] = useState(String(state.search));
    const typed = useDebounced(search, 300);
    useEffect(() => {
        list.update({search: typed.trim()});
    }, [typed]);
    const reset = (): void => {
        setSearch('');
        list.update({search: '', datefrom: '', dateto: ''});
    };
    const filtered = FILTERS.some((name) => state[name] !== '');
    const empty = <Empty filtered={filtered} icon="i/log" title={text('activity_empty_title')} text={text} strings={strings}
        message={page?.haslog === false ? text('activity_nolog') : text('activity_empty_text')}
        onClear={reset} />;
    return <div className="card">
        <div className="table-toolbar" data-region="filters">
            <div className="table-toolbar-start">
                <SearchBox strings={strings} value={search} onChange={setSearch} label={text('activity_search')} />
                <div data-region="filter-menus">
                    <DateMenu label={text('activity_filter_date')} from={String(state.datefrom)} to={String(state.dateto)} min={mindate}
                        max={maxdate} text={text} strings={strings} onChange={(datefrom, dateto) => list.update({datefrom, dateto})} />
                </div>
            </div>
            <div className="table-toolbar-end">
                <button type="button" className="btn btn-link btn-icon" disabled={!filtered} title={text('clear_filters')}
                    aria-label={text('clear_filters')} onClick={reset}><Icon name="i/reload" strings={strings} /></button>
            </div>
        </div>
        <ListBody error={list.error} onRetry={list.refresh} text={text} empty={empty}
            isEmpty={Boolean(page && page.total === 0 && !list.skeleton)}>
                <div ref={fitbox} className="table-responsive" data-region="tablewrap" aria-busy={list.busy || undefined}>
                    <table className="table" ref={table}>
                        <caption className="visually-hidden">{text('list_caption_activity')}</caption>
                        <thead>
                            <tr>
                                <th scope="col">{text('core:time')}</th>
                                <th scope="col">{text('activity_event')}</th>
                                <th scope="col">{text('activity_context')}</th>
                                <th scope="col">{text('activity_related')}</th>
                                <th scope="col">{text('activity_ip')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {list.skeleton || !page ? <SkeletonRows columns={5} /> : page.rows.map((row) => <tr key={row.id}>
                                <td className="text-nowrap">{row.time}</td>
                                <th scope="row">
                                    <span className="d-flex flex-column">
                                        {row.eventurl ? <a href={row.eventurl}>{row.eventname}</a> : row.eventname}
                                        <span className="small text-muted text-truncate" title={row.description}>
                                            {row.component} · {row.description}
                                        </span>
                                    </span>
                                </th>
                                <td>{row.contexturl ? <a href={row.contexturl}>{row.contextname}</a> : row.contextname}</td>
                                <td>{row.relateduser || '-'}</td>
                                <td className="text-nowrap">{row.ip || '-'}</td>
                            </tr>)}
                        </tbody>
                    </table>
                </div>
        </ListBody>
        {page && page.total > 0 && !list.error && <Pager page={Number(state.page)} perpage={Number(state.perpage)}
            total={page.total} onChange={list.update} text={text} strings={strings} />}
    </div>;
}
