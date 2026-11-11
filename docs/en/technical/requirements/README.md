---
id: REQ
status: draft
origin: requirements
sourceOfTruth: model
---

# Requirements

## Index

- [Actors](./actors.md)
- [Requirements catalogue](./requirements.md)

## Use cases

<!-- rup-flow:use-cases:start -->

| ID | Use case | Actor | Package | Requirement | Analysis | Design | Implementation | Status |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| UC-001 | [Create delegations](./use-cases/createDelegations/README.md) | Administrator | Delegations | RF-001 | [card](../analysis/use-cases/createDelegations/README.md) | [card](../design/use-cases/createDelegations/README.md) | [card](../implementation/use-cases/createDelegations/README.md) | draft |
| UC-002 | [Open a delegated account](./use-cases/openDelegatedAccount/README.md) | Authorised user | Sessions | RF-003 | [card](../analysis/use-cases/openDelegatedAccount/README.md) | [card](../design/use-cases/openDelegatedAccount/README.md) | [card](../implementation/use-cases/openDelegatedAccount/README.md) | draft |
| UC-003 | [Edit session permissions](./use-cases/editSessionPermissions/README.md) | Administrator | Sessions | RF-005 | [card](../analysis/use-cases/editSessionPermissions/README.md) | [card](../design/use-cases/editSessionPermissions/README.md) | [card](../implementation/use-cases/editSessionPermissions/README.md) | draft |

### Use-case diagram

![Use cases](./use-case-overview.svg)

### By actor

#### Administrator

![Administrator](./use-cases-administrator.svg)

#### Authorised user

![Authorised user](./use-cases-authorised_user.svg)

<!-- rup-flow:use-cases:end -->

The index and its diagrams are generated from the use-case cards by the `index` command. Edit the cards (front matter: `actor`, `package`, `includes`, `extends`), not this block.

## Conventions

- `specification.puml` is the behavioural source of truth of a use case: a statechart with the Actor/System conversation.
- `wireframes.puml` is the interface prototype, even when it holds a single screen.
- Each use-case folder holds `README.md`, `specification.puml` and `wireframes.puml`.
