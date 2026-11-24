var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
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
import { useEffect, useId, useLayoutEffect, useRef, useState } from "react";
import { Icon } from "./ui";
const SEARCH_ROOM = 160;
function FilterGroup({ count, text, strings, children }) {
  const [grouped, setGrouped] = useState(false);
  const [open, setOpen] = useState(false);
  const box = useRef(null);
  const group = useRef(null);
  const needed = useRef(0);
  const id = useId();
  useLayoutEffect(() => {
    const row = box.current?.parentElement;
    if (!box.current || !row) {
      return void 0;
    }
    const measure = /* @__PURE__ */ __name(() => {
      const gap = parseFloat(getComputedStyle(row).columnGap) || 0;
      const room = row.clientWidth - SEARCH_ROOM - gap;
      if (!grouped) {
        const menus = Array.from(box.current?.children ?? []);
        const width = menus.reduce((sum, menu) => sum + menu.offsetWidth, 0) + gap * Math.max(0, menus.length - 1);
        if (width > room) {
          needed.current = width;
          setGrouped(true);
        }
      } else if (room >= needed.current) {
        setOpen(false);
        setGrouped(false);
      }
    }, "measure");
    measure();
    const observer = new ResizeObserver(measure);
    observer.observe(row);
    return () => observer.disconnect();
  });
  useEffect(() => {
    if (!open) {
      return void 0;
    }
    const outside = /* @__PURE__ */ __name((event) => {
      const target = event.target;
      if (!group.current?.contains(target) && !target.closest(".dropdown-menu, .dropdown-menu")) {
        setOpen(false);
      }
    }, "outside");
    document.addEventListener("pointerdown", outside, true);
    return () => document.removeEventListener("pointerdown", outside, true);
  }, [open]);
  if (!grouped) {
    return /* @__PURE__ */ jsxDEV("div", { ref: box, "data-region": "filter-menus", children }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/FilterGroup.tsx",
      lineNumber: 83,
      columnNumber: 16
    }, this);
  }
  const label = count > 0 ? `${text("core:filters")} (${count})` : text("core:filters");
  return /* @__PURE__ */ jsxDEV("div", { ref: box, "data-region": "filter-menus", children: /* @__PURE__ */ jsxDEV("span", { ref: group, className: "dropdown", "data-menu": "filters", children: [
    /* @__PURE__ */ jsxDEV(
      "button",
      {
        type: "button",
        className: `form-select${count > 0 ? " border-primary" : ""}`,
        "aria-haspopup": "true",
        "aria-expanded": open,
        "aria-controls": open ? id : void 0,
        "aria-label": text("core:filters"),
        onClick: () => setOpen(!open),
        children: [
          /* @__PURE__ */ jsxDEV(Icon, { name: "i/filter", strings }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/components/FilterGroup.tsx",
            lineNumber: 91,
            columnNumber: 17
          }, this),
          " ",
          label
        ]
      },
      void 0,
      true,
      {
        fileName: "public/local/delegateaccount/js/esm/src/components/FilterGroup.tsx",
        lineNumber: 88,
        columnNumber: 13
      },
      this
    ),
    /* @__PURE__ */ jsxDEV("div", { id, className: "dropdown-menu show dropdown-menu-end", role: "group", "aria-label": text("core:filters"), hidden: !open, children: /* @__PURE__ */ jsxDEV("div", { "data-region": "filter-panel", children }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/FilterGroup.tsx",
      lineNumber: 94,
      columnNumber: 17
    }, this) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/FilterGroup.tsx",
      lineNumber: 93,
      columnNumber: 13
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/components/FilterGroup.tsx",
    lineNumber: 87,
    columnNumber: 9
  }, this) }, void 0, false, {
    fileName: "public/local/delegateaccount/js/esm/src/components/FilterGroup.tsx",
    lineNumber: 86,
    columnNumber: 12
  }, this);
}
__name(FilterGroup, "FilterGroup");
export {
  FilterGroup as default
};
//# sourceMappingURL=FilterGroup.dev.js.map
