---
id: UC-003
name: editSessionPermissions
title: Editar los permisos de sesión
status: draft
origin: code
sourceOfTruth: code
---

# UC-003 Editar los permisos de sesión — implementación

| Resumen | Requisitos | Análisis | Diseño | Implementación | Pruebas |
| --- | --- | --- | --- | --- | --- |
| [Casos de uso](../../../requirements/use-case-overview.svg) | [Requisitos](../../../requirements/use-cases/editSessionPermissions/README.md) | [Análisis](../../../analysis/use-cases/editSessionPermissions/README.md) | [Diseño](../../../design/use-cases/editSessionPermissions/README.md) | **Implementación** | [Pruebas](../../../implementation/use-cases/editSessionPermissions/README.md#pruebas) |

Diseño: [README](../../../design/use-cases/editSessionPermissions/README.md)

## Código

| Elemento | Tipo | Ubicación | Verificado |
| --- | --- | --- | --- |
| `session_capabilities::update` | servicio | `classes/session_capabilities.php:171` | sí |
| `session_capabilities::apply` | servicio | `classes/session_capabilities.php:309` | sí |
| Editor de permisos | AMD | `amd/src/permission_editor.js` | sí |

## Pruebas

| Prueba | Cubre | Ubicación |
| --- | --- | --- |
| Las sobrescrituras se guardan y se aplican | flujo principal, RN-007 | `tests/session_capabilities_test.php` |
| Servicios web | flujo principal, A1 | `tests/external/session_permissions_test.php` |
