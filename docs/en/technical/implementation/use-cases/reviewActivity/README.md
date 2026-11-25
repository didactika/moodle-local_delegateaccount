---
id: UC-005
name: reviewActivity
title: Review delegated activity
status: draft
origin: code
sourceOfTruth: code
---

# UC-005 Review delegated activity — implementation

| Overview | Requirements | Analysis | Design | Implementation | Tests |
| --- | --- | --- | --- | --- | --- |
| [Use cases](../../../requirements/use-case-overview.svg) | [Requirements](../../../requirements/use-cases/reviewActivity/README.md) | [Analysis](../../../analysis/use-cases/reviewActivity/README.md) | [Design](../../../design/use-cases/reviewActivity/README.md) | **Implementation** | [Tests](../../../implementation/use-cases/reviewActivity/README.md#tests) |

Design: [README](../../../design/use-cases/reviewActivity/README.md)

## Code

| Element | Kind | Location | Verified |
| --- | --- | --- | --- |
| `manager::get_delegation_activity_page` | service | `classes/manager.php:961` | yes |
| Activity page | page | `pages/activity.php` | yes |
| Activity screen | AMD | `amd/src/activity.js` | yes |

## Tests

| Test | Covers | Location |
| --- | --- | --- |
| Activity of a delegation inside its period | main flow, RN-012 | `tests/manager_test.php` |
| Behat journey of the report | main flow | `tests/behat/delegated_accounts.feature` |
