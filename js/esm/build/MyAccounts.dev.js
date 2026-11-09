var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The accounts a person may open now, as cards, searched as they type. The first page comes with the page, so a guided tour
 * finds the cards from the start.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_delegateaccount/MyAccounts
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useState } from "react";
import { Avatar, Empty, Icon, ListBody, Pager, SearchBox } from "./components/ui";
import { useList, useDebounced } from "./hooks/useList";
import { useText } from "./engine/text";
import { getMyAccounts } from "./services/api";
function MyAccounts({ strings, id, initial }) {
  const text = useText(strings);
  const list = useList(
    { search: "", page: 0, perpage: 20 },
    (state2) => getMyAccounts(String(state2.search), { page: Number(state2.page), perpage: Number(state2.perpage) }),
    initial
  );
  const { state, page } = list;
  const [search, setSearch] = useState(String(state.search));
  const typed = useDebounced(search.trim(), 300);
  useEffect(() => {
    list.update({ search: typed });
  }, [typed]);
  return /* @__PURE__ */ jsxDEV("section", { id, className: "d-grid gap-3", children: [
    /* @__PURE__ */ jsxDEV("div", { className: "table-toolbar px-0 border-0", children: /* @__PURE__ */ jsxDEV("div", { className: "table-toolbar-start", children: /* @__PURE__ */ jsxDEV(SearchBox, { strings, value: search, onChange: setSearch, label: text("search_accounts") }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/MyAccounts.tsx",
      lineNumber: 44,
      columnNumber: 17
    }, this) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/MyAccounts.tsx",
      lineNumber: 43,
      columnNumber: 13
    }, this) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/MyAccounts.tsx",
      lineNumber: 42,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV(
      ListBody,
      {
        error: list.error,
        onRetry: list.refresh,
        text,
        isEmpty: Boolean(page && page.total === 0),
        empty: /* @__PURE__ */ jsxDEV("div", { className: "card", children: /* @__PURE__ */ jsxDEV(
          Empty,
          {
            filtered: Boolean(state.search),
            icon: "i/switch",
            text,
            strings,
            title: text("empty_mine_title"),
            message: text("empty_mine_text"),
            onClear: () => setSearch("")
          },
          void 0,
          false,
          {
            fileName: "public/local/delegateaccount/js/esm/src/MyAccounts.tsx",
            lineNumber: 48,
            columnNumber: 42
          },
          this
        ) }, void 0, false, {
          fileName: "public/local/delegateaccount/js/esm/src/MyAccounts.tsx",
          lineNumber: 48,
          columnNumber: 20
        }, this),
        children: /* @__PURE__ */ jsxDEV("ul", { className: "row list-unstyled mb-0", "aria-busy": list.busy || void 0, children: (page?.rows ?? []).map((row) => /* @__PURE__ */ jsxDEV("li", { className: "col-sm-6 col-xl-4 mb-3", children: /* @__PURE__ */ jsxDEV("div", { className: "card h-100", children: /* @__PURE__ */ jsxDEV("div", { className: "card-body d-flex flex-column", children: [
          /* @__PURE__ */ jsxDEV("div", { className: "d-flex align-items-center mb-3", children: [
            /* @__PURE__ */ jsxDEV(Avatar, { user: row.user, size: 50 }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/MyAccounts.tsx",
              lineNumber: 55,
              columnNumber: 33
            }, this),
            /* @__PURE__ */ jsxDEV("div", { className: "d-flex flex-column", children: [
              /* @__PURE__ */ jsxDEV("p", { className: "card-title mb-0 text-truncate", children: row.user.fullname }, void 0, false, {
                fileName: "public/local/delegateaccount/js/esm/src/MyAccounts.tsx",
                lineNumber: 57,
                columnNumber: 37
              }, this),
              /* @__PURE__ */ jsxDEV("span", { className: "small text-muted text-truncate", children: row.user.email }, void 0, false, {
                fileName: "public/local/delegateaccount/js/esm/src/MyAccounts.tsx",
                lineNumber: 58,
                columnNumber: 37
              }, this)
            ] }, void 0, true, {
              fileName: "public/local/delegateaccount/js/esm/src/MyAccounts.tsx",
              lineNumber: 56,
              columnNumber: 33
            }, this)
          ] }, void 0, true, {
            fileName: "public/local/delegateaccount/js/esm/src/MyAccounts.tsx",
            lineNumber: 54,
            columnNumber: 29
          }, this),
          /* @__PURE__ */ jsxDEV("span", { className: "d-flex flex-column mb-3", children: [
            /* @__PURE__ */ jsxDEV("small", { className: "text-muted", children: text("delegation_end") }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/MyAccounts.tsx",
              lineNumber: 62,
              columnNumber: 33
            }, this),
            row.end
          ] }, void 0, true, {
            fileName: "public/local/delegateaccount/js/esm/src/MyAccounts.tsx",
            lineNumber: 61,
            columnNumber: 29
          }, this),
          /* @__PURE__ */ jsxDEV(
            "a",
            {
              className: "btn btn-primary btn-tonal mt-auto align-self-start",
              href: row.loginurl,
              title: row.loginlabel,
              children: [
                /* @__PURE__ */ jsxDEV(Icon, { name: "i/switch", strings }, void 0, false, {
                  fileName: "public/local/delegateaccount/js/esm/src/MyAccounts.tsx",
                  lineNumber: 66,
                  columnNumber: 33
                }, this),
                " ",
                text("open_account")
              ]
            },
            void 0,
            true,
            {
              fileName: "public/local/delegateaccount/js/esm/src/MyAccounts.tsx",
              lineNumber: 64,
              columnNumber: 29
            },
            this
          )
        ] }, void 0, true, {
          fileName: "public/local/delegateaccount/js/esm/src/MyAccounts.tsx",
          lineNumber: 53,
          columnNumber: 53
        }, this) }, void 0, false, {
          fileName: "public/local/delegateaccount/js/esm/src/MyAccounts.tsx",
          lineNumber: 53,
          columnNumber: 25
        }, this) }, row.delegationid, false, {
          fileName: "public/local/delegateaccount/js/esm/src/MyAccounts.tsx",
          lineNumber: 52,
          columnNumber: 54
        }, this)) }, void 0, false, {
          fileName: "public/local/delegateaccount/js/esm/src/MyAccounts.tsx",
          lineNumber: 51,
          columnNumber: 17
        }, this)
      },
      void 0,
      false,
      {
        fileName: "public/local/delegateaccount/js/esm/src/MyAccounts.tsx",
        lineNumber: 47,
        columnNumber: 9
      },
      this
    ),
    page && page.total > Number(state.perpage) && /* @__PURE__ */ jsxDEV("div", { className: "card", children: /* @__PURE__ */ jsxDEV(
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
        fileName: "public/local/delegateaccount/js/esm/src/MyAccounts.tsx",
        lineNumber: 73,
        columnNumber: 13
      },
      this
    ) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/MyAccounts.tsx",
      lineNumber: 72,
      columnNumber: 56
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/MyAccounts.tsx",
    lineNumber: 41,
    columnNumber: 12
  }, this);
}
__name(MyAccounts, "MyAccounts");
export {
  MyAccounts as default
};
//# sourceMappingURL=MyAccounts.dev.js.map
