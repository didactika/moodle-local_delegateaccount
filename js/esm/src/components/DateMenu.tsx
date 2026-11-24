/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The menu of a date of the design system: a button with the shape of a select that says the period chosen and, under it, the
 * usual periods and the two days of choice. The days stay inside the bounds given and never go the wrong way round; once a period
 * is chosen the menu is tinted and a cross takes the place of its arrow.
 *
 * @module     local_delegateaccount/components/DateMenu
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useId, useRef, useState} from 'react';
import {Icon} from './ui';
import type {Strings, Text} from '../types';

/**
 * A day as a date field writes it.
 *
 * @param date The day.
 * @returns The day, as year-month-day.
 */
const iso = (date: Date): string => [date.getFullYear(), String(date.getMonth() + 1).padStart(2, '0'),
    String(date.getDate()).padStart(2, '0')].join('-');

/**
 * The usual periods, counted from today and kept inside the bounds.
 *
 * @param min The first day that can be chosen, or empty.
 * @param max The last day that can be chosen, or empty.
 * @returns The periods, by name.
 */
const periods = (min: string, max: string): Array<{key: string; from: string; to: string}> => {
    const today = new Date();
    const back = (days: number): Date => new Date(today.getFullYear(), today.getMonth(), today.getDate() - days);
    const clamp = (day: string): string => (min && day < min ? min : max && day > max ? max : day);
    return [
        {key: 'today', from: iso(today), to: iso(today)},
        {key: 'week', from: iso(back(6)), to: iso(today)},
        {key: 'month', from: iso(new Date(today.getFullYear(), today.getMonth(), 1)), to: iso(today)},
        {key: 'last30', from: iso(back(29)), to: iso(today)},
    ].map((period) => ({...period, from: clamp(period.from), to: clamp(period.to)}));
};

/**
 * Two days that bound a period, never the wrong way round: when the day just chosen passes the other one, the other one moves to
 * it.
 *
 * @param from The first day, or empty.
 * @param to The last day, or empty.
 * @param changed Which of the two was just chosen.
 * @returns The two days, in order.
 */
export const orderedDays = (from: string, to: string, changed: 'from' | 'to'): [string, string] => {
    if (from === '' || to === '' || from <= to) {
        return [from, to];
    }
    return changed === 'from' ? [from, from] : [to, to];
};

/**
 * The menu of a date.
 *
 * @param props What it filters, the period chosen, its bounds, the texts and what to do when it changes.
 * @returns The menu.
 */
export default function DateMenu({label, from, to, min, max, text, strings, onChange}: {
    label: string;
    from: string;
    to: string;
    min: string;
    max: string;
    text: Text;
    strings: Strings;
    onChange: (from: string, to: string) => void;
}) {
    const [open, setOpen] = useState(false);
    const root = useRef<HTMLSpanElement>(null);
    const toggle = useRef<HTMLButtonElement>(null);
    const id = useId();
    const list = periods(min, max);
    const preset = list.find((period) => period.from === from && period.to === to);
    const active = from !== '' || to !== '';
    let button = label;
    if (preset) {
        button = text(`filter_date_${preset.key}`);
    } else if (from && to) {
        button = text('filter_date_range', {from, to});
    } else if (from) {
        button = text('filter_date_since', from);
    } else if (to) {
        button = text('filter_date_until', to);
    }

    useEffect(() => {
        if (!open) {
            return undefined;
        }
        const outside = (event: PointerEvent): void => {
            if (!root.current?.contains(event.target as Node)) {
                setOpen(false);
            }
        };
        const keys = (event: KeyboardEvent): void => {
            if (event.key === 'Escape') {
                setOpen(false);
                toggle.current?.focus();
            }
        };
        document.addEventListener('pointerdown', outside, true);
        document.addEventListener('keydown', keys, true);
        return () => {
            document.removeEventListener('pointerdown', outside, true);
            document.removeEventListener('keydown', keys, true);
        };
    }, [open]);

    const clear = `${text('core:clear')}: ${label}`;
    return <span className="dropdown d-inline-flex" data-menu="date" ref={root}>
        <button ref={toggle} type="button" aria-label={label} aria-haspopup="true" aria-expanded={open} aria-controls={open ? id : undefined}
            className={`form-select${active ? ' border-primary' : ''}`} onClick={() => setOpen(!open)}>{button}</button>
        {active && <button type="button" className="btn btn-link p-0" data-menu-clear title={clear} aria-label={clear} onClick={() => {
            setOpen(false);
            onChange('', '');
            toggle.current?.focus();
        }}><Icon name="e/cancel" strings={strings} /></button>}
        {open && <div id={id} className="dropdown-menu show" role="group" aria-label={label}>
            {list.map((period) => <button key={period.key} type="button" role="option" className="dropdown-item"
                aria-selected={preset?.key === period.key} onClick={() => {
                    setOpen(false);
                    onChange(period.from, period.to);
                }}>{text(`filter_date_${period.key}`)}</button>)}
            <hr />
            <div className="d-grid gap-2 px-3 pt-2 pb-3">
                <div className="d-flex gap-2">
                    <div className="flex-fill">
                        <label className="form-label small mb-1" htmlFor={`${id}-from`}>{text('activity_filter_datefrom')}</label>
                        <input id={`${id}-from`} type="date" className="form-control form-control-sm" value={from} min={min || undefined}
                            max={to || max || undefined} onChange={(event) => onChange(...orderedDays(event.target.value, to, 'from'))} />
                    </div>
                    <div className="flex-fill">
                        <label className="form-label small mb-1" htmlFor={`${id}-to`}>{text('activity_filter_dateto')}</label>
                        <input id={`${id}-to`} type="date" className="form-control form-control-sm" value={to} min={from || min || undefined}
                            max={max || undefined} onChange={(event) => onChange(...orderedDays(from, event.target.value, 'to'))} />
                    </div>
                </div>
            </div>
        </div>}
    </span>;
}
