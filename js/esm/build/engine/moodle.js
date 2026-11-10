import{requireAsync as m}from"@moodle/lms/core/amd";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Typed adapters for the parts of Moodle that have no native ESM module yet: its toasts and the dialogs of its forms. The
 * forms that create and edit delegations stay Moodle forms, as the design system asks.
 *
 * @module     local_delegateaccount/engine/moodle
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const r=async e=>{const t=await m(e);return typeof t=="object"&&t!==null&&"default"in t?t.default:t},u=async(e,t="success")=>{await(await r("core/toast")).add(e,{type:t,closeButton:!0})},g=e=>e instanceof Error?e.message:e!==null&&typeof e=="object"&&"message"in e?String(e.message):String(e),d=e=>{const t=window.matchMedia("(max-width: 575.98px)").matches;e.querySelectorAll(".fdate_selector, .fdate_time_selector").forEach(o=>{o.classList.toggle("flex-wrap",t),o.classList.toggle("flex-nowrap",!t),Array.from(o.children).forEach(s=>{s.classList.remove("mx-1","mx-2","ms-1","ms-2","me-1","me-2")})})},f=async(e,t,o,s,i)=>{const l=await r("core_form/modalform"),n=new l({modalConfig:{title:o,large:!0},formClass:e,args:t,returnFocus:s});n.addEventListener(n.events.LOADED,()=>{const a=n.modal.getRoot()[0];d(a)}),n.addEventListener(n.events.FORM_SUBMITTED,a=>i(a.detail||{})),await n.show()};export{g as describeError,f as openForm,u as toast};
