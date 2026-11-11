---
title: Give people permission
summary: Create the role that lets people use delegations, and assign it.
order: 11
---

1. Go to **Site administration > Users > Permissions > Define roles** and add a new role, for example *Delegated account user*.
2. Select **System** as the only context type and allow `local/delegateaccount:use` (**Log in as a delegated account**).
3. Go to **Site administration > Users > Permissions > Assign system roles** and give the role to the people who should be able to use delegations.

Until a role grants the capability, the management page shows a reminder with links to these two pages.

> [!WARNING]
> Do not grant `local/delegateaccount:use` to the **Authenticated user** role. Every user would then count as authorised.
