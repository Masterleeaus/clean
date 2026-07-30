# Agent 2 — PWA Authority and Dependency Map

## Authority map

| Concern | Device authority | Cloud authority | Agent 2 responsibility |
|---|---|---|---|
| Customers, premises, jobs, tasks | Agent 1 WorkCore Device Runtime | WorkCore sync services | Invoke typed capabilities and render results |
| Quotes, invoices, payments | Agent 1 WorkCore rules and commands | WorkCore/MagicAI finance services | UI, drafts, approvals and accurate sync state |
| Operational events and audit | Agent 1 event/audit stores | WorkCore cloud acceptance | Display trace and never forge entries |
| Interaction definitions | Signed compiled bundle | Cloud authoring/compiler | Verify, persist and execute device bundle |
| Interaction session state | Device interaction persistence | Optional published recovery | Resume safely through restart/update |
| Intent classification | Deterministic Tier 0 | Optional heavy AI | Local baseline, explicit fallback |
| Agent planning | Local managers/specialists | Optional cloud intelligence | Limit plans to declared capabilities |
| Operational execution | WorkCore capability invocation | Sync/provider delivery | Tier 3 may invoke but never mutate storage |
| Camera/signature/location | Device tool registry | Optional remote processing | Typed available/deferred/unavailable results |
| Provider keys | Titan Vault | User-approved provider | Never expose to service worker or extensions |
| Application updates | Service-worker staged lifecycle | Release publication | Preserve all device data and unsynced work |
| Conflicts | Agent 1 conflict contracts | Cloud comparison/acceptance | Visible conflict centre and governed resolution |
| Extensions | Signed constrained runtime | Signing and revocation | Validate declaration and enforce access |

## Required Agent 1 contracts

Agent 2 must resolve or request these contracts before implementing dependent features:

1. `DeviceWorkCore.commands.execute(context, command)` or its canonical equivalent.
2. `DeviceWorkCore.queries.execute(context, query)` or its canonical equivalent.
3. `DeviceWorkCore.capabilities.invoke(context, capability, input)` with governed result states.
4. Operation-context creation and validation including tenant, company, membership, device and offline lease.
5. Capability manifest publication and lookup.
6. Local record/query APIs for role-scoped replicated data.
7. Sync state and receipt APIs distinguishing local persistence from cloud acceptance.
8. Conflict list, inspect and resolve contracts.
9. Attachment metadata and encrypted-vault references.
10. Device identity, revocation and offline-lease status.
11. Transactional idempotency and command replay behavior.
12. Domain availability/version information for role-pack activation.

## Missing-contract rule

When one of these contracts is absent, Agent 2 must return or document one of:

- `unavailable`
- `online_required`
- `deferred`
- an Agent 1 dependency record

Agent 2 must not create a parallel repository, command handler, state machine, money calculator, event store, audit store or sync-server endpoint.

## Agent 2 package boundaries

```text
packages/interaction-device
  consumes: workcore capability interface, device tools, persistence adapter
  owns: definition verification, navigation, validation, resumable session state

packages/titan-ai-device
  consumes: role/context/capability manifests, local search, interaction runtime
  owns: deterministic routing, managers, specialists, governed agents, provider fallback

packages/titan-ui-schema
  consumes: local query and governed-result DTOs
  owns: versioned schema validation and accessible render contracts

packages/titan-role-packs
  consumes: signed role/domain/permission/capability manifests
  owns: screen/menu activation, never authorization

packages/titan-extension-runtime
  consumes: signed extension declarations and constrained capability proxies
  owns: loading, sandbox policy, disable/revoke behavior

app/Extensions/Chatbot
  consumes: all Agent 2 packages and Agent 1 client adapter
  owns: shell, routes, navigation, screens, offline/recovery/privacy UX and service worker
```

## Pass 2 architecture tests

The next pass must establish automated failures for:

- `fetch('/api/workcore/...')` or equivalent ordinary command paths inside device UI/action code;
- SQL statements or Eloquent imports in Agent 2 TypeScript/PWA packages;
- direct IndexedDB business-record mutation by AI or interaction handlers;
- operational success responses not backed by a governed WorkCore result;
- service-worker code referencing vault secrets, provider keys or operational database contents;
- role-menu visibility being treated as authorization.

## Current dependency assessment

The repository includes WorkCore and Chatbot source, but the Agent 1 package entrypoints named in the Agent 2 plan were not discoverable through the current repository search index. Pass 2 must inspect explicit package manifests and paths. Until those entrypoints are confirmed, no substitute WorkCore implementation is permitted.
