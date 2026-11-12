---
id: RQ
status: draft
origin: code
sourceOfTruth: code
---

# Requirements catalogue

## Functional

| ID | Requirement | Priority | Origin | Use cases |
| --- | --- | --- | --- | --- |
| RF-001 | An administrator can authorise one or many users to open one or many accounts, with a start, an optional end and a notification mode. | Must | `manager::create_delegations` | UC-001 |
| RF-002 | An administrator can revoke delegations; revoked ones stay as history. | Must | `manager::revoke_delegations` | UC-004 |
| RF-003 | An authorised user can open an account they are delegated from their user menu, and leave it. | Must | `pages/loginas.php`, `hook\extend_user_menu` | UC-002 |
| RF-004 | A delegated session ends by itself when its delegation stops being valid. | Must | `hook\after_config` | UC-002 |
| RF-005 | An administrator can allow, prevent or prohibit capabilities for delegated sessions, for one delegation or for all. | Should | `session_capabilities` | UC-003 |
| RF-006 | The activity done through a delegation can be reviewed with filters. | Should | `pages/activity.php`, `manager::get_delegation_activity_page` | UC-005 |
| RF-007 | The people involved are notified when a delegation is created or revoked, in their language. | Could | `notification_manager` | UC-001 |

## Non-functional

| ID | Quality attribute | Requirement | Measure | Priority |
| --- | --- | --- | --- | --- |
| RNF-001 | Security | Access is re-evaluated on every request of a delegated session. | `after_config` runs `end_invalid_delegated_session` on each web request | Must |
| RNF-002 | Security | Changing session permissions needs its own capability, which no role gets by default, because it can widen what an account may do. | `local/delegateaccount:managepermissions` with no default archetype | Must |
| RNF-003 | Privacy | The plugin stores who delegated whom and implements the Privacy API. | `classes/privacy/provider.php` | Must |
| RNF-004 | Integrity | Bulk creation is atomic. | one delegated transaction in `create_delegations` | Must |

## Business rules

Rules live in the [domain model dictionary](../domain/README.md#business-rules). Rules that change behaviour are repeated here with their ID.
