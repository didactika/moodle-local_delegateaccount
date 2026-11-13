var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The people of the management page: the ones who may use delegated accounts, or the ones who lost the permission but keep
 * delegations, with the delegations each one has.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_delegateaccount/PeopleList
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useRef, useState } from "react";
import { Chips, Empty, Icon, ListBody, Pager, Person, SearchBox, SkeletonRows, SortHeader, useStableColumns } from "./components/ui";
import { useList, useDebounced } from "./hooks/useList";
import { useText } from "./engine/text";
import { DEFAULT_PAGE_SIZE } from "./engine/paging";
import { openCreate } from "./CreateButton";
import { getUserList } from "./services/api";
const CHIPS = [
  { key: "", label: "perm_filter_all" },
  { key: "active", label: "delegation_status_active" },
  { key: "scheduled", label: "delegation_status_scheduled" },
  { key: "expired", label: "delegation_status_expired" },
  { key: "revoked", label: "delegation_status_revoked" },
  { key: "none", label: "no_delegations" }
];
function PeopleList({ strings, scope, initial }) {
  const text = useText(strings);
  const list = useList(
    { search: "", status: "", sort: "name", direction: "asc", page: 0, perpage: DEFAULT_PAGE_SIZE },
    (state2) => getUserList(scope, String(state2.search), String(state2.status), {
      sort: String(state2.sort),
      direction: String(state2.direction),
      page: Number(state2.page),
      perpage: Number(state2.perpage)
    }),
    initial
  );
  const { state, page } = list;
  const table = useRef(null);
  useStableColumns(table, Boolean(page && page.rows.length > 0));
  const [search, setSearch] = useState(String(state.search));
  const typed = useDebounced(search.trim(), 300);
  useEffect(() => {
    list.update({ search: typed });
  }, [typed]);
  useEffect(() => {
    const created = /* @__PURE__ */ __name(() => list.refresh(), "created");
    document.addEventListener("local_delegateaccount:created", created);
    return () => document.removeEventListener("local_delegateaccount:created", created);
  }, []);
  const sort = /* @__PURE__ */ __name((column, direction) => list.update({ sort: column, direction, page: 0 }), "sort");
  const headers = { sort: String(state.sort), direction: String(state.direction), onSort: sort };
  const prefix = scope === "historical" ? "empty_historical_" : "empty_authorised_";
  const empty = /* @__PURE__ */ jsxDEV(
    Empty,
    {
      filtered: Boolean(state.search) || Boolean(state.status),
      icon: "i/users",
      text,
      strings,
      title: text(`${prefix}title`),
      message: text(`${prefix}text`),
      onClear: () => {
        setSearch("");
        list.update({ search: "", status: "" });
      }
    },
    void 0,
    false,
    {
      fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
      lineNumber: 68,
      columnNumber: 19
    },
    this
  );
  return /* @__PURE__ */ jsxDEV("div", { className: "d-grid gap-3", children: [
    /* @__PURE__ */ jsxDEV(
      Chips,
      {
        label: text("filter_by_status"),
        value: String(state.status),
        counts: page?.counts,
        chips: CHIPS.map(({ key, label }) => ({ key, label: text(label) })),
        onChange: (status) => list.update({ status })
      },
      void 0,
      false,
      {
        fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
        lineNumber: 76,
        columnNumber: 9
      },
      this
    ),
    /* @__PURE__ */ jsxDEV("div", { className: "card", children: [
      /* @__PURE__ */ jsxDEV("div", { className: "table-toolbar", children: /* @__PURE__ */ jsxDEV("div", { className: "table-toolbar-start", children: /* @__PURE__ */ jsxDEV(SearchBox, { strings, value: search, onChange: setSearch, label: text("search_people") }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
        lineNumber: 81,
        columnNumber: 21
      }, this) }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
        lineNumber: 80,
        columnNumber: 17
      }, this) }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
        lineNumber: 79,
        columnNumber: 13
      }, this),
      /* @__PURE__ */ jsxDEV(
        ListBody,
        {
          error: list.error,
          onRetry: list.refresh,
          text,
          empty,
          isEmpty: Boolean(page && page.total === 0 && !list.skeleton),
          children: /* @__PURE__ */ jsxDEV("div", { className: "table-responsive", "data-region": "tablewrap", "aria-busy": list.busy || void 0, children: /* @__PURE__ */ jsxDEV("table", { className: "table", ref: table, children: [
            /* @__PURE__ */ jsxDEV("caption", { className: "visually-hidden", children: text("list_caption_people") }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
              lineNumber: 88,
              columnNumber: 25
            }, this),
            /* @__PURE__ */ jsxDEV("thead", { children: /* @__PURE__ */ jsxDEV("tr", { children: [
              /* @__PURE__ */ jsxDEV(SortHeader, { column: "name", label: text("authoriseduser"), ...headers }, void 0, false, {
                fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
                lineNumber: 91,
                columnNumber: 33
              }, this),
              /* @__PURE__ */ jsxDEV(SortHeader, { column: "active", label: text("active_delegations"), numeric: true, ...headers }, void 0, false, {
                fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
                lineNumber: 92,
                columnNumber: 33
              }, this),
              /* @__PURE__ */ jsxDEV(SortHeader, { column: "scheduled", label: text("scheduled_delegations"), numeric: true, ...headers }, void 0, false, {
                fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
                lineNumber: 93,
                columnNumber: 33
              }, this),
              /* @__PURE__ */ jsxDEV("th", { scope: "col", className: "table-actions", children: /* @__PURE__ */ jsxDEV("span", { className: "visually-hidden", children: text("core:actions") }, void 0, false, {
                fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
                lineNumber: 95,
                columnNumber: 37
              }, this) }, void 0, false, {
                fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
                lineNumber: 94,
                columnNumber: 33
              }, this)
            ] }, void 0, true, {
              fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
              lineNumber: 90,
              columnNumber: 29
            }, this) }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
              lineNumber: 89,
              columnNumber: 25
            }, this),
            /* @__PURE__ */ jsxDEV("tbody", { children: list.skeleton || !page ? /* @__PURE__ */ jsxDEV(SkeletonRows, { columns: 4 }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
              lineNumber: 100,
              columnNumber: 55
            }, this) : page.rows.map((row) => /* @__PURE__ */ jsxDEV("tr", { children: [
              /* @__PURE__ */ jsxDEV("th", { scope: "row", children: /* @__PURE__ */ jsxDEV(Person, { user: row.user }, void 0, false, {
                fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
                lineNumber: 101,
                columnNumber: 49
              }, this) }, void 0, false, {
                fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
                lineNumber: 101,
                columnNumber: 33
              }, this),
              /* @__PURE__ */ jsxDEV("td", { className: "text-end", children: row.activecount }, void 0, false, {
                fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
                lineNumber: 102,
                columnNumber: 33
              }, this),
              /* @__PURE__ */ jsxDEV("td", { className: "text-end", children: row.scheduledcount }, void 0, false, {
                fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
                lineNumber: 103,
                columnNumber: 33
              }, this),
              /* @__PURE__ */ jsxDEV("td", { className: "table-actions", children: row.cancreate && /* @__PURE__ */ jsxDEV(
                "button",
                {
                  type: "button",
                  className: "btn btn-link btn-icon btn-sm text-primary",
                  title: row.addlabel,
                  "aria-label": row.addlabel,
                  onClick: (event) => void openCreate(row.user.id, event.currentTarget, text),
                  children: /* @__PURE__ */ jsxDEV(Icon, { name: "t/add", strings }, void 0, false, {
                    fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
                    lineNumber: 108,
                    columnNumber: 41
                  }, this)
                },
                void 0,
                false,
                {
                  fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
                  lineNumber: 105,
                  columnNumber: 55
                },
                this
              ) }, void 0, false, {
                fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
                lineNumber: 104,
                columnNumber: 33
              }, this)
            ] }, row.user.id, true, {
              fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
              lineNumber: 100,
              columnNumber: 109
            }, this)) }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
              lineNumber: 99,
              columnNumber: 25
            }, this)
          ] }, void 0, true, {
            fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
            lineNumber: 87,
            columnNumber: 21
          }, this) }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
            lineNumber: 86,
            columnNumber: 17
          }, this)
        },
        void 0,
        false,
        {
          fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
          lineNumber: 84,
          columnNumber: 13
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
          fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
          lineNumber: 116,
          columnNumber: 55
        },
        this
      )
    ] }, void 0, true, {
      fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
      lineNumber: 78,
      columnNumber: 9
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
    lineNumber: 75,
    columnNumber: 12
  }, this);
}
__name(PeopleList, "PeopleList");
export {
  PeopleList as default
};
//# sourceMappingURL=PeopleList.dev.js.map
