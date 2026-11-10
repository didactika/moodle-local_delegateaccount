/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Every web service call of the plugin, in one place.
 *
 * @module     local_delegateaccount/services/api
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {fetchOne} from '@moodle/lms/core/ajax';
import type {
    AccountRow,
    ActivityRow,
    DelegationRow,
    Filters,
    Override,
    Page,
    PersonRow,
    Permissions,
    Selection,
} from '../types';

/** How a list is sorted and paged. */
export interface Paging {
    sort?: string;
    direction?: string;
    page: number;
    perpage: number;
}

/**
 * One page of the people of the management page.
 *
 * @param scope authorised or historical.
 * @param search Identity fragment.
 * @param status Status of their delegations, none, or empty.
 * @param paging Sort and page.
 * @returns The page.
 */
export const getUserList = (scope: string, search: string, status: string, paging: Paging): Promise<Page<PersonRow>> =>
    fetchOne<Page<PersonRow>>({methodname: 'local_delegateaccount_get_user_list', args: {scope, search, status, ...paging}});

/**
 * One page of delegations.
 *
 * @param filters Person, status and search.
 * @param paging Sort and page.
 * @returns The page.
 */
export const getDelegationList = (filters: Filters, paging: Paging): Promise<Page<DelegationRow>> =>
    fetchOne<Page<DelegationRow>>({methodname: 'local_delegateaccount_get_delegation_list', args: {filters, ...paging}});

/**
 * The delegations that match a list and can still be changed, within the limit of a bulk action.
 *
 * @param filters Person, status and search.
 * @returns The identifiers, or why they cannot be changed together.
 */
export const getDelegationIds = (filters: Filters): Promise<{ids: number[]; total: number; error: string}> =>
    fetchOne({methodname: 'local_delegateaccount_get_delegation_ids', args: {filters}});

/**
 * One page of the activity of a delegation.
 *
 * @param args Delegation, days, component, action and page.
 * @returns The page.
 */
export const getActivityList = (args: Record<string, string | number>): Promise<Page<ActivityRow>> =>
    fetchOne<Page<ActivityRow>>({methodname: 'local_delegateaccount_get_activity_list', args});

/**
 * One page of the accounts the person may open.
 *
 * @param search Name or email fragment.
 * @param paging Page.
 * @returns The page.
 */
export const getMyAccounts = (search: string, paging: Paging): Promise<Page<AccountRow>> =>
    fetchOne<Page<AccountRow>>({
        methodname: 'local_delegateaccount_get_my_accounts',
        args: {search, page: paging.page, perpage: paging.perpage},
    });

/**
 * Revoke delegations.
 *
 * @param delegationids The delegations.
 * @returns How many were revoked.
 */
export const revokeDelegations = (delegationids: number[]): Promise<{revokedcount: number}> =>
    fetchOne({methodname: 'local_delegateaccount_revoke_delegations', args: {delegationids, confirm: true}});

/**
 * The capabilities of the site and the overrides of the site or of one delegation.
 *
 * @param delegationid Delegation, or zero for the site.
 * @returns What the editor reads.
 */
export const getPermissions = (delegationid: number): Promise<Permissions> =>
    fetchOne<Permissions>({methodname: 'local_delegateaccount_get_session_permissions', args: {delegationid}});

/**
 * Change some overrides of the site or of one delegation.
 *
 * @param delegationid Delegation, or zero for the site.
 * @param changes The changes; permission 0 removes an override.
 * @returns How many changed and the overrides now.
 */
export const savePermissions = (delegationid: number, changes: Override[]): Promise<{changed: number; overrides: Override[]}> =>
    fetchOne({methodname: 'local_delegateaccount_save_session_permissions', args: {delegationid, changes}});

/**
 * Change, or copy from one delegation, the overrides of many delegations.
 *
 * @param args The selection and either the changes or the delegation to copy from.
 * @returns How many delegations were reached.
 */
export const updatePermissions = (
    args: {selection: Selection; changes?: Override[]; copyfrom?: number; replace?: boolean},
): Promise<{count: number}> =>
    fetchOne({methodname: 'local_delegateaccount_update_session_permissions', args});
