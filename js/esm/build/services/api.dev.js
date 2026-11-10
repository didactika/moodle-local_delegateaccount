var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
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
import { fetchOne } from "@moodle/lms/core/ajax";
const getUserList = /* @__PURE__ */ __name((scope, search, status, paging) => fetchOne({ methodname: "local_delegateaccount_get_user_list", args: { scope, search, status, ...paging } }), "getUserList");
const getDelegationList = /* @__PURE__ */ __name((filters, paging) => fetchOne({ methodname: "local_delegateaccount_get_delegation_list", args: { filters, ...paging } }), "getDelegationList");
const getDelegationIds = /* @__PURE__ */ __name((filters) => fetchOne({ methodname: "local_delegateaccount_get_delegation_ids", args: { filters } }), "getDelegationIds");
const getActivityList = /* @__PURE__ */ __name((args) => fetchOne({ methodname: "local_delegateaccount_get_activity_list", args }), "getActivityList");
const getMyAccounts = /* @__PURE__ */ __name((search, paging) => fetchOne({
  methodname: "local_delegateaccount_get_my_accounts",
  args: { search, page: paging.page, perpage: paging.perpage }
}), "getMyAccounts");
const revokeDelegations = /* @__PURE__ */ __name((delegationids) => fetchOne({ methodname: "local_delegateaccount_revoke_delegations", args: { delegationids, confirm: true } }), "revokeDelegations");
const getPermissions = /* @__PURE__ */ __name((delegationid) => fetchOne({ methodname: "local_delegateaccount_get_session_permissions", args: { delegationid } }), "getPermissions");
const savePermissions = /* @__PURE__ */ __name((delegationid, changes) => fetchOne({ methodname: "local_delegateaccount_save_session_permissions", args: { delegationid, changes } }), "savePermissions");
const updatePermissions = /* @__PURE__ */ __name((args) => fetchOne({ methodname: "local_delegateaccount_update_session_permissions", args }), "updatePermissions");
export {
  getActivityList,
  getDelegationIds,
  getDelegationList,
  getMyAccounts,
  getPermissions,
  getUserList,
  revokeDelegations,
  savePermissions,
  updatePermissions
};
//# sourceMappingURL=api.dev.js.map
