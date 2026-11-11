<div align="center">

<img src="pix/icon.svg" width="96" alt="Plugin Delegate Account Icon">

# Delegate Account for Moodle

*Let chosen people log in as other accounts, only when and for as long as you allow it*

[![Release](https://img.shields.io/github/v/release/didactika/moodle-local_delegateaccount?style=flat-square)](https://github.com/didactika/moodle-local_delegateaccount/releases)
[![Moodle](https://img.shields.io/badge/Moodle-4.5_to_5.2-f98012?style=flat-square&logo=moodle&logoColor=white)](https://moodle.org)
[![PHP](https://img.shields.io/badge/PHP-8.1+-777bb4?style=flat-square&logo=php&logoColor=white)](https://www.php.net)
[![License](https://img.shields.io/badge/License-GPL_v3-blue?style=flat-square)](LICENSE)

[Overview](#overview) • [Installation](#installation) • [Usage](#usage) • [Configuration](#configuration) • [Troubleshooting](#troubleshooting)

</div>

Delegate Account (`local_delegateaccount`) is a Moodle local plugin that gives specific people access to specific accounts. An administrator decides who may act as whom and for which period. The person then opens the account from their user menu through Moodle's own **Log in as**, and everything they do is recorded in the site log under both names.

> [!IMPORTANT]
> **Only site administrators can use delegated accounts until you give the permission to someone else.** Create a role with the capability `local/delegateaccount:use` and assign it in the system context to the people who should be able to use delegations. See [Give people permission](#1-give-people-permission-administrators).

> [!NOTE]
> Delegate Account never stores passwords and never creates accounts. It only decides whether Moodle's **Log in as** may be used for a given pair of users at a given time.

## Overview

Some people need to work inside another person's account: a support team fixing a profile, an assistant managing a manager's calendar, a teacher covering for a colleague. Moodle's **Log in as** capability lets someone open *any* account they can see, with no time limit. Delegate Account narrows that down to explicit, time-bound pairs.

### How access is decided

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

### Features

- **Time-bound delegations:** a start date, an optional end date and explicit revocation, with a full history of who created, changed and revoked each one.
- **Bulk management:** create delegations for several people and several accounts at once, and edit or revoke many delegations together.
- **Session permissions:** allow, prevent or prohibit any capability while someone works through a delegation, for every delegation or for one, without creating roles, and change them for many delegations at once.
- **Activity report:** for each delegation, every event recorded while the account was being used through it, with date, component and action filters.
- **User menu entry:** people with delegations open them from **Delegated accounts** in their user menu.
- **Site limits:** maximum delegations per person, maximum duration, maximum records per bulk action, and protection of administrator accounts.
- **Notifications:** optional messages to the people involved when access is granted or revoked, in each recipient's language and customisable per language.
- **Integration:** events for every change, a restricted web service and Privacy API support.

## Installation

**Requirements:** Moodle 4.5 to 5.2, with the PHP version your Moodle release requires. For Moodle 5.3 and later, use the `main` branch and the `-m503` releases, which bring the same features built with React and TypeScript.

| Moodle | Branch | Releases |
|---|---|---|
| 4.5 to 5.2 | `MOODLE_405_STABLE` | `X.Y.Z-m405` |
| 5.3 | `main` | `X.Y.Z-m503` |

**From a release:** download the latest release ZIP, go to **Site administration > Plugins > Install plugins**, upload the file and follow the prompts.

**From Git:**

```bash
cd /path/to/moodle
# Moodle 4.5 and 5.0
git clone -b MOODLE_405_STABLE https://github.com/didactika/moodle-local_delegateaccount.git local/delegateaccount
# Moodle 5.1 and 5.2
git clone -b MOODLE_405_STABLE https://github.com/didactika/moodle-local_delegateaccount.git public/local/delegateaccount
php admin/cli/upgrade.php
```

## Usage

### 1. Give people permission (administrators)

1. Go to **Site administration > Users > Permissions > Define roles** and add a new role, for example *Delegated account user*.
2. Select **System** as the only context type and allow `local/delegateaccount:use` (**Log in as a delegated account**).
3. Go to **Site administration > Users > Permissions > Assign system roles** and give the role to the people who should be able to use delegations.

Until a role grants the capability, the management page shows a reminder with links to these two pages.

> [!WARNING]
> Do not grant `local/delegateaccount:use` to the **Authenticated user** role. Every user would then count as authorised.

### 2. Create delegations

Go to **Site administration > Users > Accounts > Delegated accounts**. The **Authorised users** tab lists everyone who holds the permission, with their active and scheduled delegations. Search by name, username, email or ID number, and use the chips to see the people who have delegations in a status, or none.

- Select **Create delegations** at the top of the page to choose several authorised users and several accounts at once, or the add icon on a user's row to create delegations for that user only.
- Choose when access starts and, optionally, when it ends.
- When the site allows it, choose whether to notify the people involved.

The account picker only offers active accounts. It leaves out the authorised user themselves, accounts they already have a delegation for that has not been revoked, the guest account and, while they are protected, site administrators. To give access again after a delegation expires, edit the expired delegation instead of creating a new one.

### 3. Manage delegations

Select a person's name to open their delegations, or use the **All delegations** tab to work with the delegations of everyone. The chips show the **Active**, **Scheduled**, **Expired** and **Revoked** ones with their counts, and each row shows the period, the last time it was used and its session permissions.

- The icons at the end of a row edit the period, open the session permissions, open the activity report and revoke the delegation (in red: it ends access at once, also in a session that is open).
- Select rows to act on several at once from the bar that appears at the foot of the window: **Edit period**, **Session permissions** and **Revoke**. When a whole page is selected, **Select all** extends the selection to every delegation that matches the search and the chip.
- Select the name of an account to open the page of its delegation, with its details, its history and its **Session permissions** tab.

The **Users without permission** tab lists people who still have delegations but no longer hold the permission, for example because their role was removed or their account was suspended. Their history and activity stay available, but they cannot use their delegations and cannot receive new ones.

### 4. Use a delegated account

People with active delegations see **Delegated accounts** in their user menu. Choosing an account opens it through Moodle's **Log in as** and goes to that account's Dashboard. When someone has more accounts than the menu shows, **View all delegated accounts** opens the **My delegated accounts** page.

To stop working as the other account, log out. A delegated account cannot open another delegated account: log out first.

### 5. Guided tours

Two guided tours for Moodle's User Tours are shipped as `tour_*.json` files in the plugin folder. They are written for the people who use delegated accounts, not for administrators. Each tour is shown only to people who currently have an active delegation, because it depends on the **Delegated accounts** entry in the user menu, and each person sees it once, until they finish or skip it.

| File | Where it appears | What it explains |
| --- | --- | --- |
| `tour_use_delegated_accounts.json` | Dashboard and My courses | Where to find **Delegated accounts** in the user menu and how to open an account. |
| `tour_my_delegated_accounts.json` | **My delegated accounts** page | The cards of the accounts, until when each can be opened and the **Open account** button. |

The tours are imported automatically when the plugin is installed or upgraded. A tour that already exists, identified by its name, is never overwritten, so your edits are kept. Tours can be edited, translated or disabled under **Site administration > Appearance > User tours**. If you delete a tour and want it back, select **Import tour** on that page and choose the file.

## Configuration

Settings are located at **Site administration > Plugins > Local plugins > Delegated account settings**, in four tabs: **Delegations**, **Notifications**, **Messages** and **Session permissions**.

### Delegation controls

| Setting | Default | Description |
|---|---|---|
| Maximum delegated accounts per user | 10 | How many current or scheduled delegations one authorised user can have. Enter `0` for no limit |
| Allow delegations with no end date | On | When off, every delegation needs an end date |
| Maximum delegation duration | 0 | The longest a delegation can last, in days. Only shown and applied when **Allow delegations with no end date** is off. `0` means no limit |
| Protect site administrator accounts | On | Site administrators cannot be delegated, and an existing delegation stops working if its account becomes an administrator |
| Maximum records per bulk action | 100 | How many delegations one bulk action can create, edit or revoke. Enter `0` for no limit |
| Delegated accounts shown in the user menu | 10 | Accounts listed in the user menu before **View all delegated accounts**. Enter `0` to list them all |

> [!CAUTION]
> With **Protect site administrator accounts** turned off, an authorised user with a delegation to an administrator can log in as that administrator and take full control of the site.

### Notifications

| Setting | Default | Description |
|---|---|---|
| Notification policy | Allow the person creating the delegation to choose | Or **Always notify**, or **Never notify**. **Never notify** sends nothing, also for delegations created before it was selected, and hides the other notification settings |
| Notification recipients | Both users | The authorised user, the delegated account, or both |
| Notify when a delegation is revoked | On | Also notify when access is revoked |
| Subject and message when access is granted or revoked | Empty | One subject and one message per action for each installed language. Empty fields use the built-in text, which is worded for each recipient. A custom message is sent unchanged to both recipients, so write it to read well for both |

> [!TIP]
> Use **Send yourself a test notification**, below these settings, to receive the saved subject and message for a language as both recipients would see them. Save your changes first.

Each recipient gets the notification in their profile language, or in the site language when theirs is not installed. Messages use the **Delegated account notifications** provider, sent as web and email notifications by default; users and administrators can change that in the usual notification preferences.

### Session permissions

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

### Capabilities

All capabilities apply in the system context.

| Capability | Default roles | Allows the user to |
|---|---|---|
| `local/delegateaccount:use` | None | Open the accounts delegated to them |
| `local/delegateaccount:view` | Manager | See delegations and the management pages |
| `local/delegateaccount:create` | Manager | Create delegations |
| `local/delegateaccount:update` | Manager | Change the period and notification choice of delegations |
| `local/delegateaccount:revoke` | Manager | Revoke delegations |
| `local/delegateaccount:viewactivity` | Manager | Open the activity report of a delegation |
| `local/delegateaccount:manage` | Manager | Everything above except `use` and `managepermissions`. Setting one of the specific capabilities to Prevent or Prohibit still refuses that action |
| `local/delegateaccount:managepermissions` | None | Change the session permissions of the site (also needs `moodle/site:config`) and of delegations |

> [!WARNING]
> Whoever can create delegations decides who may log in as whom. Give `local/delegateaccount:create` and `local/delegateaccount:manage` only to people you would trust with Moodle's own **Log in as**.

### Web services

The plugin adds a **Delegated account management** service that is disabled by default. It covers reading, creating, updating and revoking delegations and reading their activity, with the same rules and capabilities as the pages. See [Web-service integration](docs/en/reference/webservices.md) for the functions, limits and examples.

### Scheduled tasks

None. Start and end dates are checked each time an account is opened and on every page of a delegated session, so delegations start and stop on time without cron.

### Privacy and backup

- **Privacy:** the plugin implements the Privacy API. It stores, for each delegation, the authorised user, the delegated account, the period, the notification choice and who created, changed or revoked it. This data can be exported. Deleting a user's data removes the delegations they are part of and removes their name from the ones they only created, changed or revoked.
- **Activity:** the plugin keeps no activity of its own. The activity report reads the site's log store, so it follows your log retention settings.
- **Backup:** delegations are site-level data and are not part of course backups.

## Troubleshooting

| Problem | Possible cause |
|---|---|
| A person does not appear under **Authorised users** | They do not hold `local/delegateaccount:use` in the system context, or their account is suspended |
| An account cannot be chosen as a target | It is suspended, the guest account, a protected site administrator or the authorised user themselves, or that user already has a delegation to it that has not been revoked, including an expired one. Edit that delegation instead |
| **Delegated accounts** is missing from the user menu | The person has no active delegation, does not hold the permission, or is already logged in as someone else |
| "Delegated session ended" appears | The delegation was revoked or expired, the person lost the permission, or the account became unavailable while it was in use |
| The activity report is empty | Nothing was done through that delegation yet, no SQL log store is enabled, or the events are anonymous and you cannot view anonymous events |
| Nobody receives notifications | The notification policy is **Never notify**, **Do not send a notification** was chosen, or the recipients disabled these notifications in their preferences |

## Getting help

To report a bug or request a feature, please open an [issue](https://github.com/didactika/moodle-local_delegateaccount/issues). Include your Moodle and PHP versions, the plugin settings involved and the steps that reproduce the problem. Do not include passwords, tokens or personal data.

Report security problems privately through the repository's [security advisory form](https://github.com/didactika/moodle-local_delegateaccount/security/advisories/new).
