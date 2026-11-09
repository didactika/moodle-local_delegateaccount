/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A list people work with, as the design system describes it: its state kept in the address (search, chip, sort, page and
 * rows per page), one page loaded at a time from a web service, the skeleton shown only when a load is slow, and a selection
 * that can grow to every row that matches.
 *
 * @module     local_delegateaccount/hooks/useList
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useCallback, useEffect, useRef, useState} from 'react';
import Pending from '@moodle/lms/core/pending';
import type {Page} from '../types';

/** Milliseconds a load may take before the skeleton appears, so a fast answer does not flash. */
const SKELETON_DELAY = 300;

/** The state of a list, all of it kept in the address. */
export type ListState = Record<string, string | number>;

/**
 * Read the state from the address, falling back on the defaults.
 *
 * @param defaults State the list starts from.
 * @returns The state.
 */
const readState = (defaults: ListState): ListState => {
    const params = new URL(window.location.href).searchParams;
    const state: ListState = {...defaults};
    Object.keys(defaults).forEach((key) => {
        const value = params.get(key);
        if (value !== null) {
            state[key] = typeof defaults[key] === 'number' ? Number(value) || 0 : value;
        }
    });
    return state;
};

/**
 * Write the state into the address, leaving out what equals the defaults.
 *
 * @param state The state.
 * @param defaults The defaults.
 */
const writeState = (state: ListState, defaults: ListState): void => {
    const url = new URL(window.location.href);
    Object.keys(defaults).forEach((key) => {
        if (state[key] === defaults[key] || state[key] === '') {
            url.searchParams.delete(key);
        } else {
            url.searchParams.set(key, String(state[key]));
        }
    });
    window.history.replaceState(window.history.state, '', url);
};

/** Keys whose change starts the list again from its first page and clears the selection. */
const FILTERS = ['search', 'status', 'datefrom', 'dateto', 'component', 'action'];

/**
 * A list.
 *
 * @param defaults State the list starts from.
 * @param load Loads one page for a state.
 * @param initial A first page the server already drew, used while the state equals the defaults.
 * @returns The state, the page, how it is loading and the selection.
 */
export function useList<Row extends {id?: number}>(
    defaults: ListState,
    load: (state: ListState) => Promise<Page<Row>>,
    initial?: Page<Row>,
) {
    const [state, setState] = useState<ListState>(() => readState(defaults));
    const [page, setPage] = useState<Page<Row> | null>(initial ?? null);
    const [error, setError] = useState<unknown>(null);
    const [busy, setBusy] = useState(false);
    const [skeleton, setSkeleton] = useState(false);
    const [selected, setSelected] = useState<Set<number>>(new Set());
    const [all, setAll] = useState(false);
    const [reload, setReload] = useState(0);
    const first = useRef(Boolean(initial));
    const loader = useRef(load);
    loader.current = load;

    useEffect(() => {
        writeState(state, defaults);
        if (first.current) {
            first.current = false;
            if (Object.keys(defaults).every((key) => state[key] === defaults[key])) {
                return undefined;
            }
        }
        let current = true;
        const pending = new Pending('local_delegateaccount/list:load');
        const timer = window.setTimeout(() => setSkeleton(true), SKELETON_DELAY);
        setBusy(true);
        loader.current(state).then((response) => {
            window.clearTimeout(timer);
            if (!current) {
                return response;
            }
            // A page past the end, after rows went away, goes back to the last page that has rows.
            const lastpage = Math.max(0, Math.ceil(response.total / Number(state.perpage)) - 1);
            if (Number(state.page) > lastpage) {
                setState((before) => ({...before, page: lastpage}));
                return response;
            }
            setPage(response);
            setError(null);
            return response;
        }).catch((failure: unknown) => {
            if (current) {
                setError(failure);
            }
        }).finally(() => {
            window.clearTimeout(timer);
            if (current) {
                setBusy(false);
                setSkeleton(false);
            }
            pending.resolve();
        });
        return () => {
            current = false;
            window.clearTimeout(timer);
        };
        // The defaults never change for a list; the state and the reload counter decide when it loads.
    }, [state, reload]);

    /**
     * Change the state; a new filter goes back to the first page and clears the selection.
     *
     * @param changes What changes.
     */
    const update = useCallback((changes: ListState): void => {
        setState((before) => {
            const filtered = Object.keys(changes).some((key) => FILTERS.includes(key) && changes[key] !== before[key]);
            if (filtered || ('page' in changes && changes.page !== before.page)) {
                setSelected(new Set());
                setAll(false);
            }
            return {...before, ...(filtered && !('page' in changes) ? {page: 0} : {}), ...changes};
        });
    }, []);

    /**
     * Choose a row, or forget it.
     *
     * @param id The row.
     * @param checked Whether it is chosen.
     */
    const toggle = useCallback((id: number, checked: boolean): void => {
        setSelected((before) => {
            const next = new Set(before);
            if (checked) {
                next.add(id);
            } else {
                next.delete(id);
            }
            return next;
        });
        if (!checked) {
            setAll(false);
        }
    }, []);

    /**
     * Choose every selectable row of the page, or none.
     *
     * @param ids The rows of the page that can be chosen.
     * @param checked Whether they are chosen.
     */
    const togglePage = useCallback((ids: number[], checked: boolean): void => {
        setSelected((before) => {
            const next = new Set(before);
            ids.forEach((id) => (checked ? next.add(id) : next.delete(id)));
            return next;
        });
        setAll(false);
    }, []);

    /** Forget the selection. */
    const clear = useCallback((): void => {
        setSelected(new Set());
        setAll(false);
    }, []);

    return {
        state,
        update,
        page,
        error,
        busy,
        skeleton,
        selected,
        all,
        setAll,
        toggle,
        togglePage,
        clear,
        refresh: () => setReload((count) => count + 1),
    };
}

/**
 * A value that follows another one once it stops changing for a while.
 *
 * @param value The value.
 * @param delay Milliseconds without changes.
 * @returns The value, late.
 */
export function useDebounced<T>(value: T, delay: number): T {
    const [late, setLate] = useState(value);
    useEffect(() => {
        const timer = window.setTimeout(() => setLate(value), delay);
        return () => window.clearTimeout(timer);
    }, [value, delay]);
    return late;
}
