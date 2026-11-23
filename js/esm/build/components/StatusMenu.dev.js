var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
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
import { useEffect, useId, useRef, useState } from "react";
import { Icon } from "./ui";
function StatusMenu({ label, name = "status", options, chosen, single = false, text, strings, onChange }) {
  const [open, setOpen] = useState(false);
  const root = useRef(null);
  const toggle = useRef(null);
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
  return /* @__PURE__ */ jsxDEV("span", { className: "dropdown d-inline-flex", "data-menu": name, ref: root, children: [
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
        fileName: "public/local/delegateaccount/js/esm/src/components/StatusMenu.tsx",
        lineNumber: 79,
        columnNumber: 9
      },
      this
    ),
    active && /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-link p-0", "data-menu-clear": true, title: clear, "aria-label": clear, onClick: () => {
      setOpen(false);
      onChange([]);
      toggle.current?.focus();
    }, children: /* @__PURE__ */ jsxDEV(Icon, { name: "e/cancel", strings }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/StatusMenu.tsx",
      lineNumber: 85,
      columnNumber: 12
    }, this) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/StatusMenu.tsx",
      lineNumber: 81,
      columnNumber: 20
    }, this),
    open && single && /* @__PURE__ */ jsxDEV("div", { id, className: "dropdown-menu show", role: "listbox", "aria-label": label, children: options.map((option) => /* @__PURE__ */ jsxDEV(
      "button",
      {
        type: "button",
        role: "option",
        className: "dropdown-item",
        "aria-selected": chosen.includes(option.key),
        onClick: () => {
          setOpen(false);
          onChange([option.key]);
          toggle.current?.focus();
        },
        children: [
          /* @__PURE__ */ jsxDEV("span", { children: option.label }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/components/StatusMenu.tsx",
            lineNumber: 93,
            columnNumber: 17
          }, this),
          option.count !== void 0 && /* @__PURE__ */ jsxDEV("span", { className: "badge text-bg-secondary ms-auto", children: option.count }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/components/StatusMenu.tsx",
            lineNumber: 94,
            columnNumber: 48
          }, this)
        ]
      },
      option.key,
      true,
      {
        fileName: "public/local/delegateaccount/js/esm/src/components/StatusMenu.tsx",
        lineNumber: 87,
        columnNumber: 38
      },
      this
    )) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/StatusMenu.tsx",
      lineNumber: 86,
      columnNumber: 28
    }, this),
    open && !single && /* @__PURE__ */ jsxDEV("div", { id, className: "dropdown-menu show", role: "group", "aria-label": label, children: options.map((option) => /* @__PURE__ */ jsxDEV("label", { className: "dropdown-item", htmlFor: `${id}-${option.key}`, children: [
      /* @__PURE__ */ jsxDEV(
        "input",
        {
          type: "checkbox",
          className: "form-check-input",
          id: `${id}-${option.key}`,
          checked: chosen.includes(option.key),
          onChange: () => onChange(chosen.includes(option.key) ? chosen.filter((key) => key !== option.key) : [...chosen, option.key])
        },
        void 0,
        false,
        {
          fileName: "public/local/delegateaccount/js/esm/src/components/StatusMenu.tsx",
          lineNumber: 99,
          columnNumber: 17
        },
        this
      ),
      /* @__PURE__ */ jsxDEV("span", { children: option.label }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/components/StatusMenu.tsx",
        lineNumber: 103,
        columnNumber: 17
      }, this),
      option.count !== void 0 && /* @__PURE__ */ jsxDEV("span", { className: "badge text-bg-secondary ms-auto", children: option.count }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/components/StatusMenu.tsx",
        lineNumber: 104,
        columnNumber: 48
      }, this)
    ] }, option.key, true, {
      fileName: "public/local/delegateaccount/js/esm/src/components/StatusMenu.tsx",
      lineNumber: 98,
      columnNumber: 38
    }, this)) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/StatusMenu.tsx",
      lineNumber: 97,
      columnNumber: 29
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/components/StatusMenu.tsx",
    lineNumber: 78,
    columnNumber: 12
  }, this);
}
__name(StatusMenu, "StatusMenu");
export {
  StatusMenu as default
};
//# sourceMappingURL=StatusMenu.dev.js.map
