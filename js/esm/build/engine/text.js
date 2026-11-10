import{useMemo as s}from"react";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Text of the page, which the server sends once with the properties of each component.
 *
 * @module     local_delegateaccount/engine/text
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const p=e=>(r,t="")=>{const o=e[r]??r;return t!==null&&typeof t=="object"?Object.keys(t).reduce((i,n)=>i.split(`{${n}}`).join(String(t[n])),o):o.split("{n}").join(String(t))},c=e=>s(()=>p(e),[e]);export{p as createText,c as useText};
