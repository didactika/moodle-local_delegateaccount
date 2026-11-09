/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The four permissions an override can set, and the search of the capabilities of the site.
 *
 * @module     local_delegateaccount/engine/capabilities
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import type {Capability, Override} from '../types';

/** Not set: the override is removed. */
export const INHERIT = 0;
/** Allow. */
export const ALLOW = 1;
/** Prevent. */
export const PREVENT = -1;
/** Prohibit. */
export const PROHIBIT = -1000;

/** The permissions in the order they are offered, with the name of the text of each. */
export const PERMISSIONS: Array<{value: number; key: string; tone: string}> = [
    {value: INHERIT, key: 'perm_notset', tone: 'notset'},
    {value: ALLOW, key: 'permissionallow', tone: 'allow'},
    {value: PREVENT, key: 'perm_prevent', tone: 'prevent'},
    {value: PROHIBIT, key: 'permissionprohibit', tone: 'prohibit'},
];

/**
 * Split a search into words, in lower case.
 *
 * @param text What was typed.
 * @returns The words.
 */
export const words = (text: string): string[] => text.toLowerCase().split(/\s+/).filter(Boolean);

const haystacks = new WeakMap<Capability, string>();

/**
 * Whether a capability matches what was typed: every word must be in its name, its description or its component.
 *
 * @param capability The capability.
 * @param typed The words typed.
 * @returns Whether it matches.
 */
export const matches = (capability: Capability, typed: string[]): boolean => {
    if (!typed.length) {
        return true;
    }
    let haystack = haystacks.get(capability);
    if (haystack === undefined) {
        haystack = `${capability.label} ${capability.name} ${capability.componentlabel}`.toLowerCase();
        haystacks.set(capability, haystack);
    }
    return typed.every((word) => (haystack as string).includes(word));
};

/**
 * Turn a list of overrides into a map.
 *
 * @param overrides The overrides.
 * @returns Permission by capability.
 */
export const toMap = (overrides: Override[]): Map<string, number> =>
    new Map(overrides.map(({capability, permission}) => [capability, permission]));
