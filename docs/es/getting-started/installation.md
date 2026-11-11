---
title: Instalación
summary: Requisitos y cómo instalar el plugin desde una release o desde Git.
order: 10
---

**Requisitos:** Moodle 5.3, con la versión de PHP que requiera. Para Moodle 4.5 a 5.2, use la rama `MOODLE_405_STABLE` y las releases `-m405`, que ofrecen las mismas funciones.

| Moodle | Rama | Releases |
|---|---|---|
| 4.5 a 5.2 | `MOODLE_405_STABLE` | `X.Y.Z-m405` |
| 5.3 | `main` | `X.Y.Z-m503` |

**Desde una release:** descargue el ZIP de la última release, vaya a **Administración del sitio > Extensiones > Instalar plugins**, suba el archivo y siga las indicaciones.

**Desde Git:**

```bash
cd /path/to/moodle
git clone https://github.com/didactika/moodle-local_delegateaccount.git public/local/delegateaccount
php admin/cli/upgrade.php
```

Como Moodle 5.3 sirve su código desde la carpeta `public`, el plugin va en `public/local/delegateaccount`.
