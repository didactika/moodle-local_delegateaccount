---
title: Troubleshooting
summary: What to check when something does not work as expected.
order: 29
---

| Problem | Possible cause |
|---|---|
| A person does not appear under **Authorised users** | They do not hold `local/delegateaccount:use` in the system context, or their account is suspended |
| An account cannot be chosen as a target | It is suspended, the guest account, a protected site administrator or the authorised user themselves, or that user already has a delegation to it that has not been revoked, including an expired one. Edit that delegation instead |
| **Delegated accounts** is missing from the user menu | The person has no active delegation, does not hold the permission, or is already logged in as someone else |
| "Delegated session ended" appears | The delegation was revoked or expired, the person lost the permission, or the account became unavailable while it was in use |
| The activity report is empty | Nothing was done through that delegation yet, no SQL log store is enabled, or the events are anonymous and you cannot view anonymous events |
| Nobody receives notifications | The notification policy is **Never notify**, **Do not send a notification** was chosen, or the recipients disabled these notifications in their preferences |
