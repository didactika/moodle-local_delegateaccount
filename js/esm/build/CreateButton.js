import{Icon as a}from"./components/ui";import{useText as r}from"./engine/text";import{openForm as m,toast as c}from"./engine/moodle";import{jsx as p,jsxs as d}from"react/jsx-runtime";/**
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
 */const s=(e,n,o)=>m("local_delegateaccount\\form\\assign_dynamic_form",{realuserid:e},o("create_delegations"),n,t=>{document.dispatchEvent(new CustomEvent("local_delegateaccount:created",{detail:t})),t.message&&c(t.message,t.type??"success")});function i({strings:e,realuserid:n}){const o=r(e);return d("button",{type:"button",className:"btn btn-primary",onClick:t=>void s(n,t.currentTarget,o),children:[p(a,{name:"t/add",strings:e})," ",o("create_delegations")]})}export{i as default,s as openCreate};
