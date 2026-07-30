# Event-Driven Mailboxes

Mailboxes are projections of runtime events. They are not folders that agents poll for arbitrary files.

```text
Message
→ runtime event
→ dispatcher
→ mailbox projection
→ agent state update
→ acknowledgement
```

Mailbox definitions contain routing policy, subscriptions, delivery cursor, retry state and acknowledgement state. Message payloads follow `/.titan/kernel/schemas/mailbox-message.schema.json`; originating events follow the event-envelope schema.

Recommended mailbox identities:

- `claude`
- `agent-architecture`
- `agent-backend`
- `agent-frontend`
- `agent-integration`
- `agent-security`
- `broadcast`

No mailbox transport is operational in the bootstrap pass.
