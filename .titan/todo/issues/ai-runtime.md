# AI Runtime Issues

## AI-001 — Titan AI request context is not application-complete

### Current State

`TitanAIExecutionController` and `TitanAIRequest` carry tenant, user, device, conversation, template, workflow, payload, permissions and response mode. They do not explicitly carry active application, current role, structured WorkCore context, conversation context summary, AI context or offline state.

### Required Changes

Add a backwards-compatible typed context envelope containing:

- `application_id`;
- `role_id` and role scope;
- `workcore_context` with authorised active entity references;
- `conversation_context`;
- `ai_context`;
- `offline_state` and sync status;
- effective permissions;
- workflow and current view.

Create this context through a single resolver rather than trusting arbitrary client payloads.

### Why

The assistant cannot reliably change behaviour between Titan Zero, Go, Launch, Desk and Hub without a validated application context.

### Risk

High. Older PWA clients will omit new fields, requiring defaults and legacy slug mapping.

### Priority

Critical

### Dependencies

Application registry, authentication, permission resolver, WorkCore context provider, PWA client and request DTO.

### Estimated Work

Large

### Completion Status

Pending

---

## AI-002 — Orchestrator validates only tenant, user and device

### Current State

`FiveTierOrchestrator::assertContext()` requires only `tenant_id`, `user_id` and `device_id`. Worker selection and governed execution proceed without validating application, role, WorkCore context or offline state.

### Required Changes

Validate the canonical context envelope before intent classification and tool execution. Reject unknown applications, mismatched roles, unauthorised WorkCore references and invalid offline actions. Preserve existing governance, approval, audit and idempotency behaviour.

### Why

Tool permissions alone do not prove that an action belongs in the active application or workflow.

### Risk

High. Overly strict validation could block legitimate legacy requests during migration.

### Priority

Critical

### Dependencies

Canonical context DTO, compatibility resolver, application tool policy and governed executor.

### Estimated Work

Medium

### Completion Status

Pending

---

## AI-003 — Intent routing is application-agnostic

### Current State

`IntentGateway` uses hard-coded phrase matching for business actions. It does not use active application, workflow, selected record or actor role to rank or constrain intent decisions.

### Required Changes

Retain deterministic intent definitions but add application-aware routing inputs and policy checks. Application context should narrow available intents and provide defaults, while WorkCore permissions remain authoritative. Examples:

- Titan Zero prioritises attention, approvals and administration.
- Titan Go prioritises assignment, next-job and completion actions.
- Titan Launch prioritises business/vertical creation and growth.
- Titan Desk prioritises summarisation, intake and qualification.
- Titan Hub prioritises self-service actions scoped to the authenticated customer.

### Why

Identical words can imply different authorised actions in different applications.

### Risk

Medium. Changing classifier order may alter existing intent matches.

### Priority

High

### Dependencies

Application registry, context envelope, intent tests and WorkCore tool mapping.

### Estimated Work

Medium

### Completion Status

Pending

---

## AI-004 — Legacy app-specific WorkCore allowlists do not match five-app ownership

### Current State

Titan AI uses per-app WorkCore manifests for the old application set. The bridge injects `_titan_app` and checks allowed tools against those manifests.

### Required Changes

Consolidate the allowlists into five canonical application policies. Map legacy slugs before policy evaluation. Keep WorkCore as the final validator and writer.

### Why

Old app identities currently determine which tools Titan AI can execute.

### Risk

High. Incorrect consolidation could grant too many tools or remove required workflows.

### Priority

High

### Dependencies

WorkCore mapping audit, application ownership matrix, permission model and tool tests.

### Estimated Work

Large

### Completion Status

Pending
