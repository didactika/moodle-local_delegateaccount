var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * What was done through one delegation, newest first, narrowed by a search (words of the component, the action or the event)
 * and by a menu of the date, as every list of the design system.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_delegateaccount/ActivityList
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useRef, useState } from "react";
import { Empty, Icon, ListBody, Pager, SearchBox, SkeletonRows, useFitHeight, useStableColumns } from "./components/ui";
import DateMenu from "./components/DateMenu";
import { useList, useDebounced } from "./hooks/useList";
import { useText } from "./engine/text";
import { DEFAULT_PAGE_SIZE } from "./engine/paging";
import { getActivityList } from "./services/api";
const FILTERS = ["datefrom", "dateto", "search"];
function ActivityList({ strings, delegationid, mindate, maxdate }) {
  const text = useText(strings);
  const list = useList(
    { datefrom: "", dateto: "", search: "", page: 0, perpage: DEFAULT_PAGE_SIZE },
    (state2) => getActivityList({
      delegationid,
      datefrom: String(state2.datefrom),
      dateto: String(state2.dateto),
      search: String(state2.search),
      page: Number(state2.page),
      perpage: Number(state2.perpage)
    })
  );
  const { state, page } = list;
  const table = useRef(null);
  const fitbox = useRef(null);
  useFitHeight(fitbox);
  useStableColumns(table, Boolean(page && page.rows.length > 0));
  const [search, setSearch] = useState(String(state.search));
  const typed = useDebounced(search, 300);
  useEffect(() => {
    list.update({ search: typed.trim() });
  }, [typed]);
  const reset = /* @__PURE__ */ __name(() => {
    setSearch("");
    list.update({ search: "", datefrom: "", dateto: "" });
  }, "reset");
  const filtered = FILTERS.some((name) => state[name] !== "");
  const empty = /* @__PURE__ */ jsxDEV(
    Empty,
    {
      filtered,
      icon: "i/log",
      title: text("activity_empty_title"),
      text,
      strings,
      message: page?.haslog === false ? text("activity_nolog") : text("activity_empty_text"),
      onClear: reset
    },
    void 0,
    false,
    {
      fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
      lineNumber: 61,
      columnNumber: 19
    },
    this
  );
  return /* @__PURE__ */ jsxDEV("div", { className: "card", children: [
    /* @__PURE__ */ jsxDEV("div", { className: "d-flex align-items-center justify-content-between gap-3 px-3 py-2 border-bottom", "data-region": "filters", children: [
      /* @__PURE__ */ jsxDEV("div", { className: "d-flex align-items-center gap-2 flex-grow-1", children: [
        /* @__PURE__ */ jsxDEV(SearchBox, { strings, value: search, onChange: setSearch, label: text("activity_search") }, void 0, false, {
          fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
          lineNumber: 67,
          columnNumber: 17
        }, this),
        /* @__PURE__ */ jsxDEV("div", { "data-region": "filter-menus", children: /* @__PURE__ */ jsxDEV(
          DateMenu,
          {
            label: text("activity_filter_date"),
            from: String(state.datefrom),
            to: String(state.dateto),
            min: mindate,
            max: maxdate,
            text,
            strings,
            onChange: (datefrom, dateto) => list.update({ datefrom, dateto })
          },
          void 0,
          false,
          {
            fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
            lineNumber: 69,
            columnNumber: 21
          },
          this
        ) }, void 0, false, {
          fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
          lineNumber: 68,
          columnNumber: 17
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
        lineNumber: 66,
        columnNumber: 13
      }, this),
      /* @__PURE__ */ jsxDEV("div", { className: "d-flex align-items-center gap-2 flex-shrink-0", children: /* @__PURE__ */ jsxDEV(
        "button",
        {
          type: "button",
          className: "btn btn-link btn-icon",
          disabled: !filtered,
          title: text("clear_filters"),
          "aria-label": text("clear_filters"),
          onClick: reset,
          children: /* @__PURE__ */ jsxDEV(Icon, { name: "i/reload", strings }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
            lineNumber: 75,
            columnNumber: 72
          }, this)
        },
        void 0,
        false,
        {
          fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
          lineNumber: 74,
          columnNumber: 17
        },
        this
      ) }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
        lineNumber: 73,
        columnNumber: 13
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
      lineNumber: 65,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV(
      ListBody,
      {
        error: list.error,
        onRetry: list.refresh,
        text,
        empty,
        isEmpty: Boolean(page && page.total === 0 && !list.skeleton),
        children: /* @__PURE__ */ jsxDEV("div", { ref: fitbox, className: "table-responsive", "data-region": "tablewrap", "aria-busy": list.busy || void 0, children: /* @__PURE__ */ jsxDEV("table", { className: "table", ref: table, children: [
          /* @__PURE__ */ jsxDEV("caption", { className: "visually-hidden", children: text("list_caption_activity") }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
            lineNumber: 82,
            columnNumber: 25
          }, this),
          /* @__PURE__ */ jsxDEV("thead", { children: /* @__PURE__ */ jsxDEV("tr", { children: [
            /* @__PURE__ */ jsxDEV("th", { scope: "col", children: text("core:time") }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
              lineNumber: 85,
              columnNumber: 33
            }, this),
            /* @__PURE__ */ jsxDEV("th", { scope: "col", children: text("activity_event") }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
              lineNumber: 86,
              columnNumber: 33
            }, this),
            /* @__PURE__ */ jsxDEV("th", { scope: "col", children: text("activity_context") }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
              lineNumber: 87,
              columnNumber: 33
            }, this),
            /* @__PURE__ */ jsxDEV("th", { scope: "col", children: text("activity_related") }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
              lineNumber: 88,
              columnNumber: 33
            }, this),
            /* @__PURE__ */ jsxDEV("th", { scope: "col", children: text("activity_ip") }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
              lineNumber: 89,
              columnNumber: 33
            }, this)
          ] }, void 0, true, {
            fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
            lineNumber: 84,
            columnNumber: 29
          }, this) }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
            lineNumber: 83,
            columnNumber: 25
          }, this),
          /* @__PURE__ */ jsxDEV("tbody", { children: list.skeleton || !page ? /* @__PURE__ */ jsxDEV(SkeletonRows, { columns: 5 }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
            lineNumber: 93,
            columnNumber: 55
          }, this) : page.rows.map((row) => /* @__PURE__ */ jsxDEV("tr", { children: [
            /* @__PURE__ */ jsxDEV("td", { className: "text-nowrap", children: row.time }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
              lineNumber: 94,
              columnNumber: 33
            }, this),
            /* @__PURE__ */ jsxDEV("th", { scope: "row", children: /* @__PURE__ */ jsxDEV("span", { className: "d-flex flex-column", children: [
              row.eventurl ? /* @__PURE__ */ jsxDEV("a", { href: row.eventurl, children: row.eventname }, void 0, false, {
                fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
                lineNumber: 97,
                columnNumber: 57
              }, this) : row.eventname,
              /* @__PURE__ */ jsxDEV("span", { className: "small text-muted text-truncate", title: row.description, children: [
                row.component,
                " \xB7 ",
                row.description
              ] }, void 0, true, {
                fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
                lineNumber: 98,
                columnNumber: 41
              }, this)
            ] }, void 0, true, {
              fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
              lineNumber: 96,
              columnNumber: 37
            }, this) }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
              lineNumber: 95,
              columnNumber: 33
            }, this),
            /* @__PURE__ */ jsxDEV("td", { children: row.contexturl ? /* @__PURE__ */ jsxDEV("a", { href: row.contexturl, children: row.contextname }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
              lineNumber: 103,
              columnNumber: 55
            }, this) : row.contextname }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
              lineNumber: 103,
              columnNumber: 33
            }, this),
            /* @__PURE__ */ jsxDEV("td", { children: row.relateduser || "-" }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
              lineNumber: 104,
              columnNumber: 33
            }, this),
            /* @__PURE__ */ jsxDEV("td", { className: "text-nowrap", children: row.ip || "-" }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
              lineNumber: 105,
              columnNumber: 33
            }, this)
          ] }, row.id, true, {
            fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
            lineNumber: 93,
            columnNumber: 109
          }, this)) }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
            lineNumber: 92,
            columnNumber: 25
          }, this)
        ] }, void 0, true, {
          fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
          lineNumber: 81,
          columnNumber: 21
        }, this) }, void 0, false, {
          fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
          lineNumber: 80,
          columnNumber: 17
        }, this)
      },
      void 0,
      false,
      {
        fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
        lineNumber: 78,
        columnNumber: 9
      },
      this
    ),
    page && page.total > 0 && !list.error && /* @__PURE__ */ jsxDEV(
      Pager,
      {
        page: Number(state.page),
        perpage: Number(state.perpage),
        total: page.total,
        onChange: list.update,
        text,
        strings
      },
      void 0,
      false,
      {
        fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
        lineNumber: 111,
        columnNumber: 51
      },
      this
    )
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/ActivityList.tsx",
    lineNumber: 64,
    columnNumber: 12
  }, this);
}
__name(ActivityList, "ActivityList");
export {
  ActivityList as default
};
//# sourceMappingURL=ActivityList.dev.js.map
