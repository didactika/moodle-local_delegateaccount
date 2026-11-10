import{fetchOne as o}from"@moodle/lms/core/ajax";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Every web service call of the plugin, in one place.
 *
 * @module     local_delegateaccount/services/api
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const i=(e,t,s,r)=>o({methodname:"local_delegateaccount_get_user_list",args:{scope:e,search:t,status:s,...r}}),a=(e,t)=>o({methodname:"local_delegateaccount_get_delegation_list",args:{filters:e,...t}}),g=e=>o({methodname:"local_delegateaccount_get_delegation_ids",args:{filters:e}}),c=e=>o({methodname:"local_delegateaccount_get_activity_list",args:e}),m=(e,t)=>o({methodname:"local_delegateaccount_get_my_accounts",args:{search:e,page:t.page,perpage:t.perpage}}),l=e=>o({methodname:"local_delegateaccount_revoke_delegations",args:{delegationids:e,confirm:!0}}),d=e=>o({methodname:"local_delegateaccount_get_session_permissions",args:{delegationid:e}}),_=(e,t)=>o({methodname:"local_delegateaccount_save_session_permissions",args:{delegationid:e,changes:t}}),u=e=>o({methodname:"local_delegateaccount_update_session_permissions",args:e});export{c as getActivityList,g as getDelegationIds,a as getDelegationList,m as getMyAccounts,d as getPermissions,i as getUserList,l as revokeDelegations,_ as savePermissions,u as updatePermissions};
