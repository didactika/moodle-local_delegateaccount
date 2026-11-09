/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The session permissions of many delegations at once: add, change or remove overrides on all of them, or copy the
 * overrides of one delegation to all of them. The server writes them in bounded batches, so the selection can be every
 * delegation that matches a list. The dialog keeps one size and shows its lists from the start.
 *
 * @module     local_delegateaccount/BulkPermissions
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useMemo, useState} from 'react';
import {Dialog, Icon, SearchBox, Segmented} from './components/ui';
import {useDebounced} from './hooks/useList';
import {useText} from './engine/text';
import {describeError, toast} from './engine/moodle';
import {ALLOW, INHERIT, PREVENT, PROHIBIT, matches, words} from './engine/capabilities';
import {getDelegationList, getPermissions, updatePermissions} from './services/api';
import type {Capability, DelegationRow, Selection, Strings} from './types';

/** How many options a list shows at once; typing narrows it. */
const LIMIT = 60;

/** A delegation that can be copied from. */
export interface Source {
    id: number;
    label: string;
}

/**
 * The list of delegations to copy from: it shows delegations from the start and searches them as one types.
 *
 * @param props The one chosen, what to do when one is chosen, the delegation that cannot be chosen and the texts.
 * @returns The list.
 */
export function SourcePicker({chosen, onChoose, exclude, strings}: {
    chosen: number;
    onChoose: (source: Source) => void;
    exclude: number;
    strings: Strings;
}) {
    const text = useText(strings);
    const [search, setSearch] = useState('');
    const typed = useDebounced(search.trim(), 300);
    const [rows, setRows] = useState<DelegationRow[]>([]);
    const [error, setError] = useState('');

    useEffect(() => {
        let current = true;
        getDelegationList({realuserid: 0, status: '', search: typed}, {sort: 'account', page: 0, perpage: 30})
            .then((page) => {
                if (current) {
                    setRows(page.rows.filter((row) => row.id !== exclude));
                    setError('');
                }
                return page;
            })
            .catch((failure: unknown) => current && setError(describeError(failure)));
        return () => {
            current = false;
        };
    }, [typed, exclude]);

    return <div>
        <label className="font-weight-bold fw-bold d-block mb-2">{text('bulk_copy_source')}</label>
        <div className="mb-2">
            <SearchBox wide strings={strings} value={search} onChange={setSearch} label={text('search_delegations')} />
        </div>
        <ul className="list-group" role="listbox" aria-label={text('bulk_copy_source')}>
            {!rows.length && <li className="list-group-item text-muted text-center">{error || text('empty_search_title')}</li>}
            {rows.map((row) => <li key={row.id} role="presentation">
                <button type="button" role="option" aria-selected={row.id === chosen}
                    onClick={() => onChoose({id: row.id, label: row.label})}>
                    <span className="d-flex flex-column">
                        <span>{row.label}</span>
                        <small>{row.statuslabel} · {row.overrideslabel}</small>
                    </span>
                </button>
            </li>)}
        </ul>
    </div>;
}

/**
 * Lets one delegation be chosen, to copy its overrides into the editor.
 *
 * @param props The delegation that cannot be chosen, what to do and the texts.
 * @returns The dialog.
 */
export function CopyFromDialog({exclude, onChoose, onClose, strings}: {
    exclude: number;
    onChoose: (source: Source) => void;
    onClose: () => void;
    strings: Strings;
}) {
    const text = useText(strings);
    const [chosen, setChosen] = useState<Source | null>(null);
    return <Dialog title={text('copy_from_delegation')} onClose={onClose} footer={<>
        <button type="button" className="btn btn-secondary" onClick={onClose}>{text('core:cancel')}</button>
        <button type="button" className="btn btn-primary" disabled={!chosen} onClick={() => {
            if (chosen) {
                onChoose(chosen);
            }
            onClose();
        }}>{text('bulk_mode_copy')}</button>
    </>}>
        <SourcePicker chosen={chosen?.id ?? 0} onChoose={setChosen} exclude={exclude} strings={strings} />
    </Dialog>;
}

/**
 * The dialog that changes the session permissions of many delegations.
 *
 * @param props The selection, how many delegations it has, what to do after and the texts.
 * @returns The dialog.
 */
export function BulkPermissions({selection, count, onClose, onApplied, strings}: {
    selection: Selection;
    count: number;
    onClose: () => void;
    onApplied: () => void;
    strings: Strings;
}) {
    const text = useText(strings);
    const [mode, setMode] = useState<'change' | 'copy'>('change');
    const [capabilities, setCapabilities] = useState<Capability[]>([]);
    const [changes, setChanges] = useState<Map<string, number>>(new Map());
    const [search, setSearch] = useState('');
    const typed = useDebounced(search, 150);
    const [source, setSource] = useState<Source | null>(null);
    const [replace, setReplace] = useState(false);
    const [error, setError] = useState('');
    const [working, setWorking] = useState(false);

    useEffect(() => {
        getPermissions(0).then((data) => {
            setCapabilities(data.capabilities);
            return data;
        }).catch((failure: unknown) => setError(describeError(failure)));
    }, []);

    const byname = useMemo(() => new Map(capabilities.map((capability) => [capability.name, capability])), [capabilities]);
    const found = useMemo(
        () => capabilities.filter((capability) => matches(capability, words(typed))).slice(0, LIMIT),
        [capabilities, typed],
    );

    const toggle = (name: string): void => setChanges((before) => {
        const next = new Map(before);
        if (next.has(name)) {
            next.delete(name);
        } else {
            next.set(name, PROHIBIT);
        }
        return next;
    });

    const apply = async(): Promise<void> => {
        setWorking(true);
        setError('');
        try {
            const result = mode === 'copy' && source
                ? await updatePermissions({selection, copyfrom: source.id, replace})
                : await updatePermissions({
                    selection,
                    changes: Array.from(changes, ([capability, permission]) => ({capability, permission})),
                });
            onClose();
            await toast(text('bulk_applied', result.count));
            onApplied();
        } catch (failure) {
            setError(describeError(failure));
            setWorking(false);
        }
    };

    const ready = mode === 'change' ? changes.size > 0 : source !== null;
    return <Dialog title={text('bulk_permissions_title', count)} onClose={onClose} footer={<>
        <button type="button" className="btn btn-secondary" onClick={onClose}>{text('core:cancel')}</button>
        <button type="button" className="btn btn-primary" disabled={!ready || working} onClick={() => void apply()}>
            {text('apply_to_count', count)}
        </button>
    </>}>
        <div data-region="bulk">
            <Segmented label={text('bulk_mode')} value={mode} onChange={setMode} options={[
                {value: 'change', label: text('bulk_mode_change')},
                {value: 'copy', label: text('bulk_mode_copy')},
            ]} />
            {mode === 'change' ? <div data-region="change">
                <div>
                    <label className="font-weight-bold fw-bold d-block mb-2">{text('bulk_add_capability')}</label>
                    <div className="mb-2">
                        <SearchBox wide strings={strings} value={search} onChange={setSearch} label={text('bulk_add_capability')}
                            placeholder={text('perm_search_placeholder')} />
                    </div>
                    <ul className="list-group" role="listbox" aria-multiselectable="true" data-region="capresults"
                        aria-label={text('bulk_add_capability')}>
                        {!found.length && <li className="list-group-item text-muted text-center">
                            {text('bulk_search_noresults')}
                        </li>}
                        {found.map((capability) => <li key={capability.name} role="presentation">
                            <button type="button" role="option" aria-selected={changes.has(capability.name)}
                                onClick={() => toggle(capability.name)}>
                                <span className="d-flex flex-column">
                                    <span>{capability.label}</span>
                                    <small>{capability.name} · {capability.componentlabel}</small>
                                </span>
                                <span className="small font-weight-bold fw-bold text-primary">
                                    {text(changes.has(capability.name) ? 'bulk_added' : 'bulk_add')}
                                </span>
                            </button>
                        </li>)}
                    </ul>
                </div>
                <div>
                    <p className="font-weight-bold fw-bold d-block mb-2">
                        {text('bulk_changes')} {changes.size > 0 && <span className="text-muted fw-normal">({changes.size})</span>}
                    </p>
                    {changes.size === 0 ? <p className="text-muted mb-0">{text('bulk_changes_empty')}</p>
                        : <ul className="list-group">
                            {Array.from(changes, ([name, permission]) => {
                                const capability = byname.get(name);
                                return <li key={name} className="list-group-item">
                                    <span className="d-flex flex-column">
                                        <span className="font-weight-bold fw-bold">{capability?.label ?? name}</span>
                                        <span className="small text-muted">{name}</span>
                                    </span>
                                    <Segmented label={capability?.label ?? name} value={permission}
                                        onChange={(value) => setChanges((before) => new Map(before).set(name, value))}
                                        options={[
                                            {value: ALLOW, tone: 'allow', label: text('permissionallow'),
                                                disabled: !capability?.canallow,
                                                title: capability?.canallow ? undefined : text('cannotallow_hint')},
                                            {value: PREVENT, tone: 'prevent', label: text('perm_prevent')},
                                            {value: PROHIBIT, tone: 'prohibit', label: text('permissionprohibit')},
                                            {value: INHERIT, label: text('bulk_remove')},
                                        ]} />
                                    <button type="button" className="btn btn-link btn-icon btn-sm" title={text('bulk_remove')}
                                        aria-label={`${text('bulk_remove')}: ${capability?.label ?? name}`}
                                        onClick={() => toggle(name)}>
                                        <Icon name="e/cancel" strings={strings} />
                                    </button>
                                </li>;
                            })}
                        </ul>}
                </div>
            </div> : <div data-region="copy">
                <SourcePicker chosen={source?.id ?? 0} onChoose={setSource} exclude={0} strings={strings} />
                <div>
                    <p className="font-weight-bold fw-bold d-block mb-2">{text('bulk_copy_how')}</p>
                    <ul className="row list-unstyled mb-0">
                        {[false, true].map((value) => <li key={String(value)} className="col-sm-6 mb-2">
                            <label className="card card-body flex-row h-100 mb-0">
                                <input type="radio" name="replace" checked={replace === value} onChange={() => setReplace(value)} />
                                <span className="ms-3">
                                    <b className="d-block">{text(value ? 'bulk_copy_replace' : 'bulk_copy_merge')}</b>
                                    <small className="text-muted">
                                        {text(value ? 'bulk_copy_replace_desc' : 'bulk_copy_merge_desc')}
                                    </small>
                                </span>
                            </label>
                        </li>)}
                    </ul>
                </div>
            </div>}
            {error && <div className="alert alert-danger mt-3 mb-0" role="alert">{error}</div>}
        </div>
    </Dialog>;
}
