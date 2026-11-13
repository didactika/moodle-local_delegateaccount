/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The pieces every screen of the plugin is made of, drawn with the classes of Bootstrap, Moodle and the Didactika design
 * system: a person, the chips of a list, the search, the pager, the states of a list, the menu of a select, a segmented
 * control, the floating bar of a selection and a dialog.
 *
 * @module     local_delegateaccount/components/ui
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useId, useLayoutEffect, useRef, useState} from 'react';
import type {KeyboardEvent as ReactKeyboardEvent, ReactNode} from 'react';
import {createPortal} from 'react-dom';
import type {Counts, Person as PersonData, Strings, Text} from '../types';
import {fitPageSizes, pageRange} from '../engine/paging';

/**
 * A Moodle icon, drawn by the server with the icon map of the theme.
 *
 * @param props The icon and the texts of the page, where the server put the icon.
 * @returns The icon.
 */
export function Icon({name, strings}: {name: string; strings: Strings}) {
    return <span className="d-inline-flex" aria-hidden="true" dangerouslySetInnerHTML={{__html: strings[`pix:${name}`] ?? ''}} />;
}

/**
 * The picture of a person or, when there is none, their initials, as Moodle draws them.
 *
 * @param props The person and the size.
 * @returns The avatar.
 */
export function Avatar({user, size = 35}: {user: PersonData; size?: number}) {
    return user.hasimage
        ? <img src={user.imageurl} className="userpicture" width={size} height={size} alt="" />
        : <span className={`userinitials size-${size}`} aria-hidden="true">{user.initials}</span>;
}

/**
 * A person in a row: picture or initials, the name as a link and the email under it.
 *
 * @param props The person.
 * @returns The person.
 */
export function Person({user}: {user: PersonData}) {
    return <span className="d-flex align-items-center">
        <Avatar user={user} />
        <span className="d-flex flex-column">
            {user.url ? <a href={user.url} title={user.linklabel || undefined}>{user.fullname}</a> : <b>{user.fullname}</b>}
            {user.email && <span className="small text-muted text-truncate">{user.email}</span>}
        </span>
    </span>;
}

/**
 * The filter chips of a list, with the rows each one leaves.
 *
 * @param props The chips, the one pressed, the counts and what to do when one is pressed.
 * @returns The chips.
 */
export function Chips({chips, value, counts, label, onChange}: {
    chips: Array<{key: string; label: string}>;
    value: string;
    counts?: Counts;
    label: string;
    onChange: (key: string) => void;
}) {
    return <div className="dd-chips" role="group" aria-label={label}>
        {chips.map((chip) => <button key={chip.key} type="button" className="dd-chip" aria-pressed={chip.key === value}
            onClick={() => onChange(chip.key)}>
            {chip.label} <span className="dd-chip-count">{counts?.[chip.key || 'all'] ?? ' '}</span>
        </button>)}
    </div>;
}

/**
 * The search of a list: the magnifier inside the field.
 *
 * @param props The text, what to do as it changes, the label and the texts of the page.
 * @returns The search.
 */
export function SearchBox({value, onChange, label, placeholder, strings, wide = false}: {
    value: string;
    onChange: (value: string) => void;
    label: string;
    placeholder?: string;
    strings: Strings;
    wide?: boolean;
}) {
    const id = useId();
    return <div className={`dd-searchbox${wide ? ' mw-100' : ''}`} role="search">
        <Icon name="a/search" strings={strings} />
        <label className="visually-hidden" htmlFor={id}>{label}</label>
        <input className="form-control" type="search" id={id} value={value} placeholder={placeholder ?? label} autoComplete="off"
            onChange={(event) => onChange(event.target.value)}
            onKeyDown={(event) => event.key === 'Enter' && event.preventDefault()} />
    </div>;
}

/** An option of a select. */
export interface Option {
    value: string;
    label: string;
}

/**
 * A select that opens the menu of the design system instead of the list the browser draws, below the select or above it,
 * wherever it fits. It keeps a native select underneath, so the value is the one of a form field.
 *
 * Keyboard: Enter, Space and the arrows open it; the arrows, Home and End move, Enter chooses, a letter jumps to the next
 * option that starts with it, Escape and Tab close it.
 *
 * A select that filters a list (clearable) does not offer its base state, the option with the empty value, as one more
 * choice: the select says it while nothing is chosen, and once something is, its arrow becomes a cross that goes back to it.
 *
 * @param props The options, the value, what to do when it changes, the label, the classes and whether it filters.
 * @returns The select.
 */
export function SelectMenu({options: all, value, onChange, label, className = '', small = false, clearable = false, text,
    strings}: {
    options: Option[];
    value: string;
    onChange: (value: string) => void;
    label: string;
    className?: string;
    small?: boolean;
    clearable?: boolean;
    text?: Text;
    strings?: Strings;
}) {
    const options = clearable ? all.filter((option) => option.value !== '') : all;
    const set = clearable && value !== '';
    const select = useRef<HTMLSelectElement>(null);
    const menu = useRef<HTMLDivElement>(null);
    const [open, setOpen] = useState(false);
    const [active, setActive] = useState(0);
    const [place, setPlace] = useState<{top: number; left: number; width: number; height: number} | null>(null);
    const id = useId();

    useLayoutEffect(() => {
        if (!open || !select.current || !menu.current) {
            return;
        }
        const box = select.current.getBoundingClientRect();
        const below = window.innerHeight - box.bottom - 8;
        const above = box.top - 8;
        const height = menu.current.scrollHeight;
        const up = height > below && above > below;
        const max = Math.max(120, Math.min(272, up ? above : below));
        const shown = Math.min(height, max);
        setPlace({
            top: up ? box.top - shown - 2 : box.bottom + 2,
            left: Math.max(8, Math.min(box.left, window.innerWidth - Math.max(box.width, 160) - 8)),
            width: box.width,
            height: max,
        });
        (menu.current.querySelector<HTMLElement>('[aria-selected="true"]') ?? menu.current.querySelector<HTMLElement>('button'))
            ?.focus();
    }, [open]);

    useEffect(() => {
        if (!open) {
            return undefined;
        }
        const close = (event: Event): void => {
            if (!menu.current?.contains(event.target as Node)) {
                setOpen(false);
            }
        };
        window.addEventListener('resize', close);
        window.addEventListener('scroll', close, {capture: true, passive: true});
        document.addEventListener('mousedown', close);
        return () => {
            window.removeEventListener('resize', close);
            window.removeEventListener('scroll', close, {capture: true});
            document.removeEventListener('mousedown', close);
        };
    }, [open]);

    const choose = (index: number): void => {
        setOpen(false);
        select.current?.focus();
        if (options[index] && options[index].value !== value) {
            onChange(options[index].value);
        }
    };

    const focus = (index: number): void => {
        const at = (index + options.length) % options.length;
        setActive(at);
        menu.current?.querySelectorAll<HTMLElement>('button')[at]?.focus();
    };

    const keys = (event: ReactKeyboardEvent): void => {
        const at = Array.from(menu.current?.querySelectorAll('button') ?? []).indexOf(document.activeElement as HTMLButtonElement);
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            focus(at + 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            focus(at - 1);
        } else if (event.key === 'Home') {
            event.preventDefault();
            focus(0);
        } else if (event.key === 'End') {
            event.preventDefault();
            focus(-1);
        } else if (event.key === 'Escape') {
            event.preventDefault();
            event.stopPropagation();
            setOpen(false);
            select.current?.focus();
        } else if (event.key === 'Tab') {
            setOpen(false);
        } else if (event.key.length === 1 && /\S/.test(event.key)) {
            const letter = event.key.toLowerCase();
            const order = [...options.slice(at + 1), ...options.slice(0, at + 1)];
            const found = order.find((option) => option.label.toLowerCase().startsWith(letter));
            if (found) {
                focus(options.indexOf(found));
            }
        }
    };

    const scope = select.current?.closest('.lda, .modal') ?? document.body;
    const position = place
        ? {top: place.top, left: place.left, minWidth: place.width, maxHeight: place.height}
        : {visibility: 'hidden' as const};
    const classes = ['form-select dd-select', small ? 'form-select-sm' : '', set ? 'dd-select-set border-primary' : '', className]
        .filter(Boolean).join(' ');
    const control = <>
        <select ref={select} className={classes} value={value}
            aria-label={label} aria-expanded={open || undefined} aria-controls={open ? id : undefined}
            onChange={(event) => onChange(event.target.value)}
            onMouseDown={(event) => {
                event.preventDefault();
                select.current?.focus();
                setOpen(!open);
            }}
            onKeyDown={(event) => {
                if (['Enter', ' ', 'ArrowDown', 'ArrowUp'].includes(event.key)) {
                    event.preventDefault();
                    setOpen(true);
                }
            }}>
            {all.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}
        </select>
        {open && createPortal(<div ref={menu} id={id} className="dropdown-menu show dd-select-menu" role="listbox" tabIndex={-1}
            onKeyDown={keys} style={position}>
            {options.map((option, index) => <button key={option.value} type="button" role="option"
                className={`dropdown-item${index === active ? ' active' : ''}`} aria-selected={option.value === value}
                onClick={() => choose(index)}>{option.label}</button>)}
        </div>, scope)}
    </>;
    if (!clearable) {
        return control;
    }
    const clear = text ? `${text('core:clear')}: ${label}` : label;
    return <span className="dd-select-filter">
        {control}
        {set && <button type="button" className="dd-select-clear" title={clear} aria-label={clear} onClick={() => {
            onChange('');
            select.current?.focus();
        }}>
            {strings && <Icon name="e/cancel" strings={strings} />}
        </button>}
    </span>;
}

/**
 * The foot of a list: rows per page, which rows are shown, and the arrows.
 *
 * @param props The page, the rows per page, the total, what to do and the text.
 * @returns The pager.
 */
export function Pager({page, perpage, total, onChange, text, strings}: {
    page: number;
    perpage: number;
    total: number;
    onChange: (changes: {page?: number; perpage?: number}) => void;
    text: Text;
    strings: Strings;
}) {
    const {from, to} = pageRange(page, perpage, total);
    // Only the sizes smaller than the list are offered, besides all of it, and the select always shows one of them.
    const {sizes, shown} = fitPageSizes(perpage, total);
    return <div className="table-foot">
        <div className="dd-pager">
            <div className="dd-pager-size">
                <span>{text('rows_per_page')}</span>
                <SelectMenu small label={text('rows_per_page')} value={String(shown)}
                    options={sizes.map((size) => ({value: String(size), label: size ? String(size) : text('core:all')}))}
                    onChange={(size) => onChange({perpage: Number(size), page: 0})} />
            </div>
            <div className="dd-pager-end">
                <span className="dd-pager-range" role="status">{text('pager_range', {from, to, total})}</span>
                <div className="dd-pager-arrows">
                    <button type="button" className="btn btn-outline-secondary btn-sm btn-icon" disabled={page === 0}
                        title={text('previous_page')} aria-label={text('previous_page')} onClick={() => onChange({page: page - 1})}>
                        <Icon name="i/previous" strings={strings} />
                    </button>
                    <button type="button" className="btn btn-outline-secondary btn-sm btn-icon" disabled={to >= total}
                        title={text('next_page')} aria-label={text('next_page')} onClick={() => onChange({page: page + 1})}>
                        <Icon name="i/next" strings={strings} />
                    </button>
                </div>
            </div>
        </div>
    </div>;
}

/**
 * The empty state of a list: the first time, or after a search or a filter with the way to clear them.
 *
 * @param props What is missing and what to do.
 * @returns The empty state.
 */
export function Empty({filtered, icon, title, message, onClear, text, strings}: {
    filtered: boolean;
    icon: string;
    title: string;
    message: string;
    onClear?: () => void;
    text: Text;
    strings: Strings;
}) {
    return <div className="dd-empty" data-region="empty">
        <span className="dd-tile dd-tile-lg"><Icon name={filtered ? 'a/search' : icon} strings={strings} /></span>
        <p className="dd-empty-title">{filtered ? text('empty_search_title') : title}</p>
        <p className="dd-empty-text">{filtered ? text('empty_search_text') : message}</p>
        {filtered && onClear && <button type="button" className="btn btn-outline-secondary" onClick={onClear}>
            {text('clear_filters')}
        </button>}
    </div>;
}

/**
 * The alert of a list that did not load, where its rows were, with the way to try again.
 *
 * @param props What failed and what to do.
 * @returns The alert.
 */
export function ListError({message, onRetry, text}: {message: string; onRetry: () => void; text: Text}) {
    return <div className="alert alert-danger d-flex flex-wrap align-items-center justify-content-between" role="status">
        <span>{text('error_loading', message)}</span>
        <button type="button" className="btn btn-outline-danger btn-sm" onClick={onRetry}>{text('try_again')}</button>
    </div>;
}

/**
 * What a list shows below its toolbar: the alert when it did not load, the empty state when there is nothing, or its rows.
 *
 * @param props What failed, the empty state, whether the list is empty, and the rows.
 * @returns The body of the list.
 */
export function ListBody({error, empty, isEmpty, onRetry, text, children}: {
    error: unknown;
    empty: ReactNode;
    isEmpty: boolean;
    onRetry: () => void;
    text: Text;
    children: ReactNode;
}) {
    if (error) {
        const message = error instanceof Error ? error.message : String((error as {message?: string})?.message ?? error);
        return <ListError message={message} onRetry={onRetry} text={text} />;
    }
    return <>{isEmpty ? empty : children}</>;
}

/**
 * A header that sorts its column, with the arrow that says how.
 *
 * @param props The column, its label, the sorted column and its direction, and what to do on a click.
 * @returns The header.
 */
export function SortHeader({column, label, sort, direction, onSort, numeric = false}: {
    column: string;
    label: string;
    sort: string;
    direction: string;
    onSort: (column: string, direction: string) => void;
    numeric?: boolean;
}) {
    const sorted = sort === column;
    const ariasort = direction === 'desc' ? 'descending' : 'ascending';
    return <th scope="col" className={numeric ? 'text-end' : undefined} aria-sort={sorted ? ariasort : undefined}>
        <button type="button" className="table-sort"
            onClick={() => onSort(column, sorted && direction === 'asc' ? 'desc' : 'asc')}>{label}</button>
    </th>;
}

/**
 * Rows of the right height while a page loads.
 *
 * @param props How many columns and rows.
 * @returns The rows.
 */
export function SkeletonRows({columns, rows = 5}: {columns: number; rows?: number}) {
    return <>{Array.from({length: rows}, (_, row) => <tr key={row} aria-hidden="true">
        {Array.from({length: columns}, (__, cell) => <td key={cell}><span className="dd-skeleton" /></td>)}
    </tr>)}</>;
}

/**
 * A segmented control: one choice among a few, as a radio group the arrow keys move through.
 *
 * @param props The options, the value, what to do when it changes and the label.
 * @returns The control.
 */
export function Segmented<T extends string | number>({options, value, onChange, label}: {
    options: Array<{value: T; label: string; disabled?: boolean; title?: string; tone?: string}>;
    value: T;
    onChange: (value: T) => void;
    label: string;
}) {
    const group = useRef<HTMLSpanElement>(null);
    const keys = (event: ReactKeyboardEvent): void => {
        if (!['ArrowRight', 'ArrowDown', 'ArrowLeft', 'ArrowUp'].includes(event.key)) {
            return;
        }
        event.preventDefault();
        const enabled = options.filter((option) => !option.disabled);
        const at = enabled.findIndex((option) => option.value === value);
        const step = ['ArrowRight', 'ArrowDown'].includes(event.key) ? 1 : -1;
        const next = enabled[(at + step + enabled.length) % enabled.length];
        onChange(next.value);
        window.requestAnimationFrame(() => group.current?.querySelector<HTMLElement>('[aria-checked="true"]')?.focus());
    };
    return <span ref={group} className="dd-segmented" role="radiogroup" aria-label={label} onKeyDown={keys}>
        {options.map((option) => <button key={String(option.value)} type="button" role="radio"
            data-permission={option.value} data-tone={option.tone}
            aria-checked={option.value === value} tabIndex={option.value === value ? 0 : -1} disabled={option.disabled}
            title={option.title} onClick={() => onChange(option.value)}>{option.label}</button>)}
    </span>;
}

/**
 * The floating bar of a selection: how many rows are chosen, the way to choose every row that matches, what to do with them
 * and a button that clears the choice.
 *
 * @param props The count, the offer to choose them all, the actions and the text.
 * @returns The bar, or nothing when nothing is chosen.
 */
export function FloatBar({count, offerAll, total, onAll, onClear, label, children, text}: {
    count: number;
    offerAll: boolean;
    total: number;
    onAll: () => void;
    onClear: () => void;
    label: string;
    children: ReactNode;
    text: Text;
}) {
    if (count === 0) {
        return null;
    }
    return <div className="dd-floatbar" role="region" aria-label={label} data-region="floatbar">
        <span className="dd-floatbar-count" role="status">{text('selected_count', count)}</span>
        {offerAll && <button type="button" className="btn btn-link dd-floatbar-all" onClick={onAll}>
            {text('select_all_matching', total)}
        </button>}
        <div className="dd-floatbar-actions">{children}</div>
        <button type="button" className="dd-floatbar-clear" title={text('clear_selection')} aria-label={text('clear_selection')}
            onClick={onClear} />
    </div>;
}

/**
 * A dialog as Moodle and Bootstrap draw it: the focus enters, stays inside, goes back to where it was on closing, and
 * Escape closes it.
 *
 * @param props The title, the body, the buttons and what to do on closing.
 * @returns The dialog.
 */
export function Dialog({title, children, footer, onClose, size = 'lg', initialFocus}: {
    title: string;
    children: ReactNode;
    footer: ReactNode;
    onClose: () => void;
    size?: 'sm' | 'md' | 'lg';
    initialFocus?: string;
}) {
    const root = useRef<HTMLDivElement>(null);
    const titleid = useId();
    useEffect(() => {
        const previous = document.activeElement as HTMLElement | null;
        document.body.classList.add('modal-open');
        const first = initialFocus ? root.current?.querySelector<HTMLElement>(initialFocus) : null;
        (first ?? root.current?.querySelector<HTMLElement>('input, button:not(.btn-close), [tabindex="0"]'))?.focus();
        return () => {
            document.body.classList.remove('modal-open');
            previous?.focus();
        };
        // The dialog takes the focus once, when it opens.
    }, []);
    const keys = (event: ReactKeyboardEvent): void => {
        if (event.key === 'Escape') {
            event.stopPropagation();
            onClose();
            return;
        }
        if (event.key !== 'Tab' || !root.current) {
            return;
        }
        const focusable = Array.from(root.current.querySelectorAll<HTMLElement>(
            'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex="0"]'
        ));
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last?.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first?.focus();
        }
    };
    return createPortal(<>
        <div ref={root} className="modal show d-block" role="dialog" aria-modal="true" aria-labelledby={titleid} tabIndex={-1}
            onKeyDown={keys} onMouseDown={(event) => event.target === event.currentTarget && onClose()}>
            <div className={`modal-dialog${size === 'md' ? '' : ` modal-${size}`} modal-dialog-scrollable`}>
                <div className="modal-content">
                    <div className="modal-header">
                        <h5 className="modal-title" id={titleid}>{title}</h5>
                        <button type="button" className="btn-close" aria-label="×" onClick={onClose} />
                    </div>
                    <div className="modal-body">{children}</div>
                    <div className="modal-footer">{footer}</div>
                </div>
            </div>
        </div>
        <div className="modal-backdrop show" />
    </>, document.body);
}

/**
 * The question asked before a destructive action: the danger tile, what is lost and what is kept, Cancel with the focus
 * and the danger button named by the action. It stays open with the error when the action fails.
 *
 * @param props The question, the action and the text.
 * @returns The dialog.
 */
export function ConfirmDialog({title, message, button, onConfirm, onClose, text, strings}: {
    title: string;
    message: string;
    button: string;
    onConfirm: () => Promise<void>;
    onClose: () => void;
    text: Text;
    strings: Strings;
}) {
    const [error, setError] = useState('');
    const [working, setWorking] = useState(false);
    const confirm = async(): Promise<void> => {
        setWorking(true);
        setError('');
        try {
            await onConfirm();
            onClose();
        } catch (failure) {
            setError(failure instanceof Error ? failure.message : String((failure as {message?: string})?.message ?? failure));
            setWorking(false);
        }
    };
    return <Dialog title={title} size="md" onClose={onClose} initialFocus="[data-action='cancel']" footer={<>
        <button type="button" className="btn btn-secondary" data-action="cancel" onClick={onClose}>{text('core:cancel')}</button>
        <button type="button" className="btn btn-danger" disabled={working} onClick={confirm}>{button}</button>
    </>}>
        <div className="d-flex align-items-start">
            <span className="dd-tile dd-tile-danger me-3"><Icon name="t/delete" strings={strings} /></span>
            <p className="mb-0">{message}</p>
        </div>
        {error && <div className="alert alert-danger mt-3 mb-0" role="alert">{error}</div>}
    </Dialog>;
}
