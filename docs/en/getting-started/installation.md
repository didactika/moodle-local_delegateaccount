---
title: Installation
summary: Requirements, and how to install the plugin from a release or from Git.
order: 10
---

**Requirements:** Moodle 5.3, with the PHP version it requires. For Moodle 4.5 to 5.2, use the `MOODLE_405_STABLE` branch and the `-m405` releases, which bring the same features.

| Moodle | Branch | Releases |
|---|---|---|
| 4.5 to 5.2 | `MOODLE_405_STABLE` | `X.Y.Z-m405` |
| 5.3 | `main` | `X.Y.Z-m503` |

**From a release:** download the latest release ZIP, go to **Site administration > Plugins > Install plugins**, upload the file and follow the prompts.

**From Git:**

```bash
cd /path/to/moodle
git clone https://github.com/didactika/moodle-local_delegateaccount.git public/local/delegateaccount
php admin/cli/upgrade.php
```

Since Moodle 5.3 serves its code from the `public` folder, the plugin goes in `public/local/delegateaccount`.
