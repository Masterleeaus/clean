# Offline Runtime Issues

## OFF-001 — Offline operations do not carry canonical application context

### Current State

The PWA and operational workspace identify activity using template and view values. The sync protocol tracks tenant, user, device, entity, operation, versions and conflicts, but the inspected boundary does not establish a canonical application/workflow context for every queued action.

### Required Changes

Add canonical application ID, workflow ID, role scope and authorised WorkCore context references to offline commands and outbox metadata. Preserve backward compatibility for existing operations that lack these fields.

### Why

When an action is replayed after connectivity returns, the server must know which application policy and workflow produced it.

### Risk

High. Context fields must not contain sensitive full records or become trusted authorization claims from the device.

### Priority

Critical

### Dependencies

Context envelope, outbox schema, sync API, application registry and permission revalidation.

### Estimated Work

Large

### Completion Status

Pending

---

## OFF-002 — Local record profiles are hard-coded for 14 applications

### Current State

`titan-operational-screens.js` defines hard-coded application profiles and local record types for 14 legacy applications.

### Required Changes

Replace the 14 top-level profiles with five canonical profiles. Move module-specific record selections into workflow/schema configuration. Add legacy slug resolution for stored local state.

### Why

Offline projections currently reinforce the legacy app model even if server-side navigation is changed.

### Risk

High. Existing devices may retain cached scripts and records under old profile keys.

### Priority

High

### Dependencies

Service worker versioning, schema registry, local database migration and operational screen tests.

### Estimated Work

Medium

### Completion Status

Pending

---

## OFF-003 — Offline state is displayed but not propagated into AI execution

### Current State

The operational workspace reports `navigator.onLine` and pending sync counts. The Titan AI request contract does not include a structured offline state or sync readiness snapshot.

### Required Changes

Pass a bounded offline-state object containing connectivity, queue state, last sync, conflict state and available local capabilities into the canonical context. Server-side execution must treat it as informational and revalidate all actions.

### Why

Titan AI should avoid suggesting online-only actions when the device is offline and should understand whether a request is a proposal, local action or server-confirmed operation.

### Risk

Medium. Browser connectivity signals are imperfect and must not determine authorization.

### Priority

High

### Dependencies

PWA runtime, Titan AI request DTO, context resolver and response UX.

### Estimated Work

Medium

### Completion Status

Pending

---

## OFF-004 — Sync entity authority is limited to chatbot-owned records

### Current State

The server sync registry supports chatbot conversation, message and customer-like records. WorkCore local projections are handled by separate PWA modules and client behaviour.

### Required Changes

Document the boundary between chatbot sync and WorkCore sync. Do not expand `SyncEntityRegistry` into a second WorkCore datastore. Use WorkCore's canonical sync/API boundary for operational records and retain chatbot sync for communication-owned projections.

### Why

A single authoritative business model requires clear ownership even when both systems work offline.

### Risk

High if operational entities are added to chatbot tables or sync handlers without WorkCore validation.

### Priority

High

### Dependencies

WorkCore offline contracts, PWA local repositories, communication identity boundary and sync documentation.

### Estimated Work

Medium

### Completion Status

Pending
