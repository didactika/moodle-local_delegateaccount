import{useEffect as d}from"react";/**
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
 */const l='a[href*="/local/delegateaccount/pages/accounts.php"]',r="#carousel-item-delegatedaccounts",m=["#user-menu-toggle",'a[data-carousel-target-id="carousel-item-delegatedaccounts"]',"#user-action-menu"].map(o=>`${o}[data-flexitour="highlight"]`).join(", "),g=['#user-menu-toggle[data-flexitour="highlight"]','a[data-carousel-target-id="carousel-item-delegatedaccounts"][data-flexitour="highlight"]',`#user-action-menu[data-flexitour="highlight"] ${r} .items a:not(${l})`,'[data-flexitour="container"] button[data-role]'].join(", ");function p({html:o}){return d(()=>{const n=document.querySelector(".usermenu #usermenu-carousel .carousel-inner"),t=document.querySelector(".usermenu #carousel-item-main")?.querySelector(l);if(!n||!t||n.querySelector(r))return;const a=document.createElement("template");a.innerHTML=o,n.append(a.content.cloneNode(!0)),t.href="#",t.classList.add("carousel-navigation-link"),t.dataset.carouselTargetId="carousel-item-delegatedaccounts";const c=e=>{e.target!==t&&(e.preventDefault(),e.stopPropagation(),t.click())};t.addEventListener("click",c);const u=()=>document.querySelector(m)!==null&&document.querySelector(r)!==null,s=e=>{u()&&!e.target.closest(g)&&(e.preventDefault(),e.stopPropagation())},i=e=>{e.key==="Escape"&&u()&&e.stopPropagation()};return document.addEventListener("click",s,!0),document.addEventListener("keydown",i,!0),()=>{t.removeEventListener("click",c),document.removeEventListener("click",s,!0),document.removeEventListener("keydown",i,!0)}},[o]),null}export{p as default};
