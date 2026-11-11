---
id: UC-002
name: openDelegatedAccount
title: Open a delegated account
status: draft
origin: code
sourceOfTruth: code
---

# UC-002 Open a delegated account — implementation

| Overview | Requirements | Analysis | Design | Implementation | Tests |
| --- | --- | --- | --- | --- | --- |
| [Use cases](../../../requirements/use-case-overview.svg) | [Requirements](../../../requirements/use-cases/openDelegatedAccount/README.md) | [Analysis](../../../analysis/use-cases/openDelegatedAccount/README.md) | [Design](../../../design/use-cases/openDelegatedAccount/README.md) | **Implementation** | [Tests](../../../implementation/use-cases/openDelegatedAccount/README.md#tests) |

Design: [README](../../../design/use-cases/openDelegatedAccount/README.md)

## Code

| Element | Kind | Location | Verified |
| --- | --- | --- | --- |
| `manager::get_delegated_access_error` | service | `classes/manager.php:278` | yes |
| `manager::start_delegated_session` | service | `classes/manager.php:302` | yes |
| `manager::end_invalid_delegated_session` | service | `classes/manager.php:323` | yes |
| `after_config::execute` | hook | `classes/hook/after_config.php:38` | yes |
| User menu entry | hook | `classes/hook/extend_user_menu.php:33` | yes |

## Tests

| Test | Covers | Location |
| --- | --- | --- |
| Access is refused for unavailable, administrator and guest targets | A2, A3, RN-003, RN-004 | `tests/delegated_session_test.php` |
| The user menu offers the accounts | main flow | `tests/extend_user_menu_test.php` |
| Behat journey of an authorised user | main flow | `tests/behat/delegated_accounts.feature` |
