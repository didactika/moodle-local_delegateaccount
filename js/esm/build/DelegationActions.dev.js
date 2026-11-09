var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The actions of the page of one delegation: open its activity, change its period and revoke it. The page is drawn again
 * afterwards, so its details and its history say what changed.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_delegateaccount/DelegationActions
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useState } from "react";
import Pending from "@moodle/lms/core/pending";
import { ConfirmDialog, Icon } from "./components/ui";
import { useText } from "./engine/text";
import { openForm } from "./engine/moodle";
import { revokeDelegations } from "./services/api";
const reload = /* @__PURE__ */ __name(() => {
  new Pending("local_delegateaccount/delegation:reload");
  window.location.reload();
}, "reload");
function DelegationActions(props) {
  const { strings, delegationid, realuserid, canupdate, canrevoke, activityurl } = props;
  const text = useText(strings);
  const [revoking, setRevoking] = useState(false);
  return /* @__PURE__ */ jsxDEV("div", { className: "dd-page-actions", children: [
    activityurl && /* @__PURE__ */ jsxDEV("a", { className: "btn btn-outline-secondary", href: activityurl, children: [
      /* @__PURE__ */ jsxDEV(Icon, { name: "i/log", strings }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/DelegationActions.tsx",
        lineNumber: 42,
        columnNumber: 13
      }, this),
      " ",
      text("view_delegated_activity")
    ] }, void 0, true, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationActions.tsx",
      lineNumber: 41,
      columnNumber: 25
    }, this),
    canupdate && /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-outline-secondary", onClick: (event) => void openForm(
      "local_delegateaccount\\form\\bulk_edit_dynamic_form",
      { realuserid, delegationids: String(delegationid) },
      text("edit_period"),
      event.currentTarget,
      reload
    ), children: [
      /* @__PURE__ */ jsxDEV(Icon, { name: "t/edit", strings }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/DelegationActions.tsx",
        lineNumber: 51,
        columnNumber: 13
      }, this),
      " ",
      text("edit_period")
    ] }, void 0, true, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationActions.tsx",
      lineNumber: 44,
      columnNumber: 23
    }, this),
    canrevoke && /* @__PURE__ */ jsxDEV("button", { type: "button", className: "btn btn-outline-danger", onClick: () => setRevoking(true), children: [
      /* @__PURE__ */ jsxDEV(Icon, { name: "t/delete", strings }, void 0, false, {
        fileName: "public/local/delegateaccount/js/esm/src/DelegationActions.tsx",
        lineNumber: 54,
        columnNumber: 13
      }, this),
      " ",
      text("revoke")
    ] }, void 0, true, {
      fileName: "public/local/delegateaccount/js/esm/src/DelegationActions.tsx",
      lineNumber: 53,
      columnNumber: 23
    }, this),
    revoking && /* @__PURE__ */ jsxDEV(
      ConfirmDialog,
      {
        text,
        strings,
        onClose: () => setRevoking(false),
        title: text("revoke_confirm_title_one"),
        message: text("revoke_confirm_text"),
        button: text("revoke_confirm_button_one"),
        onConfirm: async () => {
          await revokeDelegations([delegationid]);
          reload();
        }
      },
      void 0,
      false,
      {
        fileName: "public/local/delegateaccount/js/esm/src/DelegationActions.tsx",
        lineNumber: 56,
        columnNumber: 22
      },
      this
    )
  ] }, void 0, true, {
    fileName: "public/local/delegateaccount/js/esm/src/DelegationActions.tsx",
    lineNumber: 40,
    columnNumber: 12
  }, this);
}
__name(DelegationActions, "DelegationActions");
export {
  DelegationActions as default
};
//# sourceMappingURL=DelegationActions.dev.js.map
