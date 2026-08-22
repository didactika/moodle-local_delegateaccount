# Delegated accounts for Moodle

[![Moodle 4.5 to 5.2](https://img.shields.io/badge/Moodle-4.5%20to%205.2-orange)](https://moodledev.io/general/releases)
[![License: GPL v3 or later](https://img.shields.io/badge/license-GPLv3%2B-blue)](LICENSE)

`local_delegateaccount` lets authorised site administrators define a clear,
auditable list of accounts that a user may access with Moodle's built-in
**Log in as** capability. It is intended for support, service and delegated
administration workflows where access must be granted deliberately instead of
being available to every administrator.

The plugin does not create accounts, impersonate users on its own, or bypass
Moodle permissions. It adds a managed delegation list and only invokes the
same core session-switching feature Moodle already provides.

## Requirements

- Moodle 4.5 through 5.2.
- A PHP version supported by the installed Moodle release.
- A site administrator to install and manage the plugin.

The supported range is declared in [`version.php`](version.php). Pull requests
are tested on the endpoints of that range with every PHP and database variant
supported by the project workflow.

## Installation

1. Place the plugin in `local/delegateaccount` in the Moodle code directory.
2. Sign in as a site administrator.
3. Visit **Site administration > Notifications** and complete the database
   upgrade.
4. Purge caches if the new administration page is not visible immediately.

For a command-line installation, run Moodle's normal upgrade command from the
Moodle root:

```bash
php admin/cli/upgrade.php --non-interactive
```

## Using delegated accounts

1. Go to **Site administration > Accounts > Manage delegated accounts**.
2. Open **Authorised users** to browse every active user who currently has
   `local/delegateaccount:use`, whether or not they already have a delegation.
   Use the Moodle-style **Filters** control to narrow that list, then open the
   relevant user's delegation list.
3. Review each target account's lifecycle, validity dates and latest recorded
   use under delegated access. Select the information action to open the
   lifecycle details without leaving the list; its link remains available as a
   full-page fallback when JavaScript is unavailable. With the update
   capability, adjust the validity dates and notification decision; revoke
   access from that list when it is no longer required.
4. Use **Add delegated account** to open the Moodle modal. Select one or more
   authorised users and one or more target accounts; the plugin creates the
   requested user-account matrix while enforcing site limits. In an individual
   delegation list, checkbox selections can also receive one common validity
   period or be revoked together after explicit confirmation. The linked page
   remains a functional fallback when JavaScript is unavailable.
5. The authorised person can open **Delegated accounts** in Moodle's user
   menu, then choose a target from the carousel submenu. The single native
   menu entry links to a complete 25-row paginated list when JavaScript is
   unavailable or the visual menu limit is reached.
6. Moodle displays its normal session-switching state. Return to the original
   account before selecting another delegated account.

The **Users without permission** tab retains people who have delegation
records but no longer hold `local/delegateaccount:use`. It is intentionally
read-only for new assignments, while preserving their delegation details and
activity reports for audit and support work.

Site administrators can configure the maximum number of current or scheduled
accounts per authorised user, a maximum validity period, whether an end date
is mandatory, protection for site-administrator accounts, and a safe size
limit for bulk actions. Notification policy, recipients and language templates
are configured under **Site administration > Plugins > Local plugins >
Delegate account**. The delegation form applies those boundaries directly:
the person creating access selects its start and end dates, and can choose
whether to notify affected users only when the site policy permits that choice.

When notification is enabled, Moodle delivers an accessible HTML message with
a plain-text fallback through its standard popup and email processors. Its
professional default is rendered by a Moodle Mustache template in each
recipient's language. Site administrators can configure a subject and, when
needed, replace that default with rich content using only documented
placeholders. The plugin records only the delivery time; it does not duplicate
message content in the delegation record. Selecting **Never notify** hides
every dependent notification setting immediately and preserves its existing
values for a later reactivation.

`local/delegateaccount:manage` remains available while sites transition to
the granular `:view`, `:create`, `:update`, `:revoke` and `:viewactivity`
capabilities. A user needs `local/delegateaccount:use` and an active,
explicit delegation record to access a target account. The assignment form
lists only current holders of that capability as authorised users, and the
server enforces the same condition for every submitted request.

The overview requires `local/delegateaccount:view`. Creating, adding and
revoking records additionally require their respective granular capability.
The last-access value is derived from Moodle's standard log store only when
the authorised user acted through that specific delegation period; it never
represents the target account's ordinary sign-ins.

Users with `local/delegateaccount:viewactivity` can open the related report
from an individual delegation. It contains only standard-log events where the
target account was used through that authorised user's selected delegation
period. Repeated delegations between the same users remain separate, and the
report uses Moodle's standard 25-row pagination. Day-only date, component and
action filters can narrow that immutable period but can never expose activity before
access began or after it ended.


## Privacy and security

Delegations contain the source account, target account, the people who create,
modify or revoke the record, its validity period, and the notification choice.
The plugin exposes this information to Moodle's privacy subsystem and records
each lifecycle change in Moodle's standard log store. Management remains
restricted to the system context.

Before granting access, confirm that the target account is appropriate for the
service, support or administrative purpose. Remove the delegation when that
purpose ends. Revocation preserves the audit record while immediately blocking
new delegated sessions. Delegated access is not suitable for sharing personal
credentials or for avoiding normal role and permission design.

## Languages

The release package includes English, Spanish, Portuguese, Italian and French
strings. A Moodle language pack can supersede the bundled translation where
available.

## Development

The `Moodle Plugin CI` workflow runs Moodle's linting, validation, AMD,
Mustache, PHPUnit and Behat checks for pull requests to `main` and maintained
`MOODLE_*_STABLE` branches. The required status check is named `CI complete`.

For a local check, use [moodle-plugin-ci](https://github.com/moodlehq/moodle-plugin-ci)
against a checkout of this plugin. Generated AMD files must be rebuilt through
Moodle's Grunt task whenever an AMD source module changes.

## License

[GNU GPL v3 or later](LICENSE), the same license used by Moodle.
