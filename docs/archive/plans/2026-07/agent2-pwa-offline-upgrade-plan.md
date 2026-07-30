> [!IMPORTANT]
> **Historical record — not current implementation guidance.** This document is retained for provenance because it describes an earlier branch, source version, import, or completed upgrade pass. Use `docs/README.md` and `docs/plans/CURRENT_UPGRADE_PLAN.md` for current guidance.

# Agent 2 PWA, Interaction Engine and Five-Tier AI Offline Integration Plan

> **Branch:** `agent2/pwa-offline-integration`  
> **Base:** `main`  
> **Owner:** Agent 2 — PWA and device intelligence integration  
> **Status:** Prepared for implementation  
> **Required execution method:** test-first, cumulative passes, evidence before completion claims

## Goal

Transform the existing `app/Extensions/Chatbot` extension into the single Titan Zero device-first operating interface. It must execute WorkCore capabilities locally, run signed Interaction Engine bundles offline, route deterministic five-tier AI locally, preserve encrypted work through restart and update, and synchronize only when connectivity is available.

## Confirmed repository starting point

- The canonical PWA extension is `app/Extensions/Chatbot`.
- Its extension manifest identifies version `6.9.0-unified-ai-shell`.
- The repository contains the MagicAI host, WorkCore domain code, Titan Zero extensions, extension SDK material and imported extension packages.
- `main` remains the stable integration base.
- This branch must not introduce another chatbot host, Meetup host, or parallel WorkCore implementation.

## Architecture

```text
app/Extensions/Chatbot
├── Application shell and role-aware navigation
├── Local WorkCore client adapter
├── Interaction Engine device adapter
├── Five-tier AI device adapter
├── Generative UI renderer
├── Voice and device-tool adapters
├── Search and knowledge UI
├── Sync and conflict UX
├── Vault and privacy UX
└── Service-worker lifecycle

packages
├── workcore-contracts          # Agent 1 source of truth
├── workcore-device             # Agent 1 governed local runtime
├── interaction-device          # Agent 2 signed offline workflow runtime
├── titan-ai-device             # Agent 2 five-tier deterministic/local AI runtime
├── titan-ui-schema             # Agent 2 versioned generative UI contracts
├── titan-role-packs            # Agent 2 signed role/domain manifests
└── titan-extension-runtime     # Agent 2 PWA extension sandbox and loader
```

## Non-negotiable boundaries

1. PWA calls local WorkCore first.
2. Interaction Engine and AI use `workCore.capabilities.invoke(context, capability, input)`.
3. No direct SQL, Eloquent, IndexedDB business-entity mutation or fabricated success.
4. Agent 1 owns WorkCore schemas, repositories, commands, money rules, state machines, event/audit stores and sync-server endpoints.
5. Agent 2 owns PWA integration, interaction execution, device AI, UI, voice, service worker, device tools, privacy UX and conflict UX.
6. Core operations must work without an LLM, network, WebGPU or provider key.
7. Service-worker caches must never contain secrets, operational databases, audit keys or provider keys.
8. Unsynchronized work must never be deleted automatically.
9. Updates must preserve SQLite/OPFS, IndexedDB fallback metadata, attachment vaults and interaction sessions.
10. Role-based visibility supplements but never replaces permission checks.

## Definition of a governed result

All Interaction Engine capabilities, Tier 3 agents and Tier 4 tools must return one of:

```ts
type GovernedStatus =
  | 'completed'
  | 'validation_failed'
  | 'permission_denied'
  | 'approval_required'
  | 'online_required'
  | 'deferred'
  | 'conflict'
  | 'unavailable';
```

No handler may translate `deferred`, `online_required`, `conflict` or `unavailable` into success.

---

# Multi-pass implementation sequence

## Pass 1 — Repository and PWA reconciliation audit

**Objective:** Establish the real baseline before modifying runtime behaviour.

### Actions

- Inventory `app/Extensions/Chatbot` shell, routes, menus, frontend build system, manifest and service worker.
- Locate every IndexedDB database, local repository, vault, outbox, sync engine, search implementation, AI runtime and WorkCore client.
- Locate Interaction Engine code inside the host, extension packages and imported archives.
- Locate five-tier AI managers, specialists, agents, governance and tool registries.
- Identify duplicate local WorkCore implementations and direct HTTP-first operational flows.
- Classify each subsystem as operational, partial, unwired, duplicate, drifted, placeholder, broken, dormant, missing dependency or superseded by Agent 1.
- Produce `docs/agent2/pass01-pwa-reconciliation-audit.md`.
- Produce `docs/agent2/pass01-file-ownership-map.json`.
- Produce `docs/agent2/pass01-agent1-contract-gaps.md`.

### Test gate

- Existing frontend build and test commands are recorded and executed.
- Existing PHP tests relevant to Chatbot and WorkCore are recorded and executed.
- Every claimed operational subsystem has an actual entrypoint and a reachable execution path.

### Exit condition

No implementation begins until duplicate WorkCore ownership and direct operational-write paths are identified.

---

## Pass 2 — Workspace, dependency and contract convergence

**Objective:** Make Agent 1 packages the only device-side WorkCore authority consumed by the PWA.

### Target paths

```text
packages/workcore-contracts
packages/workcore-device
app/Extensions/Chatbot/resources/js/workcore
app/Extensions/Chatbot/tests/device/workcore
```

### Actions

- Add workspace/package references without copying Agent 1 internals into Chatbot.
- Create a single `DeviceWorkCoreClient` adapter that exposes commands, queries and capabilities.
- Create browser bootstrap for Agent 1 SQLite/WASM + OPFS persistence bundle.
- Remove or quarantine parallel PWA-local business repositories.
- Replace HTTP-first operational calls with local commands or queries.
- Retain HTTP only in synchronization and external provider adapters.
- Add typed mappings between PWA session context and Agent 1 `OperationContext`.
- Record every missing capability in `docs/agent2/agent1-dependency-report.md`.

### Required tests

- PWA boot opens the Agent 1 persistence bundle once.
- Commands receive company, membership, device and lease context.
- Permission denial remains denial in the UI.
- Expired leases block local writes.
- Direct operational HTTP calls fail an architecture test.
- Direct WorkCore table imports fail an architecture test.

### Exit condition

The PWA has one local WorkCore entrypoint and no competing local business authority.

---

## Pass 3 — Offline application boot, vault and recovery shell

**Objective:** Start, unlock and recover the PWA without network or cloud services.

### Target paths

```text
app/Extensions/Chatbot/resources/js/app/bootstrap
app/Extensions/Chatbot/resources/js/vault
app/Extensions/Chatbot/resources/js/device
app/Extensions/Chatbot/resources/js/recovery
app/Extensions/Chatbot/tests/device/bootstrap
```

### Actions

- Implement staged boot: shell → vault → device identity → lease → WorkCore database → role pack → domain packs → interaction registry → AI registry.
- Add PIN/biometric capability detection with typed fallback.
- Add locked, unlocking, ready, recovery-required, revoked-device and expired-lease states.
- Ensure boot never depends on MagicAI, cloud AI or network availability.
- Preserve pending local work when boot or migration fails.
- Add export-before-reset and safe local-wipe paths.

### Required tests

- Airplane-mode cold start.
- Restart with pending outbox operations.
- Wrong PIN and lockout handling.
- Revoked-device boot denial.
- Expired-lease read-only mode.
- Recovery path does not delete unsynchronized work.

---

## Pass 4 — Signed role packs and capability-aware navigation

**Objective:** Load only the UI and domains appropriate to the actor and device.

### Target paths

```text
packages/titan-role-packs
app/Extensions/Chatbot/resources/js/roles
app/Extensions/Chatbot/resources/js/navigation
app/Extensions/Chatbot/tests/device/roles
```

### Role packs

- Owner
- Manager
- Dispatcher
- Field worker / cleaner
- Customer
- Kiosk

### Actions

- Define versioned signed role manifests.
- Define enabled-domain manifests and offline availability flags.
- Generate menus from signed role manifest, permissions, enabled domains, capabilities and offline availability.
- Lazy-load role-specific routes and domain packs.
- Add route guards that re-check effective permissions.
- Prevent cleaner devices from loading owner-only domain data.

### Required tests

- Hidden route direct invocation is denied.
- Modified or unsigned role manifest is rejected.
- Permission changes remove capabilities without requiring UI reinstall.
- Cleaner pack does not load owner finance/report bundles.
- Offline-unavailable screens render explicit unavailable states.

---

## Pass 5 — Interaction Engine device package foundation

**Objective:** Create the standalone signed, resumable offline workflow runtime.

### Target package

```text
packages/interaction-device/
├── definitions
├── compiler
├── registry
├── runtime
├── state
├── navigation
├── validation
├── context
├── resolution
├── capabilities
├── rendering
├── persistence
├── events
└── testing
```

### Actions

- Define compiled interaction bundle schema and signature envelope.
- Implement registry and version selection.
- Implement deterministic state transitions and conditional navigation.
- Implement fields, repeatable sections, timers, validation and approvals.
- Persist drafts and resumable sessions through the Agent 1-compatible device persistence boundary.
- Reject unsigned, expired, incompatible or remotely disabled bundles.
- Emit immutable interaction events without writing operational records.

### Required tests

- Signed bundle loads offline.
- Tampered bundle is rejected.
- Conditional branches are deterministic.
- Repeatable sections survive restart.
- App update resumes the compatible interaction version.
- Incompatible interaction version opens a safe recovery path.

---

## Pass 6 — Interaction capability bridge and device tools

**Objective:** Execute WorkCore actions and hardware tools through explicit registries.

### Target paths

```text
packages/interaction-device/capabilities
app/Extensions/Chatbot/resources/js/device-tools
app/Extensions/Chatbot/resources/js/attachments
app/Extensions/Chatbot/tests/device/capabilities
```

### Actions

- Implement the WorkCore capability bridge.
- Add camera, file picker, signature, QR/barcode, geolocation, local notification, document reader and local calendar-draft tools.
- Encrypt attachments before persistent storage.
- Return typed unavailable or deferred states when a device/provider is absent.
- Require confirmation and approval for destructive or high-risk capabilities.

### Required tests

- Every interaction capability reaches WorkCore through the capability registry.
- Missing camera returns `unavailable`.
- Online-only provider returns `online_required` or `deferred`.
- Permission denial remains visible.
- Attachment encryption round-trip succeeds.
- Attachment access across companies is denied.

---

## Pass 7 — Five-tier deterministic AI device runtime

**Objective:** Route, plan and execute core work without a language model.

### Target package

```text
packages/titan-ai-device/
├── tier0-router
├── tier1-managers
├── tier2-specialists
├── tier3-agents
├── tier4-tools
├── context
├── governance
├── explainability
├── providers
└── testing
```

### Actions

- Implement Tier 0 deterministic classification, emergency precedence, entity extraction and active-screen/interaction awareness.
- Implement managers for operations, customer, workforce, finance, compliance, knowledge and device.
- Implement deterministic specialists for scheduling, dispatch, job intake, customer service, quoting, invoicing, premises, checklists, incidents, inventory, documents, knowledge and offline help.
- Implement governed Tier 3 action agents that invoke WorkCore capabilities.
- Implement Tier 4 typed tool availability.
- Add explainability trace for routing, confidence, risk, authority and execution result.

### Required tests

- Core intents route with no model installed.
- Emergency intent takes precedence.
- Low confidence abstains or requests clarification.
- High confidence with insufficient authority returns `approval_required` or `permission_denied`.
- Tier 3 agents cannot import database modules.
- No agent fabricates success for deferred or rejected actions.

---

## Pass 8 — Optional local intelligence and explicit cloud fallback

**Objective:** Improve language handling without making models operational authorities.

### Interface

```ts
interface LocalIntelligenceProvider {
  availability(): Promise<Availability>;
  classify(input: ClassificationInput): Promise<ClassificationResult>;
  extract(input: ExtractionInput): Promise<ExtractionResult>;
  summarize(input: SummaryInput): Promise<SummaryResult>;
  generate(input: GenerationInput): Promise<GenerationResult>;
}
```

### Actions

- Add rules-only provider as mandatory baseline.
- Add WebGPU, WebAssembly and Capacitor provider adapters behind availability checks.
- Run model work in workers.
- Scope model context to replicated records and effective permissions.
- Add visible cloud-sharing consent and Titan Vault key access.
- Make cloud fallback explicit in the UI and audit trace.

### Required tests

- No-WebGPU mode remains fully operational.
- No-model mode completes core workflows.
- Cloud provider denial leaves local workflow usable.
- Provider keys never enter service-worker or application-shell caches.
- Cross-company records never enter model prompts.

---

## Pass 9 — Versioned generative UI renderer

**Objective:** Render real local WorkCore and Interaction Engine results using a validated schema.

### Target package and adapters

```text
packages/titan-ui-schema
app/Extensions/Chatbot/resources/js/generative-ui
app/Extensions/Chatbot/tests/device/generative-ui
```

### Components

- Text and notices
- Cards and tables
- Forms and checklists
- Timelines and schedules
- Customer, premises and job cards
- Quote, invoice and payment cards
- Evidence galleries
- Signature and approval cards
- Conflict, sync, offline and device-warning cards

### Required tests

- Unknown schema versions fail safely.
- Permission-sensitive fields are removed before render.
- No placeholder operational records are fabricated.
- Components meet keyboard, focus, label and contrast requirements.
- Mobile, tablet and desktop layouts remain responsive.

---

## Pass 10 — Local search, knowledge and document retrieval

**Objective:** Make records, SOPs and downloaded knowledge searchable offline.

### Target paths

```text
app/Extensions/Chatbot/resources/js/search
app/Extensions/Chatbot/resources/js/knowledge
app/Extensions/Chatbot/resources/js/workers/search
app/Extensions/Chatbot/tests/device/search
```

### Actions

- Build incremental role-scoped indexes from WorkCore queries and published knowledge packs.
- Add lexical baseline search independent of embeddings.
- Add optional local embeddings without replacing lexical fallback.
- Include permission and replication-scope filtering before ranking.
- Provide citations to local source records and documents.
- Keep indexing off the main thread.

### Required tests

- Airplane-mode record and SOP search.
- Deleted/tombstoned records disappear from active results.
- Cross-company records are excluded.
- Search index rebuild survives interruption.
- Local citations resolve to authorized records.

---

## Pass 11 — Unified text and voice pipeline

**Objective:** Use one governed path for typed and spoken requests.

### Target paths

```text
app/Extensions/Chatbot/resources/js/voice
app/Extensions/Chatbot/resources/js/input
app/Extensions/Chatbot/tests/device/voice
```

### Actions

- Implement push-to-talk with visible transcript.
- Support device speech API, downloaded local speech model and typed fallback.
- Allow edit-before-submit.
- Route transcript through Tier 0, managers, specialists and Interaction Engine/Tier 3.
- Require confirmation before destructive actions.
- Add optional local TTS.

### Required tests

- Voice and text produce the same governed command path.
- Transcript correction changes the submitted intent.
- Cloud STT absence does not block core work.
- Destructive command requires explicit confirmation.

---

## Pass 12 — Service worker and safe update lifecycle

**Objective:** Cache the shell safely while preserving operational state.

### Target paths

```text
app/Extensions/Chatbot/public/service-worker.js
app/Extensions/Chatbot/resources/js/service-worker
app/Extensions/Chatbot/tests/pwa/service-worker
```

### Update states

```text
downloaded
waiting
database-compatible
migration-required
ready-to-activate
activated
rollback-available
```

### Actions

- Restrict caching to shell and safe static assets.
- Add offline navigation fallback.
- Add runtime messaging and background sync triggers.
- Gate activation on database and interaction compatibility.
- Preserve SQLite/OPFS, IndexedDB metadata, attachments, capability manifests and unsynchronized operations.
- Add rollback metadata for failed application updates.

### Required tests

- Cache inspection contains no secrets or operational databases.
- Update with pending work preserves data.
- Migration-required update does not activate early.
- Failed update returns to previous shell.
- Offline navigation works after restart.

---

## Pass 13 — Sync status, conflict centre and approval UX

**Objective:** Make synchronization truth visible and conflicts recoverable.

### Target paths

```text
app/Extensions/Chatbot/resources/js/sync
app/Extensions/Chatbot/resources/js/conflicts
app/Extensions/Chatbot/resources/js/approvals
app/Extensions/Chatbot/tests/device/sync
```

### Required states

- Offline
- Saved on this device
- Waiting to sync
- Synchronizing
- Accepted by WorkCore cloud
- Conflict detected
- Approval required
- Permission expired
- Device revoked
- Schema upgrade required
- Sync failed
- Retry scheduled

### Actions

- Consume Agent 1 outbox, inbox, version and conflict APIs.
- Build conflict comparison with local/cloud values, changed fields, timeline and audit explanation.
- Support recommended and manual resolution through WorkCore capabilities.
- Never label queued remote delivery as complete.

### Required tests

- Sync acceptance, rejection and conflict states are distinct.
- Conflict survives restart.
- Manual resolution is permission checked.
- Device revocation blocks pending writes while preserving evidence for recovery.

---

## Pass 14 — Privacy, device management and extension runtime

**Objective:** Give users control over local data and safely load signed PWA extensions.

### Target paths

```text
app/Extensions/Chatbot/resources/js/settings/privacy
app/Extensions/Chatbot/resources/js/settings/device
packages/titan-extension-runtime
app/Extensions/Chatbot/tests/device/extensions
```

### Actions

- Add vault lock, storage summary, downloaded packs, attachment storage, key recovery, last sync and device ID screens.
- Add remote revoke status, export, local wipe, privacy mode and cloud-AI sharing controls.
- Validate extension ID, version, signature, domains, permissions, capabilities and declared access.
- Sandbox network, data, provider-key, device and background access.
- Support remote disable without deleting unsynchronized WorkCore data.

### Required tests

- Unsigned extension is rejected.
- Undeclared domain or device access is denied.
- Extension cannot read provider keys directly.
- Local wipe deletes device material but does not issue cloud deletions.
- Unsafe export is blocked.

---

## Pass 15 — Role-specific offline workflow completion

**Objective:** Prove real offline operation across all required roles.

### Cleaner workflow

- Open assigned job offline.
- Read premises instructions.
- Start job.
- Complete checklist and record time.
- Add notes, photos and signature.
- Report incident.
- Complete job.
- Restart the app and confirm persistence.
- Synchronize later.

### Dispatcher workflow

- View cached schedule.
- Assign cached staff.
- Move jobs.
- Detect local schedule conflict.
- Queue changes and resolve cloud conflict.

### Manager workflow

- Create customer, premises and job offline.
- Assign worker.
- Review completion and approve action.
- Search local operations.

### Owner workflow

- Draft quote and invoice.
- Review outstanding invoices.
- Approve high-risk action.
- View local summaries and synchronize changes.

### Customer workflow

- View and accept/reject quote.
- View job and sign completion.
- View invoice and record payment intent.
- Read documents.

### Required tests

Each workflow must run in airplane mode, survive restart, emit WorkCore events/audit/outbox operations, and later handle sync acceptance or rejection.

---

## Pass 16 — Performance, accessibility and mobile hardening

**Objective:** Make the device node viable on lower-powered phones and tablets.

### Actions

- Move database, search and model work into workers.
- Lazy-load role/domain packs and models.
- Add incremental indexing, attachment thumbnails and memory-pressure handling.
- Add battery-aware background policies and low-storage mode.
- Audit keyboard, touch target, screen-reader, focus and reduced-motion behaviour.
- Test graceful no-WebGPU and no-local-model modes.

### Required reports

- `docs/agent2/performance-report.md`
- `docs/agent2/accessibility-report.md`
- `docs/agent2/mobile-device-test-matrix.md`

---

## Pass 17 — Security and architecture enforcement

**Objective:** Prove that UI, Interaction Engine, AI and extensions cannot bypass WorkCore governance.

### Required security tests

- Cross-company query attempt.
- Role bypass and hidden-button direct invocation.
- Expired lease and revoked device.
- Attachment cross-tenant access.
- Extension permission violation.
- Provider-key cache inspection.
- Plaintext local-database inspection.
- Unsafe export.
- Direct SQL or repository import from Interaction Engine/AI/PWA.
- Fabricated-success detection.

### Required reports

- `docs/agent2/security-report.md`
- `docs/agent2/workcore-contract-usage-report.md`
- `docs/agent2/architecture-boundary-report.md`

---

## Pass 18 — Cloud handoff, cumulative verification and release candidate

**Objective:** Freeze a cumulative staging candidate with honest limitations.

### Cloud handoff documentation

Document required MagicAI responsibilities:

- Device registration and revocation
- Signed capability publication
- Offline lease issuance
- Sync endpoints
- Heavy AI provider routing
- Cloud Interaction Engine compilation
- Extension signing
- Knowledge-pack publication
- Cloud notifications
- Subscription enforcement
- Central reporting

### Required deliverables

1. Repaired cumulative PWA archive
2. Interaction Engine Device Runtime
3. Five-tier AI Device Runtime
4. Role-pack system
5. Generative UI renderer
6. Local voice pipeline
7. WorkCore Device Runtime integration
8. Local capability registry
9. Device-tool registry
10. Local search and knowledge integration
11. Conflict centre and sync UI
12. Privacy and device settings
13. Extension loader
14. Safe service-worker update system
15. Offline workflow tests
16. AI governance tests
17. Interaction Engine tests
18. Performance, accessibility and security reports
19. Role/domain matrix
20. Capability matrix
21. Offline/online feature matrix
22. WorkCore contract usage report
23. Agent 1 dependency report
24. MagicAI cloud handoff report
25. Change manifest
26. Remaining-risk report
27. Complete cumulative ZIP or release artifact

### Final verification gates

- Offline cold start passes.
- Signed role packs load offline.
- WorkCore queries and commands execute locally.
- Interaction workflows execute and resume offline.
- Five-tier routing and governed actions work without an LLM.
- Cleaner workflow completes in airplane mode.
- Customer, job, quote and invoice drafts persist locally.
- Photos and signatures remain encrypted.
- Unsynchronized work survives restart and service-worker update.
- Conflicts are visible and recoverable.
- Revoked devices and expired leases are blocked.
- Role restrictions govern UI, capability use and replicated data.
- Cache inspection finds no protected secrets or business database.
- Real mobile and tablet test evidence exists.
- No unresolved critical or high-severity defects remain.

## Required final status language

The branch may finish with exactly one of:

- `PWA device operating node production ready`
- `PWA device operating node staging ready with documented limitations`
- `PWA internally functional but incomplete`
- `PWA structurally integrated but offline workflows incomplete`
- `Unsafe for deployment`

Linting, file counts, placeholders or mocked tests are never sufficient evidence for production readiness.

---

# Agent 1 dependency register

Agent 2 must request, not bypass, missing WorkCore capabilities. At minimum the local runtime must expose governed operations for:

```text
customers.search
customers.create
premises.read
premises.create
jobs.create
jobs.start
jobs.complete
jobs.assign
tasks.complete
checklists.update
time.record
evidence.attach
signatures.attach
quotes.draft
quotes.accept
quotes.reject
invoices.draft
payment_intents.record
incidents.create
conflicts.resolve
approvals.request
approvals.resolve
```

Each missing operation is recorded with required input DTO, result DTO, permissions, approval rules, events and offline eligibility in `docs/agent2/agent1-dependency-report.md`.

# Branch working rules

- Keep every pass cumulative.
- Commit independently verifiable changes.
- Write failing tests before behaviour changes.
- Do not modify `main` directly.
- Do not open a non-draft PR until all relevant pass gates are green.
- Rebase or merge `main` deliberately and rerun boundary tests after Agent 1 contract updates.
- Never remove apparently unused code until routes, dynamic loading, service-container bindings, events, configuration and runtime discovery are traced.
- Record additions, modifications, moves and removals in a machine-readable change manifest.
- Preserve source attribution and licensing metadata from imported packages.
