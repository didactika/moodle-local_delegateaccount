var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The main action of the management pages: create delegations with the Moodle form of the plugin, for several people or for
 * one, and tell the lists of the page so they show them at once.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_delegateaccount/CreateButton
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { Icon } from "./components/ui";
import { useText } from "./engine/text";
import { openForm, toast } from "./engine/moodle";
const openCreate = /* @__PURE__ */ __name((realuserid, trigger, text) => openForm(
  "local_delegateaccount\\form\\assign_dynamic_form",
  { realuserid },
  text("create_delegations"),
  trigger,
  (result) => {
    document.dispatchEvent(new CustomEvent("local_delegateaccount:created", { detail: result }));
    if (result.message) {
      void toast(result.message, result.type ?? "success");
    }
  }
), "openCreate");
function CreateButton({ strings, realuserid }) {
  const text = useText(strings);
  return /* @__PURE__ */ jsxDEV(
    "button",
    {
      type: "button",
      className: "btn btn-primary",
      onClick: (event) => void openCreate(realuserid, event.currentTarget, text),
      children: [
        /* @__PURE__ */ jsxDEV(Icon, { name: "t/add", strings }, void 0, false, {
          fileName: "public/local/delegateaccount/js/esm/src/CreateButton.tsx",
          lineNumber: 50,
          columnNumber: 9
        }, this),
        " ",
        text("create_delegations")
      ]
    },
    void 0,
    true,
    {
      fileName: "public/local/delegateaccount/js/esm/src/CreateButton.tsx",
      lineNumber: 48,
      columnNumber: 12
    },
    this
  );
}
__name(CreateButton, "CreateButton");
export {
  CreateButton as default,
  openCreate
};
//# sourceMappingURL=CreateButton.dev.js.map
