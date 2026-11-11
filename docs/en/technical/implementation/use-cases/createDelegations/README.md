---
id: UC-001
name: createDelegations
title: Create delegations
status: draft
origin: code
sourceOfTruth: code
---

# UC-001 Create delegations — implementation

| Overview | Requirements | Analysis | Design | Implementation | Tests |
| --- | --- | --- | --- | --- | --- |
| [Use cases](../../../requirements/use-case-overview.svg) | [Requirements](../../../requirements/use-cases/createDelegations/README.md) | [Analysis](../../../analysis/use-cases/createDelegations/README.md) | [Design](../../../design/use-cases/createDelegations/README.md) | **Implementation** | [Tests](../../../implementation/use-cases/createDelegations/README.md#tests) |

Design: [README](../../../design/use-cases/createDelegations/README.md)

## Code

| Element | Kind | Location | Verified |
| --- | --- | --- | --- |
| `manager::create_delegations` | service | `classes/manager.php:360` | yes |
| `create_delegations::execute` | external | `classes/external/create_delegations.php` | yes |
| Dialog form | form | `classes/form/assign_dynamic_form.php` | yes |
| Dialog script | AMD | `amd/src/create.js` | yes |

## Tests

| Test | Covers | Location | Result |
| --- | --- | --- | --- |
| Creating delegations is idempotent and excludes self-delegation | main flow, RN-002 | `tests/manager_test.php` | not run |
| Delegation API | main flow | `tests/delegation_api_test.php` | not run |
