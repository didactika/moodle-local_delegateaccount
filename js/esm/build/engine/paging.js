/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The sizes a page of a list may have, and which rows a page shows.
 *
 * @module     local_delegateaccount/engine/paging
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const u=[12,24,48,96,0],b=12,c=(n,e=b)=>u.includes(n)?n:e,i=(n,e,r)=>{const t=e>0?e:Math.max(r,1),m=Math.max(0,Math.ceil(r/t)-1),s=Math.min(n,m)*t,o=Math.min(r,s+t);return{from:r?s+1:0,to:o,lastpage:m,start:s,end:o}},a=(n,e)=>({sizes:u.filter(r=>r===0||r<e),shown:n>0&&n<e?n:0});export{b as DEFAULT_PAGE_SIZE,u as PAGE_SIZES,a as fitPageSizes,i as pageRange,c as pageSize};
