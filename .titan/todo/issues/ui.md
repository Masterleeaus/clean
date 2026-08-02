# UI Issues

## UI-001 — Operational screen implementation is hard-coded to 14 app profiles

### Current State

`titan-operational-screens.js` contains a hard-coded profile for each legacy app, including headline, record types, actions and empty-state copy. The renderer itself is reusable and already reads schema navigation, widgets, prompts and local WorkCore projections.

### Required Changes

Preserve the renderer and local-data loading mechanisms. Replace the 14 hard-coded top-level profiles with five canonical application profiles. Move domain/module variations into workflow and vertical schema configuration.

### Why

The current UI would continue presenting retired applications even after server-side registry changes.

### Risk

High for cached PWA clients; medium code-change risk because the renderer is already configuration-oriented.

### Priority

Critical

### Dependencies

Application registry, schema redesign, PWA cache update, local-state migration and JS tests.

### Estimated Work

Medium

### Completion Status

Pending

---

## UI-002 — Five applications need distinct workflows, not renamed copies

### Current State

The shared shell supports schema-driven navigation and widgets, but legacy profiles mostly vary through labels, record types and quick actions.

### Required Changes

Define distinct workspace composition for each application:

- Titan Zero: attention, approvals, governance and executive summaries.
- Titan Go: dispatch/assignment for supervisors and job execution for field workers, selected by role.
- Titan Launch: business/vertical generation, launch stages and growth workspaces.
- Titan Desk: unified communications, intake, qualification and booking/quote request queues.
- Titan Hub: customer self-service with relationship-scoped records.

### Why

The migration explicitly requires application, role, workflow and business-stage-aware UX.

### Risk

Medium. Over-specialisation can duplicate components; use shared primitives with different compositions.

### Priority

High

### Dependencies

Application schemas, role context, workflow registry, WorkCore projections and UX tests.

### Estimated Work

Large

### Completion Status

Pending

---

## UI-003 — Reports and settings ownership is not visible in the shell model

### Current State

Legacy apps such as Titan Analytics are top-level shells, and common settings are exposed broadly. The schema model does not clearly separate application-owned reports from Titan Zero executive reporting.

### Required Changes

Add explicit report and settings metadata to the canonical application registry. Remove Titan Analytics as a top-level application while retaining report components. Assign operational reports to their application and cross-platform reporting to Titan Zero.

### Why

Reports and settings are ownership boundaries, not merely navigation labels.

### Risk

Medium. Existing links and report permissions require migration.

### Priority

High

### Dependencies

Application registry, report inventory, navigation policy, permission model and compatibility mapping.

### Estimated Work

Medium

### Completion Status

Pending

---

## UI-004 — Application context events are incomplete

### Current State

Client events include template, view, action and proposal/local authority hints. They do not consistently include application, role, workflow, permission snapshot, selected WorkCore context or offline status.

### Required Changes

Emit and consume one versioned client context envelope for navigation, prompt requests, operational actions, record opening and offline queueing. Treat client permission/context values as hints and rebuild authoritative context server-side.

### Why

The frontend and AI/runtime layers currently describe the same user state differently.

### Risk

Medium. Event listeners across bundled scripts may rely on existing detail fields.

### Priority

High

### Dependencies

Context schema, frontend event inventory, PWA runtime and API request builder.

### Estimated Work

Medium

### Completion Status

Pending
