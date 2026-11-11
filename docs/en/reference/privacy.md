---
title: Scheduled tasks, privacy and backup
summary: What the plugin stores, what it exports and what it leaves out of backups.
order: 43
---

## Scheduled tasks

None. Start and end dates are checked each time an account is opened and on every page of a delegated session, so delegations start and stop on time without cron.


## Privacy and backup

- **Privacy:** the plugin implements the Privacy API. It stores, for each delegation, the authorised user, the delegated account, the period, the notification choice and who created, changed or revoked it. This data can be exported. Deleting a user's data removes the delegations they are part of and removes their name from the ones they only created, changed or revoked.
- **Activity:** the plugin keeps no activity of its own. The activity report reads the site's log store, so it follows your log retention settings.
- **Backup:** delegations are site-level data and are not part of course backups.
