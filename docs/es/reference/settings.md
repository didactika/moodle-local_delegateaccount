---
title: Ajustes
summary: "Los ajustes del plugin: controles de las delegaciones y notificaciones."
order: 30
---

Los ajustes están en **Administración del sitio > Extensiones > Plugins locales > Ajustes de Cuentas delegadas**, en cuatro pestañas: **Delegaciones**, **Notificaciones**, **Mensajes** y **Permisos de sesión**.

## Controles de las delegaciones

| Ajuste | Valor por defecto | Descripción |
|---|---|---|
| Máximo de cuentas delegadas por usuario | 10 | Cuántas delegaciones vigentes o programadas puede tener un usuario autorizado. Introduzca `0` para no limitar |
| Permitir delegaciones sin fecha de fin | Sí | Si se desactiva, toda delegación necesita una fecha de fin |
| Duración máxima de una delegación | 0 | Lo máximo que puede durar una delegación, en días. Solo se muestra y se aplica cuando **Permitir delegaciones sin fecha de fin** está desactivado. `0` significa sin límite |
| Proteger las cuentas de administrador del sitio | Sí | Los administradores del sitio no se pueden delegar, y una delegación existente deja de funcionar si su cuenta pasa a ser administrador |
| Máximo de registros por acción en bloque | 100 | Cuántas delegaciones puede crear, editar o revocar una acción en bloque. Introduzca `0` para no limitar |
| Cuentas delegadas mostradas en el menú de usuario | 10 | Cuentas que se listan en el menú de usuario antes de **Ver todas las cuentas delegadas**. Introduzca `0` para listarlas todas |

> [!CAUTION]
> Con **Proteger las cuentas de administrador del sitio** desactivado, un usuario autorizado con una delegación a un administrador puede acceder como ese administrador y tomar el control total del sitio.

## Notificaciones

| Ajuste | Valor por defecto | Descripción |
|---|---|---|
| Política de notificaciones | Permitir que la persona que crea la delegación elija | O **Notificar siempre**, o **No notificar nunca**. **No notificar nunca** no envía nada, tampoco para las delegaciones creadas antes de seleccionarlo, y oculta los demás ajustes de notificación |
| Destinatarios de las notificaciones | Ambos usuarios | El usuario autorizado, la cuenta delegada, o ambos |
| Notificar cuando se revoca una delegación | Sí | Notifica también cuando se revoca el acceso |
| Asunto y mensaje cuando se concede o se revoca el acceso | Vacío | Un asunto y un mensaje por acción para cada idioma instalado. Los campos vacíos usan el texto incorporado, redactado para cada destinatario. Un mensaje personalizado se envía sin cambios a ambos destinatarios, así que escríbalo para que se lea bien en los dos casos |

> [!TIP]
> Use **Enviarme una notificación de prueba**, debajo de estos ajustes, para recibir el asunto y el mensaje guardados de un idioma tal como los verían ambos destinatarios. Guarde antes los cambios.

Cada destinatario recibe la notificación en el idioma de su perfil, o en el idioma del sitio cuando el suyo no está instalado. Los mensajes usan el proveedor **Notificaciones de cuentas delegadas**, enviado como notificaciones web y por correo por defecto; los usuarios y los administradores pueden cambiarlo en las preferencias de notificación habituales.
