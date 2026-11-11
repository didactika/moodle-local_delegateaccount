---
id: IMP
status: draft
origin: code
sourceOfTruth: code
---

# Implementación

Esta carpeta une el modelo con el código. El [mapa del código](./code-map.md) lista cada elemento del modelo y dónde vive; cada caso de uso tiene una tarjeta con su código y sus pruebas.

La dirección de trabajo fue **del código a la documentación**: el modelo describe el plugin tal como es y el código sigue siendo la fuente de verdad. Los resultados de las pruebas en las tarjetas dicen `no ejecutada` hasta que alguien las ejecute; no son afirmaciones.

| ID | Caso de uso | Tarjeta |
| --- | --- | --- |
| UC-001 | Crear delegaciones | [implementación](./use-cases/createDelegations/README.md) |
| UC-002 | Abrir una cuenta delegada | [implementación](./use-cases/openDelegatedAccount/README.md) |
| UC-003 | Editar los permisos de sesión | [implementación](./use-cases/editSessionPermissions/README.md) |
