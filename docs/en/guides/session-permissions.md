---
title: Session permissions
summary: Allow, prevent or prohibit capabilities in the sessions opened through a delegation.
order: 23
---

The **Session permissions** tab of the settings, and the **Session permissions** tab of each delegation, set what a session opened through a delegation may do. Each capability of the site has one of four permissions:

| Permission | In the session |
|---|---|
| Not set | Nothing changes: the account keeps what its roles give it. Every capability starts here |
| Allow | The capability is granted everywhere in the session, unless a role prohibits it |
| Prevent | The capability is taken from the roles of the account on the whole site, as Prevent does in **Define roles**. An override of a role in a course or an activity still applies |
| Prohibit | The capability is refused everywhere in the session, and nothing can grant it back |

The setting of a delegation wins over the one of the site; a delegation set to Not set follows the site. Find capabilities by name, technical name or component, narrow them with the chips (overridden, each permission, with risks), and save the changes from the bar at the foot of the window. **Copy from another delegation** loads the overrides of another delegation to review before saving.

To change many delegations at once, select them in a list and choose **Session permissions** in the bar: pick the capabilities and the permission each gets (or **Remove the override**), or copy the overrides of one delegation, adding them to the ones there or replacing them. The selection can be every delegation that matches the list, and the change is written in batches, so it scales to any number of delegations.

The overrides apply only to sessions opened through a delegation. No role is created or changed: the access data of the session is rewritten on every request, so a change reaches open sessions from their next page, and the owner of the account keeps every permission when logging in normally. Site administrator accounts have every capability whatever is set, which is another reason to keep **Protect site administrator accounts** enabled. Capabilities that Moodle checks with database queries instead of the session (for instance some lists of users or courses) are not affected.

> [!WARNING]
> Allow gives a capability to a session even when the account does not have it. Changing session permissions needs `local/delegateaccount:managepermissions`, which no role has by default, and a person can only allow capabilities they hold themselves.
