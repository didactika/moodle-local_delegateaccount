---
id: ACT
status: draft
origin: code
sourceOfTruth: code
---

# Actores

| Actor | Tipo | Objetivo | Casos de uso |
| --- | --- | --- | --- |
| Administrador | Primario | Decidir quién puede actuar como quién y qué puede hacer mientras tanto. Tiene `local/delegateaccount:manage` y las capacidades para crear, actualizar, revocar y ver la actividad. | [UC-001](./use-cases/createDelegations/README.md), [UC-003](./use-cases/editSessionPermissions/README.md), [UC-004](./use-cases/revokeDelegations/README.md), [UC-005](./use-cases/reviewActivity/README.md) |
| Persona autorizada | Primario | Trabajar dentro de una cuenta que se le ha delegado y salir de ella. Una administradora del sitio o alguien con `local/delegateaccount:use`. | [UC-002](./use-cases/openDelegatedAccount/README.md) |
| Moodle | Secundario | Aporta el acceso como otro usuario, el sistema de capacidades, el registro de eventos y la mensajería en los que se apoya el plugin. | todos |
