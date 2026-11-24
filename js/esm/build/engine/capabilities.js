/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The four permissions an override can set, and the search of the capabilities of the site.
 *
 * @module     local_delegateaccount/engine/capabilities
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const s=0,a=1,i=-1,p=-1e3,l=[{value:0,key:"perm_notset",tone:"light"},{value:1,key:"permissionallow",tone:"success"},{value:-1,key:"perm_prevent",tone:"warning"},{value:-1e3,key:"permissionprohibit",tone:"danger"}],g=e=>e.toLowerCase().split(/\s+/).filter(Boolean),r=new WeakMap,u=(e,n)=>{if(!n.length)return!0;let t=r.get(e);return t===void 0&&(t=`${e.label} ${e.name} ${e.componentlabel}`.toLowerCase(),r.set(e,t)),n.every(o=>t.includes(o))},c=e=>new Map(e.map(({capability:n,permission:t})=>[n,t]));export{a as ALLOW,s as INHERIT,l as PERMISSIONS,i as PREVENT,p as PROHIBIT,u as matches,c as toMap,g as words};
