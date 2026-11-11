---
id: UC-001
name: createDelegations
title: Crear delegaciones
status: draft
origin: code
sourceOfTruth: code
---

# UC-001 Crear delegaciones — implementación

| Resumen | Requisitos | Análisis | Diseño | Implementación | Pruebas |
| --- | --- | --- | --- | --- | --- |
| [Casos de uso](../../../requirements/use-case-overview.svg) | [Requisitos](../../../requirements/use-cases/createDelegations/README.md) | [Análisis](../../../analysis/use-cases/createDelegations/README.md) | [Diseño](../../../design/use-cases/createDelegations/README.md) | **Implementación** | [Pruebas](../../../implementation/use-cases/createDelegations/README.md#pruebas) |

Diseño: [README](../../../design/use-cases/createDelegations/README.md)

## Código

| Elemento | Tipo | Ubicación | Verificado |
| --- | --- | --- | --- |
| `manager::create_delegations` | servicio | `classes/manager.php:360` | sí |
| `create_delegations::execute` | externa | `classes/external/create_delegations.php` | sí |
| Formulario del diálogo | formulario | `classes/form/assign_dynamic_form.php` | sí |
| Script del diálogo | AMD | `amd/src/create.js` | sí |

## Pruebas

| Prueba | Cubre | Ubicación | Resultado |
| --- | --- | --- | --- |
| Crear delegaciones es idempotente y excluye la delegación de uno mismo | flujo principal, RN-002 | `tests/manager_test.php` | no ejecutada |
| API de delegaciones | flujo principal | `tests/delegation_api_test.php` | no ejecutada |
