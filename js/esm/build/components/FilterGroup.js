import{useEffect as T,useId as H,useLayoutEffect as N,useRef as u,useState as h}from"react";import{Icon as O}from"./ui";import{jsx as o,jsxs as y}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The menus of a row of filters, beside the search. When they do not fit, on a narrow screen or when the values chosen are long,
 * they are grouped in one menu, Filters, that says how many narrow the list and holds them; they come back to the row when there
 * is room again, so the row never breaks in two.
 *
 * @module     local_delegateaccount/components/FilterGroup
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const x=160;function C({count:a,text:s,strings:L,children:c}){const[l,f]=h(!1),[t,d]=h(!1),r=u(null),p=u(null),m=u(0),v=H();if(N(()=>{const e=r.current?.parentElement;if(!r.current||!e)return;const i=()=>{const g=parseFloat(getComputedStyle(e).columnGap)||0,b=e.clientWidth-x-g;if(l)b>=m.current&&(d(!1),f(!1));else{const w=Array.from(r.current?.children??[]),E=w.reduce((R,S)=>R+S.offsetWidth,0)+g*Math.max(0,w.length-1);E>b&&(m.current=E,f(!0))}};i();const n=new ResizeObserver(i);return n.observe(e),()=>n.disconnect()}),T(()=>{if(!t)return;const e=i=>{const n=i.target;!p.current?.contains(n)&&!n.closest(".dropdown-menu, .dropdown-menu")&&d(!1)};return document.addEventListener("pointerdown",e,!0),()=>document.removeEventListener("pointerdown",e,!0)},[t]),!l)return o("div",{ref:r,"data-region":"filter-menus",children:c});const M=a>0?`${s("core:filters")} (${a})`:s("core:filters");return o("div",{ref:r,"data-region":"filter-menus",children:y("span",{ref:p,className:"dropdown","data-menu":"filters",children:[y("button",{type:"button",className:`form-select${a>0?" border-primary":""}`,"aria-haspopup":"true","aria-expanded":t,"aria-controls":t?v:void 0,"aria-label":s("core:filters"),onClick:()=>d(!t),children:[o(O,{name:"i/filter",strings:L})," ",M]}),o("div",{id:v,className:"dropdown-menu show dropdown-menu-end",role:"group","aria-label":s("core:filters"),hidden:!t,children:o("div",{"data-region":"filter-panel",children:c})})]})})}export{C as default};
