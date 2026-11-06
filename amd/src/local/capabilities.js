// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * The capabilities of the site and the four permissions an override can set, for the permission editor and the dialog of
 * many delegations.
 *
 * @module     local_delegateaccount/local/capabilities
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import {getStrings} from 'core/str';

/** Not set: the override is removed. */
export const INHERIT = 0;
/** Allow. */
export const ALLOW = 1;
/** Prevent. */
export const PREVENT = -1;
/** Prohibit. */
export const PROHIBIT = -1000;

/** The permissions in the order they are offered, with the class that colours each one. */
export const PERMISSIONS = [
    {value: INHERIT, key: 'perm_notset', tone: 'notset'},
    {value: ALLOW, key: 'permissionallow', tone: 'allow'},
    {value: PREVENT, key: 'perm_prevent', tone: 'prevent'},
    {value: PROHIBIT, key: 'permissionprohibit', tone: 'prohibit'},
];

let names = null;

/**
 * The names of the permissions, by value.
 *
 * @returns {Promise<Object<number, string>>}
 */
export const permissionNames = async() => {
    if (!names) {
        const strings = await getStrings(PERMISSIONS.map(({key}) => ({key, component: 'local_delegateaccount'})));
        names = Object.fromEntries(PERMISSIONS.map(({value}, index) => [value, strings[index]]));
    }
    return names;
};

/**
 * Loads the capabilities and the overrides of the site or of one delegation.
 *
 * @param {number} delegationid Delegation identifier, or zero for the site.
 * @returns {Promise<Object>} What local_delegateaccount_get_session_permissions returns.
 */
export const loadPermissions = (delegationid) => Ajax.call([{
    methodname: 'local_delegateaccount_get_session_permissions',
    args: {delegationid},
}])[0];

/**
 * Turns a list of overrides into a map.
 *
 * @param {Array<{capability: string, permission: number}>} overrides The overrides.
 * @returns {Map<string, number>} Permission by capability.
 */
export const toMap = (overrides) => new Map(overrides.map(({capability, permission}) => [capability, permission]));

/**
 * Whether a capability matches what was typed: every word must be in its name, its description or its component.
 *
 * @param {Object} capability The capability.
 * @param {string[]} words What was typed, in lower case and split by spaces.
 * @returns {boolean}
 */
export const matches = (capability, words) => {
    if (!words.length) {
        return true;
    }
    if (!capability.haystack) {
        capability.haystack = `${capability.label} ${capability.name} ${capability.componentlabel}`.toLowerCase();
    }
    return words.every((word) => capability.haystack.includes(word));
};

/**
 * Splits a search into words.
 *
 * @param {string} text What was typed.
 * @returns {string[]} The words.
 */
export const words = (text) => text.toLowerCase().split(/\s+/).filter(Boolean);
