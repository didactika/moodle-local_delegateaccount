/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The menus of a row of filters, beside the search. When they do not fit, on a narrow screen or when the values chosen are long,
 * they are grouped in one menu, Filters, that says how many narrow the list and holds them; they come back to the row when there
 * is room again, so the row never breaks in two.
 *
 * @module     local_delegateaccount/components/FilterGroup
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useId, useLayoutEffect, useRef, useState} from 'react';
import type {ReactNode} from 'react';
import {Icon} from './ui';
import type {Strings, Text} from '../types';

/** The width the search keeps beside the filters before they are grouped, in pixels (10rem). */
const SEARCH_ROOM = 160;

/**
 * The filters, in the row or grouped.
 *
 * @param props How many filters narrow the list, the texts and the filters.
 * @returns The filters.
 */
export default function FilterGroup({count, text, strings, children}: {
    count: number;
    text: Text;
    strings: Strings;
    children: ReactNode;
}) {
    const [grouped, setGrouped] = useState(false);
    const [open, setOpen] = useState(false);
    const box = useRef<HTMLDivElement>(null);
    const group = useRef<HTMLSpanElement>(null);
    const needed = useRef(0);
    const id = useId();

    useLayoutEffect(() => {
        const row = box.current?.parentElement;
        if (!box.current || !row) {
            return undefined;
        }
        const measure = (): void => {
            const gap = parseFloat(getComputedStyle(row).columnGap) || 0;
            const room = row.clientWidth - SEARCH_ROOM - gap;
            if (!grouped) {
                const menus = Array.from(box.current?.children ?? []) as HTMLElement[];
                const width = menus.reduce((sum, menu) => sum + menu.offsetWidth, 0) + gap * Math.max(0, menus.length - 1);
                if (width > room) {
                    needed.current = width;
                    setGrouped(true);
                }
            } else if (room >= needed.current) {
                setOpen(false);
                setGrouped(false);
            }
        };
        measure();
        const observer = new ResizeObserver(measure);
        observer.observe(row);
        return () => observer.disconnect();
    });

    useEffect(() => {
        if (!open) {
            return undefined;
        }
        // The list of a select inside the group floats outside it; a click there does not close the group.
        const outside = (event: PointerEvent): void => {
            const target = event.target as HTMLElement;
            if (!group.current?.contains(target) && !target.closest('.dropdown-menu, .dropdown-menu')) {
                setOpen(false);
            }
        };
        document.addEventListener('pointerdown', outside, true);
        return () => document.removeEventListener('pointerdown', outside, true);
    }, [open]);

    if (!grouped) {
        return <div ref={box} data-region="filter-menus">{children}</div>;
    }
    const label = count > 0 ? `${text('core:filters')} (${count})` : text('core:filters');
    return <div ref={box} data-region="filter-menus">
        <span ref={group} className="dropdown" data-menu="filters">
            <button type="button" className={`form-select${count > 0 ? ' border-primary' : ''}`} aria-haspopup="true"
                aria-expanded={open} aria-controls={open ? id : undefined} aria-label={text('core:filters')}
                onClick={() => setOpen(!open)}>
                <Icon name="i/filter" strings={strings} /> {label}
            </button>
            <div id={id} className="dropdown-menu show dropdown-menu-end" role="group" aria-label={text('core:filters')} hidden={!open}>
                <div data-region="filter-panel">{children}</div>
            </div>
        </span>
    </div>;
}
