# Interaction Engine Issues

## IE-001 — No explicit Interaction Engine adapter was evidenced inside the chatbot extension

### Current State

The repository contains a canonical Interaction Engine package outside the scoped extension, but the inspected chatbot runtime does not expose a clear adapter or typed contract into it. Chatbot controllers and Titan AI orchestration currently coordinate significant execution behaviour directly.

### Required Changes

Inspect the canonical package's public contracts during implementation and create one chatbot adapter at the extension boundary. Do not copy engine classes into the extension. Route applicable conversational workflows through the package while preserving existing API routes and response contracts.

### Why

The migration requires every request to propagate application, user, role, WorkCore, conversation, AI, offline and permission context through the Interaction Engine.

### Risk

High. The package API was outside this pass's approved scan scope, so implementation must not guess its contract.

### Priority

Critical

### Dependencies

Canonical Interaction Engine package, service provider binding, context DTO, Titan AI runtime and existing controller tests.

### Estimated Work

Large

### Completion Status

Pending

---

## IE-002 — Required context is not propagated end to end

### Current State

The current AI context includes tenant, user, device and selected optional fields, but no single immutable interaction context carrying the complete application state. Offline events also emit template/view/action values without a shared server-side contract.

### Required Changes

Define a context mapper between the chatbot application context and the Interaction Engine execution context. Ensure every workflow propagates:

- current application;
- current user;
- current role;
- current WorkCore context;
- current workflow;
- current conversation context;
- current AI context;
- current offline state;
- current permissions.

### Why

Partial context propagation produces inconsistent authorization, AI behaviour, auditing and replay.

### Risk

High. Sensitive WorkCore context must be reduced to authorised identifiers and summaries rather than raw records.

### Priority

Critical

### Dependencies

Context resolver, WorkCore context provider, permission snapshot, offline runtime and Interaction Engine contracts.

### Estimated Work

Large

### Completion Status

Pending

---

## IE-003 — Execution responsibilities may overlap

### Current State

The chatbot has a five-tier orchestrator, governed tool executor, direct controller services, a WorkCore bridge and a separate canonical Interaction Engine package. Their exact execution ownership is not documented at the extension boundary.

### Required Changes

Document and enforce the sequence:

1. chatbot receives and authenticates request;
2. context resolver builds canonical context;
3. Titan AI interprets and plans;
4. Interaction Engine executes workflow/pipeline;
5. WorkCore validates and changes operational records;
6. events, audit and response return through the same context.

Remove only proven overlap; preserve specialised components behind this sequence.

### Why

Unclear ownership encourages parallel execution pipelines and bypasses.

### Risk

High. Existing governance and approval behaviour must remain intact.

### Priority

High

### Dependencies

Architecture decision record, AI governance, Interaction Engine adapter, WorkCore runtime and route/controller refactoring.

### Estimated Work

Medium

### Completion Status

Pending
