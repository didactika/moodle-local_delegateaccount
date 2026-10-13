# Changelog

All notable changes to this plugin are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-10-09

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
