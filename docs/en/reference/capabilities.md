---
title: Capabilities
summary: Every capability of the plugin, who has it by default and what it allows.
order: 41
---

All capabilities apply in the system context.

| Capability | Default roles | Allows the user to |
|---|---|---|
| `local/delegateaccount:use` | None | Open the accounts delegated to them |
| `local/delegateaccount:view` | Manager | See delegations and the management pages |
| `local/delegateaccount:create` | Manager | Create delegations |
| `local/delegateaccount:update` | Manager | Change the period and notification choice of delegations |
| `local/delegateaccount:revoke` | Manager | Revoke delegations |
| `local/delegateaccount:viewactivity` | Manager | Open the activity report of a delegation |
| `local/delegateaccount:manage` | Manager | Everything above except `use` and `managepermissions`. Setting one of the specific capabilities to Prevent or Prohibit still refuses that action |
| `local/delegateaccount:managepermissions` | None | Change the session permissions of the site (also needs `moodle/site:config`) and of delegations |

> [!WARNING]
> Whoever can create delegations decides who may log in as whom. Give `local/delegateaccount:create` and `local/delegateaccount:manage` only to people you would trust with Moodle's own **Log in as**.
