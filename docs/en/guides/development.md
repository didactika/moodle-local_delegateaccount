---
title: Development
summary: How the screens of the Moodle 5.3 line are built, and how to rebuild and customise them.
order: 40
---

The screens of this line are React 19 components written in TypeScript, mounted by Moodle's own `core/react_autoinit` from `core\output\react_component_renderable` classes.

- **Source and build:** the source is in `js/esm/src` and the build that Moodle serves is committed in `js/esm/build`. Rebuild it with `npx grunt esm` from the root of Moodle, and check the types with `npx tsc --project public/local/delegateaccount/tsconfig.json`.
- **Styles:** nested SCSS (`styles.scss` and `scss/`), compiled into `styles.css` by `node tools/build-styles.mjs /path/to/moodle`.
- **Customising a component:** each component can be overridden with the swizzle mechanism of Moodle 5.3 (`js/esm/src/swizzle.json`).
- **Forms:** the forms that create and edit delegations stay Moodle forms.
