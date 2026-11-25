---
id: UC-004
name: revokeDelegations
title: Revocar delegaciones
status: draft
origin: code
sourceOfTruth: code
---

# UC-004 Revocar delegaciones — implementación

| Resumen | Requisitos | Análisis | Diseño | Implementación | Pruebas |
| --- | --- | --- | --- | --- | --- |
| [Casos de uso](../../../requirements/use-case-overview.svg) | [Requisitos](../../../requirements/use-cases/revokeDelegations/README.md) | [Análisis](../../../analysis/use-cases/revokeDelegations/README.md) | [Diseño](../../../design/use-cases/revokeDelegations/README.md) | **Implementación** | [Pruebas](../../../implementation/use-cases/revokeDelegations/README.md#pruebas) |

Diseño: [README](../../../design/use-cases/revokeDelegations/README.md)

## Código

| Elemento | Tipo | Ubicación | Verificado |
| --- | --- | --- | --- |
| `manager::revoke_delegations` | servicio | `classes/manager.php:516` | sí |
| `manager::end_sessions` | servicio | `classes/manager.php:354` | sí |
| Evento de revocación | evento | `classes/event/delegation_revoked.php` | sí |

## Pruebas

| Prueba | Cubre | Ubicación |
| --- | --- | --- |
| Revocar solo afecta a las delegaciones elegidas | flujo principal, RN-010 | `tests/manager_test.php` |
| Una sesión revocada termina | RN-011 | `tests/delegated_session_test.php` |
| Recorrido Behat de una revocación | flujo principal | `tests/behat/delegated_accounts.feature` |
