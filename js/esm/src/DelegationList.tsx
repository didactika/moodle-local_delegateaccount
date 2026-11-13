/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A list of delegations, of one person or of everyone, and what can be done to one or to many of them: change their period,
 * change or copy their session permissions, and revoke them.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_delegateaccount/DelegationList
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useState} from 'react';
import {Chips, ConfirmDialog, Empty, FloatBar, Icon, ListBody, Pager, Person, SearchBox, SkeletonRows, SortHeader}
    from './components/ui';
import {BulkPermissions} from './BulkPermissions';
import {useList, useDebounced} from './hooks/useList';
import {useText} from './engine/text';
import {DEFAULT_PAGE_SIZE} from './engine/paging';
import {describeError, openForm, toast} from './engine/moodle';
import {getDelegationIds, getDelegationList, revokeDelegations} from './services/api';
import type {DelegationListProps, DelegationRow, Filters, Strings, Text} from './types';

/** The statuses a delegation can be filtered by, in the order of their chips. */
const STATUSES = ['', 'active', 'scheduled', 'expired', 'revoked'];

/**
 * One delegation of the list.
 *
 * @param props The delegation, whether it is chosen, which columns there are, the actions and the texts.
 * @returns The row.
 */
function Row({row, checked, canbulk, showuser, onToggle, onEdit, onRevoke, text, strings}: {
    row: DelegationRow;
    checked: boolean;
    canbulk: boolean;
    showuser: boolean;
    onToggle: (checked: boolean) => void;
    onEdit: (trigger: HTMLElement) => void;
    onRevoke: () => void;
    text: Text;
    strings: Strings;
}) {
    const overrides = row.overrides
        ? <a className="badge badge-outline text-bg-primary" href={row.permissionsurl}>{row.overrideslabel}</a>
        : <span className="small text-muted">{text('overrides_none')}</span>;
    return <tr className={checked ? 'table-active' : undefined}>
        {canbulk && <td className="table-select">
            {row.selectable && <input type="checkbox" className="form-check-input" checked={checked}
                aria-label={text('select_row', row.label)} onChange={(event) => onToggle(event.target.checked)} />}
        </td>}
        <th scope="row"><Person user={row.delegateduser} /></th>
        {showuser && <td><Person user={row.realuser} /></td>}
        <td>
            <span className={`badge badge-soft badge-dot rounded-pill text-bg-${row.statustone}`}>{row.statuslabel}</span>
        </td>
        <td><span className="d-flex flex-column">{row.start}<small className="text-muted">{row.end}</small></span></td>
        <td><small className="text-muted">{row.lastaccess}</small></td>
        <td>{overrides}</td>
        <td className="table-actions">
            <button type="button" className="btn btn-link btn-icon btn-sm text-primary" disabled={!row.canupdate}
                title={text('edit_period_of', row.label)} aria-label={text('edit_period_of', row.label)}
                onClick={(event) => onEdit(event.currentTarget)}>
                <Icon name="t/edit" strings={strings} />
            </button>
            <a className="btn btn-link btn-icon btn-sm text-info" href={row.permissionsurl}
                title={text('session_permissions_of', row.label)} aria-label={text('session_permissions_of', row.label)}>
                <Icon name="i/permissions" strings={strings} />
            </a>
            {row.canviewactivity
                ? <a className="btn btn-link btn-icon btn-sm text-success" href={row.activityurl}
                    title={text('view_activity_of', row.label)} aria-label={text('view_activity_of', row.label)}>
                    <Icon name="i/log" strings={strings} />
                </a>
                : <span className="btn btn-link btn-icon btn-sm text-success disabled" aria-disabled="true"
                    title={text('view_activity_of', row.label)}>
                    <Icon name="i/log" strings={strings} />
                </span>}
            <button type="button" className="btn btn-link btn-icon btn-sm text-danger" disabled={!row.canrevoke}
                title={text('revoke_of', row.label)} aria-label={text('revoke_of', row.label)} onClick={onRevoke}>
                <Icon name="t/delete" strings={strings} />
            </button>
        </td>
    </tr>;
}

/**
 * The table of the list: the check of the page, the sortable headers and the rows.
 *
 * @param props The list, its rows, which can be chosen, which columns there are, the actions and the texts.
 * @returns The table.
 */
function Table({list, rows, selectable, pagefull, canbulk, showuser, onSort, onEdit, onRevoke, text, strings}: {
    list: ReturnType<typeof useList<DelegationRow>>;
    rows: DelegationRow[];
    selectable: number[];
    pagefull: boolean;
    canbulk: boolean;
    showuser: boolean;
    onSort: (column: string, direction: string) => void;
    onEdit: (ids: number[], trigger: HTMLElement) => void;
    onRevoke: (ids: number[]) => void;
    text: Text;
    strings: Strings;
}) {
    return <div className="table-responsive" data-region="tablewrap" aria-busy={list.busy || undefined}>
        <table className="table">
            <caption className="visually-hidden">{text('list_caption_delegations')}</caption>
            <thead>
                <tr>
                    {canbulk && <th scope="col" className="table-select">
                        <input type="checkbox" className="form-check-input" aria-label={text('select_page')}
                            data-region="selectpage"
                            checked={selectable.length > 0 && (list.all || pagefull)}
                            disabled={selectable.length === 0}
                            ref={(input) => {
                                if (input) {
                                    const some = selectable.some((id) => list.selected.has(id));
                                    input.indeterminate = !list.all && some && !pagefull;
                                }
                            }}
                            onChange={(event) => list.togglePage(selectable, event.target.checked)} />
                    </th>}
                    <SortHeader column="account" label={text('delegateduser')} sort={String(list.state.sort)}
                        direction={String(list.state.direction)} onSort={onSort} />
                    {showuser && <SortHeader column="user" label={text('authoriseduser')} sort={String(list.state.sort)}
                        direction={String(list.state.direction)} onSort={onSort} />}
                    <th scope="col">{text('delegation_status')}</th>
                    <SortHeader column="start" label={text('delegation_period')} sort={String(list.state.sort)}
                        direction={String(list.state.direction)} onSort={onSort} />
                    <th scope="col">{text('last_access')}</th>
                    <th scope="col">{text('session_permissions')}</th>
                    <th scope="col" className="table-actions">
                        <span className="visually-hidden">{text('core:actions')}</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                {list.skeleton || !list.page
                    ? <SkeletonRows columns={6 + (showuser ? 1 : 0) + (canbulk ? 1 : 0)} />
                    : rows.map((row) => <Row key={row.id} row={row} canbulk={canbulk} showuser={showuser}
                        checked={list.all || list.selected.has(row.id)} text={text} strings={strings}
                        onToggle={(checked) => list.toggle(row.id, checked)}
                        onEdit={(trigger) => onEdit([row.id], trigger)}
                        onRevoke={() => onRevoke([row.id])} />)}
            </tbody>
        </table>
    </div>;
}

/**
 * The actions of the floating bar: the same as the ones of a row, with their names, the one that removes last.
 *
 * @param props What may be done, what to do and the texts.
 * @returns The actions.
 */
function BulkActions({canupdate, canpermissions, canrevoke, onEdit, onPermissions, onRevoke, text, strings}: {
    canupdate: boolean;
    canpermissions: boolean;
    canrevoke: boolean;
    onEdit: (trigger: HTMLElement) => void;
    onPermissions: () => void;
    onRevoke: () => void;
    text: Text;
    strings: Strings;
}) {
    return <>
        {canupdate && <button type="button" className="btn btn-secondary btn-tonal btn-sm"
            onClick={(event) => onEdit(event.currentTarget)}>
            <Icon name="t/edit" strings={strings} /> {text('edit_period')}
        </button>}
        {canpermissions && <button type="button" className="btn btn-secondary btn-tonal btn-sm" onClick={onPermissions}>
            <Icon name="i/permissions" strings={strings} /> {text('session_permissions')}
        </button>}
        {canrevoke && <button type="button" className="btn btn-danger btn-tonal btn-sm" onClick={onRevoke}>
            <Icon name="t/delete" strings={strings} /> {text('revoke')}
        </button>}
    </>;
}

/**
 * The list.
 *
 * @param props What the list may do and the texts of the page.
 * @returns The list.
 */
export default function DelegationList(props: DelegationListProps) {
    const {strings, realuserid, canbulk, canupdate, canrevoke, canpermissions} = props;
    const text = useText(strings);
    const list = useList<DelegationRow>(
        {search: '', status: props.status, sort: 'account', direction: 'asc', page: 0, perpage: DEFAULT_PAGE_SIZE},
        (state) => getDelegationList(
            {realuserid, status: String(state.status), search: String(state.search)},
            {
                sort: String(state.sort),
                direction: String(state.direction),
                page: Number(state.page),
                perpage: Number(state.perpage),
            },
        ),
        props.initial,
    );
    const {state, page} = list;
    const [search, setSearch] = useState(String(state.search));
    const typed = useDebounced(search.trim(), 300);
    const [revoking, setRevoking] = useState<number[] | null>(null);
    const [bulk, setBulk] = useState(false);

    useEffect(() => {
        list.update({search: typed});
    }, [typed]);
    useEffect(() => {
        // A delegation created from the header of the page appears at once.
        const created = (): void => list.refresh();
        document.addEventListener('local_delegateaccount:created', created);
        return () => document.removeEventListener('local_delegateaccount:created', created);
    }, []);

    const filters: Filters = {realuserid, status: String(state.status), search: String(state.search)};
    const rows = page?.rows ?? [];
    const total = page?.total ?? 0;
    const selectable = rows.filter((row) => row.selectable).map((row) => row.id);
    const count = list.all ? total : list.selected.size;
    const pagefull = selectable.length > 0 && selectable.every((id) => list.selected.has(id));
    const showuser = !realuserid;

    /**
     * The delegations an action on the selection reaches, within the limit of records per bulk action.
     *
     * @returns Their identifiers.
     */
    const resolve = async(): Promise<number[]> => {
        if (!list.all) {
            return Array.from(list.selected);
        }
        const result = await getDelegationIds(filters);
        if (result.error) {
            throw new Error(result.error);
        }
        return result.ids;
    };

    const afterChange = (): void => {
        list.clear();
        list.refresh();
    };

    const edit = async(ids: number[] | null, trigger: HTMLElement): Promise<void> => {
        try {
            const delegationids = ids ?? await resolve();
            await openForm(
                'local_delegateaccount\\form\\bulk_edit_dynamic_form',
                {realuserid, delegationids: delegationids.join(',')},
                text(delegationids.length === 1 ? 'edit_period' : 'edit_selected_delegations'),
                trigger,
                (result) => {
                    afterChange();
                    if (result.message) {
                        void toast(result.message, result.type ?? 'success');
                    }
                },
            );
        } catch (error) {
            await toast(describeError(error), 'danger');
        }
    };

    const revoke = async(ids: number[] | null): Promise<void> => {
        try {
            setRevoking(ids ?? await resolve());
        } catch (error) {
            await toast(describeError(error), 'danger');
        }
    };

    const sort = (column: string, direction: string): void => list.update({sort: column, direction, page: 0});
    const filtered = Boolean(state.search) || Boolean(state.status);
    const empty = <Empty filtered={filtered} icon="i/users" title={text('empty_delegations_title')}
        message={text('empty_delegations_text')} text={text} strings={strings}
        onClear={() => {
            setSearch('');
            list.update({search: '', status: ''});
        }} />;
    const one = revoking?.length === 1;

    return <div className="d-grid gap-3" style={count ? {paddingBottom: '5.5rem'} : undefined}>
        <Chips label={text('filter_by_status')} value={String(state.status)} counts={page?.counts}
            chips={STATUSES.map((key) => ({key, label: text(key ? `delegation_status_${key}` : 'perm_filter_all')}))}
            onChange={(status) => list.update({status})} />
        <div className="card">
            <div className="table-toolbar">
                <div className="table-toolbar-start">
                    <SearchBox strings={strings} value={search} onChange={setSearch}
                        label={text(realuserid ? 'search_accounts' : 'search_delegations')} />
                </div>
            </div>
            <ListBody error={list.error} onRetry={list.refresh} text={text} empty={empty}
                isEmpty={Boolean(page && page.total === 0 && !list.skeleton)}>
                <Table list={list} rows={rows} selectable={selectable} pagefull={pagefull} canbulk={canbulk}
                    showuser={showuser} onSort={sort} onEdit={(ids, trigger) => void edit(ids, trigger)}
                    onRevoke={(ids) => void revoke(ids)} text={text} strings={strings} />
            </ListBody>
            {total > 0 && !list.error && <Pager page={Number(state.page)} perpage={Number(state.perpage)}
                total={total} onChange={list.update} text={text} strings={strings} />}
        </div>
        {canbulk && <FloatBar count={count} total={total} label={text('bulk_region')} text={text}
            offerAll={!list.all && pagefull && total > selectable.length}
            onAll={() => list.setAll(true)} onClear={list.clear}>
            <BulkActions canupdate={canupdate} canpermissions={canpermissions} canrevoke={canrevoke} text={text}
                strings={strings} onEdit={(trigger) => void edit(null, trigger)} onPermissions={() => setBulk(true)}
                onRevoke={() => void revoke(null)} />
        </FloatBar>}
        {revoking && <ConfirmDialog text={text} strings={strings} onClose={() => setRevoking(null)}
            title={one ? text('revoke_confirm_title_one') : text('revoke_confirm_title', revoking.length)}
            message={text('revoke_confirm_text')}
            button={one ? text('revoke_confirm_button_one') : text('revoke_confirm_button', revoking.length)}
            onConfirm={async() => {
                const result = await revokeDelegations(revoking);
                afterChange();
                await toast(text('revoked_count', result.revokedcount));
            }} />}
        {bulk && <BulkPermissions strings={strings} count={count}
            selection={{delegationids: list.all ? [] : Array.from(list.selected), all: list.all, filters}}
            onClose={() => setBulk(false)} onApplied={afterChange} />}
    </div>;
}
