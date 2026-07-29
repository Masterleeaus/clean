# Titan Zero Multi-Step Upgrade Plan

**Repository:** `Masterleeaus/clean`  
**Working branch:** `agent/v070-upgrade-base`  
**Prepared:** 2026-07-29  
**Baseline:** Titan Zero + Meetup + WorkCore integrated source v0.7.0

## 1. Purpose

This branch becomes the controlled engineering base for the next Titan Zero development cycle. It imports the verified v0.7.0 source checkpoint, preserves its architectural authority boundaries, and upgrades the system through small, independently testable phases.

The project is not to be replaced with another application or a parallel framework. New capabilities must extend the existing Laravel application and canonical bounded domains.

## 2. Canonical authority model

The following boundaries are non-negotiable:

- **Meetup Chat** owns messaging, channels, presence, voice, video, notifications, collaboration and the user-facing communication shell.
- **Titan Zero** owns conversation state, intent recognition, planning, orchestration, memory, AI delegation and governed tool selection.
- **WorkCore** owns all structured operational business records, validation, transactions, permissions, audit history and domain events.
- **Titan Money / ZeroPay** owns payment initiation, provider observations, matching, settlement and reconciliation; it does not own invoice truth.
- **Titan Vault** owns credentials, secrets and protected configuration.
- **Titan Rewind** will own temporal correction, replay and rollback when activated.
- **Titan Intelligence** owns agents, skills, provider routing, connectors, memory and voice runtime definitions.
- **Titan Creative & Marketing** owns creative/campaign lifecycle records while WorkCore CRM remains customer and lead authority.

AI code must never write operational tables directly. Every operational mutation must pass through a registered WorkCore action, permission enforcement, company context, confirmation rules, idempotency, audit and domain-event emission.

## 3. Imported baseline

The branch imports the verified `Titan-Zero-Meetup-WorkCore-Integrated-v0.7.0.zip` source archive.

### Baseline capabilities

- Laravel 12 Meetup communication application
- Company, membership, role and active-company tenancy
- Titan Vault, immutable audit and capability registry
- Canonical WorkCore runtime and vertical manifests
- CRM, premises, jobs, scheduling, workforce, inventory and supply
- Documents, evidence, assurance, inspections, risk and compliance
- Property, agreements, occupancy and accommodation execution
- Finance, receivables, trust accounting and ZeroPay reconciliation
- Titan Maps Intelligence
- Titan Intelligence runtime: workspace, memory, skills, agents, connectors, providers and voice
- Titan Creative and Marketing lifecycle
- Operations command centre
- Docker, CI and connected-verification harness

### Verified v0.7.0 evidence

- 4,901 structural and security checks
- 71 deployment-contract checks
- 990 PHP syntax checks
- 1,182 internal declarations scanned
- 679 internal imports checked
- zero unresolved internal imports
- 48 migration files, 267 tables and 815 foreign references checked
- 1,128 packaged files

The original archive checksum is:

`4a64ad4b2d0b141aeb3dd91fe19c618c0caeb2fedcea7820ced8694ea62bf6ed`

## 4. Source import procedure

The source bundle is hosted through the controlled MiniUp transfer site:

`https://titan-zero-v070-source-transfer.miniup.app`

The branch workflow `.github/workflows/import-v070-source.yml` must:

1. Download the exact v0.7.0 archive.
2. Verify its SHA-256 checksum before extraction.
3. Validate ZIP integrity.
4. Reject `.env`, `vendor`, `node_modules`, private keys and secret-bearing files.
5. Strip the archive's release wrapper directory.
6. Preserve this plan, `AGENTS.md`, the import manifest and the import workflow.
7. Commit the extracted source to this branch only.

## 5. Upgrade phases

### Phase 0 — Repository preparation and provenance

**Goal:** establish a trustworthy Git history and reproducible source baseline.

Deliverables:

- Import v0.7.0 through the checksum-verified workflow.
- Record source archive, checksum, MiniUp transfer URL and prior build metrics.
- Preserve donor archives outside runtime autoload.
- Add branch protections and CODEOWNERS after the first source commit.
- Confirm no generated secrets, `vendor`, `node_modules` or runtime caches entered Git.

Acceptance:

- Source tree matches the imported archive after excluding transfer-only files.
- Import workflow passes.
- Git status is clean after the import commit.

### Phase 1 — Connected Laravel acceptance

**Goal:** replace static confidence with a real booted Laravel build.

Steps:

1. Run `bash bin/titan-preflight`.
2. Install Composer dependencies with PHP 8.4 and required extensions.
3. Run Laravel package discovery and `artisan about`.
4. Generate and commit `package-lock.json` using Node 22.
5. Build Vite production assets.
6. Run SQLite migrations, rollback and re-migration.
7. Run PostgreSQL migrations, rollback and re-migration.
8. Run Pest/PHPUnit and Eloquent company-isolation tests.
9. Run queue-worker, scheduler and route smoke tests.
10. Run `bash bin/titan-verify-connected` as the final connected gate.

Acceptance:

- Laravel boots without unresolved providers or routes.
- Both database engines complete migration cycles.
- Framework tests and Vite build pass.
- No tenant-crossing query is observed.

### Phase 2 — WorkCore canonical completeness

**Goal:** audit the implemented WorkCore surface against the authoritative architecture and close real domain gaps without importing duplicate donor authorities.

Workstreams:

- Produce a machine-readable canonical domain catalogue.
- Map every model, aggregate, service, action, read, event, permission and migration to an owning domain.
- Identify unimplemented entities from the planned 250–350 entity catalogue.
- Complete branch/team/workspace scoping where a domain requires it.
- Standardise public IDs, lifecycle states, validation errors and event envelopes.
- Complete WorkCore REST/WebSocket contracts and API versioning.
- Add cross-domain contract tests and transaction-boundary tests.

Acceptance:

- Every operational table has exactly one owner.
- Every operational write is reachable only through WorkCore services/actions.
- Every significant mutation emits an immutable event and audit record.

### Phase 3 — Device-first PWA and offline parity

**Goal:** make the chatbot and operating surfaces genuinely usable during network loss.

Source reference:

- `Titan-Zero-Chatbot-PWA-PASS12-HOST-BOUNDARY-FIXED(1).zip`

Workstreams:

- Reconcile the latest PWA contracts against the Laravel host.
- Preserve Agent 1's IndexedDB, vault, outbox, retry, conflict and device identity foundations.
- Implement local conversations, drafts, messages, search and knowledge.
- Add local WorkCore read models and permitted offline actions.
- Add encrypted device vault and BYO provider-key experience.
- Complete bidirectional sync, tombstones, version vectors and conflict resolution.
- Ensure service workers never cache credentials or sensitive provider responses.
- Add install, update, migration and recovery UX.

Acceptance:

- Core chat and selected WorkCore workflows run offline.
- Unsynchronised records are never deleted automatically.
- Reconnection synchronises deterministically with visible conflict handling.

### Phase 4 — Interaction Engine and five-tier AI wiring

**Goal:** turn the current governed capability registries into the complete Titan Zero interaction and delegation runtime.

Hierarchy:

- Titan Zero — sole user-facing orchestrator
- Titan Uno — managers
- Titan Duo — specialists and assistants
- Titan Trio — action agents
- Titan Quattro — deterministic tools

Workstreams:

- Integrate the cumulative Interaction Engine contracts as a first-party bounded system.
- Implement context resolution, middleware pipeline, authority, confidence and approval layers.
- Register all agents, skills, tools and WorkCore actions through one capability graph.
- Implement Green/Amber/Red confidence and confirmation behaviour.
- Add durable agent runs, checkpoints, retries and compensation.
- Prevent direct model/provider calls outside provider routing.
- Connect text and voice to the same execution pipeline.

Acceptance:

- No orphaned manager, assistant, agent or tool remains unreachable.
- Every AI operation records actor, agent, company, conversation, device, reason and result.
- High-risk operations require explicit user confirmation.

### Phase 5 — Product shell and responsive operating experience

**Goal:** deliver one coherent Titan Zero application rather than disconnected admin and chatbot screens.

Workstreams:

- Preserve the persistent chat input and template-aware operational workspace.
- Keep Settings behind the top-right gear rather than a primary menu item.
- Use the primary navigation for high-value operational domains.
- Add a hamburger/overflow menu for secondary links.
- Complete responsive mobile, tablet and desktop layouts.
- Connect Operations panels to real WorkCore read models.
- Add accessible loading, empty, offline, conflict and error states.
- Keep platform administration separate from normal company operations.

Acceptance:

- Mobile, tablet and desktop share one information architecture.
- No duplicate assistant/chat menu item exists while the persistent chat bar is present.
- All visible controls have real routes or actions.

### Phase 6 — Provider, connector and channel adapters

**Goal:** activate external integrations only through governed, provider-neutral boundaries.

Priority adapters:

- AI providers through Titan provider routing
- Gmail and Outlook
- Google Drive and Calendar
- Slack, WhatsApp, Telegram, Messenger and Instagram
- Twilio voice/SMS
- PayID/bank observation and licensed card-payment providers
- Maps/Places providers
- Private object storage

Rules:

- Credentials are stored only through Titan Vault references.
- Webhooks are signed, replay-protected and company-scoped.
- Provider events are observations, not operational authority.
- Every adapter supports rate limits, retries, idempotency and safe error translation.

Acceptance:

- Each adapter has sandbox integration tests and failure-mode tests.
- No provider secret appears in URLs, logs, source or browser bundles.

### Phase 7 — Security, privacy, compliance and Rewind

**Goal:** prepare the system for production assurance and temporal recovery.

Workstreams:

- Tenant-boundary penetration tests.
- Permission and delegated-agent escalation tests.
- Secret scanning and dependency auditing.
- Signed audit-chain verification.
- Data export, retention and deletion controls.
- Australian privacy and financial/trust-accounting review.
- State-specific tenancy, bond and accommodation rule packs.
- Activate Titan Rewind through event replay and compensating actions—not raw database rollback.
- Backup, restore and disaster-recovery drills.

Acceptance:

- Critical security findings are zero.
- Every protected mutation can be explained from audit and event history.
- Rewind cannot bypass current permissions or erase history.

### Phase 8 — Release engineering and deployment

**Goal:** produce a repeatable production release rather than another untracked ZIP.

Workstreams:

- Versioned release branches and tags.
- Required GitHub Actions checks.
- Database backup and migration strategy.
- Deployment manifests for VPS/container hosting.
- Queue, scheduler, broadcasting and object-storage runbooks.
- Observability, alerts and incident procedures.
- Signed build artifacts, SBOM and checksums.
- Staging acceptance followed by production canary deployment.

Acceptance:

- A fresh environment can be built solely from Git history and documented secrets.
- Rollback and restore procedures are proven.
- Release artifacts correspond exactly to a signed Git tag.

## 6. Donor-source handling

The following archives are references, not automatically trusted runtime code:

- Base App System Extensions
- AI System Extensions
- Marketing & Creative Extensions
- Modules for Titan BOS
- Titan Zero Extension SDK v2
- MagicAI + WorkCore merged builds
- Chatbot PWA and mobile-app-builder donors

For every donor:

1. Inventory files and declared capabilities.
2. Identify canonical owner and overlap.
3. Reject duplicate tenancy, users, billing, CRM, conversation, settings and storage authorities.
4. Extract only legally usable, coherent and testable concepts/code.
5. Rebase namespaces into the existing bounded domain.
6. Add failing tests before production integration.
7. Record the decision in a machine-readable reconciliation manifest.
8. Never place donor folders directly into autoload.

## 7. Branch working rules

- Work only on `agent/v070-upgrade-base` or a child feature branch.
- Do not push directly to `main`.
- Use small commits aligned to one acceptance gate.
- Never commit `.env`, credentials, keys, production exports, `vendor`, `node_modules` or writable runtime data.
- Do not remove code solely because static search reports it unused; check routes, bindings, events, dynamic discovery, JavaScript imports and configuration first.
- Distinguish confirmed defects, probable defects, architecture risks, dead code and dormant capabilities.
- Run the nearest tests after every change and the complete connected verifier before a release PR.

## 8. Immediate branch sequence

1. Import and verify v0.7.0 source.
2. Review GitHub Actions result and imported tree.
3. Add `package-lock.json` in a connected Node 22 environment.
4. Run the Phase 1 connected acceptance sequence.
5. Fix only evidence-backed boot, migration, test or build failures.
6. Open a draft PR documenting the baseline and connected results.
7. Begin Phase 2 from the accepted source-import commit.
