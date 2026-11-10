/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The four permissions an override can set, and the search of the capabilities of the site.
 *
 * @module     local_delegateaccount/engine/capabilities
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const s=0,a=1,p=-1,i=-1e3,l=[{value:0,key:"perm_notset",tone:"notset"},{value:1,key:"permissionallow",tone:"allow"},{value:-1,key:"perm_prevent",tone:"prevent"},{value:-1e3,key:"permissionprohibit",tone:"prohibit"}],m=e=>e.toLowerCase().split(/\s+/).filter(Boolean),r=new WeakMap,u=(e,o)=>{if(!o.length)return!0;let t=r.get(e);return t===void 0&&(t=`${e.label} ${e.name} ${e.componentlabel}`.toLowerCase(),r.set(e,t)),o.every(n=>t.includes(n))},c=e=>new Map(e.map(({capability:o,permission:t})=>[o,t]));export{a as ALLOW,s as INHERIT,l as PERMISSIONS,p as PREVENT,i as PROHIBIT,u as matches,c as toMap,m as words};
