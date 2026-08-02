# Permission Issues

## PERM-001 — Effective permissions are not combined with application and role scope

### Current State

The AI execution controller collects a user permission snapshot, and the orchestrator checks permissions required by an intent. The inspected request contract does not carry a canonical current role or application scope.

### Required Changes

Resolve effective permissions from tenant, user, role, application, workflow and active WorkCore context. Pass the resolved snapshot through the canonical context and revalidate it at every WorkCore/Interaction Engine action boundary.

### Why

A user may hold broad permissions in Titan Zero but only self/assigned-job permissions in Titan Go, or customer-only access in Titan Hub.

### Risk

Critical. Incorrect intersection can expose cross-application data or block legitimate work.

### Priority

Critical

### Dependencies

Role resolver, application registry, WorkCore permission model, context DTO and policy tests.

### Estimated Work

Large

### Completion Status

Pending

---

## PERM-002 — Application tool policies still use legacy app manifests

### Current State

WorkCore tool access is constrained by per-app manifest allowlists for the old application set.

### Required Changes

Create five canonical application tool policies and map legacy application/workflow pairs to those policies. Keep WorkCore's own permission checks authoritative and treat application policy as an additional restriction.

### Why

A five-app UI with ten-app tool policies would produce inconsistent authorization.

### Risk

High. Consolidation may accidentally broaden access if tool unions are used without workflow restrictions.

### Priority

Critical

### Dependencies

WorkCore tool inventory, application ownership matrix, role scopes and AI runtime.

### Estimated Work

Large

### Completion Status

Pending

---

## PERM-003 — Navigation and UI capability filtering are not centralised

### Current State

Schema navigation and settings are rendered from application/template configuration. The inspected rendering boundary does not demonstrate one server-side filter based on effective application permissions.

### Required Changes

Add a shared capability projection used by navigation, settings, quick actions, AI prompts and operational screens. Continue to enforce permissions server-side even when an item is hidden.

### Why

Scattered UI checks drift from API authorization and create misleading or unsafe interfaces.

### Risk

High if current hidden functionality relies only on client-side checks; medium migration risk.

### Priority

High

### Dependencies

Context resolver, navigation policy, application registry and frontend bootstrap.

### Estimated Work

Medium

### Completion Status

Pending

---

## PERM-004 — Titan Hub requires customer-relationship scoping

### Current State

The current chatbot supports anonymous/session-based conversations and customer-like channel identities. The five-app target defines Titan Hub as authenticated customer self-service.

### Required Changes

Define a customer principal and relationship scope that restricts WorkCore reads/actions to records belonging to that customer and authorised contacts. Session identifiers alone must not grant operational access.

### Why

Titan Hub exposes bookings, quotes, invoices, payments, documents and service history.

### Risk

Critical. Incorrect relationship scoping can expose another customer's business records.

### Priority

Critical

### Dependencies

Authentication, WorkCore CRM relationships, customer portal policies, identity linking and API tests.

### Estimated Work

Large

### Completion Status

Pending
