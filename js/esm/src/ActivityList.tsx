/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What was done through one delegation, newest first, narrowed by days, component and action as they are typed.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_delegateaccount/ActivityList
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useId, useRef, useState} from 'react';
import {Empty, ListBody, Pager, SkeletonRows, useStableColumns} from './components/ui';
import {useList, useDebounced} from './hooks/useList';
import {useText} from './engine/text';
import {DEFAULT_PAGE_SIZE} from './engine/paging';
import {getActivityList} from './services/api';
import type {ActivityListProps, ActivityRow} from './types';

/** Filters of the report, as they are named in the address and in the web service. */
const FILTERS = ['datefrom', 'dateto', 'component', 'action'] as const;

/**
 * The report.
 *
 * @param props The delegation, the days it covers and the texts of the page.
 * @returns The report.
 */
export default function ActivityList({strings, delegationid, mindate, maxdate}: ActivityListProps) {
    const text = useText(strings);
    const id = useId();
    const list = useList<ActivityRow>(
        {datefrom: '', dateto: '', component: '', action: '', page: 0, perpage: DEFAULT_PAGE_SIZE},
        (state) => getActivityList({
            delegationid,
            datefrom: String(state.datefrom),
            dateto: String(state.dateto),
            component: String(state.component),
            action: String(state.action),
            page: Number(state.page),
            perpage: Number(state.perpage),
        }),
    );
    const {state, page} = list;
    const table = useRef<HTMLTableElement>(null);
    useStableColumns(table, Boolean(page && page.rows.length > 0));
    const [fields, setFields] = useState<Record<string, string>>(
        () => Object.fromEntries(FILTERS.map((name) => [name, String(state[name])])),
    );
    const typed = useDebounced(fields, 300);
    useEffect(() => {
        list.update(Object.fromEntries(FILTERS.map((name) => [name, typed[name].trim()])));
    }, [typed]);

    const labels: Record<string, string> = {
        datefrom: 'activity_filter_datefrom',
        dateto: 'activity_filter_dateto',
        component: 'activity_filter_component',
        action: 'activity_filter_action',
    };
    const filtered = FILTERS.some((name) => state[name] !== '');
    const empty = <Empty filtered={filtered} icon="i/log" title={text('activity_empty_title')} text={text} strings={strings}
        message={page?.haslog === false ? text('activity_nolog') : text('activity_empty_text')}
        onClear={() => setFields(Object.fromEntries(FILTERS.map((name) => [name, ''])))} />;
    return <div className="card">
        <form className="card-body d-flex flex-wrap align-items-end border-bottom" data-region="filters"
            aria-label={text('core:filters')} onSubmit={(event) => event.preventDefault()}>
            {FILTERS.map((name) => <div key={name}>
                <label htmlFor={`${id}-${name}`}>{text(labels[name])}</label>
                <input className="form-control" id={`${id}-${name}`} autoComplete="off" value={fields[name]}
                    type={name.startsWith('date') ? 'date' : 'search'} min={mindate} max={maxdate}
                    onChange={(event) => setFields((before) => ({...before, [name]: event.target.value}))} />
            </div>)}
        </form>
        <ListBody error={list.error} onRetry={list.refresh} text={text} empty={empty}
            isEmpty={Boolean(page && page.total === 0 && !list.skeleton)}>
                <div className="table-responsive" data-region="tablewrap" aria-busy={list.busy || undefined}>
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
