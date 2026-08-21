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
- Page controllers are organised under `pages/`, while the established root
  routes remain compatible entry points for existing links.

### Changed

- Standardised licensing, ownership metadata and release packaging.
- Active delegated sessions now require a delegation that is neither scheduled,
  expired nor revoked.
