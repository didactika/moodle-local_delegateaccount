# Changelog

All notable changes to this plugin are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0-m405] - 2026-11-06

Line for Moodle 4.5 to 5.2. The same delivery for Moodle 5.3 is 1.1.0-m503.

### Added

- Session permissions: Not set, Allow, Prevent or Prohibit for any capability in the sessions opened through a delegation,
  for every delegation of the site and for each delegation, applied to the session without creating any role. The
  delegation's own setting wins over the site's, and Not set keeps what the roles of the account give.
- A searchable, paged permission editor in the settings and in the page of each delegation, with a copy from another
  delegation, and a dialog that changes or copies the overrides of many delegations at once, also of every delegation that
  matches a list.
- The capability `local/delegateaccount:managepermissions`, needed to change session permissions. No role has it by default.
- A list of every delegation of the site on the management page, with bulk changes of period, permissions and revocation.
- A page for each delegation with its details, its history and its session permissions.
- Web services for the lists and the session permissions, used by the pages.
- Guided tours that show people with delegations where to find and how to open them.

### Changed

- Every page is drawn with the Didactika design system: page headers, filter chips with counts, searchable lists with
  sortable columns and a pager, a floating bar for selections, and confirmations that name what is lost.
- The settings are grouped in tabs: Delegations, Notifications, Messages and Session permissions.
- "My delegated accounts" shows the accounts as cards, each with the button that opens it.
- The activity report filters as one types and keeps its filters in the address.
- The lists open with their first page already drawn, without waiting for a request. A page holds 12, 24, 48 or 96 rows,
  or all of them; the pager offers only the sizes smaller than the list.
- The quick actions of a row keep their place in every row, each in the colour of what it does; the ones a delegation does
  not allow are shown disabled.
- The permission editor scrolls inside a box of fixed height, with the name of each component as the head of its group.
- The dialogs of the plugin are drawn with the design system.
- The management page is called "Delegated accounts" in the administration.

- New "Delegated session capabilities" tab in the plugin settings, next to the existing settings. Administrators set Allow, Prohibit or Not set for each capability in delegated sessions and can add any capability. Editing the own profile is prohibited by default. The rules apply only to sessions opened through a delegation and change no role.
- Two guided tours for Moodle's User Tours, imported automatically on install and upgrade from the `tour_*.json` files. They are shown once, only to people who have an active delegation, and explain the "Delegated accounts" user menu entry and the "My delegated accounts" page.

## [1.0.0-m405] - 2026-10-14

First public release.

- Delegations that let an authorised user log in as another account through
  Moodle's "Log in as", with a start date, an optional end date and revocation.
- Access is checked when a delegated account is opened and on every request of
  the delegated session, so revoked or expired delegations, users who lost
  permission and suspended or administrator targets end the session.
- Management pages to create, edit and revoke delegations in bulk, a details
  view and a per-delegation activity report built on the site's log store.
- A "Delegated accounts" entry in the user menu and a "My delegated accounts"
  page for authorised users.
- Granular capabilities, with `local/delegateaccount:manage` granting every
  management action unless a specific capability is set to Prevent or Prohibit.
- Site limits for delegations per user, duration, bulk actions and protection of
  site administrator accounts.
- Optional notifications when access is granted or revoked, with a subject and
  message per installed language.
- Events for every change, Privacy API support and a restricted web service.
