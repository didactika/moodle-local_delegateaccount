---
id: ACT
status: draft
origin: code
sourceOfTruth: code
---

# Actors

| Actor | Type | Goal | Related use cases |
| --- | --- | --- | --- |
| Administrator | Primary | Decide who may act as whom, and what they may do while they do. Holds `local/delegateaccount:manage` and the capabilities for creating, updating, revoking and viewing activity. | [UC-001](./use-cases/createDelegations/README.md), [UC-003](./use-cases/editSessionPermissions/README.md), [UC-004](./use-cases/revokeDelegations/README.md), [UC-005](./use-cases/reviewActivity/README.md) |
| Authorised user | Primary | Work inside an account they are delegated, then leave it. A site administrator, or someone holding `local/delegateaccount:use`. | [UC-002](./use-cases/openDelegatedAccount/README.md) |
| Moodle | Secondary | Provides login-as, the capability system, the event log and messaging that the plugin relies on. | all |
