# Web-service integration

`local_delegateaccount` registers the **Delegated account management** external
service. It is disabled by default, restricted to explicitly authorised users,
and does not allow file upload or download. Enable it only after creating a
dedicated integration account and assigning the minimum capabilities required
for that integration.

## Function and capability matrix

| Function | Capability | Purpose |
| --- | --- | --- |
| `local_delegateaccount_get_delegations` | `local/delegateaccount:view` | Read a stable page of delegation records. |
| `local_delegateaccount_get_user_delegations` | `local/delegateaccount:view` | Read one authorised user's delegation history. |
| `local_delegateaccount_create_delegation` | `local/delegateaccount:create` | Create one delegation idempotently using scalar identifiers. |
| `local_delegateaccount_create_delegations` | `local/delegateaccount:create` | Create a user-by-target matrix idempotently. |
| `local_delegateaccount_update_delegations` | `local/delegateaccount:update` | Apply one validity period and notification decision to selected records. |
| `local_delegateaccount_revoke_delegations` | `local/delegateaccount:revoke` | Logically revoke selected records after explicit confirmation. |
| `local_delegateaccount_get_delegation_activity` | `local/delegateaccount:viewactivity` | Read standard-log activity within one immutable delegation period. |

The compatibility capability `local/delegateaccount:manage` is not accepted by
these external functions. This prevents an integration intended only for
inventory from gaining mutation access and keeps service permissions aligned
with the granular management interface.

## Safety and limits

- Pages are zero-based and accept between 1 and 100 records.
- Bulk creation is the Cartesian product of the supplied authorised users and
  target accounts. The site's **Maximum records per bulk operation** and
  **Maximum delegated accounts per user** settings are enforced before any
  row is created.
- Singular and batch creation share the same domain implementation. Use the
  singular function for one exact user-target pair and the batch function when
  every supplied user should receive every supplied target account.
- An existing non-revoked user-target pair returns `unchanged`; the operation
  does not create a duplicate.
- The same user cannot be both the authorised user and target account.
- Suspended, deleted, unauthorised and protected privileged accounts are
  rejected using the same domain rules as the web interface.
- Revocation requires `confirm=true`, remains logical, preserves history and
  immediately prevents a new delegated session.
- Activity results are clamped to the selected delegation's own start and end
  boundary. A later delegation between the same users is a different period.
- Notification policy and template rules are identical to the management
  interface. Service responses never contain message bodies, tokens or
  credentials.

## Example requests

These examples show parameter shapes only. Replace `https://moodle.example`
with the site URL and provide the token through a secret manager or protected
environment variable; never commit a real token.

Create one currently valid delegation without sending a notification:

```text
POST https://moodle.example/webservice/rest/server.php
wstoken=<SECRET>&moodlewsrestformat=json
&wsfunction=local_delegateaccount_create_delegation
&realuserid=120&delegateduserid=450
&timestart=1787356800&timeend=1787961600&notificationmode=never
```

The singular response identifies the requested pair, its current delegation
record and one of three outcomes: `created`, `unchanged` or `skipped`.

Create the complete matrix between two authorised users and three target
accounts using one common validity period:

```text
POST https://moodle.example/webservice/rest/server.php
wstoken=<SECRET>&moodlewsrestformat=json
&wsfunction=local_delegateaccount_create_delegations
&realuserids[0]=120&realuserids[1]=121
&delegateduserids[0]=450&delegateduserids[1]=451&delegateduserids[2]=452
&timestart=1787356800&timeend=1787961600&notificationmode=never
```

That request evaluates six pairs and returns one outcome per pair plus the
number of records created. Repeating either creation request is safe: a
current relationship is returned as `unchanged` rather than duplicated.

Read the first 25 active delegations belonging to one authorised user:

```text
GET https://moodle.example/webservice/rest/server.php
?wstoken=<SECRET>&moodlewsrestformat=json
&wsfunction=local_delegateaccount_get_user_delegations
&realuserid=120&page=0&perpage=25&status=active
```

Revoke two records explicitly:

```text
POST https://moodle.example/webservice/rest/server.php
wstoken=<SECRET>&moodlewsrestformat=json
&wsfunction=local_delegateaccount_revoke_delegations
&delegationids[0]=81&delegationids[1]=82&confirm=1
```

The numeric identifiers above are fictitious. Client applications should
store only the identifiers they require and should not log full responses or
request URLs containing credentials.
