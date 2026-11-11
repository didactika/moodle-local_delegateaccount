---
title: Capacidades
summary: Todas las capacidades del plugin, quién las tiene por defecto y qué permiten.
order: 31
---

Todas las capacidades se aplican en el contexto del sistema.

| Capacidad | Roles por defecto | Permite al usuario |
|---|---|---|
| `local/delegateaccount:use` | Ninguno | Abrir las cuentas que se le han delegado |
| `local/delegateaccount:view` | Gestor | Ver las delegaciones y las páginas de gestión |
| `local/delegateaccount:create` | Gestor | Crear delegaciones |
| `local/delegateaccount:update` | Gestor | Cambiar el periodo y la elección de notificación de las delegaciones |
| `local/delegateaccount:revoke` | Gestor | Revocar delegaciones |
| `local/delegateaccount:viewactivity` | Gestor | Abrir el informe de actividad de una delegación |
| `local/delegateaccount:manage` | Gestor | Todo lo anterior excepto `use` y `managepermissions`. Establecer una de las capacidades específicas en Impedir o Prohibir sigue denegando esa acción |
| `local/delegateaccount:managepermissions` | Ninguno | Cambiar los permisos de sesión del sitio (requiere además `moodle/site:config`) y de las delegaciones |

> [!WARNING]
> Quien puede crear delegaciones decide quién puede acceder como quién. Conceda `local/delegateaccount:create` y `local/delegateaccount:manage` solo a personas en las que confiaría para usar **Acceder como** de Moodle.
