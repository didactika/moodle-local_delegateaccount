var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The source of the user pickers of the forms that create delegations: only the people who may use delegations, and only
 * the accounts that can be delegated to them. Moodle's autocomplete loads it through core/esm, as the transport of the
 * field.
 *
 * @module     local_delegateaccount/userSelector
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { fetchOne } from "@moodle/lms/core/ajax";
const escape = /* @__PURE__ */ __name((text) => text.replace(/[&<>"']/g, (character) => `&#${character.charCodeAt(0)};`), "escape");
const transport = /* @__PURE__ */ __name(async (selector, query, callback, failure) => {
  const select = document.querySelector(selector);
  const realuserid = Number(select?.dataset.realuserid ?? 0);
  const methodname = select?.dataset.ws || "local_delegateaccount_get_delegated_account_options";
  try {
    const results = await fetchOne({ methodname, args: { query, realuserid } });
    callback(results.map((user) => ({ ...user, label: `<span>${escape(user.name)}</span>` })));
  } catch (error) {
    failure(error);
  }
}, "transport");
const processResults = /* @__PURE__ */ __name((selector, results) => Array.isArray(results) ? results.map((result) => ({ value: result.id, label: result.label })) : results, "processResults");
export {
  processResults,
  transport
};
//# sourceMappingURL=userSelector.dev.js.map
