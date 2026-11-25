---
id: MAP
status: draft
origin: code
sourceOfTruth: code
---

# Mapa del código

| Elemento del modelo | Tipo | Ubicación en el código | Verificado |
| --- | --- | --- | --- |
| Delegation | tabla | `db/install.xml` (`local_delegateaccount`) | sí |
| SessionPermission | tabla | `db/install.xml` (`local_delegateaccount_caps`) | sí |
| Estado de una delegación (programada, activa, caducada, revocada) | método | `classes/manager.php:614` | sí |
| Decisión de acceso | método | `classes/manager.php:278` | sí |
| Inicio y fin de la sesión delegada | métodos | `classes/manager.php:302`, `classes/manager.php:323` | sí |
| Creación masiva | método | `classes/manager.php:360` | sí |
| Revocación | método | `classes/manager.php:468` | sí |
| SiteLimits | métodos | `classes/manager.php:1137`, `classes/manager.php:1185` | sí |
| Sobrescrituras de sesión | clase | `classes/session_capabilities.php` | sí |
| Notificador | clase | `classes/notification_manager.php` | sí |
| Capacidades del plugin | fichero | `db/access.php` | sí |
| Servicios web | fichero | `db/services.php` | sí |
| Eventos | clases | `classes/event/` | sí |
| Verificación en cada petición | hook | `classes/hook/after_config.php:38` | sí |
| Entrada del menú de usuario | hook | `classes/hook/extend_user_menu.php:33` | sí |
| Privacidad | clase | `classes/privacy/provider.php` | sí |
