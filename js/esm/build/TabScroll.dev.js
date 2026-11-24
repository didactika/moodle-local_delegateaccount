var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Brings the open tab of each row of tabs to the middle of its row, which on a narrow screen scrolls sideways, so the person
 * sees where they are. It draws nothing.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_delegateaccount/TabScroll
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect } from "react";
function TabScroll() {
  useEffect(() => {
    document.querySelectorAll(".lda .nav-tabs").forEach((row) => {
      const tab = row.querySelector(".nav-link.active");
      if (tab) {
        const outer = row.getBoundingClientRect();
        const inner = tab.getBoundingClientRect();
        row.scrollLeft += inner.left - outer.left - (outer.width - inner.width) / 2;
      }
    });
  }, []);
  return null;
}
__name(TabScroll, "TabScroll");
export {
  TabScroll as default
};
//# sourceMappingURL=TabScroll.dev.js.map
