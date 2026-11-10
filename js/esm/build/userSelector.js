import{fetchOne as l}from"@moodle/lms/core/ajax";/**
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
 */const p=t=>t.replace(/[&<>"']/g,e=>`&#${e.charCodeAt(0)};`),m=async(t,e,n,s)=>{const r=document.querySelector(t),c=Number(r?.dataset.realuserid??0),i=r?.dataset.ws||"local_delegateaccount_get_delegated_account_options";try{const o=await l({methodname:i,args:{query:e,realuserid:c}});n(o.map(a=>({...a,label:`<span>${p(a.name)}</span>`})))}catch(o){s(o)}},g=(t,e)=>Array.isArray(e)?e.map(n=>({value:n.id,label:n.label})):e;export{g as processResults,m as transport};
