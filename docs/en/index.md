---
title: Delegate account
summary: Let chosen people log in as other accounts, only when and for as long as you allow it.
order: 1
---

Delegate Account (`local_delegateaccount`) is a Moodle local plugin that gives specific people access to specific accounts. An administrator decides who may act as whom and for which period. The person then opens the account from their user menu through Moodle's own **Log in as**, and everything they do is recorded in the site log under both names.

> [!IMPORTANT]
> **Only site administrators can use delegated accounts until you give the permission to someone else.** Create a role with the capability `local/delegateaccount:use` and assign it in the system context to the people who should be able to use delegations. See [Give people permission](page:getting-started/give-permission).

> [!NOTE]
> Delegate Account never stores passwords and never creates accounts. It only decides whether Moodle's **Log in as** may be used for a given pair of users at a given time.


Some people need to work inside another person's account: a support team fixing a profile, an assistant managing a manager's calendar, a teacher covering for a colleague. Moodle's **Log in as** capability lets someone open *any* account they can see, with no time limit. Delegate Account narrows that down to explicit, time-bound pairs.

## How access is decided

Two things must be true for someone to open an account:

1. **They hold the permission.** They have `local/delegateaccount:use` in the system context, or they are a site administrator.
2. **A delegation exists for that account.** An administrator created a delegation from them to that account, and it is currently active.

Each delegation has a lifecycle:

| Status | Meaning |
|---|---|
| Scheduled | The start date has not been reached yet |
| Active | The account can be opened now |
| Expired | The end date has passed |
| Revoked | An administrator ended it. Revoked delegations are kept for the record |

Access is checked when the account is opened and again on every page while the delegated session lasts. The session ends on the next page if the delegation is revoked or expires, if the person loses the permission, or if the account is suspended, deleted or becomes a site administrator while that is protected.

## Features

- **Time-bound delegations:** a start date, an optional end date and explicit revocation, with a full history of who created, changed and revoked each one.
- **Bulk management:** create delegations for several people and several accounts at once, and edit or revoke many delegations together.
- **Session permissions:** allow, prevent or prohibit any capability while someone works through a delegation, for every delegation or for one, without creating roles, and change them for many delegations at once.
- **Activity report:** for each delegation, every event recorded while the account was being used through it, with date, component and action filters.
- **User menu entry:** people with delegations open them from **Delegated accounts** in their user menu.
- **Site limits:** maximum delegations per person, maximum duration, maximum records per bulk action, and protection of administrator accounts.
- **Notifications:** optional messages to the people involved when access is granted or revoked, in each recipient's language and customisable per language.
- **Integration:** events for every change, a restricted web service and Privacy API support.
