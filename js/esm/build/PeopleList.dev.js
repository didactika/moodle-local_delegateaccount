var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The people of the management page: who may use delegated accounts, or who keeps delegations without that permission. Each
 * row says which accounts the person is using now and what changes next in their delegations, the end of one in use or the start
 * of one that is scheduled. The list is searched, narrowed by the status of the delegations of each person, sorted and paged.
 *
 * @module     local_delegateaccount/PeopleList
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useRef, useState } from "react";
import { Avatar, Empty, Icon, ListBody, Pager, Person, SearchBox, SkeletonRows, SortHeader, useFitHeight, useStableColumns } from "./components/ui";
import StatusMenu from "./components/StatusMenu";
import { useList, useDebounced } from "./hooks/useList";
import { useText } from "./engine/text";
import { DEFAULT_PAGE_SIZE } from "./engine/paging";
import { openCreate } from "./CreateButton";
import { getUserList } from "./services/api";
const STATUSES = [
  { key: "active", label: "delegation_status_active" },
  { key: "scheduled", label: "delegation_status_scheduled" },
  { key: "expired", label: "delegation_status_expired" },
  { key: "revoked", label: "delegation_status_revoked" },
  { key: "none", label: "no_delegations" }
];
function Accounts({ row, text }) {
  if (row.accounts.length === 0) {
    return /* @__PURE__ */ jsxDEV("span", { className: "text-muted", children: text("current_accounts_none") }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
      lineNumber: 41,
      columnNumber: 16
    }, this);
  }
  return /* @__PURE__ */ jsxDEV("span", { className: "d-inline-flex align-items-center", "data-region": "accounts", title: row.accountnames, children: [
    row.accounts.map((account) => /* @__PURE__ */ jsxDEV(Avatar, { user: account, size: 30 }, account.id, false, {
      fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
      lineNumber: 44,
      columnNumber: 40
    }, this)),
    row.moreaccounts > 0 && /* @__PURE__ */ jsxDEV("span", { className: "userinitials size-30", children: text("current_accounts_more", row.moreaccounts) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
      lineNumber: 45,
      columnNumber: 34
    }, this),
    /* @__PURE__ */ jsxDEV("span", { className: "visually-hidden", children: row.accountnames }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
      lineNumber: 46,
      columnNumber: 9
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
    lineNumber: 43,
    columnNumber: 12
  }, this);
}
__name(Accounts, "Accounts");
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
  const fitbox = useRef(null);
  useFitHeight(fitbox);
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
  const chosen = String(state.status).split(",").filter(Boolean);
  const filtered = Boolean(state.search) || chosen.length > 0;
  const reset = /* @__PURE__ */ __name(() => {
    setSearch("");
    list.update({ search: "", status: "" });
  }, "reset");
  const sort = /* @__PURE__ */ __name((column, direction) => list.update({ sort: column, direction, page: 0 }), "sort");
  const headers = { sort: String(state.sort), direction: String(state.direction), onSort: sort };
  const prefix = scope === "historical" ? "empty_historical_" : "empty_authorised_";
  const empty = /* @__PURE__ */ jsxDEV(
    Empty,
    {
      filtered,
      icon: "i/users",
      text,
      strings,
      title: text(`${prefix}title`),
      message: text(`${prefix}text`),
      onClear: reset
    },
    void 0,
    false,
    {
      fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
      lineNumber: 93,
      columnNumber: 19
    },
    this
  );
  return /* @__PURE__ */ jsxDEV("div", { className: "card", children: [
    /* @__PURE__ */ jsxDEV("div", { className: "d-flex align-items-center justify-content-between gap-3 px-3 py-2 border-bottom", "data-region": "filters", children: [
      /* @__PURE__ */ jsxDEV("div", { className: "d-flex align-items-center gap-2 flex-grow-1", children: [
        /* @__PURE__ */ jsxDEV(SearchBox, { strings, value: search, onChange: setSearch, label: text("search_people") }, void 0, false, {
          fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
          lineNumber: 99,
          columnNumber: 17
        }, this),
        /* @__PURE__ */ jsxDEV(
          StatusMenu,
          {
            label: text("core:status"),
            chosen,
            text,
            strings,
            options: STATUSES.map(({ key, label }) => ({ key, label: text(label), count: page?.counts?.[key] })),
            onChange: (statuses) => list.update({ status: statuses.join(","), page: 0 })
          },
          void 0,
          false,
          {
            fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
            lineNumber: 100,
            columnNumber: 17
          },
          this
        )
      ] }, void 0, true, {
        fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
        lineNumber: 98,
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
            fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
            lineNumber: 106,
            columnNumber: 72
          }, this)
        },
        void 0,
        false,
        {
          fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
          lineNumber: 105,
          columnNumber: 17
        },
        this
      ) }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
        lineNumber: 104,
        columnNumber: 13
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
      lineNumber: 97,
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
          /* @__PURE__ */ jsxDEV("caption", { className: "visually-hidden", children: text("list_caption_people") }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
            lineNumber: 113,
            columnNumber: 21
          }, this),
          /* @__PURE__ */ jsxDEV("thead", { children: /* @__PURE__ */ jsxDEV("tr", { children: [
            /* @__PURE__ */ jsxDEV(SortHeader, { column: "name", label: text("authoriseduser"), ...headers }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
              lineNumber: 116,
              columnNumber: 29
            }, this),
            /* @__PURE__ */ jsxDEV(SortHeader, { column: "accounts", label: text("current_accounts"), ...headers }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
              lineNumber: 117,
              columnNumber: 29
            }, this),
            /* @__PURE__ */ jsxDEV(SortHeader, { column: "next", label: text("next_change"), ...headers }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
              lineNumber: 118,
              columnNumber: 29
            }, this),
            /* @__PURE__ */ jsxDEV("th", { scope: "col", className: "text-end text-nowrap", "data-column": "actions", children: /* @__PURE__ */ jsxDEV("span", { className: "visually-hidden", children: text("core:actions") }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
              lineNumber: 120,
              columnNumber: 33
            }, this) }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
              lineNumber: 119,
              columnNumber: 29
            }, this)
          ] }, void 0, true, {
            fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
            lineNumber: 115,
            columnNumber: 25
          }, this) }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
            lineNumber: 114,
            columnNumber: 21
          }, this),
          /* @__PURE__ */ jsxDEV("tbody", { children: list.skeleton || !page ? /* @__PURE__ */ jsxDEV(SkeletonRows, { columns: 4 }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
            lineNumber: 125,
            columnNumber: 51
          }, this) : page.rows.map((row) => /* @__PURE__ */ jsxDEV("tr", { children: [
            /* @__PURE__ */ jsxDEV("th", { scope: "row", children: /* @__PURE__ */ jsxDEV(Person, { user: row.user }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
              lineNumber: 126,
              columnNumber: 45
            }, this) }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
              lineNumber: 126,
              columnNumber: 29
            }, this),
            /* @__PURE__ */ jsxDEV("td", { children: /* @__PURE__ */ jsxDEV(Accounts, { row, text }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
              lineNumber: 127,
              columnNumber: 33
            }, this) }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
              lineNumber: 127,
              columnNumber: 29
            }, this),
            /* @__PURE__ */ jsxDEV("td", { children: row.next.kind ? /* @__PURE__ */ jsxDEV("span", { className: "d-flex flex-column", children: [
              /* @__PURE__ */ jsxDEV("span", { className: `badge rounded-pill text-bg-${row.next.tone} align-self-start`, children: row.next.label }, void 0, false, {
                fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
                lineNumber: 131,
                columnNumber: 41
              }, this),
              /* @__PURE__ */ jsxDEV("span", { className: "small text-muted", children: row.next.relative }, void 0, false, {
                fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
                lineNumber: 134,
                columnNumber: 41
              }, this)
            ] }, void 0, true, {
              fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
              lineNumber: 130,
              columnNumber: 39
            }, this) : /* @__PURE__ */ jsxDEV("span", { className: "text-muted", children: row.next.label }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
              lineNumber: 136,
              columnNumber: 39
            }, this) }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
              lineNumber: 128,
              columnNumber: 29
            }, this),
            /* @__PURE__ */ jsxDEV("td", { className: "text-end text-nowrap", "data-column": "actions", children: row.cancreate && /* @__PURE__ */ jsxDEV(
              "button",
              {
                type: "button",
                className: "btn btn-link btn-icon btn-sm text-primary",
                title: row.addlabel,
                "aria-label": row.addlabel,
                onClick: (event) => void openCreate(row.user.id, event.currentTarget, text),
                children: /* @__PURE__ */ jsxDEV(Icon, { name: "t/add", strings }, void 0, false, {
                  fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
                  lineNumber: 142,
                  columnNumber: 37
                }, this)
              },
              void 0,
              false,
              {
                fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
                lineNumber: 139,
                columnNumber: 51
              },
              this
            ) }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
              lineNumber: 138,
              columnNumber: 29
            }, this)
          ] }, row.user.id, true, {
            fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
            lineNumber: 125,
            columnNumber: 105
          }, this)) }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
            lineNumber: 124,
            columnNumber: 21
          }, this)
        ] }, void 0, true, {
          fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
          lineNumber: 112,
          columnNumber: 17
        }, this) }, void 0, false, {
          fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
          lineNumber: 111,
          columnNumber: 13
        }, this)
      },
      void 0,
      false,
      {
        fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
        lineNumber: 109,
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
        fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
        lineNumber: 150,
        columnNumber: 51
      },
      this
    )
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/PeopleList.tsx",
    lineNumber: 96,
    columnNumber: 12
  }, this);
}
__name(PeopleList, "PeopleList");
export {
  PeopleList as default
};
//# sourceMappingURL=PeopleList.dev.js.map
