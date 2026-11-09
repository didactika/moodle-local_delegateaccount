/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The permission editor: every capability of the site in one flat, paged list with Not set, Allow, Prevent and Prohibit,
 * found by typing or narrowed by component and by chips, never folded away. Changes are kept until they are saved from the
 * floating bar, which saves them through a web service and refreshes the list in place, for the site as for one delegation.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_delegateaccount/PermissionEditor
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useMemo, useRef, useState} from 'react';
import type {ReactElement} from 'react';
import Pending from '@moodle/lms/core/pending';
import {Chips, Empty, ListBody, Pager, SearchBox, Segmented, SelectMenu} from './components/ui';
import {CopyFromDialog} from './BulkPermissions';
import type {Source} from './BulkPermissions';
import {useDebounced} from './hooks/useList';
import {useText} from './engine/text';
import {describeError, toast} from './engine/moodle';
import {ALLOW, INHERIT, PERMISSIONS, matches, toMap, words} from './engine/capabilities';
import {getPermissions, savePermissions} from './services/api';
import type {Capability, PermissionEditorProps, Text} from './types';


/** Chips of the editor, by the filter each one applies. */
const CHIPS = [
    {key: '', label: 'perm_filter_all'},
    {key: 'overridden', label: 'perm_filter_overridden'},
    {key: '1', label: 'permissionallow'},
    {key: '-1', label: 'perm_prevent'},
    {key: '-1000', label: 'permissionprohibit'},
    {key: 'risky', label: 'perm_filter_risky'},
];

/**
 * The editor.
 *
 * @param props The delegation, or the site, and the texts of the page.
 * @returns The editor.
 */
export default function PermissionEditor({strings, delegationid, site}: PermissionEditorProps) {
    const text = useText(strings);
    const root = useRef<HTMLDivElement>(null);
    const [capabilities, setCapabilities] = useState<Capability[] | null>(null);
    const [saved, setSaved] = useState<Map<string, number>>(new Map());
    const [current, setCurrent] = useState<Map<string, number>>(new Map());
    const [siteoverrides, setSiteoverrides] = useState<Map<string, number>>(new Map());
    const [canedit, setCanedit] = useState(false);
    const [error, setError] = useState<unknown>(null);
    const [filter, setFilter] = useState('');
    const [component, setComponent] = useState('');
    const [search, setSearch] = useState('');
    const typed = useDebounced(search, 200);
    const [page, setPage] = useState(0);
    const [perpage, setPerpage] = useState(20);
    const [saving, setSaving] = useState(false);
    const [copying, setCopying] = useState(false);
    const [notice, setNotice] = useState('');
    const [reload, setReload] = useState(0);

    useEffect(() => {
        const pending = new Pending('local_delegateaccount/permission_editor:load');
        getPermissions(delegationid).then((data) => {
            setCapabilities(data.capabilities);
            setSaved(toMap(data.overrides));
            setCurrent(toMap(data.overrides));
            setSiteoverrides(toMap(data.site));
            setCanedit(data.canedit);
            setError(null);
            return data;
        }).catch(setError).finally(() => pending.resolve());
    }, [delegationid, reload]);

    // In the settings page the editor saves by itself, so the button of the page that saves the other tabs is hidden while
    // its tab is open.
    useEffect(() => {
        const pane = root.current?.closest('.tab-pane');
        const submit = root.current?.closest('form')?.querySelector<HTMLElement>('[type="submit"]');
        if (!pane || !submit) {
            return undefined;
        }
        const row = submit.closest<HTMLElement>('.row, div') ?? submit;
        const sync = (): void => {
            row.hidden = pane.classList.contains('active');
        };
        const observer = new MutationObserver(sync);
        observer.observe(pane, {attributes: true, attributeFilter: ['class']});
        sync();
        return () => observer.disconnect();
    }, []);

    const names = useMemo(() => Object.fromEntries(PERMISSIONS.map(({value, key}) => [value, text(key)])), [text]);
    const components = useMemo(() => Array.from(new Set((capabilities ?? []).map((capability) => capability.componentlabel))),
        [capabilities]);
    const visible = (chip: string): Capability[] => (capabilities ?? []).filter((capability) => {
        if (component && capability.componentlabel !== component) {
            return false;
        }
        if (!matches(capability, words(typed))) {
            return false;
        }
        const permission = current.get(capability.name) ?? INHERIT;
        if (chip === 'overridden') {
            return permission !== INHERIT;
        }
        if (chip === 'risky') {
            return capability.risks.length > 0;
        }
        return chip === '' || permission === Number(chip);
    });
    const shown = visible(filter);
    const counts = Object.fromEntries(CHIPS.map(({key}) => [key || 'all', visible(key).length]));
    const lastpage = Math.max(0, Math.ceil(shown.length / perpage) - 1);
    const at = Math.min(page, lastpage);
    const rows = shown.slice(at * perpage, (at + 1) * perpage);
    const changes = Array.from(new Set([...saved.keys(), ...current.keys()]))
        .filter((name) => (saved.get(name) ?? INHERIT) !== (current.get(name) ?? INHERIT))
        .map((capability) => ({capability, permission: current.get(capability) ?? INHERIT}));

    const reset = (apply: () => void): void => {
        apply();
        setPage(0);
    };

    const save = async(): Promise<void> => {
        setSaving(true);
        try {
            const result = await savePermissions(delegationid, changes);
            setSaved(toMap(result.overrides));
            setCurrent(toMap(result.overrides));
            setNotice('');
            await toast(text('perm_saved'));
            const detail = result.overrides.length;
            document.dispatchEvent(new CustomEvent('local_delegateaccount:permissionssaved', {detail}));
        } catch (failure) {
            await toast(describeError(failure), 'danger');
        } finally {
            setSaving(false);
        }
    };

    const copy = async(source: Source): Promise<void> => {
        const data = await getPermissions(source.id);
        setCurrent(toMap(data.overrides));
        setPage(0);
        setNotice(text('copy_loaded', source.label));
    };


    const legend = [
        ['perm_notset', 'perm_notset_desc'],
        ['permissionallow', 'perm_allow_desc'],
        ['perm_prevent', 'perm_prevent_desc'],
        ['permissionprohibit', 'perm_prohibit_desc'],
    ];
    const componentoptions = [
        {value: '', label: text('perm_component_all')},
        ...components.map((name) => ({value: name, label: name})),
    ];
    const items: ReactElement[] = [];
    rows.forEach((capability, index) => {
        if (index === 0 || rows[index - 1].componentlabel !== capability.componentlabel) {
            items.push(<li key={`group-${capability.componentlabel}`} className="list-group-item list-group-item-light">
                {capability.componentlabel}
            </li>);
        }
        const permission = current.get(capability.name) ?? INHERIT;
        items.push(<CapabilityRow key={capability.name} capability={capability} permission={permission}
            changed={permission !== (saved.get(capability.name) ?? INHERIT)} canedit={canedit} names={names}
            sitepermission={siteoverrides.get(capability.name)} text={text}
            onChange={(value) => setCurrent((before) => new Map(before).set(capability.name, value))} />);
    });
    const skeleton = <ul className="list-group list-group-flush" aria-busy="true">
        {[0, 1, 2].map((key) => <li key={key} className="list-group-item"><span className="dd-skeleton w-50" /></li>)}
    </ul>;
    const empty = <Empty filtered icon="a/search" title="" message="" text={text} strings={strings}
        onClear={() => reset(() => {
            setFilter('');
            setComponent('');
            setSearch('');
        })} />;

    return <div ref={root} className="lda pb-5 ignoredirty">
        <p className="dd-page-subtitle mb-0">{text(site ? 'perm_intro_site' : 'perm_intro_delegation')}</p>
        <ul className="row list-unstyled small text-muted mb-0" aria-label={text('perm_legend')}>
            {legend.map(([name, description]) => <li key={name} className="col-sm-6 col-lg-3 mb-2">
                <b className="d-block text-body">{text(name)}</b>{text(description)}
            </li>)}
        </ul>
        {capabilities && !canedit && <div className="alert alert-info mb-0">{text('perm_readonly')}</div>}
        {notice && <div className="alert alert-info mb-0" role="status">{notice}</div>}
        <div className="card">
            <div className="table-toolbar">
                <div className="table-toolbar-start">
                    <SearchBox strings={strings} value={search} label={text('perm_search')}
                        placeholder={text('perm_search_placeholder')} onChange={(value) => reset(() => setSearch(value))} />
                    <SelectMenu label={text('perm_component_all')} value={component} options={componentoptions}
                        className={component ? 'border-primary' : ''} onChange={(value) => reset(() => setComponent(value))} />
                </div>
                {!site && canedit && <div className="table-toolbar-end">
                    <button type="button" className="btn btn-outline-secondary btn-sm" onClick={() => setCopying(true)}>
                        {text('copy_from_delegation')}
                    </button>
                </div>}
            </div>
            <div className="table-toolbar">
                <Chips label={text('perm_search')} value={filter} counts={capabilities ? counts : undefined}
                    chips={CHIPS.map(({key, label}) => ({key, label: text(label)}))}
                    onChange={(chip) => reset(() => setFilter(chip))} />
            </div>
            <ListBody error={error} onRetry={() => setReload((count) => count + 1)} text={text} empty={empty}
                isEmpty={Boolean(capabilities) && shown.length === 0}>
                {capabilities ? <ul className="list-group list-group-flush">{items}</ul> : skeleton}
            </ListBody>
            {capabilities && shown.length > 0 && <Pager page={at} perpage={perpage} total={shown.length} text={text}
                strings={strings} onChange={(next) => {
                    if (next.perpage !== undefined) {
                        setPerpage(next.perpage);
                    }
                    setPage(next.page ?? 0);
                }} />}
        </div>
        {changes.length > 0 && <div className="dd-savebar" role="region" aria-live="polite" data-region="savebar"
            aria-label={text('session_permissions')}>
            <span className="dd-savebar-text">{text('perm_unsaved', changes.length)}</span>
            <span className="dd-savebar-actions">
                <button type="button" className="btn btn-outline-secondary" onClick={() => {
                    setCurrent(new Map(saved));
                    setNotice('');
                }}>{text('perm_discard')}</button>
                <button type="button" className="btn btn-primary" disabled={saving} onClick={() => void save()}>
                    {saving ? text('perm_saving') : text('core:savechanges')}
                </button>
            </span>
        </div>}
        {copying && <CopyFromDialog exclude={delegationid} strings={strings} onClose={() => setCopying(false)}
            onChoose={(source) => void copy(source)} />}
    </div>;
}

/**
 * One capability: its name, its technical name and risks, the permission of the site when the delegation sets none, and
 * the permission as a segmented control.
 *
 * @param props The capability, its permission, whether it changed, what may be chosen and the texts.
 * @returns The row.
 */
function CapabilityRow({capability, permission, changed, canedit, names, sitepermission, onChange, text}: {
    capability: Capability;
    permission: number;
    changed: boolean;
    canedit: boolean;
    names: Record<number, string>;
    sitepermission?: number;
    onChange: (value: number) => void;
    text: Text;
}) {
    const options = PERMISSIONS.map(({value, tone}) => ({
        tone,
        value,
        label: names[value],
        // Allowing gives the capability to the session, so only who has it may allow it.
        disabled: !canedit || (value === ALLOW && !capability.canallow && permission !== ALLOW),
        title: value === ALLOW && !capability.canallow ? text('cannotallow_hint') : undefined,
    }));
    const site = sitepermission !== undefined && permission === INHERIT;
    return <li className={`list-group-item${changed ? ' list-group-item-primary' : ''}`}>
        <span className="d-flex flex-column">
            <span className="font-weight-bold fw-bold">{capability.label}</span>
            <span className="small text-muted">
                <span className="me-2">{capability.name}</span>
                {capability.risks.map((risk) => <span key={risk} className="badge badge-outline text-bg-warning">
                    {text(`core:risk${risk}short`)}
                </span>)}
                {site && <span className="badge badge-soft text-bg-secondary">
                    {text('perm_site_value', names[sitepermission as number])}
                </span>}
            </span>
        </span>
        <Segmented label={text('perm_permission_for', capability.label)} value={permission} onChange={onChange}
            options={options} />
    </li>;
}
