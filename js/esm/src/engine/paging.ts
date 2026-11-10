/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The sizes a page of a list may have, and which rows a page shows.
 *
 * @module     local_delegateaccount/engine/paging
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** The sizes a page of a list may have, 0 being every row at once. */
export const PAGE_SIZES = [12, 24, 48, 96, 0];

/** The size a list starts with. */
export const DEFAULT_PAGE_SIZE = 12;

/**
 * Brings a size read from the address to one the pager offers.
 *
 * @param perpage The size asked for.
 * @param fallback The size to use when it is not one of them.
 * @returns The size.
 */
export const pageSize = (perpage: number, fallback = DEFAULT_PAGE_SIZE): number =>
    (PAGE_SIZES.includes(perpage) ? perpage : fallback);

/**
 * Which rows a page shows: the first and the last, counted from 1, the last page there is, and where the page starts and
 * ends in the list.
 *
 * @param page The page, from 0.
 * @param perpage Rows per page, 0 for all of them.
 * @param total How many rows there are.
 * @returns The range of the page.
 */
export const pageRange = (page: number, perpage: number, total: number):
    {from: number; to: number; lastpage: number; start: number; end: number} => {
    const size = perpage > 0 ? perpage : Math.max(total, 1);
    const lastpage = Math.max(0, Math.ceil(total / size) - 1);
    const start = Math.min(page, lastpage) * size;
    const end = Math.min(total, start + size);
    return {from: total ? start + 1 : 0, to: end, lastpage, start, end};
};

/**
 * The sizes a pager offers for a list, the sizes smaller than it and all of it, and the one it shows: All when every row
 * fits on the page.
 *
 * @param perpage Rows per page, 0 for all of them.
 * @param total How many rows there are.
 * @returns The sizes and the one shown.
 */
export const fitPageSizes = (perpage: number, total: number): {sizes: number[]; shown: number} => ({
    sizes: PAGE_SIZES.filter((size) => size === 0 || size < total),
    shown: perpage > 0 && perpage < total ? perpage : 0,
});
