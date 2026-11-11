---
title: Permisos de sesión
summary: Permita, impida o prohíba capacidades en las sesiones abiertas mediante una delegación.
order: 23
---

La pestaña **Permisos de sesión** de los ajustes, y la pestaña **Permisos de sesión** de cada delegación, establecen lo que puede hacer una sesión abierta mediante una delegación. Cada capacidad del sitio tiene uno de cuatro permisos:

| Permiso | En la sesión |
|---|---|
| No establecido | No cambia nada: la cuenta conserva lo que le dan sus roles. Todas las capacidades empiezan aquí |
| Permitir | La capacidad se concede en toda la sesión, salvo que un rol la prohíba |
| Impedir | La capacidad se quita de los roles de la cuenta en todo el sitio, como hace Impedir en **Definir roles**. Una sobrescritura de un rol en un curso o una actividad sigue aplicándose |
| Prohibir | La capacidad se deniega en toda la sesión, y nada puede devolverla |

El ajuste de una delegación prevalece sobre el del sitio; una delegación en No establecido sigue al sitio. Busque capacidades por nombre, nombre técnico o componente, acótelas con los chips (sobrescritas, cada permiso, con riesgos) y guarde los cambios desde la barra al pie de la ventana. **Copiar de otra delegación** carga las sobrescrituras de otra delegación para revisarlas antes de guardar.

Para cambiar muchas delegaciones a la vez, selecciónelas en una lista y elija **Permisos de sesión** en la barra: elija las capacidades y el permiso que recibe cada una (o **Quitar la sobrescritura**), o copie las sobrescrituras de una delegación, añadiéndolas a las que ya hay o reemplazándolas. La selección puede ser todas las delegaciones que coinciden con la lista, y el cambio se escribe por lotes, de modo que escala a cualquier número de delegaciones.

Las sobrescrituras se aplican solo a las sesiones abiertas mediante una delegación. No se crea ni se cambia ningún rol: los datos de acceso de la sesión se reescriben en cada petición, de modo que un cambio llega a las sesiones abiertas desde su página siguiente, y el titular de la cuenta conserva todos sus permisos al entrar con normalidad. Las cuentas de administrador del sitio tienen todas las capacidades sea cual sea el ajuste, que es otra razón para mantener activado **Proteger las cuentas de administrador del sitio**. Las capacidades que Moodle comprueba con consultas a la base de datos en lugar de con la sesión (por ejemplo, algunas listas de usuarios o de cursos) no se ven afectadas.

> [!WARNING]
> Permitir da una capacidad a una sesión aunque la cuenta no la tenga. Cambiar los permisos de sesión requiere `local/delegateaccount:managepermissions`, que ningún rol tiene por defecto, y una persona solo puede permitir las capacidades que ella misma tiene.
