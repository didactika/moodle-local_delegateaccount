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
2. Select the people who may access another account.
3. Select the target accounts they may access, then save the delegation.
4. The authorised person can open the **Delegate account** entry in their user
   menu and choose an available target account.
5. Moodle displays its normal session-switching state. Return to the original
   account before selecting another delegated account.

`local/delegateaccount:manage` remains available while sites transition to
the granular `:view`, `:create`, `:update`, `:revoke` and `:viewactivity`
capabilities. A user needs `local/delegateaccount:use` and an active,
explicit delegation record to access a target account.

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
