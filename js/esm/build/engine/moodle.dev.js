var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Typed adapters for the parts of Moodle that have no native ESM module yet: its toasts and the dialogs of its forms. The
 * forms that create and edit delegations stay Moodle forms, as the design system asks.
 *
 * @module     local_delegateaccount/engine/moodle
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { requireAsync } from "@moodle/lms/core/amd";
const load = /* @__PURE__ */ __name(async (id) => {
  const module = await requireAsync(id);
  return typeof module === "object" && module !== null && "default" in module ? module.default : module;
}, "load");
const toast = /* @__PURE__ */ __name(async (message, type = "success") => {
  const module = await load("core/toast");
  await module.add(message, { type, closeButton: true });
}, "toast");
const describeError = /* @__PURE__ */ __name((error) => {
  if (error instanceof Error) {
    return error.message;
  }
  if (error !== null && typeof error === "object" && "message" in error) {
    return String(error.message);
  }
  return String(error);
}, "describeError");
const normalise = /* @__PURE__ */ __name((root) => {
  const narrow = window.matchMedia("(max-width: 575.98px)").matches;
  root.querySelectorAll(".fdate_selector, .fdate_time_selector").forEach((controls) => {
    controls.classList.toggle("flex-wrap", narrow);
    controls.classList.toggle("flex-nowrap", !narrow);
    Array.from(controls.children).forEach((control) => {
      control.classList.remove("mx-1", "mx-2", "ms-1", "ms-2", "me-1", "me-2");
    });
  });
}, "normalise");
const openForm = /* @__PURE__ */ __name(async (formClass, args, title, trigger, onSubmitted) => {
  const ModalForm = await load("core_form/modalform");
  const form = new ModalForm({ modalConfig: { title, large: true }, formClass, args, returnFocus: trigger });
  form.addEventListener(form.events.LOADED, () => {
    const root = form.modal.getRoot()[0];
    root.classList.add("lda-modal");
    normalise(root);
  });
  form.addEventListener(form.events.FORM_SUBMITTED, (event) => onSubmitted(event.detail || {}));
  await form.show();
}, "openForm");
export {
  describeError,
  openForm,
  toast
};
//# sourceMappingURL=moodle.dev.js.map
