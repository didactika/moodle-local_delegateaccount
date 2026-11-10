var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { Fragment, jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The session permissions of many delegations at once: add, change or remove overrides on all of them, or copy the
 * overrides of one delegation to all of them. The server writes them in bounded batches, so the selection can be every
 * delegation that matches a list. The dialog keeps one size and shows its lists from the start.
 *
 * @module     local_delegateaccount/BulkPermissions
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useMemo, useState } from "react";
import { Dialog, Icon, SearchBox, Segmented } from "./components/ui";
import { useDebounced } from "./hooks/useList";
import { useText } from "./engine/text";
import { describeError, toast } from "./engine/moodle";
import { ALLOW, INHERIT, PREVENT, PROHIBIT, matches, words } from "./engine/capabilities";
import { getDelegationList, getPermissions, updatePermissions } from "./services/api";
const LIMIT = 60;
function SourcePicker({ chosen, onChoose, exclude, strings }) {
  const text = useText(strings);
  const [search, setSearch] = useState("");
  const typed = useDebounced(search.trim(), 300);
  const [rows, setRows] = useState([]);
  const [error, setError] = useState("");
  useEffect(() => {
    let current = true;
    getDelegationList({ realuserid: 0, status: "", search: typed }, { sort: "account", page: 0, perpage: 30 }).then((page) => {
      if (current) {
        setRows(page.rows.filter((row) => row.id !== exclude));
        setError("");
      }
      return page;
    }).catch((failure) => current && setError(describeError(failure)));
    return () => {
      current = false;
    };
  }, [typed, exclude]);
  return /* @__PURE__ */ jsxDEV("div", { children: [
    /* @__PURE__ */ jsxDEV("label", { className: "font-weight-bold fw-bold d-block mb-2", children: text("bulk_copy_source") }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
      lineNumber: 66,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "mb-2", children: /* @__PURE__ */ jsxDEV(SearchBox, { wide: true, strings, value: search, onChange: setSearch, label: text("search_delegations") }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
      lineNumber: 68,
      columnNumber: 13
    }, this) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
      lineNumber: 67,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("ul", { className: "list-group", role: "listbox", "aria-label": text("bulk_copy_source"), children: [
      !rows.length && /* @__PURE__ */ jsxDEV("li", { className: "list-group-item text-muted text-center", children: error || text("empty_search_title") }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
        lineNumber: 71,
        columnNumber: 30
      }, this),
      rows.map((row) => /* @__PURE__ */ jsxDEV("li", { role: "presentation", children: /* @__PURE__ */ jsxDEV(
        "button",
        {
          type: "button",
          role: "option",
          "aria-selected": row.id === chosen,
          onClick: () => onChoose({ id: row.id, label: row.label }),
          children: /* @__PURE__ */ jsxDEV("span", { className: "d-flex flex-column", children: [
            /* @__PURE__ */ jsxDEV("span", { children: row.label }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
              lineNumber: 76,
              columnNumber: 25
            }, this),
            /* @__PURE__ */ jsxDEV("small", { children: [
              row.statuslabel,
              " \xB7 ",
              row.overrideslabel
            ] }, void 0, true, {
              fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
              lineNumber: 77,
              columnNumber: 25
            }, this)
          ] }, void 0, true, {
            fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
            lineNumber: 75,
            columnNumber: 21
          }, this)
        },
        void 0,
        false,
        {
          fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
          lineNumber: 73,
          columnNumber: 17
        },
        this
      ) }, row.id, false, {
        fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
        lineNumber: 72,
        columnNumber: 32
      }, this))
    ] }, void 0, true, {
      fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
      lineNumber: 70,
      columnNumber: 9
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
    lineNumber: 65,
    columnNumber: 12
  }, this);
}
__name(SourcePicker, "SourcePicker");
function CopyFromDialog({ exclude, onChoose, onClose, strings }) {
  const text = useText(strings);
  const [chosen, setChosen] = useState(null);
  return /* @__PURE__ */ jsxDEV(Dialog, { title: text("copy_from_delegation"), onClose, footer: /* @__PURE__ */ jsxDEV(Fragment, { children: [
    /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-secondary", onClick: onClose, children: text("core:cancel") }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
      lineNumber: 100,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-primary", disabled: !chosen, onClick: () => {
      if (chosen) {
        onChoose(chosen);
      }
      onClose();
    }, children: text("bulk_mode_copy") }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
      lineNumber: 101,
      columnNumber: 9
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
    lineNumber: 99,
    columnNumber: 83
  }, this), children: /* @__PURE__ */ jsxDEV(SourcePicker, { chosen: chosen?.id ?? 0, onChoose: setChosen, exclude, strings }, void 0, false, {
    fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
    lineNumber: 108,
    columnNumber: 9
  }, this) }, void 0, false, {
    fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
    lineNumber: 99,
    columnNumber: 12
  }, this);
}
__name(CopyFromDialog, "CopyFromDialog");
function BulkPermissions({ selection, count, onClose, onApplied, strings }) {
  const text = useText(strings);
  const [mode, setMode] = useState("change");
  const [capabilities, setCapabilities] = useState([]);
  const [changes, setChanges] = useState(/* @__PURE__ */ new Map());
  const [search, setSearch] = useState("");
  const typed = useDebounced(search, 150);
  const [source, setSource] = useState(null);
  const [replace, setReplace] = useState(false);
  const [error, setError] = useState("");
  const [working, setWorking] = useState(false);
  useEffect(() => {
    getPermissions(0).then((data) => {
      setCapabilities(data.capabilities);
      return data;
    }).catch((failure) => setError(describeError(failure)));
  }, []);
  const byname = useMemo(() => new Map(capabilities.map((capability) => [capability.name, capability])), [capabilities]);
  const found = useMemo(
    () => capabilities.filter((capability) => matches(capability, words(typed))).slice(0, LIMIT),
    [capabilities, typed]
  );
  const toggle = /* @__PURE__ */ __name((name) => setChanges((before) => {
    const next = new Map(before);
    if (next.has(name)) {
      next.delete(name);
    } else {
      next.set(name, PROHIBIT);
    }
    return next;
  }), "toggle");
  const apply = /* @__PURE__ */ __name(async () => {
    setWorking(true);
    setError("");
    try {
      const result = mode === "copy" && source ? await updatePermissions({ selection, copyfrom: source.id, replace }) : await updatePermissions({
        selection,
        changes: Array.from(changes, ([capability, permission]) => ({ capability, permission }))
      });
      onClose();
      await toast(text("bulk_applied", result.count));
      onApplied();
    } catch (failure) {
      setError(describeError(failure));
      setWorking(false);
    }
  }, "apply");
  const ready = mode === "change" ? changes.size > 0 : source !== null;
  return /* @__PURE__ */ jsxDEV(Dialog, { title: text("bulk_permissions_title", count), onClose, footer: /* @__PURE__ */ jsxDEV(Fragment, { children: [
    /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-secondary", onClick: onClose, children: text("core:cancel") }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
      lineNumber: 180,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-primary", disabled: !ready || working, onClick: () => void apply(), children: text("apply_to_count", count) }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
      lineNumber: 181,
      columnNumber: 9
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
    lineNumber: 179,
    columnNumber: 92
  }, this), children: /* @__PURE__ */ jsxDEV("div", { "data-region": "bulk", children: [
    /* @__PURE__ */ jsxDEV(Segmented, { label: text("bulk_mode"), value: mode, onChange: setMode, options: [
      { value: "change", label: text("bulk_mode_change") },
      { value: "copy", label: text("bulk_mode_copy") }
    ] }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
      lineNumber: 186,
      columnNumber: 13
    }, this),
    mode === "change" ? /* @__PURE__ */ jsxDEV("div", { "data-region": "change", children: [
      /* @__PURE__ */ jsxDEV("div", { children: [
        /* @__PURE__ */ jsxDEV("label", { className: "font-weight-bold fw-bold d-block mb-2", children: text("bulk_add_capability") }, void 0, false, {
          fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
          lineNumber: 192,
          columnNumber: 21
        }, this),
        /* @__PURE__ */ jsxDEV("div", { className: "mb-2", children: /* @__PURE__ */ jsxDEV(
          SearchBox,
          {
            wide: true,
            strings,
            value: search,
            onChange: setSearch,
            label: text("bulk_add_capability"),
            placeholder: text("perm_search_placeholder")
          },
          void 0,
          false,
          {
            fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
            lineNumber: 194,
            columnNumber: 25
          },
          this
        ) }, void 0, false, {
          fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
          lineNumber: 193,
          columnNumber: 21
        }, this),
        /* @__PURE__ */ jsxDEV(
          "ul",
          {
            className: "list-group",
            role: "listbox",
            "aria-multiselectable": "true",
            "data-region": "capresults",
            "aria-label": text("bulk_add_capability"),
            children: [
              !found.length && /* @__PURE__ */ jsxDEV("li", { className: "list-group-item text-muted text-center", children: text("bulk_search_noresults") }, void 0, false, {
                fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
                lineNumber: 199,
                columnNumber: 43
              }, this),
              found.map((capability) => /* @__PURE__ */ jsxDEV("li", { role: "presentation", children: /* @__PURE__ */ jsxDEV(
                "button",
                {
                  type: "button",
                  role: "option",
                  "aria-selected": changes.has(capability.name),
                  onClick: () => toggle(capability.name),
                  children: [
                    /* @__PURE__ */ jsxDEV("span", { className: "d-flex flex-column", children: [
                      /* @__PURE__ */ jsxDEV("span", { children: capability.label }, void 0, false, {
                        fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
                        lineNumber: 206,
                        columnNumber: 37
                      }, this),
                      /* @__PURE__ */ jsxDEV("small", { children: [
                        capability.name,
                        " \xB7 ",
                        capability.componentlabel
                      ] }, void 0, true, {
                        fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
                        lineNumber: 207,
                        columnNumber: 37
                      }, this)
                    ] }, void 0, true, {
                      fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
                      lineNumber: 205,
                      columnNumber: 33
                    }, this),
                    /* @__PURE__ */ jsxDEV("span", { className: "small font-weight-bold fw-bold text-primary", children: text(changes.has(capability.name) ? "bulk_added" : "bulk_add") }, void 0, false, {
                      fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
                      lineNumber: 209,
                      columnNumber: 33
                    }, this)
                  ]
                },
                void 0,
                true,
                {
                  fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
                  lineNumber: 203,
                  columnNumber: 29
                },
                this
              ) }, capability.name, false, {
                fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
                lineNumber: 202,
                columnNumber: 52
              }, this))
            ]
          },
          void 0,
          true,
          {
            fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
            lineNumber: 197,
            columnNumber: 21
          },
          this
        )
      ] }, void 0, true, {
        fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
        lineNumber: 191,
        columnNumber: 17
      }, this),
      /* @__PURE__ */ jsxDEV("div", { children: [
        /* @__PURE__ */ jsxDEV("p", { className: "font-weight-bold fw-bold d-block mb-2", children: [
          text("bulk_changes"),
          " ",
          changes.size > 0 && /* @__PURE__ */ jsxDEV("span", { className: "text-muted fw-normal", children: [
            "(",
            changes.size,
            ")"
          ] }, void 0, true, {
            fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
            lineNumber: 218,
            columnNumber: 69
          }, this)
        ] }, void 0, true, {
          fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
          lineNumber: 217,
          columnNumber: 21
        }, this),
        changes.size === 0 ? /* @__PURE__ */ jsxDEV("p", { className: "text-muted mb-0", children: text("bulk_changes_empty") }, void 0, false, {
          fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
          lineNumber: 220,
          columnNumber: 43
        }, this) : /* @__PURE__ */ jsxDEV("ul", { className: "list-group", children: Array.from(changes, ([name, permission]) => {
          const capability = byname.get(name);
          return /* @__PURE__ */ jsxDEV("li", { className: "list-group-item", children: [
            /* @__PURE__ */ jsxDEV("span", { className: "d-flex flex-column", children: [
              /* @__PURE__ */ jsxDEV("span", { className: "font-weight-bold fw-bold", children: capability?.label ?? name }, void 0, false, {
                fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
                lineNumber: 226,
                columnNumber: 41
              }, this),
              /* @__PURE__ */ jsxDEV("span", { className: "small text-muted", children: name }, void 0, false, {
                fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
                lineNumber: 227,
                columnNumber: 41
              }, this)
            ] }, void 0, true, {
              fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
              lineNumber: 225,
              columnNumber: 37
            }, this),
            /* @__PURE__ */ jsxDEV(
              Segmented,
              {
                label: capability?.label ?? name,
                value: permission,
                onChange: (value) => setChanges((before) => new Map(before).set(name, value)),
                options: [
                  {
                    value: ALLOW,
                    tone: "allow",
                    label: text("permissionallow"),
                    disabled: !capability?.canallow,
                    title: capability?.canallow ? void 0 : text("cannotallow_hint")
                  },
                  { value: PREVENT, tone: "prevent", label: text("perm_prevent") },
                  { value: PROHIBIT, tone: "prohibit", label: text("permissionprohibit") },
                  { value: INHERIT, label: text("bulk_remove") }
                ]
              },
              void 0,
              false,
              {
                fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
                lineNumber: 229,
                columnNumber: 37
              },
              this
            ),
            /* @__PURE__ */ jsxDEV(
              "button",
              {
                type: "button",
                className: "btn btn-link btn-icon btn-sm",
                title: text("bulk_remove"),
                "aria-label": `${text("bulk_remove")}: ${capability?.label ?? name}`,
                onClick: () => toggle(name),
                children: /* @__PURE__ */ jsxDEV(Icon, { name: "e/cancel", strings }, void 0, false, {
                  fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
                  lineNumber: 242,
                  columnNumber: 41
                }, this)
              },
              void 0,
              false,
              {
                fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
                lineNumber: 239,
                columnNumber: 37
              },
              this
            )
          ] }, name, true, {
            fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
            lineNumber: 224,
            columnNumber: 40
          }, this);
        }) }, void 0, false, {
          fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
          lineNumber: 221,
          columnNumber: 27
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
        lineNumber: 216,
        columnNumber: 17
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
      lineNumber: 190,
      columnNumber: 34
    }, this) : /* @__PURE__ */ jsxDEV("div", { "data-region": "copy", children: [
      /* @__PURE__ */ jsxDEV(SourcePicker, { chosen: source?.id ?? 0, onChoose: setSource, exclude: 0, strings }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
        lineNumber: 249,
        columnNumber: 17
      }, this),
      /* @__PURE__ */ jsxDEV("div", { children: [
        /* @__PURE__ */ jsxDEV("p", { className: "font-weight-bold fw-bold d-block mb-2", children: text("bulk_copy_how") }, void 0, false, {
          fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
          lineNumber: 251,
          columnNumber: 21
        }, this),
        /* @__PURE__ */ jsxDEV("ul", { className: "row list-unstyled mb-0", children: [false, true].map((value) => /* @__PURE__ */ jsxDEV("li", { className: "col-sm-6 mb-2", children: /* @__PURE__ */ jsxDEV("label", { className: "card card-body flex-row h-100 mb-0", children: [
          /* @__PURE__ */ jsxDEV("input", { type: "radio", name: "replace", checked: replace === value, onChange: () => setReplace(value) }, void 0, false, {
            fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
            lineNumber: 255,
            columnNumber: 33
          }, this),
          /* @__PURE__ */ jsxDEV("span", { className: "ms-3", children: [
            /* @__PURE__ */ jsxDEV("b", { className: "d-block", children: text(value ? "bulk_copy_replace" : "bulk_copy_merge") }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
              lineNumber: 257,
              columnNumber: 37
            }, this),
            /* @__PURE__ */ jsxDEV("small", { className: "text-muted", children: text(value ? "bulk_copy_replace_desc" : "bulk_copy_merge_desc") }, void 0, false, {
              fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
              lineNumber: 258,
              columnNumber: 37
            }, this)
          ] }, void 0, true, {
            fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
            lineNumber: 256,
            columnNumber: 33
          }, this)
        ] }, void 0, true, {
          fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
          lineNumber: 254,
          columnNumber: 29
        }, this) }, String(value), false, {
          fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
          lineNumber: 253,
          columnNumber: 55
        }, this)) }, void 0, false, {
          fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
          lineNumber: 252,
          columnNumber: 21
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
        lineNumber: 250,
        columnNumber: 17
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
      lineNumber: 248,
      columnNumber: 22
    }, this),
    error && /* @__PURE__ */ jsxDEV("div", { className: "alert alert-danger mt-3 mb-0", role: "alert", children: error }, void 0, false, {
      fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
      lineNumber: 267,
      columnNumber: 23
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
    lineNumber: 185,
    columnNumber: 9
  }, this) }, void 0, false, {
    fileName: "public/local/delegateaccount/js/esm/src/BulkPermissions.tsx",
    lineNumber: 179,
    columnNumber: 12
  }, this);
}
__name(BulkPermissions, "BulkPermissions");
export {
  BulkPermissions,
  CopyFromDialog,
  SourcePicker
};
//# sourceMappingURL=BulkPermissions.dev.js.map
