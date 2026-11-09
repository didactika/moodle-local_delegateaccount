var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The four permissions an override can set, and the search of the capabilities of the site.
 *
 * @module     local_delegateaccount/engine/capabilities
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
const INHERIT = 0;
const ALLOW = 1;
const PREVENT = -1;
const PROHIBIT = -1e3;
const PERMISSIONS = [
  { value: INHERIT, key: "perm_notset", tone: "notset" },
  { value: ALLOW, key: "permissionallow", tone: "allow" },
  { value: PREVENT, key: "perm_prevent", tone: "prevent" },
  { value: PROHIBIT, key: "permissionprohibit", tone: "prohibit" }
];
const words = /* @__PURE__ */ __name((text) => text.toLowerCase().split(/\s+/).filter(Boolean), "words");
const haystacks = /* @__PURE__ */ new WeakMap();
const matches = /* @__PURE__ */ __name((capability, typed) => {
  if (!typed.length) {
    return true;
  }
  let haystack = haystacks.get(capability);
  if (haystack === void 0) {
    haystack = `${capability.label} ${capability.name} ${capability.componentlabel}`.toLowerCase();
    haystacks.set(capability, haystack);
  }
  return typed.every((word) => haystack.includes(word));
}, "matches");
const toMap = /* @__PURE__ */ __name((overrides) => new Map(overrides.map(({ capability, permission }) => [capability, permission])), "toMap");
export {
  ALLOW,
  INHERIT,
  PERMISSIONS,
  PREVENT,
  PROHIBIT,
  matches,
  toMap,
  words
};
//# sourceMappingURL=capabilities.dev.js.map
