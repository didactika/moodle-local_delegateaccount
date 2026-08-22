# Changelog

All notable changes to this plugin are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Initial delegated-account management functionality.
- Moodle 4.5 through 5.2 support declaration.
- English, Spanish, Portuguese, Italian and French language packs.
- Delegation validity periods, logical revocation, lifecycle audit events and
  granular management capabilities.
- Site-wide limits for delegation quantity, duration, bulk actions and
  privileged target accounts, plus configurable notification policy and
  per-language templates.
- Moodle popup and email notifications that honour the effective policy,
  selected recipients and safely validated templates.
- Localised, configurable notification subjects and a professional Mustache
  default message, with optional rich replacement content; settings that are
  not meaningful under a never-notify policy are dynamically hidden.
- A paginated, searchable and sortable authorised-user overview, plus an
  individual delegation list with lifecycle dates, last delegated access and
  safe POST-only revocation.
- Delegation creation now records the selected validity period and, where the
  site allows it, the creator's notification decision. Individual delegation
  pages also link to the corresponding standard-log activity report and expose
  read-only lifecycle and audit details, with a capability-protected edit
  action for active delegations.
- Moodle-native GET filters, user pictures and compact lifecycle-information
  modals in the delegated-account management tables.
- A standard report description for delegated activity, plus management tabs
  that distinguish currently authorised users from retained historical users.

### Changed

- Standardised licensing, ownership metadata and release packaging.
- Active delegated sessions now require a delegation that is neither scheduled,
  expired nor revoked.
- Management tables show separate active and scheduled delegation counts; the
  redundant combined count was removed.
- The authorised-user overview now includes every active current holder of
  `local/delegateaccount:use`, including users without a delegation. New
  assignments are blocked for users who no longer hold that capability.
- Delegated accounts now use Moodle 4.5 and 5.2's native
  `core_user\hook\extend_user_menu` extension instead of injecting a custom
  carousel panel with JavaScript.
- Revoked delegations display their revocation as the end date. Last-access
  values and paginated activity reports are isolated to each delegation's
  effective access period, including repeated delegations of the same account.
