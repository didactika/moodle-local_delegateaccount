import{useEffect as l}from"react";/**
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
 */const i='a[href*="/local/delegateaccount/pages/accounts.php"]',a="#carousel-item-delegatedaccounts",d=["#user-menu-toggle",'a[data-carousel-target-id="carousel-item-delegatedaccounts"]',"#user-action-menu"].map(o=>`${o}[data-flexitour="highlight"]`).join(", "),m=['#user-menu-toggle[data-flexitour="highlight"]','a[data-carousel-target-id="carousel-item-delegatedaccounts"][data-flexitour="highlight"]',`#user-action-menu[data-flexitour="highlight"] ${a} .items a:not(${i})`,'[data-flexitour="container"] button[data-role]'].join(", ");function g({html:o}){return l(()=>{const n=document.querySelector(".usermenu #usermenu-carousel .carousel-inner"),t=document.querySelector(".usermenu #carousel-item-main")?.querySelector(i);if(!n||!t||n.querySelector(a))return;n.append(document.createRange().createContextualFragment(o)),t.href="#",t.classList.add("carousel-navigation-link"),t.dataset.carouselTargetId="carousel-item-delegatedaccounts";const r=e=>{e.target!==t&&(e.preventDefault(),e.stopPropagation(),t.click())};t.addEventListener("click",r);const c=()=>document.querySelector(d)!==null&&document.querySelector(a)!==null,u=e=>{c()&&!e.target.closest(m)&&(e.preventDefault(),e.stopPropagation())},s=e=>{e.key==="Escape"&&c()&&e.stopPropagation()};return document.addEventListener("click",u,!0),document.addEventListener("keydown",s,!0),()=>{t.removeEventListener("click",r),document.removeEventListener("click",u,!0),document.removeEventListener("keydown",s,!0)}},[o]),null}export{g as default};
