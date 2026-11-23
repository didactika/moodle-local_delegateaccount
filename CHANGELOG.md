# Changelog

All notable changes to this plugin are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed

- The list of authorised users shows the accounts each person is using now and the next change of their delegations (the end of one in use or the start of one scheduled, and how long is left), and sorts by both.
- The lists are narrowed by a Status menu beside the search, where several statuses can be chosen with the count of each, and a button at the end of the row clears every filter; the session permissions are narrowed by a Permission menu, and on a narrow screen the menus are grouped in one, Filters.
- The tabs of the pages and of the settings look like the secondary navigation of Moodle: the open one in the primary colour with a line under it, and no frame when a tab is clicked, pressed or reached with the keyboard.
- Every piece of the screens is a class of Bootstrap or of Moodle, restyled inside the plugin, so a theme restyles it with Bootstrap.
- The pages of a delegation no longer repeat the breadcrumb with a link back to the page above.

### Fixed

- A long name ends with an ellipsis in the user menu, in the cards of the accounts and in the fixed column of a list on a narrow screen, so nothing scrolls sideways.
- An empty list keeps the height of the list and its pager, and the two people of a delegation show their pictures at the same size.
- The dates of the dialogs keep even gaps on a narrow screen, and a clicked button, checkbox or switch shows no halo.
- The cards of "My delegated accounts" stay inside a narrow screen, whatever the length of an email, with even gaps between them.

## [1.2.0-m405] - 2026-11-11

Line for Moodle 4.5 to 5.2. The same delivery for Moodle 5.3 is 1.2.0-m503.

### Changed

- Only site administrators can set a session permission to Allow, because it gives a delegated session a capability that its account may not have. Copying permissions that include an Allow needs the same right. Preventing and prohibiting stay with whoever holds `local/delegateaccount:managepermissions`.
- Revoking a delegation ends the sessions that are open through it at once, instead of at their next request.
- Every list keeps the same height whatever it holds: with few rows there is empty space below them, with many it scrolls and the header stays in view.

## [1.1.1-m405] - 2026-11-11

Line for Moodle 4.5 to 5.2. The same delivery for Moodle 5.3 is 1.1.1-m503.

### Changed

- The notice that asks to create a role with the permission to use delegated accounts is no longer a banner on the management page and on the form to add authorised users. It is a help icon next to the description of the **Authorised users** tab, shown to those who can define roles until a role grants the permission.
- The guided tours moved to the `tours` folder and their titles and texts are Moodle language strings instead of fixed English text, so they can be translated. Tours already imported by earlier versions are kept as they are.
- On upgrade, the guided tours that an earlier version imported and nobody edited are moved to language strings where they are, so they keep their identifier, whether they are enabled and what each person has already seen of them. A tour that an administrator edited or deleted is left as it is.

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
