---
title: Integración con servicios web
summary: Las funciones del servicio web, la capacidad que necesita cada una, sus límites y ejemplos.
order: 42
---

`local_delegateaccount` registra el servicio externo **Gestión de cuentas delegadas**. Está desactivado por defecto, restringido a usuarios autorizados explícitamente y no permite subir ni descargar archivos. Actívelo solo después de crear una cuenta de integración dedicada y de asignarle las capacidades mínimas que necesite esa integración.

## Matriz de funciones y capacidades

| Función | Capacidad | Finalidad |
| --- | --- | --- |
| `local_delegateaccount_get_delegations` | `local/delegateaccount:view` | Leer una página estable de registros de delegaciones. |
| `local_delegateaccount_get_user_delegations` | `local/delegateaccount:view` | Leer el historial de delegaciones de un usuario autorizado. |
| `local_delegateaccount_create_delegation` | `local/delegateaccount:create` | Crear una delegación de forma idempotente usando identificadores escalares. |
| `local_delegateaccount_create_delegations` | `local/delegateaccount:create` | Crear de forma idempotente una matriz de usuarios por destinos. |
| `local_delegateaccount_update_delegations` | `local/delegateaccount:update` | Aplicar un periodo de validez y una decisión de notificación a los registros seleccionados. |
| `local_delegateaccount_revoke_delegations` | `local/delegateaccount:revoke` | Revocar lógicamente los registros seleccionados tras una confirmación explícita. |
| `local_delegateaccount_get_delegation_activity` | `local/delegateaccount:viewactivity` | Leer la actividad del log estándar dentro del periodo inmutable de una delegación. |
| `local_delegateaccount_get_session_permissions` | `local/delegateaccount:view` (`moodle/site:config` para el sitio) | Leer las capacidades del sitio y las sobrescrituras de permisos del sitio (`delegationid` 0) o de una delegación. |
| `local_delegateaccount_save_session_permissions` | `local/delegateaccount:managepermissions` (y `moodle/site:config` para el sitio) | Cambiar algunas sobrescrituras del sitio o de una delegación; `permission` 0 quita una. |
| `local_delegateaccount_update_session_permissions` | `local/delegateaccount:managepermissions` | Cambiar, o copiar de una delegación, las sobrescrituras de muchas delegaciones: los identificadores dados, o `all` con los filtros de una lista. |

Las páginas del plugin llaman además a `local_delegateaccount_get_user_list`, `local_delegateaccount_get_delegation_list`, `local_delegateaccount_get_delegation_ids`, `local_delegateaccount_get_activity_list` y `local_delegateaccount_get_my_accounts`. Devuelven lo que dibujan las pantallas (textos en el idioma del usuario, direcciones, indicadores de lo que se puede hacer) y pueden cambiar con ellas; las integraciones deberían preferir las funciones anteriores.

Una sobrescritura solo puede establecer `1` (Permitir), `-1` (Impedir) o `-1000` (Prohibir), los valores propios de Moodle. Permitir se rechaza salvo que el usuario que llama sea administrador del sitio, también cuando se copia de otra delegación. Los cambios en bloque se escriben en lotes de 500 delegaciones y no los limita **Máximo de registros por acción en bloque**, que sigue aplicándose a crear, editar y revocar.

`local/delegateaccount:manage` concede todas las funciones anteriores salvo las de permisos de sesión, exactamente como hace en las páginas de gestión. Establecer una capacidad específica en **Impedir** o **Prohibir** en uno de los roles de sistema del usuario de la integración sigue denegando esa función, aunque el rol permita `local/delegateaccount:manage`. Para una integración de solo consulta, conceda `local/delegateaccount:view` en lugar de `local/delegateaccount:manage`.

Cada función registrada se corresponde con una clase dedicada en `classes/external/`. La base compartida `delegation_service` es interna, no está registrada como función invocable y contiene solo ayudas comunes de validación, de resultado de creación y de serialización.

## Seguridad y límites

- Las páginas empiezan en cero y aceptan entre 1 y 100 registros.
- La creación en bloque es el producto cartesiano de los usuarios autorizados y las cuentas de destino suministrados. Los ajustes del sitio **Máximo de registros por acción en bloque** y **Máximo de cuentas delegadas por usuario** se aplican antes de crear ninguna fila.
- La creación singular y la de lote comparten la misma implementación de dominio. Use la función singular para un par exacto de usuario y destino, y la de lote cuando cada usuario suministrado deba recibir cada cuenta de destino.
- Un par usuario-destino existente que no esté revocado devuelve `unchanged`; la operación no crea un duplicado.
- El mismo usuario no puede ser a la vez el usuario autorizado y la cuenta de destino.
- Las cuentas suspendidas, eliminadas, de invitado, no autorizadas y de administrador del sitio protegido se rechazan con las mismas reglas que la interfaz web.
- La revocación requiere `confirm=true`, sigue siendo lógica y conserva el historial. Impide nuevas sesiones delegadas y termina al instante las sesiones delegadas que ya estén abiertas.
- Los resultados de actividad se limitan a los límites de inicio y de fin de la delegación seleccionada. Una delegación posterior entre los mismos usuarios es un periodo distinto.
- Las reglas de notificación son idénticas a las de la interfaz de gestión. Las respuestas del servicio nunca contienen cuerpos de mensajes, tokens ni credenciales.

## Decisión de notificación

`create_delegation`, `create_delegations` y `update_delegations` aceptan un `notificationmode` opcional con los valores `always`, `never` o `site`. Cuando se omite, se usa `site`, que significa "que decida el sitio". La decisión que se guarda y se aplica depende del ajuste **Política de notificaciones**:

| Política de notificaciones | `always` | `never` | `site` u omitido |
| --- | --- | --- | --- |
| Permitir que la persona que crea la delegación elija (por defecto) | Notificar | No notificar | Notificar, como hace por defecto el formulario de creación |
| Notificar siempre | Notificar | Notificar | Notificar |
| No notificar nunca | No notificar | No notificar | No notificar |

La decisión guardada es siempre `always` o `never`, de modo que el valor que devuelven las funciones de lectura muestra lo que realmente se aplicó. Envíe `notificationmode=never` de forma explícita cuando una integración no deba notificar a nadie.

## Ejemplos de peticiones

Estos ejemplos solo muestran la forma de los parámetros. Sustituya `https://moodle.example` por la URL del sitio y facilite el token mediante un gestor de secretos o una variable de entorno protegida; nunca suba al repositorio un token real.

Crear una delegación válida en este momento sin enviar notificación:

```text
POST https://moodle.example/webservice/rest/server.php
wstoken=<SECRET>&moodlewsrestformat=json
&wsfunction=local_delegateaccount_create_delegation
&realuserid=120&delegateduserid=450
&timestart=1787356800&timeend=1787961600&notificationmode=never
```

La respuesta singular identifica el par solicitado, su registro de delegación actual y uno de tres resultados: `created`, `unchanged` o `skipped`.

Crear la matriz completa entre dos usuarios autorizados y tres cuentas de destino usando un mismo periodo de validez:

```text
POST https://moodle.example/webservice/rest/server.php
wstoken=<SECRET>&moodlewsrestformat=json
&wsfunction=local_delegateaccount_create_delegations
&realuserids[0]=120&realuserids[1]=121
&delegateduserids[0]=450&delegateduserids[1]=451&delegateduserids[2]=452
&timestart=1787356800&timeend=1787961600&notificationmode=never
```

Esa petición evalúa seis pares y devuelve un resultado por par más el número de registros creados. Repetir cualquiera de las dos peticiones de creación es seguro: una relación vigente se devuelve como `unchanged` en lugar de duplicarse.

Leer las 25 primeras delegaciones activas de un usuario autorizado:

```text
GET https://moodle.example/webservice/rest/server.php
?wstoken=<SECRET>&moodlewsrestformat=json
&wsfunction=local_delegateaccount_get_user_delegations
&realuserid=120&page=0&perpage=25&status=active
```

Revocar dos registros de forma explícita:

```text
POST https://moodle.example/webservice/rest/server.php
wstoken=<SECRET>&moodlewsrestformat=json
&wsfunction=local_delegateaccount_revoke_delegations
&delegationids[0]=81&delegationids[1]=82&confirm=1
```

Los identificadores numéricos anteriores son ficticios. Las aplicaciones cliente deberían guardar solo los identificadores que necesiten y no deberían registrar respuestas completas ni URL de peticiones que contengan credenciales.
