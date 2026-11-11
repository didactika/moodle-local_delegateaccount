---
title: Solución de problemas
summary: Qué comprobar cuando algo no funciona como se espera.
order: 29
---

| Problema | Causa posible |
|---|---|
| Una persona no aparece en **Usuarios autorizados** | No tiene `local/delegateaccount:use` en el contexto del sistema, o su cuenta está suspendida |
| Una cuenta no se puede elegir como destino | Está suspendida, es la cuenta de invitado, es un administrador del sitio protegido o es el propio usuario autorizado, o ese usuario ya tiene una delegación a ella que no se ha revocado, incluida una caducada. Edite esa delegación en su lugar |
| Falta **Cuentas delegadas** en el menú de usuario | La persona no tiene ninguna delegación activa, no tiene el permiso, o ya ha accedido como otra persona |
| Aparece "La sesión delegada ha terminado" | La delegación se revocó o caducó, la persona perdió el permiso o la cuenta dejó de estar disponible mientras se usaba |
| El informe de actividad está vacío | Todavía no se ha hecho nada mediante esa delegación, no hay un almacén de logs SQL activado o los eventos son anónimos y usted no puede ver eventos anónimos |
| Nadie recibe notificaciones | La política de notificaciones es **No notificar nunca**, se eligió **No enviar una notificación**, o los destinatarios desactivaron estas notificaciones en sus preferencias |
