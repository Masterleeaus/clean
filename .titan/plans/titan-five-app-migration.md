# Titan Omni Chatbot Five-Application Migration Plan

## Status

Pass 1 audit completed. Implementation has not started.

## Scope

This migration is limited to the chatbot-related extension surface under `app/Extensions`, with `app/Extensions/Chatbot` treated as the candidate authoritative implementation. The parallel `app/Extensions/TitanZeroChatbot` tree and adjacent chatbot capability extensions are included only where they affect ownership, duplication, wiring, or migration dependencies.

The wider repository, canonical WorkCore domain, and Interaction Engine package are dependencies, not targets for restructuring during this pass.

## Objective

Refactor the existing chatbot extension into the application-aware conversational interface for five platform applications:

1. Titan Zero — administration, governance and executive operations.
2. Titan Go — dispatch, mobile and field execution.
3. Titan Launch — business creation and growth, containing Titan Sprout as an internal engine.
4. Titan Desk — business-facing communications, intake, qualification and booking requests.
5. Titan Hub — customer self-service and relationship portal.

WorkCore remains the single authority for operational business records. Titan AI remains the shared reasoning and worker runtime. The Interaction Engine remains the shared execution pipeline. The existing offline runtime remains the local-first execution and synchronisation layer.

## Architectural Guardrails

- Do not rewrite the extension.
- Do not create a parallel application registry, AI runtime, Interaction Engine, WorkCore bridge, permission system, route family, PWA runtime, sync engine or database.
- Do not let application surfaces own authoritative customers, jobs, invoices, schedules, workers or other WorkCore records.
- Preserve existing route compatibility through aliases and migration adapters where practical.
- Preserve existing events, providers, policies, migrations and service contracts unless evidence proves they must change.
- Treat the current 14 shell templates as migration inputs, not as 14 future top-level applications.
- Distinguish platform applications from vertical templates and internal business modules.

## Canonical Ownership Target

### Titan Zero

Owns global settings, administration, governance, executive views, approvals and cross-platform reporting.

### Titan Go

Owns dispatch presentation, worker assignment views, routes, mobile job execution, forms, evidence, attendance, offline job packs and completion workflows.

### Titan Launch

Owns business creation, vertical generation, launch planning and growth workflows. Titan Sprout becomes an internal engine and is not registered as a standalone application.

### Titan Desk

Owns business-facing calls, SMS, email, chat, reception, intake, lead qualification, booking requests and quote requests. It may hold communication-session identities, but creates or links authoritative business records through WorkCore.

### Titan Hub

Owns customer-facing self-service only: bookings, quotes, invoices, payments, documents, service history and relationship actions. It does not duplicate WorkCore records.

## Legacy Application Mapping

| Current shell/application | Target disposition |
|---|---|
| Titan Zero | Remains Titan Zero |
| Titan Go | Remains Titan Go |
| Titan Dispatch | Merge into Titan Go |
| Titan Front Desk | Rename and expand into Titan Desk |
| Titan Hub | Remains Titan Hub |
| Titan Sprout | Move inside Titan Launch as an engine |
| Titan Money | Become finance workflows/views, primarily surfaced through Titan Zero and Titan Hub |
| Titan Teams | Become workforce workflows/views in Titan Zero and Titan Go |
| Titan Analytics | Become application-owned reporting plus Titan Zero cross-platform reporting |
| Titan Marketing | Become Titan Launch growth workflows |
| Titan Social | Become Titan Launch growth/communication workflows |
| Titan Locker | Become inventory/assets workflows in Titan Zero and Titan Go |
| Titan Office | Reclassify into administration/document workflows |
| Titan Quality | Become compliance/quality workflows in Titan Zero and Titan Go |

## Implementation Sequence

### Phase 1 — Audit and Backlog

- Confirm authoritative chatbot extension tree.
- Record duplicate systems and architecture drift.
- Inventory current registries, schemas, navigation, AI context, WorkCore bridge, PWA, offline sync, routes and permissions.
- Create implementation backlog in `.titan/todo/issues`.

### Phase 2 — Authority and Compatibility Boundary

- Confirm `app/Extensions/Chatbot` as authoritative or document another decision.
- Stop duplicate provider, migration, route and namespace discovery from `TitanZeroChatbot`.
- Introduce compatibility aliases only where required.
- Add tests proving one authoritative extension boots.

### Phase 3 — Canonical Application Registry

- Separate platform application definitions from vertical template schemas.
- Register exactly Titan Zero, Titan Go, Titan Launch, Titan Desk and Titan Hub.
- Preserve old slugs through explicit migration mappings.
- Add ownership, navigation, reports, settings, permissions, context provider and workflow metadata.

### Phase 4 — Context Envelope

Create one canonical context object propagated through chatbot requests, Titan AI, WorkCore calls, offline operations and the Interaction Engine containing:

- current application;
- current user;
- current role;
- current WorkCore context;
- current workflow;
- current conversation context;
- current AI context;
- current offline state;
- current permissions.

### Phase 5 — AI and Interaction Routing

- Make intent selection application-aware.
- Add application-specific prompt defaults and tool boundaries.
- Adapt the chatbot runtime to the canonical Interaction Engine contract instead of introducing another executor.
- Preserve governance, approval and audit behaviour.

### Phase 6 — Navigation and UX

- Replace the 14-app selector with five platform applications.
- Preserve distinct application workflows rather than cloning one shell.
- Move global settings to Titan Zero and keep application-specific settings local.
- Make reports application-owned, with Titan Zero limited to executive and cross-platform reporting.

### Phase 7 — WorkCore and Offline Migration

- Consolidate old per-app WorkCore manifests into five application mappings.
- Define communication identity versus authoritative WorkCore customer boundaries.
- Add application/workflow context to offline records, outbox operations and sync status.
- Preserve the current sync protocol, conflict records, idempotency and device security.

### Phase 8 — Routes, PWA and Compatibility

- Preserve existing route contracts where possible.
- Add redirects or aliases for legacy app slugs.
- Make the single chatbot PWA application-aware through deep links and retained active context.
- Avoid five duplicated service workers or five duplicated offline databases.

### Phase 9 — Verification

- Run route and provider collision checks.
- Run migration and rollback checks.
- Run PHP, JavaScript and JSON validation.
- Run application registry, context propagation, permission, WorkCore boundary, PWA and offline tests.
- Update backlog statuses and migration documentation.

## Quality Gates

Implementation is not complete until:

- only one authoritative chatbot extension is bootable;
- exactly five platform applications are registered;
- no operational record is duplicated outside WorkCore;
- active application and permission context reaches every AI/business action;
- legacy slugs are deliberately mapped or retired;
- offline actions retain application/workflow context;
- route, provider and migration duplication checks pass;
- documentation matches the implemented code.
