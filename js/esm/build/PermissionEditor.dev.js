var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The permission editor: every capability of the site in one flat, paged list with Not set, Allow, Prevent and Prohibit,
 * found by typing or narrowed by component and by chips, never folded away. Changes are kept until they are saved from the
 * floating bar, which saves them through a web service and refreshes the list in place, for the site as for one delegation.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_delegateaccount/PermissionEditor
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useMemo, useRef, useState } from "react";
import Pending from "@moodle/lms/core/pending";
import { Chips, Empty, ListBody, Pager, SearchBox, Segmented, SelectMenu } from "./components/ui";
import { CopyFromDialog } from "./BulkPermissions";
import { useDebounced } from "./hooks/useList";
import { useText } from "./engine/text";
import { DEFAULT_PAGE_SIZE, pageRange } from "./engine/paging";
import { describeError, toast } from "./engine/moodle";
import { ALLOW, INHERIT, PERMISSIONS, matches, toMap, words } from "./engine/capabilities";
import { getPermissions, savePermissions } from "./services/api";
const CHIPS = [
  { key: "", label: "perm_filter_all" },
  { key: "overridden", label: "perm_filter_overridden" },
  { key: "1", label: "permissionallow" },
  { key: "-1", label: "perm_prevent" },
  { key: "-1000", label: "permissionprohibit" },
  { key: "risky", label: "perm_filter_risky" }
];
function PermissionEditor({ strings, delegationid, site }) {
  const text = useText(strings);
  const root = useRef(null);
  const [capabilities, setCapabilities] = useState(null);
  const [saved, setSaved] = useState(/* @__PURE__ */ new Map());
  const [current, setCurrent] = useState(/* @__PURE__ */ new Map());
  const [siteoverrides, setSiteoverrides] = useState(/* @__PURE__ */ new Map());
  const [canedit, setCanedit] = useState(false);
  const [error, setError] = useState(null);
  const [filter, setFilter] = useState("");
  const [component, setComponent] = useState("");
  const [search, setSearch] = useState("");
  const typed = useDebounced(search, 200);
  const [page, setPage] = useState(0);
  const [perpage, setPerpage] = useState(DEFAULT_PAGE_SIZE);
  const box = useRef(null);
  const [saving, setSaving] = useState(false);
  const [copying, setCopying] = useState(false);
  const [notice, setNotice] = useState("");
  const [reload, setReload] = useState(0);
  useEffect(() => {
    const pending = new Pending("local_delegateaccount/permission_editor:load");
    getPermissions(delegationid).then((data) => {
      setCapabilities(data.capabilities);
      setSaved(toMap(data.overrides));
      setCurrent(toMap(data.overrides));
      setSiteoverrides(toMap(data.site));
      setCanedit(data.canedit);
      setError(null);
      return data;
    }).catch(setError).finally(() => pending.resolve());
  }, [delegationid, reload]);
  useEffect(() => {
    const pane = root.current?.closest(".tab-pane");
    const submit = root.current?.closest("form")?.querySelector('[type="submit"]');
    if (!pane || !submit) {
      return void 0;
    }
    const row = submit.closest(".row, div") ?? submit;
    const sync = /* @__PURE__ */ __name(() => {
      row.hidden = pane.classList.contains("active");
    }, "sync");
    const observer = new MutationObserver(sync);
    observer.observe(pane, { attributes: true, attributeFilter: ["class"] });
    sync();
    return () => observer.disconnect();
  }, []);
  const names = useMemo(() => Object.fromEntries(PERMISSIONS.map(({ value, key }) => [value, text(key)])), [text]);
  const components = useMemo(
    () => Array.from(new Set((capabilities ?? []).map((capability) => capability.componentlabel))),
    [capabilities]
  );
  const visible = /* @__PURE__ */ __name((chip) => (capabilities ?? []).filter((capability) => {
    if (component && capability.componentlabel !== component) {
      return false;
    }
    if (!matches(capability, words(typed))) {
      return false;
    }
    const permission = current.get(capability.name) ?? INHERIT;
    if (chip === "overridden") {
      return permission !== INHERIT;
    }
    if (chip === "risky") {
      return capability.risks.length > 0;
    }
    return chip === "" || permission === Number(chip);
  }), "visible");
  const shown = visible(filter);
  const counts = Object.fromEntries(CHIPS.map(({ key }) => [key || "all", visible(key).length]));
  const { lastpage, start, end } = pageRange(page, perpage, shown.length);
  const at = Math.min(page, lastpage);
  const rows = shown.slice(start, end);
  useEffect(() => {
    if (box.current) {
      box.current.scrollTop = 0;
    }
  }, [at, perpage, filter, component, typed]);
  const changes = Array.from(/* @__PURE__ */ new Set([...saved.keys(), ...current.keys()])).filter((name) => (saved.get(name) ?? INHERIT) !== (current.get(name) ?? INHERIT)).map((capability) => ({ capability, permission: current.get(capability) ?? INHERIT }));
  const reset = /* @__PURE__ */ __name((apply) => {
    apply();
    setPage(0);
  }, "reset");
  const save = /* @__PURE__ */ __name(async () => {
    setSaving(true);
    try {
      const result = await savePermissions(delegationid, changes);
      setSaved(toMap(result.overrides));
      setCurrent(toMap(result.overrides));
      setNotice("");
      await toast(text("perm_saved"));
      const detail = result.overrides.length;
      document.dispatchEvent(new CustomEvent("local_delegateaccount:permissionssaved", { detail }));
    } catch (failure) {
      await toast(describeError(failure), "danger");
    } finally {
      setSaving(false);
    }
  }, "save");
  const copy = /* @__PURE__ */ __name(async (source) => {
    const data = await getPermissions(source.id);
    setCurrent(toMap(data.overrides));
    setPage(0);
    setNotice(text("copy_loaded", source.label));
  }, "copy");
  const legend = [
    ["perm_notset", "perm_notset_desc"],
    ["permissionallow", "perm_allow_desc"],
    ["perm_prevent", "perm_prevent_desc"],
    ["permissionprohibit", "perm_prohibit_desc"]
  ];
  const componentoptions = [
    { value: "", label: text("perm_component_all") },
    ...components.map((name) => ({ value: name, label: name }))
  ];
  const items = [];
  rows.forEach((capability, index) => {
    if (index === 0 || rows[index - 1].componentlabel !== capability.componentlabel) {
      items.push(/* @__PURE__ */ jsxDEV("li", { className: "list-group-item list-group-item-light", children: capability.componentlabel }, `group-${capability.componentlabel}`, false, {
        fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
        lineNumber: 175,
        columnNumber: 24
      }, this));
    }
    const permission = current.get(capability.name) ?? INHERIT;
    items.push(/* @__PURE__ */ jsxDEV(
      CapabilityRow,
      {
        capability,
        permission,
        changed: permission !== (saved.get(capability.name) ?? INHERIT),
        canedit,
        names,
        sitepermission: siteoverrides.get(capability.name),
        text,
        onChange: (value) => setCurrent((before) => new Map(before).set(capability.name, value))
      },
      capability.name,
      false,
      {
        fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
        lineNumber: 180,
        columnNumber: 20
      },
      this
    ));
  });
  const skeleton = /* @__PURE__ */ jsxDEV("ul", { className: "list-group list-group-flush", "aria-busy": "true", children: [0, 1, 2].map((key) => /* @__PURE__ */ jsxDEV("li", { className: "list-group-item", children: /* @__PURE__ */ jsxDEV("span", { className: "dd-skeleton w-50" }, void 0, false, {
    fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
    lineNumber: 186,
    columnNumber: 75
  }, this) }, key, false, {
    fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
    lineNumber: 186,
    columnNumber: 33
  }, this)) }, void 0, false, {
    fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
    lineNumber: 185,
    columnNumber: 22
  }, this);
  const empty = /* @__PURE__ */ jsxDEV(
    Empty,
    {
      filtered: true,
      icon: "a/search",
      title: "",
      message: "",
      text,
      strings,
      onClear: () => reset(() => {
        setFilter("");
        setComponent("");
        setSearch("");
      })
    },
    void 0,
    false,
    {
      fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
      lineNumber: 188,
      columnNumber: 19
    },
    this
  );
  return /* @__PURE__ */ jsxDEV("div", { ref: root, className: "lda pb-5 ignoredirty", children: [
    /* @__PURE__ */ jsxDEV("p", { className: "dd-page-subtitle mb-0", children: text(site ? "perm_intro_site" : "perm_intro_delegation") }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
      lineNumber: 196,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("ul", { className: "row list-unstyled small text-muted mb-0", "aria-label": text("perm_legend"), children: legend.map(([name, description]) => /* @__PURE__ */ jsxDEV("li", { className: "col-sm-6 col-lg-3 mb-2", children: [
      /* @__PURE__ */ jsxDEV("b", { className: "d-block text-body", children: text(name) }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
        lineNumber: 199,
        columnNumber: 17
      }, this),
      text(description)
    ] }, name, true, {
      fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
      lineNumber: 198,
      columnNumber: 50
    }, this)) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
      lineNumber: 197,
      columnNumber: 9
    }, this),
    capabilities && !canedit && /* @__PURE__ */ jsxDEV("div", { className: "alert alert-info mb-0", children: text("perm_readonly") }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
      lineNumber: 202,
      columnNumber: 38
    }, this),
    notice && /* @__PURE__ */ jsxDEV("div", { className: "alert alert-info mb-0", role: "status", children: notice }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
      lineNumber: 203,
      columnNumber: 20
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "card", children: [
      /* @__PURE__ */ jsxDEV("div", { className: "table-toolbar", children: [
        /* @__PURE__ */ jsxDEV("div", { className: "table-toolbar-start", children: [
          /* @__PURE__ */ jsxDEV(
            SearchBox,
            {
              strings,
              value: search,
              label: text("perm_search"),
              placeholder: text("perm_search_placeholder"),
              onChange: (value) => reset(() => setSearch(value))
            },
            void 0,
            false,
            {
              fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
              lineNumber: 207,
              columnNumber: 21
            },
            this
          ),
          /* @__PURE__ */ jsxDEV(
            SelectMenu,
            {
              clearable: true,
              text,
              strings,
              label: text("perm_component_all"),
              value: component,
              options: componentoptions,
              onChange: (value) => reset(() => setComponent(value))
            },
            void 0,
            false,
            {
              fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
              lineNumber: 209,
              columnNumber: 21
            },
            this
          )
        ] }, void 0, true, {
          fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
          lineNumber: 206,
          columnNumber: 17
        }, this),
        !site && canedit && /* @__PURE__ */ jsxDEV("div", { className: "table-toolbar-end", children: /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-outline-secondary btn-sm", onClick: () => setCopying(true), children: text("copy_from_delegation") }, void 0, false, {
          fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
          lineNumber: 213,
          columnNumber: 21
        }, this) }, void 0, false, {
          fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
          lineNumber: 212,
          columnNumber: 38
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
        lineNumber: 205,
        columnNumber: 13
      }, this),
      /* @__PURE__ */ jsxDEV("div", { className: "table-toolbar", children: /* @__PURE__ */ jsxDEV(
        Chips,
        {
          label: text("perm_search"),
          value: filter,
          counts: capabilities ? counts : void 0,
          chips: CHIPS.map(({ key, label }) => ({ key, label: text(label) })),
          onChange: (chip) => reset(() => setFilter(chip))
        },
        void 0,
        false,
        {
          fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
          lineNumber: 219,
          columnNumber: 17
        },
        this
      ) }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
        lineNumber: 218,
        columnNumber: 13
      }, this),
      /* @__PURE__ */ jsxDEV("div", { ref: box, className: "overflow-auto", "data-region": "capabilities", children: /* @__PURE__ */ jsxDEV(
        ListBody,
        {
          error,
          onRetry: () => setReload((count) => count + 1),
          text,
          empty,
          isEmpty: Boolean(capabilities) && shown.length === 0,
          children: capabilities ? /* @__PURE__ */ jsxDEV("ul", { className: "list-group list-group-flush", children: items }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
            lineNumber: 226,
            columnNumber: 37
          }, this) : skeleton
        },
        void 0,
        false,
        {
          fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
          lineNumber: 224,
          columnNumber: 17
        },
        this
      ) }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
        lineNumber: 223,
        columnNumber: 13
      }, this),
      capabilities && shown.length > 0 && /* @__PURE__ */ jsxDEV(
        Pager,
        {
          page: at,
          perpage,
          total: shown.length,
          text,
          strings,
          onChange: (next) => {
            if (next.perpage !== void 0) {
              setPerpage(next.perpage);
            }
            setPage(next.page ?? 0);
          }
        },
        void 0,
        false,
        {
          fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
          lineNumber: 229,
          columnNumber: 50
        },
        this
      )
    ] }, void 0, true, {
      fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
      lineNumber: 204,
      columnNumber: 9
    }, this),
    changes.length > 0 && /* @__PURE__ */ jsxDEV(
      "div",
      {
        className: "dd-savebar",
        role: "region",
        "aria-live": "polite",
        "data-region": "savebar",
        "aria-label": text("session_permissions"),
        children: [
          /* @__PURE__ */ jsxDEV("span", { className: "dd-savebar-text", children: text("perm_unsaved", changes.length) }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
            lineNumber: 239,
            columnNumber: 13
          }, this),
          /* @__PURE__ */ jsxDEV("span", { className: "dd-savebar-actions", children: [
            /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-outline-secondary", onClick: () => {
              setCurrent(new Map(saved));
              setNotice("");
            }, children: text("perm_discard") }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
              lineNumber: 241,
              columnNumber: 17
            }, this),
            /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-primary", disabled: saving, onClick: () => void save(), children: saving ? text("perm_saving") : text("core:savechanges") }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
              lineNumber: 245,
              columnNumber: 17
            }, this)
          ] }, void 0, true, {
            fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
            lineNumber: 240,
            columnNumber: 13
          }, this)
        ]
      },
      void 0,
      true,
      {
        fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
        lineNumber: 237,
        columnNumber: 32
      },
      this
    ),
    copying && /* @__PURE__ */ jsxDEV(
      CopyFromDialog,
      {
        exclude: delegationid,
        strings,
        onClose: () => setCopying(false),
        onChoose: (source) => void copy(source)
      },
      void 0,
      false,
      {
        fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
        lineNumber: 250,
        columnNumber: 21
      },
      this
    )
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
    lineNumber: 195,
    columnNumber: 12
  }, this);
}
__name(PermissionEditor, "PermissionEditor");
function CapabilityRow({ capability, permission, changed, canedit, names, sitepermission, onChange, text }) {
  const options = PERMISSIONS.map(({ value, tone }) => ({
    tone,
    value,
    label: names[value],
    // Allowing gives the capability to the session, so only who has it may allow it.
    disabled: !canedit || value === ALLOW && !capability.canallow && permission !== ALLOW,
    title: value === ALLOW && !capability.canallow ? text("cannotallow_hint") : void 0
  }));
  const site = sitepermission !== void 0 && permission === INHERIT;
  return /* @__PURE__ */ jsxDEV("li", { className: `list-group-item${changed ? " list-group-item-primary" : ""}`, children: [
    /* @__PURE__ */ jsxDEV("span", { className: "d-flex flex-column", children: [
      /* @__PURE__ */ jsxDEV("span", { className: "font-weight-bold fw-bold", children: capability.label }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
        lineNumber: 283,
        columnNumber: 13
      }, this),
      /* @__PURE__ */ jsxDEV("span", { className: "small text-muted", children: [
        /* @__PURE__ */ jsxDEV("span", { className: "me-2", children: capability.name }, void 0, false, {
          fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
          lineNumber: 285,
          columnNumber: 17
        }, this),
        capability.risks.map((risk) => /* @__PURE__ */ jsxDEV("span", { className: "badge badge-outline text-bg-warning", children: text(`core:risk${risk}short`) }, risk, false, {
          fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
          lineNumber: 286,
          columnNumber: 49
        }, this)),
        site && /* @__PURE__ */ jsxDEV("span", { className: "badge badge-soft text-bg-secondary", children: text("perm_site_value", names[sitepermission]) }, void 0, false, {
          fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
          lineNumber: 289,
          columnNumber: 26
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
        lineNumber: 284,
        columnNumber: 13
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
      lineNumber: 282,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV(
      Segmented,
      {
        label: text("perm_permission_for", capability.label),
        value: permission,
        onChange,
        options
      },
      void 0,
      false,
      {
        fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
        lineNumber: 294,
        columnNumber: 9
      },
      this
    )
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/PermissionEditor.tsx",
    lineNumber: 281,
    columnNumber: 12
  }, this);
}
__name(CapabilityRow, "CapabilityRow");
export {
  PermissionEditor as default
};
//# sourceMappingURL=PermissionEditor.dev.js.map
