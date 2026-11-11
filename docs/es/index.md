---
title: Cuenta delegada
summary: Permite que personas elegidas accedan como otras cuentas, solo cuando y durante el tiempo que usted permita.
order: 1
---

Delegate Account (`local_delegateaccount`) es un plugin local de Moodle que da a personas concretas acceso a cuentas concretas. Un administrador decide quién puede actuar como quién y durante qué periodo. La persona abre entonces la cuenta desde su menú de usuario mediante la función **Acceder como** de Moodle, y todo lo que hace queda registrado en el log del sitio con ambos nombres.

> [!IMPORTANT]
> **Solo los administradores del sitio pueden usar cuentas delegadas hasta que usted dé el permiso a otras personas.** Cree un rol con la capacidad `local/delegateaccount:use` y asígnelo en el contexto del sistema a quienes deban poder usar delegaciones. Consulte [Dar permiso a las personas](page:getting-started/give-permission).

> [!NOTE]
> Delegate Account nunca guarda contraseñas ni crea cuentas. Solo decide si se puede usar **Acceder como** de Moodle para un par de usuarios concreto en un momento concreto.

Algunas personas necesitan trabajar dentro de la cuenta de otra: un equipo de soporte que corrige un perfil, un asistente que gestiona el calendario de una directora, un docente que sustituye a un colega. La capacidad **Acceder como** de Moodle permite abrir *cualquier* cuenta que se pueda ver, sin límite de tiempo. Delegate Account la reduce a pares explícitos y limitados en el tiempo.

## Cómo se decide el acceso

Para que alguien pueda abrir una cuenta deben cumplirse dos cosas:

1. **Tiene el permiso.** Tiene `local/delegateaccount:use` en el contexto del sistema, o es administrador del sitio.
2. **Existe una delegación para esa cuenta.** Un administrador creó una delegación de esa persona a esa cuenta y está activa en este momento.

Cada delegación tiene un ciclo de vida:

| Estado | Significado |
|---|---|
| Programada | Todavía no se ha llegado a la fecha de inicio |
| Activa | La cuenta se puede abrir ahora |
| Caducada | Ha pasado la fecha de fin |
| Revocada | Un administrador la terminó. Las delegaciones revocadas se conservan como registro |

El acceso se comprueba al abrir la cuenta y de nuevo en cada página mientras dura la sesión delegada. La sesión termina en la página siguiente si la delegación se revoca o caduca, si la persona pierde el permiso, o si la cuenta se suspende, se elimina o pasa a ser administrador del sitio mientras eso esté protegido.

## Características

- **Delegaciones limitadas en el tiempo:** una fecha de inicio, una fecha de fin opcional y revocación explícita, con un historial completo de quién creó, cambió y revocó cada una.
- **Gestión en bloque:** cree delegaciones para varias personas y varias cuentas a la vez, y edite o revoque muchas delegaciones juntas.
- **Permisos de sesión:** permita, impida o prohíba cualquier capacidad mientras alguien trabaja mediante una delegación, para todas las delegaciones o para una, sin crear roles, y cámbielos para muchas delegaciones a la vez.
- **Informe de actividad:** para cada delegación, cada evento registrado mientras se usaba la cuenta mediante ella, con filtros por fecha, componente y acción.
- **Entrada en el menú de usuario:** las personas con delegaciones las abren desde **Cuentas delegadas** en su menú de usuario.
- **Límites del sitio:** máximo de delegaciones por persona, duración máxima, máximo de registros por acción en bloque y protección de las cuentas de administrador.
- **Notificaciones:** mensajes opcionales a las personas implicadas cuando se concede o se revoca el acceso, en el idioma de cada destinatario y personalizables por idioma.
- **Integración:** eventos para cada cambio, un servicio web restringido y compatibilidad con la API de privacidad.
