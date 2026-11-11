---
title: Desarrollo
summary: Cómo se construyen las pantallas de la línea de Moodle 5.3, y cómo reconstruirlas y personalizarlas.
order: 40
---

Las pantallas de esta línea son componentes de React 19 escritos en TypeScript, montados por el propio `core/react_autoinit` de Moodle a partir de clases `core\output\react_component_renderable`.

- **Código fuente y compilación:** el código fuente está en `js/esm/src` y la compilación que sirve Moodle está en `js/esm/build`. Se reconstruye con `npx grunt esm` desde la raíz de Moodle, y los tipos se comprueban con `npx tsc --project public/local/delegateaccount/tsconfig.json`.
- **Estilos:** SCSS anidado (`styles.scss` y `scss/`), compilado a `styles.css` con `node tools/build-styles.mjs /path/to/moodle`.
- **Personalizar un componente:** cada componente se puede sustituir con el mecanismo de swizzle de Moodle 5.3 (`js/esm/src/swizzle.json`).
- **Formularios:** los formularios que crean y editan delegaciones siguen siendo formularios de Moodle.
