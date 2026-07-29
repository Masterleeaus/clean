# Agent 2 Pass 1 — PWA Reconciliation Report

## Scope

This pass reconciles the existing Titan Zero Chatbot PWA before device-runtime implementation. It does not claim that offline workflows are complete merely because files, manifests, queues or UI labels exist.

## Canonical host

`app/Extensions/Chatbot` remains the only PWA host. Agent 2 must not add another chatbot application or restore Meetup as a parallel host.

## Classification

| Area | Classification | Disposition |
|---|---|---|
| Application shell | Operational / partially operational | Retain and extend |
| Titan template schemas | Operational with drift risk | Convert into signed role/domain manifests |
| Navigation and settings shell | Operational / partially permission-aware | Generate from role, domain, permission, capability and offline manifests |
| Generative UI | Partially operational | Retain renderer concepts; enforce versioned validated local-query schemas |
| Online vertical adapters | Operational online | Preserve as cloud/provider adapters only |
| WorkCore HTTP clients | Drifted for ordinary device operations | Route local commands through Agent 1 runtime first |
| IndexedDB foundations | Partially operational / unverified | Keep only fallback metadata or adapters approved by Agent 1 boundaries |
| Device SQLite/OPFS | Missing or not resolved in this branch | Consume Agent 1 package; do not recreate |
| Crypto vault | Partially operational / unverified | Reconcile keys, lifecycle, biometric/PIN unlock and export/wipe behavior |
| Local repositories | Duplicate-risk | Supersede business repositories with Agent 1 runtime APIs |
| Outbox and sync UI | Partially operational | Preserve UX; bind states to canonical sync receipts and conflicts |
| Local search | Partially operational | Rebuild over role-scoped local replicas and knowledge packs |
| Interaction Engine | Imported/dormant/branch-dependent | Build signed compiled TypeScript device runtime |
| Five-tier AI | Substantial structure but governance unproven | Enforce deterministic Tier 0 and capability-only Tier 3 agents |
| Local model runtime | Optional / incomplete | Provider interface with rules-only fallback |
| Service worker | Partially operational | Add safe staged updates and protected-cache tests |
| Attachment capture | Partially operational / unverified encryption | Bind camera, files and signatures to encrypted attachment vault |
| Conflict handling | UI or storage fragments; end-to-end unproven | Build visible conflict centre against Agent 1 conflict contracts |
| Voice | Partial/provider-dependent | Unify text and voice path with typed availability states |
| Notifications | Partial | Keep local notification adapter; remote delivery remains cloud-owned |
| Role-specific screens | Template-driven but not complete role packs | Build signed owner, manager, dispatcher, worker, customer and kiosk packs |
| Extension loading | Large imported estate, governance incomplete | Require signed declarations and constrained capability access |

## Confirmed drift example: Titan Train

The active Titan Train development branch explicitly marks its PWA integration as online-only, routes actions through `/api/v1/titan-train`, excludes IndexedDB/SQLite/outbox/conflict storage and displays a connection-required state offline. That is a valid cloud adapter but does not satisfy Agent 2's device-node requirements.

Agent 2 will therefore:

- preserve server-authoritative Titan Train endpoints as synchronization or cloud handoff surfaces;
- avoid copying Eloquent or server mutation code into the PWA;
- require offline Titan Train behavior to use a future Agent 1 domain contract or remain accurately marked online-required.

## Repository-history risk

Earlier imports and sanitisation used rewritten or orphaned history. Branch and commit comparisons can show only a small delta while the checked tree contains a large imported application. For this project:

1. File-tree presence and content are the source of truth.
2. No Agent 2 workflow may force-push or rewrite history.
3. Updates from shared branches must be normal merges or reviewed rebases.
4. Concurrent branches must be consumed through explicit contracts, manifests or reviewed commits.

## Architecture decision

The target execution path is:

```text
PWA input
  -> deterministic Tier 0 router
  -> manager/specialist or Interaction Engine
  -> governed capability request
  -> Agent 1 WorkCore Device Runtime
  -> signed local result/event/audit/outbox
  -> versioned generative UI
  -> later cloud synchronization
```

The prohibited path is:

```text
PWA input
  -> AI or interaction handler
  -> direct SQL / Eloquent / IndexedDB business mutation / HTTP-first WorkCore command
```

## Pass 2 acceptance criteria

- Canonical Agent 1 package imports are identified.
- A single PWA-facing `DeviceWorkCoreClient` boundary is defined.
- Governed results include `completed`, `validation_failed`, `permission_denied`, `approval_required`, `online_required`, `deferred`, `conflict` and `unavailable`.
- Architecture tests detect direct operational fetches and direct storage mutation in Agent 2 packages.
- Missing capabilities produce dependency records, never fabricated success.
