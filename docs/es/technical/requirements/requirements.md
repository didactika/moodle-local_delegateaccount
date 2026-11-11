---
id: RQ
status: draft
origin: code
sourceOfTruth: code
---

# Catálogo de requisitos

Estos requisitos se dedujeron del código y de sus pruebas (`observed`); ninguno lo ha confirmado todavía quien manda sobre el producto, así que todas las filas son `assumed` hasta que alguien las firme.

## Funcionales

| ID | Requisito | Prioridad | Origen | Confirmado por | Casos de uso |
| --- | --- | --- | --- | --- | --- |
| RF-001 | Una administradora puede autorizar a uno o varios usuarios a abrir una o varias cuentas, con un inicio, un fin opcional y un modo de notificación. | Debe | `manager::create_delegations` | | UC-001 |
| RF-002 | Una administradora puede revocar delegaciones; las revocadas quedan como historial. | Debe | `manager::revoke_delegations` | | UC-001 |
| RF-003 | Una persona autorizada puede abrir una cuenta que se le ha delegado desde su menú de usuario, y salir de ella. | Debe | `pages/loginas.php`, `hook\extend_user_menu` | | UC-002 |
| RF-004 | Una sesión delegada termina sola cuando su delegación deja de ser válida. | Debe | `hook\after_config` | | UC-002 |
| RF-005 | Una administradora puede permitir, evitar o prohibir capacidades en las sesiones delegadas, para una delegación o para todas. | Debería | `session_capabilities` | | UC-003 |
| RF-006 | La actividad hecha mediante una delegación se puede revisar con filtros. | Debería | `pages/activity.php`, `manager::get_delegation_activity_page` | | |
| RF-007 | Las personas implicadas reciben una notificación al crear o revocar una delegación, en su idioma. | Podría | `notification_manager` | | UC-001 |

## No funcionales

| ID | Atributo de calidad | Requisito | Medida | Prioridad |
| --- | --- | --- | --- | --- |
| RNF-001 | Seguridad | El acceso se reevalúa en cada petición de una sesión delegada. | `after_config` ejecuta `end_invalid_delegated_session` en cada petición web | Debe |
| RNF-002 | Seguridad | Cambiar los permisos de sesión exige su propia capacidad, que ningún rol recibe por defecto, porque puede ampliar lo que una cuenta puede hacer. | `local/delegateaccount:managepermissions` sin arquetipo por defecto | Debe |
| RNF-003 | Privacidad | El plugin guarda quién delegó a quién e implementa la API de privacidad. | `classes/privacy/provider.php` | Debe |
| RNF-004 | Integridad | La creación masiva es atómica. | una transacción delegada en `create_delegations` | Debe |

## Reglas de negocio

Las reglas viven en el [diccionario del modelo de dominio](../domain/README.md#reglas-de-negocio). Las que cambian el comportamiento se repiten aquí con su ID.
