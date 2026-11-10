import{useState as p}from"react";import _ from"@moodle/lms/core/pending";import{ConfirmDialog as b,Icon as i}from"./components/ui";import{useText as v}from"./engine/text";import{openForm as k}from"./engine/moodle";import{revokeDelegations as y}from"./services/api";import{jsx as o,jsxs as n}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The actions of the page of one delegation: open its activity, change its period and revoke it. The page is drawn again
 * afterwards, so its details and its history say what changed.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_delegateaccount/DelegationActions
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const l=()=>{new _("local_delegateaccount/delegation:reload"),window.location.reload()};function w(c){const{strings:t,delegationid:r,realuserid:d,canupdate:m,canrevoke:g,activityurl:a}=c,e=v(t),[u,s]=p(!1);return n("div",{className:"dd-page-actions",children:[a&&n("a",{className:"btn btn-outline-secondary",href:a,children:[o(i,{name:"i/log",strings:t})," ",e("view_delegated_activity")]}),m&&n("button",{type:"button",className:"btn btn-outline-secondary",onClick:f=>void k("local_delegateaccount\\form\\bulk_edit_dynamic_form",{realuserid:d,delegationids:String(r)},e("edit_period"),f.currentTarget,l),children:[o(i,{name:"t/edit",strings:t})," ",e("edit_period")]}),g&&n("button",{type:"button",className:"btn btn-outline-danger",onClick:()=>s(!0),children:[o(i,{name:"t/delete",strings:t})," ",e("revoke")]}),u&&o(b,{text:e,strings:t,onClose:()=>s(!1),title:e("revoke_confirm_title_one"),message:e("revoke_confirm_text"),button:e("revoke_confirm_button_one"),onConfirm:async()=>{await y([r]),l()}})]})}export{w as default};
