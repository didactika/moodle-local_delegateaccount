var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Turns the native "Delegated accounts" link of the user menu into a submenu of Moodle's user-menu carousel, and keeps the
 * guided tour of delegated accounts open until it is finished.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes before its body; it draws nothing of its
 * own.
 *
 * @module     local_delegateaccount/UserMenu
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect } from "react";
const FALLBACK_LINK = 'a[href*="/local/delegateaccount/pages/accounts.php"]';
const PANEL = "#carousel-item-delegatedaccounts";
const TOUR_TARGETS = [
  "#user-menu-toggle",
  'a[data-carousel-target-id="carousel-item-delegatedaccounts"]',
  "#user-action-menu"
].map((selector) => `${selector}[data-flexitour="highlight"]`).join(", ");
const TOUR_CLICKABLE = [
  '#user-menu-toggle[data-flexitour="highlight"]',
  'a[data-carousel-target-id="carousel-item-delegatedaccounts"][data-flexitour="highlight"]',
  `#user-action-menu[data-flexitour="highlight"] ${PANEL} .items a:not(${FALLBACK_LINK})`,
  '[data-flexitour="container"] button[data-role]'
].join(", ");
function UserMenu({ html }) {
  useEffect(() => {
    const carousel = document.querySelector(".usermenu #usermenu-carousel .carousel-inner");
    const main = document.querySelector(".usermenu #carousel-item-main");
    const trigger = main?.querySelector(FALLBACK_LINK);
    if (!carousel || !trigger || carousel.querySelector(PANEL)) {
      return void 0;
    }
    const template = document.createElement("template");
    template.innerHTML = html;
    carousel.append(template.content.cloneNode(true));
    trigger.href = "#";
    trigger.classList.add("carousel-navigation-link");
    trigger.dataset.carouselTargetId = "carousel-item-delegatedaccounts";
    const open = /* @__PURE__ */ __name((event) => {
      if (event.target !== trigger) {
        event.preventDefault();
        event.stopPropagation();
        trigger.click();
      }
    }, "open");
    trigger.addEventListener("click", open);
    const active = /* @__PURE__ */ __name(() => document.querySelector(TOUR_TARGETS) !== null && document.querySelector(PANEL) !== null, "active");
    const click = /* @__PURE__ */ __name((event) => {
      if (active() && !event.target.closest(TOUR_CLICKABLE)) {
        event.preventDefault();
        event.stopPropagation();
      }
    }, "click");
    const escape = /* @__PURE__ */ __name((event) => {
      if (event.key === "Escape" && active()) {
        event.stopPropagation();
      }
    }, "escape");
    document.addEventListener("click", click, true);
    document.addEventListener("keydown", escape, true);
    return () => {
      trigger.removeEventListener("click", open);
      document.removeEventListener("click", click, true);
      document.removeEventListener("keydown", escape, true);
    };
  }, [html]);
  return null;
}
__name(UserMenu, "UserMenu");
export {
  UserMenu as default
};
//# sourceMappingURL=UserMenu.dev.js.map
