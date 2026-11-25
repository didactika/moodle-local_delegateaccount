---
id: UC-005
name: reviewActivity
title: Revisar la actividad delegada
status: draft
origin: code
sourceOfTruth: code
---

# UC-005 Revisar la actividad delegada — implementación

| Resumen | Requisitos | Análisis | Diseño | Implementación | Pruebas |
| --- | --- | --- | --- | --- | --- |
| [Casos de uso](../../../requirements/use-case-overview.svg) | [Requisitos](../../../requirements/use-cases/reviewActivity/README.md) | [Análisis](../../../analysis/use-cases/reviewActivity/README.md) | [Diseño](../../../design/use-cases/reviewActivity/README.md) | **Implementación** | [Pruebas](../../../implementation/use-cases/reviewActivity/README.md#pruebas) |

Diseño: [README](../../../design/use-cases/reviewActivity/README.md)

## Código

| Elemento | Tipo | Ubicación | Verificado |
| --- | --- | --- | --- |
| `manager::get_delegation_activity_page` | servicio | `classes/manager.php:961` | sí |
| Página de actividad | página | `pages/activity.php` | sí |
| Pantalla de actividad | AMD | `amd/src/activity.js` | sí |

## Pruebas

| Prueba | Cubre | Ubicación |
| --- | --- | --- |
| Actividad de una delegación dentro de su periodo | flujo principal, RN-012 | `tests/manager_test.php` |
| Recorrido Behat del informe | flujo principal | `tests/behat/delegated_accounts.feature` |
