var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
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
import { useEffect, useId, useLayoutEffect, useRef, useState } from "react";
import { createPortal } from "react-dom";
import { fitPageSizes, pageRange } from "../engine/paging";
function Icon({ name, strings }) {
  return /* @__PURE__ */ jsxDEV("span", { className: "d-inline-flex", "aria-hidden": "true", dangerouslySetInnerHTML: { __html: strings[`pix:${name}`] ?? "" } }, void 0, false, {
    fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
    lineNumber: 26,
    columnNumber: 12
  }, this);
}
__name(Icon, "Icon");
function Avatar({ user, size = 35 }) {
  return user.hasimage ? /* @__PURE__ */ jsxDEV("img", { src: user.imageurl, className: "userpicture", width: size, height: size, alt: "" }, void 0, false, {
    fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
    lineNumber: 37,
    columnNumber: 11
  }, this) : /* @__PURE__ */ jsxDEV("span", { className: `userinitials size-${size}`, "aria-hidden": "true", children: user.initials }, void 0, false, {
    fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
    lineNumber: 38,
    columnNumber: 11
  }, this);
}
__name(Avatar, "Avatar");
function Person({ user }) {
  return /* @__PURE__ */ jsxDEV("span", { className: "d-flex align-items-center", children: [
    /* @__PURE__ */ jsxDEV(Avatar, { user }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 49,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("span", { className: "d-flex flex-column", children: [
      user.url ? /* @__PURE__ */ jsxDEV("a", { href: user.url, title: user.linklabel || void 0, children: user.fullname }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
        lineNumber: 51,
        columnNumber: 25
      }, this) : /* @__PURE__ */ jsxDEV("b", { children: user.fullname }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
        lineNumber: 51,
        columnNumber: 102
      }, this),
      user.email && /* @__PURE__ */ jsxDEV("span", { className: "small text-muted text-truncate", children: user.email }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
        lineNumber: 52,
        columnNumber: 28
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 50,
      columnNumber: 9
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
    lineNumber: 48,
    columnNumber: 12
  }, this);
}
__name(Person, "Person");
function Chips({ chips, value, counts, label, onChange }) {
  return /* @__PURE__ */ jsxDEV("div", { className: "dd-chips", role: "group", "aria-label": label, children: chips.map((chip) => /* @__PURE__ */ jsxDEV(
    "button",
    {
      type: "button",
      className: "dd-chip",
      "aria-pressed": chip.key === value,
      onClick: () => onChange(chip.key),
      children: [
        chip.label,
        " ",
        /* @__PURE__ */ jsxDEV("span", { className: "dd-chip-count", children: counts?.[chip.key || "all"] ?? "\xA0" }, void 0, false, {
          fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
          lineNumber: 73,
          columnNumber: 26
        }, this)
      ]
    },
    chip.key,
    true,
    {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 71,
      columnNumber: 30
    },
    this
  )) }, void 0, false, {
    fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
    lineNumber: 70,
    columnNumber: 12
  }, this);
}
__name(Chips, "Chips");
function SearchBox({ value, onChange, label, placeholder, strings, wide = false }) {
  const id = useId();
  return /* @__PURE__ */ jsxDEV("div", { className: `dd-searchbox${wide ? " mw-100" : ""}`, role: "search", children: [
    /* @__PURE__ */ jsxDEV(Icon, { name: "a/search", strings }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 94,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("label", { className: "visually-hidden", htmlFor: id, children: label }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 95,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV(
      "input",
      {
        className: "form-control",
        type: "search",
        id,
        value,
        placeholder: placeholder ?? label,
        autoComplete: "off",
        onChange: (event) => onChange(event.target.value),
        onKeyDown: (event) => event.key === "Enter" && event.preventDefault()
      },
      void 0,
      false,
      {
        fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
        lineNumber: 96,
        columnNumber: 9
      },
      this
    )
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
    lineNumber: 93,
    columnNumber: 12
  }, this);
}
__name(SearchBox, "SearchBox");
function SelectMenu({ options, value, onChange, label, className = "", small = false }) {
  const select = useRef(null);
  const menu = useRef(null);
  const [open, setOpen] = useState(false);
  const [active, setActive] = useState(0);
  const [place, setPlace] = useState(null);
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
      height: max
    });
    (menu.current.querySelector('[aria-selected="true"]') ?? menu.current.querySelector("button"))?.focus();
  }, [open]);
  useEffect(() => {
    if (!open) {
      return void 0;
    }
    const close = /* @__PURE__ */ __name((event) => {
      if (!menu.current?.contains(event.target)) {
        setOpen(false);
      }
    }, "close");
    window.addEventListener("resize", close);
    window.addEventListener("scroll", close, { capture: true, passive: true });
    document.addEventListener("mousedown", close);
    return () => {
      window.removeEventListener("resize", close);
      window.removeEventListener("scroll", close, { capture: true });
      document.removeEventListener("mousedown", close);
    };
  }, [open]);
  const choose = /* @__PURE__ */ __name((index) => {
    setOpen(false);
    select.current?.focus();
    if (options[index] && options[index].value !== value) {
      onChange(options[index].value);
    }
  }, "choose");
  const focus = /* @__PURE__ */ __name((index) => {
    const at = (index + options.length) % options.length;
    setActive(at);
    menu.current?.querySelectorAll("button")[at]?.focus();
  }, "focus");
  const keys = /* @__PURE__ */ __name((event) => {
    const at = Array.from(menu.current?.querySelectorAll("button") ?? []).indexOf(document.activeElement);
    if (event.key === "ArrowDown") {
      event.preventDefault();
      focus(at + 1);
    } else if (event.key === "ArrowUp") {
      event.preventDefault();
      focus(at - 1);
    } else if (event.key === "Home") {
      event.preventDefault();
      focus(0);
    } else if (event.key === "End") {
      event.preventDefault();
      focus(-1);
    } else if (event.key === "Escape") {
      event.preventDefault();
      event.stopPropagation();
      setOpen(false);
      select.current?.focus();
    } else if (event.key === "Tab") {
      setOpen(false);
    } else if (event.key.length === 1 && /\S/.test(event.key)) {
      const letter = event.key.toLowerCase();
      const order = [...options.slice(at + 1), ...options.slice(0, at + 1)];
      const found = order.find((option) => option.label.toLowerCase().startsWith(letter));
      if (found) {
        focus(options.indexOf(found));
      }
    }
  }, "keys");
  const scope = select.current?.closest(".lda, .modal") ?? document.body;
  const position = place ? { top: place.top, left: place.left, minWidth: place.width, maxHeight: place.height } : { visibility: "hidden" };
  return /* @__PURE__ */ jsxDEV(Fragment, { children: [
    /* @__PURE__ */ jsxDEV(
      "select",
      {
        ref: select,
        className: `form-select dd-select${small ? " form-select-sm" : ""} ${className}`,
        value,
        "aria-label": label,
        "aria-expanded": open || void 0,
        "aria-controls": open ? id : void 0,
        onChange: (event) => onChange(event.target.value),
        onMouseDown: (event) => {
          event.preventDefault();
          select.current?.focus();
          setOpen(!open);
        },
        onKeyDown: (event) => {
          if (["Enter", " ", "ArrowDown", "ArrowUp"].includes(event.key)) {
            event.preventDefault();
            setOpen(true);
          }
        },
        children: options.map((option) => /* @__PURE__ */ jsxDEV("option", { value: option.value, children: option.label }, option.value, false, {
          fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
          lineNumber: 237,
          columnNumber: 38
        }, this))
      },
      void 0,
      false,
      {
        fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
        lineNumber: 223,
        columnNumber: 9
      },
      this
    ),
    open && createPortal(/* @__PURE__ */ jsxDEV(
      "div",
      {
        ref: menu,
        id,
        className: "dropdown-menu show dd-select-menu",
        role: "listbox",
        tabIndex: -1,
        onKeyDown: keys,
        style: position,
        children: options.map((option, index) => /* @__PURE__ */ jsxDEV(
          "button",
          {
            type: "button",
            role: "option",
            className: `dropdown-item${index === active ? " active" : ""}`,
            "aria-selected": option.value === value,
            onClick: () => choose(index),
            children: option.label
          },
          option.value,
          false,
          {
            fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
            lineNumber: 241,
            columnNumber: 45
          },
          this
        ))
      },
      void 0,
      false,
      {
        fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
        lineNumber: 239,
        columnNumber: 31
      },
      this
    ), scope)
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
    lineNumber: 222,
    columnNumber: 12
  }, this);
}
__name(SelectMenu, "SelectMenu");
function Pager({ page, perpage, total, onChange, text, strings }) {
  const { from, to } = pageRange(page, perpage, total);
  const { sizes, shown } = fitPageSizes(perpage, total);
  return /* @__PURE__ */ jsxDEV("div", { className: "table-foot", children: /* @__PURE__ */ jsxDEV("div", { className: "dd-pager", children: [
    /* @__PURE__ */ jsxDEV("div", { className: "dd-pager-size", children: [
      /* @__PURE__ */ jsxDEV("span", { children: text("rows_per_page") }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
        lineNumber: 268,
        columnNumber: 17
      }, this),
      /* @__PURE__ */ jsxDEV(
        SelectMenu,
        {
          small: true,
          label: text("rows_per_page"),
          value: String(shown),
          options: sizes.map((size) => ({ value: String(size), label: size ? String(size) : text("core:all") })),
          onChange: (size) => onChange({ perpage: Number(size), page: 0 })
        },
        void 0,
        false,
        {
          fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
          lineNumber: 269,
          columnNumber: 17
        },
        this
      )
    ] }, void 0, true, {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 267,
      columnNumber: 13
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "dd-pager-end", children: [
      /* @__PURE__ */ jsxDEV("span", { className: "dd-pager-range", role: "status", children: text("pager_range", { from, to, total }) }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
        lineNumber: 274,
        columnNumber: 17
      }, this),
      /* @__PURE__ */ jsxDEV("div", { className: "dd-pager-arrows", children: [
        /* @__PURE__ */ jsxDEV(
          "button",
          {
            type: "button",
            className: "btn btn-outline-secondary btn-sm btn-icon",
            disabled: page === 0,
            title: text("previous_page"),
            "aria-label": text("previous_page"),
            onClick: () => onChange({ page: page - 1 }),
            children: /* @__PURE__ */ jsxDEV(Icon, { name: "i/previous", strings }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
              lineNumber: 278,
              columnNumber: 25
            }, this)
          },
          void 0,
          false,
          {
            fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
            lineNumber: 276,
            columnNumber: 21
          },
          this
        ),
        /* @__PURE__ */ jsxDEV(
          "button",
          {
            type: "button",
            className: "btn btn-outline-secondary btn-sm btn-icon",
            disabled: to >= total,
            title: text("next_page"),
            "aria-label": text("next_page"),
            onClick: () => onChange({ page: page + 1 }),
            children: /* @__PURE__ */ jsxDEV(Icon, { name: "i/next", strings }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
              lineNumber: 282,
              columnNumber: 25
            }, this)
          },
          void 0,
          false,
          {
            fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
            lineNumber: 280,
            columnNumber: 21
          },
          this
        )
      ] }, void 0, true, {
        fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
        lineNumber: 275,
        columnNumber: 17
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 273,
      columnNumber: 13
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
    lineNumber: 266,
    columnNumber: 9
  }, this) }, void 0, false, {
    fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
    lineNumber: 265,
    columnNumber: 12
  }, this);
}
__name(Pager, "Pager");
function Empty({ filtered, icon, title, message, onClear, text, strings }) {
  return /* @__PURE__ */ jsxDEV("div", { className: "dd-empty", children: [
    /* @__PURE__ */ jsxDEV("span", { className: "dd-tile dd-tile-lg", children: /* @__PURE__ */ jsxDEV(Icon, { name: filtered ? "a/search" : icon, strings }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 306,
      columnNumber: 46
    }, this) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 306,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("p", { className: "dd-empty-title", children: filtered ? text("empty_search_title") : title }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 307,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("p", { className: "dd-empty-text", children: filtered ? text("empty_search_text") : message }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 308,
      columnNumber: 9
    }, this),
    filtered && onClear && /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-outline-secondary", onClick: onClear, children: text("clear_filters") }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 309,
      columnNumber: 33
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
    lineNumber: 305,
    columnNumber: 12
  }, this);
}
__name(Empty, "Empty");
function ListError({ message, onRetry, text }) {
  return /* @__PURE__ */ jsxDEV("div", { className: "alert alert-danger d-flex flex-wrap align-items-center justify-content-between", role: "status", children: [
    /* @__PURE__ */ jsxDEV("span", { children: text("error_loading", message) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 323,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-outline-danger btn-sm", onClick: onRetry, children: text("try_again") }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 324,
      columnNumber: 9
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
    lineNumber: 322,
    columnNumber: 12
  }, this);
}
__name(ListError, "ListError");
function ListBody({ error, empty, isEmpty, onRetry, text, children }) {
  if (error) {
    const message = error instanceof Error ? error.message : String(error?.message ?? error);
    return /* @__PURE__ */ jsxDEV(ListError, { message, onRetry, text }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 344,
      columnNumber: 16
    }, this);
  }
  return /* @__PURE__ */ jsxDEV(Fragment, { children: isEmpty ? empty : children }, void 0, false, {
    fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
    lineNumber: 346,
    columnNumber: 12
  }, this);
}
__name(ListBody, "ListBody");
function SortHeader({ column, label, sort, direction, onSort, numeric = false }) {
  const sorted = sort === column;
  const ariasort = direction === "desc" ? "descending" : "ascending";
  return /* @__PURE__ */ jsxDEV("th", { scope: "col", className: numeric ? "text-end" : void 0, "aria-sort": sorted ? ariasort : void 0, children: /* @__PURE__ */ jsxDEV(
    "button",
    {
      type: "button",
      className: "table-sort",
      onClick: () => onSort(column, sorted && direction === "asc" ? "desc" : "asc"),
      children: label
    },
    void 0,
    false,
    {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 366,
      columnNumber: 9
    },
    this
  ) }, void 0, false, {
    fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
    lineNumber: 365,
    columnNumber: 12
  }, this);
}
__name(SortHeader, "SortHeader");
function SkeletonRows({ columns, rows = 5 }) {
  return /* @__PURE__ */ jsxDEV(Fragment, { children: Array.from({ length: rows }, (_, row) => /* @__PURE__ */ jsxDEV("tr", { "aria-hidden": "true", children: Array.from({ length: columns }, (__, cell) => /* @__PURE__ */ jsxDEV("td", { children: /* @__PURE__ */ jsxDEV("span", { className: "dd-skeleton" }, void 0, false, {
    fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
    lineNumber: 379,
    columnNumber: 69
  }, this) }, cell, false, {
    fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
    lineNumber: 379,
    columnNumber: 54
  }, this)) }, row, false, {
    fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
    lineNumber: 378,
    columnNumber: 54
  }, this)) }, void 0, false, {
    fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
    lineNumber: 378,
    columnNumber: 12
  }, this);
}
__name(SkeletonRows, "SkeletonRows");
function Segmented({ options, value, onChange, label }) {
  const group = useRef(null);
  const keys = /* @__PURE__ */ __name((event) => {
    if (!["ArrowRight", "ArrowDown", "ArrowLeft", "ArrowUp"].includes(event.key)) {
      return;
    }
    event.preventDefault();
    const enabled = options.filter((option) => !option.disabled);
    const at = enabled.findIndex((option) => option.value === value);
    const step = ["ArrowRight", "ArrowDown"].includes(event.key) ? 1 : -1;
    const next = enabled[(at + step + enabled.length) % enabled.length];
    onChange(next.value);
    window.requestAnimationFrame(() => group.current?.querySelector('[aria-checked="true"]')?.focus());
  }, "keys");
  return /* @__PURE__ */ jsxDEV("span", { ref: group, className: "dd-segmented", role: "radiogroup", "aria-label": label, onKeyDown: keys, children: options.map((option) => /* @__PURE__ */ jsxDEV(
    "button",
    {
      type: "button",
      role: "radio",
      "data-permission": option.value,
      "data-tone": option.tone,
      "aria-checked": option.value === value,
      tabIndex: option.value === value ? 0 : -1,
      disabled: option.disabled,
      title: option.title,
      onClick: () => onChange(option.value),
      children: option.label
    },
    String(option.value),
    false,
    {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 409,
      columnNumber: 34
    },
    this
  )) }, void 0, false, {
    fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
    lineNumber: 408,
    columnNumber: 12
  }, this);
}
__name(Segmented, "Segmented");
function FloatBar({ count, offerAll, total, onAll, onClear, label, children, text }) {
  if (count === 0) {
    return null;
  }
  return /* @__PURE__ */ jsxDEV("div", { className: "dd-floatbar", role: "region", "aria-label": label, "data-region": "floatbar", children: [
    /* @__PURE__ */ jsxDEV("span", { className: "dd-floatbar-count", role: "status", children: text("selected_count", count) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 437,
      columnNumber: 9
    }, this),
    offerAll && /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-link dd-floatbar-all", onClick: onAll, children: text("select_all_matching", total) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 438,
      columnNumber: 22
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "dd-floatbar-actions", children }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 441,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV(
      "button",
      {
        type: "button",
        className: "dd-floatbar-clear",
        title: text("clear_selection"),
        "aria-label": text("clear_selection"),
        onClick: onClear
      },
      void 0,
      false,
      {
        fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
        lineNumber: 442,
        columnNumber: 9
      },
      this
    )
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
    lineNumber: 436,
    columnNumber: 12
  }, this);
}
__name(FloatBar, "FloatBar");
function Dialog({ title, children, footer, onClose, size = "lg", initialFocus }) {
  const root = useRef(null);
  const titleid = useId();
  useEffect(() => {
    const previous = document.activeElement;
    document.body.classList.add("modal-open");
    const first = initialFocus ? root.current?.querySelector(initialFocus) : null;
    (first ?? root.current?.querySelector('input, button:not(.btn-close), [tabindex="0"]'))?.focus();
    return () => {
      document.body.classList.remove("modal-open");
      previous?.focus();
    };
  }, []);
  const keys = /* @__PURE__ */ __name((event) => {
    if (event.key === "Escape") {
      event.stopPropagation();
      onClose();
      return;
    }
    if (event.key !== "Tab" || !root.current) {
      return;
    }
    const focusable = Array.from(root.current.querySelectorAll(
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
  }, "keys");
  return createPortal(/* @__PURE__ */ jsxDEV(Fragment, { children: [
    /* @__PURE__ */ jsxDEV(
      "div",
      {
        ref: root,
        className: "modal show d-block",
        role: "dialog",
        "aria-modal": "true",
        "aria-labelledby": titleid,
        tabIndex: -1,
        onKeyDown: keys,
        onMouseDown: (event) => event.target === event.currentTarget && onClose(),
        children: /* @__PURE__ */ jsxDEV("div", { className: `modal-dialog${size === "md" ? "" : ` modal-${size}`} modal-dialog-scrollable`, children: /* @__PURE__ */ jsxDEV("div", { className: "modal-content", children: [
          /* @__PURE__ */ jsxDEV("div", { className: "modal-header", children: [
            /* @__PURE__ */ jsxDEV("h5", { className: "modal-title", id: titleid, children: title }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
              lineNumber: 503,
              columnNumber: 25
            }, this),
            /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn-close", "aria-label": "\xD7", onClick: onClose }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
              lineNumber: 504,
              columnNumber: 25
            }, this)
          ] }, void 0, true, {
            fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
            lineNumber: 502,
            columnNumber: 21
          }, this),
          /* @__PURE__ */ jsxDEV("div", { className: "modal-body", children }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
            lineNumber: 506,
            columnNumber: 21
          }, this),
          /* @__PURE__ */ jsxDEV("div", { className: "modal-footer", children: footer }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
            lineNumber: 507,
            columnNumber: 21
          }, this)
        ] }, void 0, true, {
          fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
          lineNumber: 501,
          columnNumber: 17
        }, this) }, void 0, false, {
          fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
          lineNumber: 500,
          columnNumber: 13
        }, this)
      },
      void 0,
      false,
      {
        fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
        lineNumber: 498,
        columnNumber: 9
      },
      this
    ),
    /* @__PURE__ */ jsxDEV("div", { className: "modal-backdrop show" }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 511,
      columnNumber: 9
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
    lineNumber: 497,
    columnNumber: 25
  }, this), document.body);
}
__name(Dialog, "Dialog");
function ConfirmDialog({ title, message, button, onConfirm, onClose, text, strings }) {
  const [error, setError] = useState("");
  const [working, setWorking] = useState(false);
  const confirm = /* @__PURE__ */ __name(async () => {
    setWorking(true);
    setError("");
    try {
      await onConfirm();
      onClose();
    } catch (failure) {
      setError(failure instanceof Error ? failure.message : String(failure?.message ?? failure));
      setWorking(false);
    }
  }, "confirm");
  return /* @__PURE__ */ jsxDEV(Dialog, { title, size: "md", onClose, initialFocus: "[data-action='cancel']", footer: /* @__PURE__ */ jsxDEV(Fragment, { children: [
    /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-secondary", "data-action": "cancel", onClick: onClose, children: text("core:cancel") }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 545,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-danger", disabled: working, onClick: confirm, children: button }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 546,
      columnNumber: 9
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
    lineNumber: 544,
    columnNumber: 108
  }, this), children: [
    /* @__PURE__ */ jsxDEV("div", { className: "d-flex align-items-start", children: [
      /* @__PURE__ */ jsxDEV("span", { className: "dd-tile dd-tile-danger me-3", children: /* @__PURE__ */ jsxDEV(Icon, { name: "t/delete", strings }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
        lineNumber: 549,
        columnNumber: 59
      }, this) }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
        lineNumber: 549,
        columnNumber: 13
      }, this),
      /* @__PURE__ */ jsxDEV("p", { className: "mb-0", children: message }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
        lineNumber: 550,
        columnNumber: 13
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 548,
      columnNumber: 9
    }, this),
    error && /* @__PURE__ */ jsxDEV("div", { className: "alert alert-danger mt-3 mb-0", role: "alert", children: error }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
      lineNumber: 552,
      columnNumber: 19
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/components/ui.tsx",
    lineNumber: 544,
    columnNumber: 12
  }, this);
}
__name(ConfirmDialog, "ConfirmDialog");
export {
  Avatar,
  Chips,
  ConfirmDialog,
  Dialog,
  Empty,
  FloatBar,
  Icon,
  ListBody,
  ListError,
  Pager,
  Person,
  SearchBox,
  Segmented,
  SelectMenu,
  SkeletonRows,
  SortHeader
};
//# sourceMappingURL=ui.dev.js.map
