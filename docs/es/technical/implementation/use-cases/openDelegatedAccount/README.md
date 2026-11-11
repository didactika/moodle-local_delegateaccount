---
id: UC-002
name: openDelegatedAccount
title: Abrir una cuenta delegada
status: draft
origin: code
sourceOfTruth: code
---

# UC-002 Abrir una cuenta delegada — implementación

| Resumen | Requisitos | Análisis | Diseño | Implementación | Pruebas |
| --- | --- | --- | --- | --- | --- |
| [Casos de uso](../../../requirements/use-case-overview.svg) | [Requisitos](../../../requirements/use-cases/openDelegatedAccount/README.md) | [Análisis](../../../analysis/use-cases/openDelegatedAccount/README.md) | [Diseño](../../../design/use-cases/openDelegatedAccount/README.md) | **Implementación** | [Pruebas](../../../implementation/use-cases/openDelegatedAccount/README.md#pruebas) |

Diseño: [README](../../../design/use-cases/openDelegatedAccount/README.md)

## Código

| Elemento | Tipo | Ubicación | Verificado |
| --- | --- | --- | --- |
| `manager::get_delegated_access_error` | servicio | `classes/manager.php:278` | sí |
| `manager::start_delegated_session` | servicio | `classes/manager.php:302` | sí |
| `manager::end_invalid_delegated_session` | servicio | `classes/manager.php:323` | sí |
| `after_config::execute` | hook | `classes/hook/after_config.php:38` | sí |
| Entrada del menú de usuario | hook | `classes/hook/extend_user_menu.php:33` | sí |

## Pruebas

| Prueba | Cubre | Ubicación | Resultado |
| --- | --- | --- | --- |
| El acceso se rechaza para destinos no disponibles, administradores e invitado | A2, A3, RN-003, RN-004 | `tests/delegated_session_test.php` | no ejecutada |
| El menú de usuario ofrece las cuentas | flujo principal | `tests/extend_user_menu_test.php` | no ejecutada |
| Recorrido Behat de una persona autorizada | flujo principal | `tests/behat/delegated_accounts.feature` | no ejecutada |
