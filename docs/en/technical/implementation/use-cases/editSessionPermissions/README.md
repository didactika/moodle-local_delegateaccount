---
id: UC-003
name: editSessionPermissions
title: Edit session permissions
status: draft
origin: code
sourceOfTruth: code
---

# UC-003 Edit session permissions — implementation

| Overview | Requirements | Analysis | Design | Implementation | Tests |
| --- | --- | --- | --- | --- | --- |
| [Use cases](../../../requirements/use-case-overview.svg) | [Requirements](../../../requirements/use-cases/editSessionPermissions/README.md) | [Analysis](../../../analysis/use-cases/editSessionPermissions/README.md) | [Design](../../../design/use-cases/editSessionPermissions/README.md) | **Implementation** | [Tests](../../../implementation/use-cases/editSessionPermissions/README.md#tests) |

Design: [README](../../../design/use-cases/editSessionPermissions/README.md)

## Code

| Element | Kind | Location | Verified |
| --- | --- | --- | --- |
| `session_capabilities::update` | service | `classes/session_capabilities.php:171` | yes |
| `session_capabilities::apply` | service | `classes/session_capabilities.php:309` | yes |
| Permission editor | AMD | `amd/src/permission_editor.js` | yes |

## Tests

| Test | Covers | Location | Result |
| --- | --- | --- | --- |
| Overrides are stored and applied | main flow, RN-007 | `tests/session_capabilities_test.php` | not run |
| Web services | main flow, A1 | `tests/external/session_permissions_test.php` | not run |
