var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
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
const PAGE_SIZES = [12, 24, 48, 96, 0];
const DEFAULT_PAGE_SIZE = 12;
const pageSize = /* @__PURE__ */ __name((perpage, fallback = DEFAULT_PAGE_SIZE) => PAGE_SIZES.includes(perpage) ? perpage : fallback, "pageSize");
const pageRange = /* @__PURE__ */ __name((page, perpage, total) => {
  const size = perpage > 0 ? perpage : Math.max(total, 1);
  const lastpage = Math.max(0, Math.ceil(total / size) - 1);
  const start = Math.min(page, lastpage) * size;
  const end = Math.min(total, start + size);
  return { from: total ? start + 1 : 0, to: end, lastpage, start, end };
}, "pageRange");
const fitPageSizes = /* @__PURE__ */ __name((perpage, total) => ({
  sizes: PAGE_SIZES.filter((size) => size === 0 || size < total),
  shown: perpage > 0 && perpage < total ? perpage : 0
}), "fitPageSizes");
export {
  DEFAULT_PAGE_SIZE,
  PAGE_SIZES,
  fitPageSizes,
  pageRange,
  pageSize
};
//# sourceMappingURL=paging.dev.js.map
