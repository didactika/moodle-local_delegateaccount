---
id: IMP
status: draft
origin: code
sourceOfTruth: code
---

# Implementation

This folder links the model to the code. The [code map](./code-map.md) lists each model element and where it lives; each use case has a card with its code and its tests.

The direction of work was **code to documentation**: the model describes the plugin as it is, and the code stays the source of truth. The test results in the cards say `not run` until someone runs them; they are not claims.

| ID | Use case | Card |
| --- | --- | --- |
| UC-001 | Create delegations | [implementation](./use-cases/createDelegations/README.md) |
| UC-002 | Open a delegated account | [implementation](./use-cases/openDelegatedAccount/README.md) |
| UC-003 | Edit session permissions | [implementation](./use-cases/editSessionPermissions/README.md) |
