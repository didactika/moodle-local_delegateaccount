---
id: MAP
status: draft
origin: code
sourceOfTruth: code
---

# Code map

| Model element | Kind | Code location | Verified |
| --- | --- | --- | --- |
| Delegation | table | `db/install.xml` (`local_delegateaccount`) | yes |
| SessionPermission | table | `db/install.xml` (`local_delegateaccount_caps`) | yes |
| Delegation status (scheduled, active, expired, revoked) | method | `classes/manager.php:614` | yes |
| Access decision | method | `classes/manager.php:278` | yes |
| Delegated session start and end | methods | `classes/manager.php:302`, `classes/manager.php:323` | yes |
| Bulk creation | method | `classes/manager.php:360` | yes |
| Revocation | method | `classes/manager.php:468` | yes |
| SiteLimits | methods | `classes/manager.php:1137`, `classes/manager.php:1185` | yes |
| Session overrides | class | `classes/session_capabilities.php` | yes |
| Notifier | class | `classes/notification_manager.php` | yes |
| Capabilities of the plugin | file | `db/access.php` | yes |
| Web services | file | `db/services.php` | yes |
| Events | classes | `classes/event/` | yes |
| Per-request verification | hook | `classes/hook/after_config.php:38` | yes |
| User menu entry | hook | `classes/hook/extend_user_menu.php:33` | yes |
| Privacy | class | `classes/privacy/provider.php` | yes |
