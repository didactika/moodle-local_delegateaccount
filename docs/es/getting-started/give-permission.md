---
title: Dar permiso a las personas
summary: Cree el rol que permite usar delegaciones y asígnelo.
order: 11
---

1. Vaya a **Administración del sitio > Usuarios > Permisos > Definir roles** y añada un rol nuevo, por ejemplo *Usuario de cuentas delegadas*.
2. Seleccione **Sistema** como único tipo de contexto y permita `local/delegateaccount:use` (**Acceder como una cuenta delegada**).
3. Vaya a **Administración del sitio > Usuarios > Permisos > Asignar roles de sistema** y asigne el rol a las personas que deban poder usar delegaciones.

Mientras ningún rol conceda la capacidad, la página de gestión recuerda estos dos pasos con enlaces a las dos páginas.

> [!WARNING]
> No conceda `local/delegateaccount:use` al rol **Usuario autenticado**. Todos los usuarios pasarían a contar como autorizados.
