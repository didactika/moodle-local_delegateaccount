var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
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
import { useEffect, useId, useRef, useState } from "react";
import { Icon } from "./ui";
const iso = /* @__PURE__ */ __name((date) => [
  date.getFullYear(),
  String(date.getMonth() + 1).padStart(2, "0"),
  String(date.getDate()).padStart(2, "0")
].join("-"), "iso");
const periods = /* @__PURE__ */ __name((min, max) => {
  const today = /* @__PURE__ */ new Date();
  const back = /* @__PURE__ */ __name((days) => new Date(today.getFullYear(), today.getMonth(), today.getDate() - days), "back");
  const clamp = /* @__PURE__ */ __name((day) => min && day < min ? min : max && day > max ? max : day, "clamp");
  return [
    { key: "today", from: iso(today), to: iso(today) },
    { key: "week", from: iso(back(6)), to: iso(today) },
    { key: "month", from: iso(new Date(today.getFullYear(), today.getMonth(), 1)), to: iso(today) },
    { key: "last30", from: iso(back(29)), to: iso(today) }
  ].map((period) => ({ ...period, from: clamp(period.from), to: clamp(period.to) }));
}, "periods");
const orderedDays = /* @__PURE__ */ __name((from, to, changed) => {
  if (from === "" || to === "" || from <= to) {
    return [from, to];
  }
  return changed === "from" ? [from, from] : [to, to];
}, "orderedDays");
function DateMenu({ label, from, to, min, max, text, strings, onChange }) {
  const [open, setOpen] = useState(false);
  const root = useRef(null);
  const toggle = useRef(null);
  const id = useId();
  const list = periods(min, max);
  const preset = list.find((period) => period.from === from && period.to === to);
  const active = from !== "" || to !== "";
  let button = label;
  if (preset) {
    button = text(`filter_date_${preset.key}`);
  } else if (from && to) {
    button = text("filter_date_range", { from, to });
  } else if (from) {
    button = text("filter_date_since", from);
  } else if (to) {
    button = text("filter_date_until", to);
  }
  useEffect(() => {
    if (!open) {
      return void 0;
    }
    const outside = /* @__PURE__ */ __name((event) => {
      if (!root.current?.contains(event.target)) {
        setOpen(false);
      }
    }, "outside");
    const keys = /* @__PURE__ */ __name((event) => {
      if (event.key === "Escape") {
        setOpen(false);
        toggle.current?.focus();
      }
    }, "keys");
    document.addEventListener("pointerdown", outside, true);
    document.addEventListener("keydown", keys, true);
    return () => {
      document.removeEventListener("pointerdown", outside, true);
      document.removeEventListener("keydown", keys, true);
    };
  }, [open]);
  const clear = `${text("core:clear")}: ${label}`;
  return /* @__PURE__ */ jsxDEV("span", { className: "dropdown d-inline-flex", "data-menu": "date", ref: root, children: [
    /* @__PURE__ */ jsxDEV(
      "button",
      {
        ref: toggle,
        type: "button",
        "aria-label": label,
        "aria-haspopup": "true",
        "aria-expanded": open,
        "aria-controls": open ? id : void 0,
        className: `form-select${active ? " border-primary" : ""}`,
        onClick: () => setOpen(!open),
        children: button
      },
      void 0,
      false,
      {
        fileName: "public/local/delegateaccount/js/esm/src/components/DateMenu.tsx",
        lineNumber: 120,
        columnNumber: 9
      },
      this
    ),
    active && /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-link p-0", "data-menu-clear": true, title: clear, "aria-label": clear, onClick: () => {
      setOpen(false);
      onChange("", "");
      toggle.current?.focus();
    }, children: /* @__PURE__ */ jsxDEV(Icon, { name: "e/cancel", strings }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/DateMenu.tsx",
      lineNumber: 126,
      columnNumber: 12
    }, this) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/DateMenu.tsx",
      lineNumber: 122,
      columnNumber: 20
    }, this),
    open && /* @__PURE__ */ jsxDEV("div", { id, className: "dropdown-menu show", role: "group", "aria-label": label, children: [
      list.map((period) => /* @__PURE__ */ jsxDEV(
        "button",
        {
          type: "button",
          role: "option",
          className: "dropdown-item",
          "aria-selected": preset?.key === period.key,
          onClick: () => {
            setOpen(false);
            onChange(period.from, period.to);
          },
          children: text(`filter_date_${period.key}`)
        },
        period.key,
        false,
        {
          fileName: "public/local/delegateaccount/js/esm/src/components/DateMenu.tsx",
          lineNumber: 128,
          columnNumber: 35
        },
        this
      )),
      /* @__PURE__ */ jsxDEV("hr", {}, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/components/DateMenu.tsx",
        lineNumber: 133,
        columnNumber: 13
      }, this),
      /* @__PURE__ */ jsxDEV("div", { className: "d-grid gap-2 px-3 pt-2 pb-3", children: /* @__PURE__ */ jsxDEV("div", { className: "d-flex gap-2", children: [
        /* @__PURE__ */ jsxDEV("div", { className: "flex-fill", children: [
          /* @__PURE__ */ jsxDEV("label", { className: "form-label small mb-1", htmlFor: `${id}-from`, children: text("activity_filter_datefrom") }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/components/DateMenu.tsx",
            lineNumber: 137,
            columnNumber: 25
          }, this),
          /* @__PURE__ */ jsxDEV(
            "input",
            {
              id: `${id}-from`,
              type: "date",
              className: "form-control form-control-sm",
              value: from,
              min: min || void 0,
              max: to || max || void 0,
              onChange: (event) => onChange(...orderedDays(event.target.value, to, "from"))
            },
            void 0,
            false,
            {
              fileName: "public/local/delegateaccount/js/esm/src/components/DateMenu.tsx",
              lineNumber: 138,
              columnNumber: 25
            },
            this
          )
        ] }, void 0, true, {
          fileName: "public/local/delegateaccount/js/esm/src/components/DateMenu.tsx",
          lineNumber: 136,
          columnNumber: 21
        }, this),
        /* @__PURE__ */ jsxDEV("div", { className: "flex-fill", children: [
          /* @__PURE__ */ jsxDEV("label", { className: "form-label small mb-1", htmlFor: `${id}-to`, children: text("activity_filter_dateto") }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/components/DateMenu.tsx",
            lineNumber: 142,
            columnNumber: 25
          }, this),
          /* @__PURE__ */ jsxDEV(
            "input",
            {
              id: `${id}-to`,
              type: "date",
              className: "form-control form-control-sm",
              value: to,
              min: from || min || void 0,
              max: max || void 0,
              onChange: (event) => onChange(...orderedDays(from, event.target.value, "to"))
            },
            void 0,
            false,
            {
              fileName: "public/local/delegateaccount/js/esm/src/components/DateMenu.tsx",
              lineNumber: 143,
              columnNumber: 25
            },
            this
          )
        ] }, void 0, true, {
          fileName: "public/local/delegateaccount/js/esm/src/components/DateMenu.tsx",
          lineNumber: 141,
          columnNumber: 21
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/delegateaccount/js/esm/src/components/DateMenu.tsx",
        lineNumber: 135,
        columnNumber: 17
      }, this) }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/components/DateMenu.tsx",
        lineNumber: 134,
        columnNumber: 13
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/delegateaccount/js/esm/src/components/DateMenu.tsx",
      lineNumber: 127,
      columnNumber: 18
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/components/DateMenu.tsx",
    lineNumber: 119,
    columnNumber: 12
  }, this);
}
__name(DateMenu, "DateMenu");
export {
  DateMenu as default,
  orderedDays
};
//# sourceMappingURL=DateMenu.dev.js.map
