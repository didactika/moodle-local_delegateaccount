var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A list of delegations, of one person or of everyone, and what can be done to one or to many of them: change their period,
 * change or copy their session permissions, and revoke them.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_delegateaccount/DelegationList
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useRef, useState } from "react";
import {
  ConfirmDialog,
  Empty,
  FloatBar,
  Icon,
  ListBody,
  Pager,
  Person,
  SearchBox,
  SkeletonRows,
  SortHeader,
  useFitHeight,
  useStableColumns
} from "./components/ui";
import { BulkPermissions } from "./BulkPermissions";
import { useList, useDebounced } from "./hooks/useList";
import { useText } from "./engine/text";
import { DEFAULT_PAGE_SIZE } from "./engine/paging";
import { describeError, openForm, toast } from "./engine/moodle";
import { getDelegationIds, getDelegationList, revokeDelegations } from "./services/api";
import StatusMenu from "./components/StatusMenu";
const STATUSES = ["active", "scheduled", "expired", "revoked"];
function Row({ row, checked, canbulk, showuser, onToggle, onEdit, onRevoke, text, strings }) {
  const overrides = row.overrides ? /* @__PURE__ */ jsxDEV("a", { className: "badge border text-bg-primary", href: row.permissionsurl, children: row.overrideslabel }, void 0, false, {
    fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
    lineNumber: 48,
    columnNumber: 11
  }, this) : /* @__PURE__ */ jsxDEV("span", { className: "small text-muted", children: text("overrides_none") }, void 0, false, {
    fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
    lineNumber: 49,
    columnNumber: 11
  }, this);
  return /* @__PURE__ */ jsxDEV("tr", { className: checked ? "table-active" : void 0, children: [
    canbulk && /* @__PURE__ */ jsxDEV("td", { className: "align-middle", "data-column": "select", children: row.selectable && /* @__PURE__ */ jsxDEV(
      "input",
      {
        type: "checkbox",
        className: "form-check-input",
        checked,
        "aria-label": text("select_row", row.label),
        onChange: (event) => onToggle(event.target.checked)
      },
      void 0,
      false,
      {
        fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
        lineNumber: 52,
        columnNumber: 32
      },
      this
    ) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
      lineNumber: 51,
      columnNumber: 21
    }, this),
    /* @__PURE__ */ jsxDEV("th", { scope: "row", children: /* @__PURE__ */ jsxDEV(Person, { user: row.delegateduser }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
      lineNumber: 55,
      columnNumber: 25
    }, this) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
      lineNumber: 55,
      columnNumber: 9
    }, this),
    showuser && /* @__PURE__ */ jsxDEV("td", { children: /* @__PURE__ */ jsxDEV(Person, { user: row.realuser }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
      lineNumber: 56,
      columnNumber: 26
    }, this) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
      lineNumber: 56,
      columnNumber: 22
    }, this),
    /* @__PURE__ */ jsxDEV("td", { children: /* @__PURE__ */ jsxDEV("span", { className: `badge rounded-pill text-bg-${row.statustone}`, children: row.statuslabel }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
      lineNumber: 58,
      columnNumber: 13
    }, this) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
      lineNumber: 57,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("td", { className: "text-nowrap", children: /* @__PURE__ */ jsxDEV("span", { className: "d-flex flex-column", children: [
      row.start,
      /* @__PURE__ */ jsxDEV("small", { className: "text-muted", children: row.end }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
        lineNumber: 60,
        columnNumber: 85
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
      lineNumber: 60,
      columnNumber: 37
    }, this) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
      lineNumber: 60,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("td", { className: "text-nowrap", children: /* @__PURE__ */ jsxDEV("small", { className: "text-muted", children: row.lastaccess }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
      lineNumber: 61,
      columnNumber: 37
    }, this) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
      lineNumber: 61,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("td", { children: overrides }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
      lineNumber: 62,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("td", { className: "text-end text-nowrap", "data-column": "actions", children: [
      /* @__PURE__ */ jsxDEV(
        "button",
        {
          type: "button",
          className: "btn btn-link btn-icon btn-sm text-primary",
          disabled: !row.canupdate,
          title: text("edit_period_of", row.label),
          "aria-label": text("edit_period_of", row.label),
          onClick: (event) => onEdit(event.currentTarget),
          children: /* @__PURE__ */ jsxDEV(Icon, { name: "t/edit", strings }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
            lineNumber: 67,
            columnNumber: 17
          }, this)
        },
        void 0,
        false,
        {
          fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
          lineNumber: 64,
          columnNumber: 13
        },
        this
      ),
      /* @__PURE__ */ jsxDEV(
        "a",
        {
          className: "btn btn-link btn-icon btn-sm text-info",
          href: row.permissionsurl,
          title: text("session_permissions_of", row.label),
          "aria-label": text("session_permissions_of", row.label),
          children: /* @__PURE__ */ jsxDEV(Icon, { name: "i/permissions", strings }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
            lineNumber: 71,
            columnNumber: 17
          }, this)
        },
        void 0,
        false,
        {
          fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
          lineNumber: 69,
          columnNumber: 13
        },
        this
      ),
      row.canviewactivity ? /* @__PURE__ */ jsxDEV(
        "a",
        {
          className: "btn btn-link btn-icon btn-sm text-success",
          href: row.activityurl,
          title: text("view_activity_of", row.label),
          "aria-label": text("view_activity_of", row.label),
          children: /* @__PURE__ */ jsxDEV(Icon, { name: "i/log", strings }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
            lineNumber: 76,
            columnNumber: 21
          }, this)
        },
        void 0,
        false,
        {
          fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
          lineNumber: 74,
          columnNumber: 19
        },
        this
      ) : /* @__PURE__ */ jsxDEV(
        "span",
        {
          className: "btn btn-link btn-icon btn-sm text-success disabled",
          "aria-disabled": "true",
          title: text("view_activity_of", row.label),
          children: /* @__PURE__ */ jsxDEV(Icon, { name: "i/log", strings }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
            lineNumber: 80,
            columnNumber: 21
          }, this)
        },
        void 0,
        false,
        {
          fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
          lineNumber: 78,
          columnNumber: 19
        },
        this
      ),
      /* @__PURE__ */ jsxDEV(
        "button",
        {
          type: "button",
          className: "btn btn-link btn-icon btn-sm text-danger",
          disabled: !row.canrevoke,
          title: text("revoke_of", row.label),
          "aria-label": text("revoke_of", row.label),
          onClick: onRevoke,
          children: /* @__PURE__ */ jsxDEV(Icon, { name: "t/delete", strings }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
            lineNumber: 84,
            columnNumber: 17
          }, this)
        },
        void 0,
        false,
        {
          fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
          lineNumber: 82,
          columnNumber: 13
        },
        this
      )
    ] }, void 0, true, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
      lineNumber: 63,
      columnNumber: 9
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
    lineNumber: 50,
    columnNumber: 12
  }, this);
}
__name(Row, "Row");
function Table({ list, rows, selectable, pagefull, canbulk, showuser, onSort, onEdit, onRevoke, text, strings }) {
  const table = useRef(null);
  const fitbox = useRef(null);
  useFitHeight(fitbox);
  useStableColumns(table, rows.length > 0);
  return /* @__PURE__ */ jsxDEV("div", { ref: fitbox, className: "table-responsive", "data-region": "tablewrap", "aria-busy": list.busy || void 0, children: /* @__PURE__ */ jsxDEV("table", { className: "table", ref: table, children: [
    /* @__PURE__ */ jsxDEV("caption", { className: "visually-hidden", children: text("list_caption_delegations") }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
      lineNumber: 115,
      columnNumber: 13
    }, this),
    /* @__PURE__ */ jsxDEV("thead", { children: /* @__PURE__ */ jsxDEV("tr", { children: [
      canbulk && /* @__PURE__ */ jsxDEV("th", { scope: "col", className: "align-middle", "data-column": "select", children: /* @__PURE__ */ jsxDEV(
        "input",
        {
          type: "checkbox",
          className: "form-check-input",
          "aria-label": text("select_page"),
          "data-region": "selectpage",
          checked: selectable.length > 0 && (list.all || pagefull),
          disabled: selectable.length === 0,
          ref: (input) => {
            if (input) {
              const some = selectable.some((id) => list.selected.has(id));
              input.indeterminate = !list.all && some && !pagefull;
            }
          },
          onChange: (event) => list.togglePage(selectable, event.target.checked)
        },
        void 0,
        false,
        {
          fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
          lineNumber: 119,
          columnNumber: 25
        },
        this
      ) }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
        lineNumber: 118,
        columnNumber: 33
      }, this),
      /* @__PURE__ */ jsxDEV(
        SortHeader,
        {
          column: "account",
          label: text("delegateduser"),
          sort: String(list.state.sort),
          direction: String(list.state.direction),
          onSort
        },
        void 0,
        false,
        {
          fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
          lineNumber: 131,
          columnNumber: 21
        },
        this
      ),
      showuser && /* @__PURE__ */ jsxDEV(
        SortHeader,
        {
          column: "user",
          label: text("authoriseduser"),
          sort: String(list.state.sort),
          direction: String(list.state.direction),
          onSort
        },
        void 0,
        false,
        {
          fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
          lineNumber: 133,
          columnNumber: 34
        },
        this
      ),
      /* @__PURE__ */ jsxDEV("th", { scope: "col", children: text("delegation_status") }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
        lineNumber: 135,
        columnNumber: 21
      }, this),
      /* @__PURE__ */ jsxDEV(
        SortHeader,
        {
          column: "start",
          label: text("delegation_period"),
          sort: String(list.state.sort),
          direction: String(list.state.direction),
          onSort
        },
        void 0,
        false,
        {
          fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
          lineNumber: 136,
          columnNumber: 21
        },
        this
      ),
      /* @__PURE__ */ jsxDEV("th", { scope: "col", children: text("last_access") }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
        lineNumber: 138,
        columnNumber: 21
      }, this),
      /* @__PURE__ */ jsxDEV("th", { scope: "col", children: text("session_permissions") }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
        lineNumber: 139,
        columnNumber: 21
      }, this),
      /* @__PURE__ */ jsxDEV("th", { scope: "col", className: "text-end text-nowrap", "data-column": "actions", children: /* @__PURE__ */ jsxDEV("span", { className: "visually-hidden", children: text("core:actions") }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
        lineNumber: 141,
        columnNumber: 25
      }, this) }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
        lineNumber: 140,
        columnNumber: 21
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
      lineNumber: 117,
      columnNumber: 17
    }, this) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
      lineNumber: 116,
      columnNumber: 13
    }, this),
    /* @__PURE__ */ jsxDEV("tbody", { children: list.skeleton || !list.page ? /* @__PURE__ */ jsxDEV(SkeletonRows, { columns: 6 + (showuser ? 1 : 0) + (canbulk ? 1 : 0) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
      lineNumber: 147,
      columnNumber: 23
    }, this) : rows.map((row) => /* @__PURE__ */ jsxDEV(
      Row,
      {
        row,
        canbulk,
        showuser,
        checked: list.all || list.selected.has(row.id),
        text,
        strings,
        onToggle: (checked) => list.toggle(row.id, checked),
        onEdit: (trigger) => onEdit([row.id], trigger),
        onRevoke: () => onRevoke([row.id])
      },
      row.id,
      false,
      {
        fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
        lineNumber: 148,
        columnNumber: 41
      },
      this
    )) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
      lineNumber: 145,
      columnNumber: 13
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
    lineNumber: 114,
    columnNumber: 9
  }, this) }, void 0, false, {
    fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
    lineNumber: 113,
    columnNumber: 12
  }, this);
}
__name(Table, "Table");
function BulkActions({ canupdate, canpermissions, canrevoke, onEdit, onPermissions, onRevoke, text, strings }) {
  return /* @__PURE__ */ jsxDEV(Fragment, { children: [
    canupdate && /* @__PURE__ */ jsxDEV(
      "button",
      {
        type: "button",
        className: "btn btn-outline-secondary btn-sm",
        onClick: (event) => onEdit(event.currentTarget),
        children: [
          /* @__PURE__ */ jsxDEV(Icon, { name: "t/edit", strings }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
            lineNumber: 177,
            columnNumber: 13
          }, this),
          " ",
          text("edit_period")
        ]
      },
      void 0,
      true,
      {
        fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
        lineNumber: 175,
        columnNumber: 23
      },
      this
    ),
    canpermissions && /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-outline-secondary btn-sm", onClick: onPermissions, children: [
      /* @__PURE__ */ jsxDEV(Icon, { name: "i/permissions", strings }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
        lineNumber: 180,
        columnNumber: 13
      }, this),
      " ",
      text("session_permissions")
    ] }, void 0, true, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
      lineNumber: 179,
      columnNumber: 28
    }, this),
    canrevoke && /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-outline-danger btn-sm", onClick: onRevoke, children: [
      /* @__PURE__ */ jsxDEV(Icon, { name: "t/delete", strings }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
        lineNumber: 183,
        columnNumber: 13
      }, this),
      " ",
      text("revoke")
    ] }, void 0, true, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
      lineNumber: 182,
      columnNumber: 23
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
    lineNumber: 174,
    columnNumber: 12
  }, this);
}
__name(BulkActions, "BulkActions");
function DelegationList(props) {
  const { strings, realuserid, canbulk, canupdate, canrevoke, canpermissions } = props;
  const text = useText(strings);
  const list = useList(
    { search: "", status: props.status, sort: "account", direction: "asc", page: 0, perpage: DEFAULT_PAGE_SIZE },
    (state2) => getDelegationList(
      { realuserid, status: String(state2.status), search: String(state2.search) },
      {
        sort: String(state2.sort),
        direction: String(state2.direction),
        page: Number(state2.page),
        perpage: Number(state2.perpage)
      }
    ),
    props.initial
  );
  const { state, page } = list;
  const [search, setSearch] = useState(String(state.search));
  const typed = useDebounced(search.trim(), 300);
  const [revoking, setRevoking] = useState(null);
  const [bulk, setBulk] = useState(false);
  useEffect(() => {
    list.update({ search: typed });
  }, [typed]);
  useEffect(() => {
    const created = /* @__PURE__ */ __name(() => list.refresh(), "created");
    document.addEventListener("local_delegateaccount:created", created);
    return () => document.removeEventListener("local_delegateaccount:created", created);
  }, []);
  const filters = { realuserid, status: String(state.status), search: String(state.search) };
  const rows = page?.rows ?? [];
  const total = page?.total ?? 0;
  const selectable = rows.filter((row) => row.selectable).map((row) => row.id);
  const count = list.all ? total : list.selected.size;
  const pagefull = selectable.length > 0 && selectable.every((id) => list.selected.has(id));
  const showuser = !realuserid;
  const resolve = /* @__PURE__ */ __name(async () => {
    if (!list.all) {
      return Array.from(list.selected);
    }
    const result = await getDelegationIds(filters);
    if (result.error) {
      throw new Error(result.error);
    }
    return result.ids;
  }, "resolve");
  const afterChange = /* @__PURE__ */ __name(() => {
    list.clear();
    list.refresh();
  }, "afterChange");
  const edit = /* @__PURE__ */ __name(async (ids, trigger) => {
    try {
      const delegationids = ids ?? await resolve();
      await openForm(
        "local_delegateaccount\\form\\bulk_edit_dynamic_form",
        { realuserid, delegationids: delegationids.join(",") },
        text(delegationids.length === 1 ? "edit_period" : "edit_selected_delegations"),
        trigger,
        (result) => {
          afterChange();
          if (result.message) {
            void toast(result.message, result.type ?? "success");
          }
        }
      );
    } catch (error) {
      await toast(describeError(error), "danger");
    }
  }, "edit");
  const revoke = /* @__PURE__ */ __name(async (ids) => {
    try {
      setRevoking(ids ?? await resolve());
    } catch (error) {
      await toast(describeError(error), "danger");
    }
  }, "revoke");
  const sort = /* @__PURE__ */ __name((column, direction) => list.update({ sort: column, direction, page: 0 }), "sort");
  const chosen = String(state.status).split(",").filter(Boolean);
  const filtered = Boolean(state.search) || chosen.length > 0;
  const reset = /* @__PURE__ */ __name(() => {
    setSearch("");
    list.update({ search: "", status: "" });
  }, "reset");
  const empty = /* @__PURE__ */ jsxDEV(
    Empty,
    {
      filtered,
      icon: "i/users",
      title: text("empty_delegations_title"),
      message: text("empty_delegations_text"),
      text,
      strings,
      onClear: reset
    },
    void 0,
    false,
    {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
      lineNumber: 290,
      columnNumber: 19
    },
    this
  );
  const one = revoking?.length === 1;
  return /* @__PURE__ */ jsxDEV("div", { className: "d-grid gap-3", style: count ? { paddingBottom: "5.5rem" } : void 0, children: [
    /* @__PURE__ */ jsxDEV("div", { className: "card", children: [
      /* @__PURE__ */ jsxDEV("div", { className: "d-flex align-items-center justify-content-between gap-3 px-3 py-2 border-bottom", "data-region": "filters", children: [
        /* @__PURE__ */ jsxDEV("div", { className: "d-flex align-items-center gap-2 flex-grow-1", children: [
          /* @__PURE__ */ jsxDEV(
            SearchBox,
            {
              strings,
              value: search,
              onChange: setSearch,
              label: text(realuserid ? "search_accounts" : "search_delegations")
            },
            void 0,
            false,
            {
              fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
              lineNumber: 298,
              columnNumber: 21
            },
            this
          ),
          /* @__PURE__ */ jsxDEV(
            StatusMenu,
            {
              label: text("core:status"),
              chosen,
              text,
              strings,
              options: STATUSES.map((key) => ({ key, label: text(`delegation_status_${key}`), count: page?.counts?.[key] })),
              onChange: (statuses) => list.update({ status: statuses.join(","), page: 0 })
            },
            void 0,
            false,
            {
              fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
              lineNumber: 300,
              columnNumber: 21
            },
            this
          )
        ] }, void 0, true, {
          fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
          lineNumber: 297,
          columnNumber: 17
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
              fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
              lineNumber: 306,
              columnNumber: 76
            }, this)
          },
          void 0,
          false,
          {
            fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
            lineNumber: 305,
            columnNumber: 21
          },
          this
        ) }, void 0, false, {
          fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
          lineNumber: 304,
          columnNumber: 17
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
        lineNumber: 296,
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
          children: /* @__PURE__ */ jsxDEV(
            Table,
            {
              list,
              rows,
              selectable,
              pagefull,
              canbulk,
              showuser,
              onSort: sort,
              onEdit: (ids, trigger) => void edit(ids, trigger),
              onRevoke: (ids) => void revoke(ids),
              text,
              strings
            },
            void 0,
            false,
            {
              fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
              lineNumber: 311,
              columnNumber: 17
            },
            this
          )
        },
        void 0,
        false,
        {
          fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
          lineNumber: 309,
          columnNumber: 13
        },
        this
      ),
      total > 0 && !list.error && /* @__PURE__ */ jsxDEV(
        Pager,
        {
          page: Number(state.page),
          perpage: Number(state.perpage),
          total,
          onChange: list.update,
          text,
          strings
        },
        void 0,
        false,
        {
          fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
          lineNumber: 315,
          columnNumber: 42
        },
        this
      )
    ] }, void 0, true, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
      lineNumber: 295,
      columnNumber: 9
    }, this),
    canbulk && /* @__PURE__ */ jsxDEV(
      FloatBar,
      {
        count,
        total,
        label: text("bulk_region"),
        text,
        offerAll: !list.all && pagefull && total > selectable.length,
        onAll: () => list.setAll(true),
        onClear: list.clear,
        children: /* @__PURE__ */ jsxDEV(
          BulkActions,
          {
            canupdate,
            canpermissions,
            canrevoke,
            text,
            strings,
            onEdit: (trigger) => void edit(null, trigger),
            onPermissions: () => setBulk(true),
            onRevoke: () => void revoke(null)
          },
          void 0,
          false,
          {
            fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
            lineNumber: 321,
            columnNumber: 13
          },
          this
        )
      },
      void 0,
      false,
      {
        fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
        lineNumber: 318,
        columnNumber: 21
      },
      this
    ),
    revoking && /* @__PURE__ */ jsxDEV(
      ConfirmDialog,
      {
        text,
        strings,
        onClose: () => setRevoking(null),
        title: one ? text("revoke_confirm_title_one") : text("revoke_confirm_title", revoking.length),
        message: text("revoke_confirm_text"),
        button: one ? text("revoke_confirm_button_one") : text("revoke_confirm_button", revoking.length),
        onConfirm: async () => {
          const result = await revokeDelegations(revoking);
          afterChange();
          await toast(text("revoked_count", result.revokedcount));
        }
      },
      void 0,
      false,
      {
        fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
        lineNumber: 325,
        columnNumber: 22
      },
      this
    ),
    bulk && /* @__PURE__ */ jsxDEV(
      BulkPermissions,
      {
        strings,
        count,
        selection: { delegationids: list.all ? [] : Array.from(list.selected), all: list.all, filters },
        onClose: () => setBulk(false),
        onApplied: afterChange
      },
      void 0,
      false,
      {
        fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
        lineNumber: 334,
        columnNumber: 18
      },
      this
    )
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/DelegationList.tsx",
    lineNumber: 294,
    columnNumber: 12
  }, this);
}
__name(DelegationList, "DelegationList");
export {
  DelegationList as default
};
//# sourceMappingURL=DelegationList.dev.js.map
