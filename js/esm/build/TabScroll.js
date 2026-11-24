import{useEffect as c}from"react";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Brings the open tab of each row of tabs to the middle of its row, which on a narrow screen scrolls sideways, so the person
 * sees where they are. It draws nothing.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_delegateaccount/TabScroll
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */function o(){return c(()=>{document.querySelectorAll(".lda .nav-tabs").forEach(t=>{const e=t.querySelector(".nav-link.active");if(e){const n=t.getBoundingClientRect(),l=e.getBoundingClientRect();t.scrollLeft+=l.left-n.left-(n.width-l.width)/2}})},[]),null}export{o as default};
