---
title: Tareas programadas, privacidad y copias de seguridad
summary: Qué guarda el plugin, qué exporta y qué deja fuera de las copias de seguridad.
order: 33
---

## Tareas programadas

Ninguna. Las fechas de inicio y de fin se comprueban cada vez que se abre una cuenta y en cada página de una sesión delegada, de modo que las delegaciones empiezan y terminan a tiempo sin cron.

## Privacidad y copias de seguridad

- **Privacidad:** el plugin implementa la API de privacidad. Guarda, para cada delegación, el usuario autorizado, la cuenta delegada, el periodo, la elección de notificación y quién la creó, cambió o revocó. Estos datos se pueden exportar. Eliminar los datos de un usuario elimina las delegaciones de las que forma parte y quita su nombre de las que solo creó, cambió o revocó.
- **Actividad:** el plugin no guarda actividad propia. El informe de actividad lee el almacén de logs del sitio, de modo que sigue sus ajustes de retención de logs.
- **Copia de seguridad:** las delegaciones son datos del sitio y no forman parte de las copias de seguridad de los cursos.
