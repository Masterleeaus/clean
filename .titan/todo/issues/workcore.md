# WorkCore Issues

## WC-001 — WorkCore integration is still configured for the old application set

### Current State

`WorkCoreApps` contains ten per-app manifests and documentation for Titan Go, Dispatch, Hub, Money, Teams, Locker, Analytics, Front Desk, Marketing and Social. `WorkCoreAppBridge` loads all enabled manifests and uses their slugs for tool allowlists.

### Required Changes

Replace the old top-level application mapping with five canonical application policies while preserving reusable tool lists and sync configuration. Introduce a legacy-to-canonical mapping layer before removing old manifests.

### Why

WorkCore tool ownership must follow the five platform applications without changing WorkCore's authority over operational records.

### Risk

High. Incorrect mapping can either over-authorise applications or break existing workflows.

### Priority

Critical

### Dependencies

Application registry, tool inventory, permission model, AI routing, legacy slug compatibility and WorkCore integration tests.

### Estimated Work

Large

### Completion Status

Pending

---

## WC-002 — Chatbot communication records need an explicit authority boundary

### Current State

The chatbot extension owns conversation, message and customer-like communication records and synchronises them through its own protocol. The customer-like record includes contact details and channel/session identity.

### Required Changes

Define these records as communication/intake projections. Add canonical links to WorkCore customer/contact/lead identifiers. All promotion, matching and update of authoritative business records must use WorkCore services and emit auditable results.

### Why

Titan Desk creates WorkCore records but never owns them. Titan Hub also must read customer-authorised WorkCore projections rather than duplicate operational data.

### Risk

Critical. Existing communication history must remain linked and usable throughout migration.

### Priority

Critical

### Dependencies

WorkCore CRM contracts, data mapping, identity resolution, migrations, sync protocol and audit events.

### Estimated Work

Large

### Completion Status

Pending

---

## WC-003 — Embedded WorkCore compatibility runtime remains present

### Current State

The authoritative chatbot provider explicitly states that the host `App\Domains\WorkCore` namespace is canonical and conditionally enables an embedded legacy WorkCore runtime only when configured and the host provider is absent.

### Required Changes

Preserve the guard during migration. Inventory every use of the embedded runtime and create a retirement plan. Do not allow five-app work to introduce additional embedded WorkCore models or direct database access.

### Why

The guard is a valuable existing architecture boundary, but a permanent fallback can hide missing host integration and create behavioural divergence.

### Risk

Medium. Some standalone extension packaging may rely on the fallback.

### Priority

High

### Dependencies

Deployment modes, feature flags, host WorkCore provider, standalone packaging and diagnostics.

### Estimated Work

Medium

### Completion Status

Pending

---

## WC-004 — Application context is injected as an untyped input field

### Current State

`WorkCoreAppBridge::executeForApp()` validates the old app tool allowlist and adds `_titan_app` to the tool input payload.

### Required Changes

Move application identity into the canonical execution context while retaining `_titan_app` temporarily for compatibility. Validate application and workflow context before tool selection and record it in audit events.

### Why

Application identity is execution metadata, not business input supplied to individual tools.

### Risk

Medium. Existing tools or logs may currently read `_titan_app` from input.

### Priority

High

### Dependencies

Context envelope, WorkCore runtime contract, audit schema and compatibility tests.

### Estimated Work

Medium

### Completion Status

Pending
