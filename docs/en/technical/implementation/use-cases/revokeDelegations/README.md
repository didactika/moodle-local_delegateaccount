---
id: UC-004
name: revokeDelegations
title: Revoke delegations
status: draft
origin: code
sourceOfTruth: code
---

# UC-004 Revoke delegations — implementation

| Overview | Requirements | Analysis | Design | Implementation | Tests |
| --- | --- | --- | --- | --- | --- |
| [Use cases](../../../requirements/use-case-overview.svg) | [Requirements](../../../requirements/use-cases/revokeDelegations/README.md) | [Analysis](../../../analysis/use-cases/revokeDelegations/README.md) | [Design](../../../design/use-cases/revokeDelegations/README.md) | **Implementation** | [Tests](../../../implementation/use-cases/revokeDelegations/README.md#tests) |

Design: [README](../../../design/use-cases/revokeDelegations/README.md)

## Code

| Element | Kind | Location | Verified |
| --- | --- | --- | --- |
| `manager::revoke_delegations` | service | `classes/manager.php:516` | yes |
| `manager::end_sessions` | service | `classes/manager.php:354` | yes |
| Revocation event | event | `classes/event/delegation_revoked.php` | yes |

## Tests

| Test | Covers | Location |
| --- | --- | --- |
| Revoking only affects the chosen delegations | main flow, RN-010 | `tests/manager_test.php` |
| A revoked session ends | RN-011 | `tests/delegated_session_test.php` |
| Behat journey of a revocation | main flow | `tests/behat/delegated_accounts.feature` |
