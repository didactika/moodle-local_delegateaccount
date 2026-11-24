/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The menu of the statuses of a list, as the design system draws it: a button with the shape of a select and, under it, one box
 * for each status with the rows it leaves. Several statuses can be chosen, and a row matches when it is in one of them; a menu of
 * one choice (single) offers the options as a list instead, and closes when one is chosen. The menu does not offer its base state:
 * while nothing is chosen the button says what it filters, and once something is, its arrow becomes a cross that clears it.
 *
 * @module     local_delegateaccount/components/StatusMenu
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useId, useRef, useState} from 'react';
import {Icon} from './ui';
import type {Strings, Text} from '../types';

/** A status the list can be narrowed to, with its rows (undefined while they are not known). */
export interface StatusOption {
    key: string;
    label: string;
    count?: number;
}

/**
 * The menu.
 *
 * @param props What it filters, the statuses, the ones chosen, whether only one can be, the texts and what to do when they change.
 * @returns The menu.
 */
export default function StatusMenu({label, name = 'status', options, chosen, single = false, text, strings, onChange}: {
    label: string;
    name?: string;
    options: StatusOption[];
    chosen: string[];
    single?: boolean;
    text: Text;
    strings: Strings;
    onChange: (chosen: string[]) => void;
}) {
    const [open, setOpen] = useState(false);
    const root = useRef<HTMLSpanElement>(null);
    const toggle = useRef<HTMLButtonElement>(null);
    const id = useId();
    const active = chosen.length > 0;
    let button = label;
    if (chosen.length === 1) {
        button = options.find((option) => option.key === chosen[0])?.label ?? label;
    } else if (chosen.length > 1) {
        button = `${label}: ${chosen.length}`;
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
    return <span className="dropdown d-inline-flex" data-menu={name} ref={root}>
        <button ref={toggle} type="button" aria-label={label} aria-haspopup="true" aria-expanded={open} aria-controls={open ? id : undefined}
            className={`form-select${active ? ' border-primary' : ''}`} onClick={() => setOpen(!open)}>{button}</button>
        {active && <button type="button" className="btn btn-link p-0" data-menu-clear title={clear} aria-label={clear} onClick={() => {
            setOpen(false);
            onChange([]);
            toggle.current?.focus();
        }}><Icon name="e/cancel" strings={strings} /></button>}
        {open && single && <div id={id} className="dropdown-menu show" role="listbox" aria-label={label}>
            {options.map((option) => <button key={option.key} type="button" role="option" className="dropdown-item"
                aria-selected={chosen.includes(option.key)} onClick={() => {
                    setOpen(false);
                    onChange([option.key]);
                    toggle.current?.focus();
                }}>
                <span>{option.label}</span>
                {option.count !== undefined && <span className="badge text-bg-secondary ms-auto">{option.count}</span>}
            </button>)}
        </div>}
        {open && !single && <div id={id} className="dropdown-menu show" role="group" aria-label={label}>
            {options.map((option) => <label key={option.key} className="dropdown-item" htmlFor={`${id}-${option.key}`}>
                <input type="checkbox" className="form-check-input" id={`${id}-${option.key}`} checked={chosen.includes(option.key)}
                    onChange={() => onChange(chosen.includes(option.key)
                        ? chosen.filter((key) => key !== option.key)
                        : [...chosen, option.key])} />
                <span>{option.label}</span>
                {option.count !== undefined && <span className="badge text-bg-secondary ms-auto">{option.count}</span>}
            </label>)}
        </div>}
    </span>;
}
