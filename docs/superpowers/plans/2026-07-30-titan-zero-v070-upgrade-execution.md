# Titan Zero v0.7.0 Upgrade Execution Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Convert the verified Titan Zero v0.7.0 source checkpoint into a connected, governed, offline-capable and release-ready business operating system without introducing duplicate authorities or bypassing WorkCore.

**Architecture:** Keep the Laravel 12 Meetup host as the communication and application shell. WorkCore remains the sole operational-data authority; Titan Zero owns orchestration; the Interaction Engine becomes a first-party bounded runtime under Titan Zero; Titan Intelligence supplies agents, skills, tools, providers, memory and voice; Titan Vault supplies credentials; ZeroPay observes and reconciles payments through WorkCore Finance.

**Tech Stack:** PHP 8.2+, Laravel 12, Sanctum 4, Pest 3, Vite 7, Tailwind CSS 4, SQLite for local/CI verification, PostgreSQL for production-parity verification, Redis-compatible queues/cache where enabled, IndexedDB and Service Worker APIs for the PWA.

## Global Constraints

- Repository: `Masterleeaus/clean`.
- Canonical upgrade base: `agent/v070-upgrade-base`.
- Do not push upgrade implementation directly to `main`.
- WorkCore is the only component permitted to mutate operational records.
- Server-side actor and active-company context override all request-body tenant identifiers.
- Confidence never grants permission or delegated authority.
- Every mutation requires a registered action, permission check, confirmation policy, idempotency, audit record and domain event.
- AI, chatbot, PWA and provider adapters must not call WorkCore Eloquent models directly.
- Credentials are stored only through Titan Vault references.
- Unsynchronised device data and conflict history must never be silently deleted.
- No `.env`, private keys, production exports, `vendor/`, `node_modules/`, writable runtime data or generated caches may enter Git.
- Write a failing behavioural or structural test before each production change.
- Each pass is one independently reviewable PR and must satisfy its exit gate before the next pass starts.

---

## Baseline State

- Verified source: `Titan-Zero-Meetup-WorkCore-Integrated-v0.7.0`.
- Source checksum: `4a64ad4b2d0b141aeb3dd91fe19c618c0caeb2fedcea7820ced8694ea62bf6ed`.
- Imported files: 1,128.
- Source import evidence: `SOURCE_IMPORT_COMPLETE.md`.
- Existing upgrade overview: `UPGRADE_PLAN.md`.
- Existing engineering rules: `AGENTS.md`.
- Draft integration PR: `#5`.
- The archive workflow `titan-verify.yml` was deliberately excluded from automated import and must be restored through a human-authored commit after review.

## Branch and PR Sequence

Implement passes sequentially. Each branch starts from the latest accepted `agent/v070-upgrade-base` commit and targets that branch:

1. `upgrade/01-repository-safety`
2. `upgrade/02-connected-baseline`
3. `upgrade/03-laravel-runtime`
4. `upgrade/04-workcore-authority`
5. `upgrade/05-interaction-engine`
6. `upgrade/06-five-tier-ai`
7. `upgrade/07-offline-pwa`
8. `upgrade/08-product-shell`
9. `upgrade/09-provider-adapters`
10. `upgrade/10-security-rewind`
11. `upgrade/11-performance-observability`
12. `upgrade/12-release-candidate`

After each PR passes review and verification, merge it into `agent/v070-upgrade-base`. Merge the final accepted base to `main` only after Pass 12.

---

### Task 1: Repository Safety, Provenance and CI Restoration

**Goal:** Lock the imported baseline and remove repository-level security ambiguity before executing application code.

**Files:**
- Create: `.github/workflows/titan-verify.yml`
- Create: `tests/Architecture/RepositorySafetyContractTest.php`
- Modify: `.env.example`
- Modify: `.gitignore`
- Modify: `SOURCE_IMPORT_COMPLETE.md`
- Modify: `BRANCH_STATUS.md`
- Modify: `docs/integration/CONNECTED_VERIFICATION_HARNESS.md`

**Interfaces:**
- Consumes: `bin/titan-preflight`, `bin/titan-verify-offline`, `bin/titan-verify-connected`.
- Produces: required CI checks named `repository-safety`, `php-static`, `laravel-tests`, `frontend-build`, `sqlite-migrations`, and `postgres-migrations`.

- [ ] Write `RepositorySafetyContractTest` to fail when tracked files contain a populated `APP_KEY`, private-key headers, obvious provider tokens, committed `.env`, `vendor/`, `node_modules/`, or writable runtime files.
- [ ] Run `php artisan test tests/Architecture/RepositorySafetyContractTest.php` and confirm it fails against any unsafe fixture added by the test.
- [ ] Set `.env.example` to `APP_DEBUG=false`, leave `APP_KEY=` empty, use non-secret local defaults, and document required queue, cache, database, broadcasting, object-storage and Titan Vault variables.
- [ ] Recreate `.github/workflows/titan-verify.yml` manually with pinned action majors and six jobs: repository safety, PHP syntax/Composer validation, Pest, frontend build, SQLite migration cycle and PostgreSQL migration cycle.
- [ ] Add concurrency cancellation for superseded PR commits and upload test/build logs on failure.
- [ ] Run `bash bin/titan-preflight` and `bash bin/titan-verify-offline`.
- [ ] Commit with `chore: lock repository safety and verification baseline`.

**Exit gate:** No tracked secret-bearing files; CI workflow is present; offline verifier passes; provenance files match the imported source checksum and file count.

---

### Task 2: Connected Dependency and Build Baseline

**Goal:** Prove that the imported source can be reconstructed from Git in a clean environment.

**Files:**
- Modify: `composer.lock` only when `composer install` proves it is inconsistent with `composer.json`
- Create: `package-lock.json`
- Modify: `package.json`
- Modify: `docker-compose.yml`
- Modify: `docker/php/Dockerfile`
- Modify: `DEPLOYMENT.md`
- Create: `docs/integration/CONNECTED_BASELINE_RESULTS.md`

**Interfaces:**
- Consumes: PHP 8.2+, Composer, Node 22, npm, SQLite and PostgreSQL.
- Produces: reproducible Composer/npm installs and a recorded connected-baseline report.

- [ ] Run `composer validate --strict`.
- [ ] Run `composer install --no-interaction --prefer-dist` in a clean checkout and record package discovery output.
- [ ] Run `php artisan about`, `php artisan route:list`, `php artisan event:list`, and `php artisan schedule:list`.
- [ ] Run `npm install --package-lock-only`, review the resolved dependency tree, then commit `package-lock.json`.
- [ ] Run `npm ci` followed by `npm run build`.
- [ ] Ensure Docker uses the same supported PHP/Node versions as CI and does not bake secrets into image layers.
- [ ] Record exact commands, versions, warnings and failures in `CONNECTED_BASELINE_RESULTS.md`.
- [ ] Commit with `build: establish connected dependency baseline`.

**Exit gate:** Clean Composer and npm installs succeed; Laravel package discovery succeeds; production frontend assets build; no unrecorded manual installation step remains.

---

### Task 3: Laravel Boot, Providers, Routes, Queues and Scheduler

**Goal:** Make the host application boot deterministically with every intended provider and route registered exactly once.

**Files:**
- Modify: `bootstrap/app.php`
- Modify: `bootstrap/providers.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Modify: `app/Providers/TitanServiceProvider.php`
- Modify: `app/Domains/WorkCore/WorkCoreServiceProvider.php`
- Modify: `routes/web.php`
- Modify: `routes/api.php`
- Modify: `routes/titan.php`
- Modify: `routes/console.php`
- Create: `tests/Architecture/ProviderRegistrationContractTest.php`
- Create: `tests/Architecture/RouteNameUniquenessTest.php`
- Create: `tests/Feature/LaravelBootSmokeTest.php`

**Interfaces:**
- Consumes: Laravel service container and the existing WorkCore, Titan Intelligence, Titan Creative and Titan Maps providers.
- Produces: deterministic provider registration, route namespaces, queue configuration and schedule registration.

- [ ] Write provider and route tests that detect duplicate providers, duplicate route names, missing middleware and unresolved controller classes.
- [ ] Boot the application under `testing`, `local` and a production-like configuration without touching external providers.
- [ ] Ensure web requests never start queue workers or scheduler processes; queue workers remain process-manager responsibilities.
- [ ] Ensure caught boot exceptions are logged with redacted context instead of silently swallowed.
- [ ] Register WorkCore and Titan providers in one canonical location only.
- [ ] Run `php artisan optimize:clear`, `php artisan config:cache`, `php artisan route:cache`, and `php artisan event:cache`.
- [ ] Run focused tests and `php artisan route:list --json`.
- [ ] Commit with `fix: harden Laravel runtime registration`.

**Exit gate:** Application boot, config cache, route cache and event cache succeed; provider and route uniqueness tests pass; queue/scheduler work is absent from request boot.

---

### Task 4: WorkCore Authority, Tenancy and Mutation Boundary

**Goal:** Prove that every operational mutation is company-scoped, governed and reachable only through WorkCore actions.

**Files:**
- Modify: `app/Domains/WorkCore/System/Actions/BusinessActionDispatcher.php`
- Modify: `app/Domains/WorkCore/System/Actions/BusinessActionRegistry.php`
- Modify: `app/Domains/WorkCore/System/Context/CompanyOperatingContextResolver.php`
- Modify: `app/Domains/WorkCore/System/Persistence/TenantScopedRepository.php`
- Modify: `app/Domains/WorkCore/System/Tenancy/BelongsToCompany.php`
- Modify: `app/Http/Controllers/Titan/WorkCoreActionController.php`
- Modify: `app/Http/Controllers/Titan/WorkCoreReadController.php`
- Create: `tests/Architecture/NoDirectOperationalModelWritesTest.php`
- Create: `tests/Feature/WorkCore/TenantIsolationTest.php`
- Create: `tests/Feature/WorkCore/ActionGovernanceTest.php`
- Create: `docs/integration/WORKCORE_AUTHORITY_MATRIX.json`

**Interfaces:**
- Consumes: `ActionRequest`, `OperationContext`, permission resolver, confirmation verifier, idempotency store, audit recorder and domain-event recorder.
- Produces: one governed mutation path and a machine-readable authority matrix.

- [ ] Generate an inventory of operational models, repositories, actions, read models, routes, controllers and jobs.
- [ ] Add a structural test that rejects `save`, `create`, `update`, `delete`, `insert`, `upsert` and raw mutation calls on WorkCore models outside approved repositories/action infrastructure.
- [ ] Add cross-company tests for controllers, repositories, queued jobs, events, attachments, approvals and idempotency records.
- [ ] Resolve actor and active company on the server; ignore or reject conflicting request-body company identifiers.
- [ ] Verify permission, delegation, risk, confidence and confirmation are evaluated independently.
- [ ] Verify high-confidence requests still fail when permission or delegation is missing.
- [ ] Verify retries return the prior idempotent result and do not emit duplicate events.
- [ ] Commit with `test: enforce WorkCore authority and tenant isolation`.

**Exit gate:** No unapproved direct operational writes; cross-company tests pass; every registered mutation produces one audit record and one domain-event envelope.

---

### Task 5: First-Party Interaction Engine Bounded Runtime

**Goal:** Integrate the cumulative Interaction Engine as a governed Titan Zero runtime without duplicating WorkCore or host authority.

**Files:**
- Create: `app/Titan/Interaction/Contracts/InteractionDefinitionRepository.php`
- Create: `app/Titan/Interaction/Contracts/InteractionSessionRepository.php`
- Create: `app/Titan/Interaction/Domain/InteractionDefinition.php`
- Create: `app/Titan/Interaction/Domain/InteractionSession.php`
- Create: `app/Titan/Interaction/Domain/InteractionState.php`
- Create: `app/Titan/Interaction/Runtime/InteractionCompiler.php`
- Create: `app/Titan/Interaction/Runtime/InteractionRuntime.php`
- Create: `app/Titan/Interaction/Runtime/InteractionPipeline.php`
- Create: `app/Titan/Interaction/Authority/AuthorityDecisionService.php`
- Create: `app/Titan/Interaction/Confidence/ConfidenceAssessmentService.php`
- Create: `app/Titan/Interaction/Evidence/EvidenceGraph.php`
- Create: `app/Titan/Interaction/Providers/TitanInteractionServiceProvider.php`
- Create: `config/titan_interaction.php`
- Create: `routes/titan-interaction.php`
- Create: `database/migrations/2026_07_30_070000_create_titan_interaction_runtime_tables.php`
- Create: `tests/Architecture/TitanInteractionBoundaryContractTest.php`
- Create: `tests/Feature/TitanInteraction/InteractionLifecycleTest.php`

**Interfaces:**
- Consumes: host authentication/company context, Titan Intelligence tools/providers and WorkCore `BusinessActionDispatcher`.
- Produces: compile, start, advance, pause, resume, cancel, abstain, request-approval and complete interaction operations.

- [ ] Inventory the cumulative Interaction Engine source and map every retained file to host, Titan, WorkCore or optional adapter authority before copying code.
- [ ] Define immutable interaction definitions with explicit version, schema, steps, transitions, validation and compatibility metadata.
- [ ] Store sessions with company, actor, device, definition version, current step, state hash, timestamps and resumability metadata.
- [ ] Implement deterministic step transitions, conditional branching, validation, cancellation, recovery and version compatibility.
- [ ] Route operational commands only through `BusinessActionDispatcher`; prohibit Eloquent dependencies in `app/Titan/Interaction`.
- [ ] Implement delegated scopes, approval expiry, signed approval tokens, reversibility checks, risk classification, abstention and escalation.
- [ ] Implement confidence bands, calibration metadata, evidence provenance, contradiction/staleness penalties and explanation records without allowing confidence to grant authority.
- [ ] Register provider, routes, policies, events and queue jobs through `TitanInteractionServiceProvider`.
- [ ] Commit with `feat: add governed Titan Interaction Engine runtime`.

**Exit gate:** Lifecycle tests pass; Interaction Engine has no WorkCore model imports; high-risk actions require valid permission, delegation and confirmation; sessions resume deterministically.

---

### Task 6: Five-Tier AI Capability Graph and Execution Wiring

**Goal:** Make every manager, specialist, action agent and deterministic tool reachable through one governed capability graph.

**Files:**
- Modify: `app/Titan/AI/TitanZeroOrchestrator.php`
- Modify: `app/Titan/AI/ToolRouter.php`
- Modify: `app/Titan/AI/ConversationContextBuilder.php`
- Modify: `app/Titan/Capabilities/CapabilityRegistry.php`
- Modify: `app/Titan/Intelligence/Providers/TitanIntelligenceServiceProvider.php`
- Create: `app/Titan/Intelligence/Runtime/CapabilityGraph.php`
- Create: `app/Titan/Intelligence/Runtime/AgentExecutionCoordinator.php`
- Create: `app/Titan/Intelligence/Runtime/ExecutionReceipt.php`
- Create: `tests/Architecture/FiveTierReachabilityTest.php`
- Create: `tests/Feature/TitanAI/AuthorityConfidenceSeparationTest.php`
- Create: `tests/Feature/TitanAI/AgentExecutionReceiptTest.php`

**Interfaces:**
- Consumes: Interaction Engine context, WorkCore action/read registries and Titan Intelligence agent/skill/tool/provider definitions.
- Produces: `Titan Zero -> Uno -> Duo -> Trio -> Quattro` execution plans and durable receipts.

- [ ] Register Titan Zero as the sole user-facing orchestrator.
- [ ] Register Uno managers, Duo specialists/assistants, Trio action agents and Quattro deterministic tools with stable identifiers and versioned capability definitions.
- [ ] Reject orphaned, duplicate or cyclic capability registrations during application boot.
- [ ] Require every agent run to carry actor, company, conversation, device, interaction session, delegation, permission and idempotency context.
- [ ] Persist checkpoints, retries, provider failures, abstentions, approvals and compensation results.
- [ ] Use the same execution pipeline for text and voice.
- [ ] Add Green/Amber/Red presentation states while preserving separate permission and authority decisions.
- [ ] Commit with `feat: wire governed five-tier AI capability graph`.

**Exit gate:** All registered capabilities are reachable or explicitly dormant; no agent bypasses the Interaction Engine/WorkCore boundary; execution receipts are complete and tenant-scoped.

---

### Task 7: Device-First PWA and Offline WorkCore Reconciliation

**Goal:** Provide useful, deterministic operation during network loss without accepting unvalidated server mutations.

**Files:**
- Modify: `public/service-worker.js`
- Modify: `public/js/pwa.js`
- Modify: `public/manifest.json`
- Modify: `public/offline.html`
- Create: `resources/js/titan/offline/database.js`
- Create: `resources/js/titan/offline/vault.js`
- Create: `resources/js/titan/offline/outbox.js`
- Create: `resources/js/titan/offline/sync-engine.js`
- Create: `resources/js/titan/offline/conflict-store.js`
- Create: `app/Http/Controllers/Titan/OfflineBootstrapController.php`
- Create: `app/Http/Controllers/Titan/OfflinePullController.php`
- Create: `app/Http/Controllers/Titan/OfflinePushController.php`
- Create: `app/Http/Controllers/Titan/OfflineAttachmentController.php`
- Create: `tests/Feature/Offline/OfflineReplayIdempotencyTest.php`
- Create: `tests/Feature/Offline/OfflineTenantRevalidationTest.php`
- Create: `tests/Standalone/PWA/offline-runtime.test.mjs`

**Interfaces:**
- Consumes: device identity, active-company context, WorkCore read/action registries and Interaction Engine session state.
- Produces: bootstrap, incremental pull, operation push, acknowledgement, tombstones, conflict responses, attachment upload and command receipts.

- [ ] Version the IndexedDB schema for conversations, messages, definitions, sessions, read models, drafts, outbox, conflicts, attachments and receipts.
- [ ] Encrypt provider keys and sensitive local records with a device-bound vault; never place credentials in Service Worker caches.
- [ ] Cache only an explicit application shell and non-sensitive definitions/read packs.
- [ ] Generate client UUIDs and idempotency keys for offline-created records and commands.
- [ ] Revalidate actor, company, permission, delegation, confirmation, idempotency, record version and business invariants on the server.
- [ ] Preserve both versions of conflicts and expose manual resolution where deterministic merge is unsafe.
- [ ] Add resumable attachment upload and visible sync receipts.
- [ ] Test duplicate replay, stale versions, revoked membership, expired approval and cross-company command rejection.
- [ ] Commit with `feat: add device-first offline reconciliation`.

**Exit gate:** Chat, saved interaction progress and selected WorkCore workflows function offline; duplicate replay creates no duplicate mutation; revoked/invalid commands fail on reconnect; unsynchronised data is preserved.

---

### Task 8: Unified Product Shell and Responsive Operational UX

**Goal:** Present one coherent business application with persistent conversational access and stable operational navigation.

**Files:**
- Modify: `resources/views/layouts/app.blade.php`
- Modify: `resources/views/layouts/partials/mini-sidebar.blade.php`
- Modify: `resources/views/chat.blade.php`
- Modify: `resources/views/titan/operations.blade.php`
- Modify: `resources/css/app.css`
- Modify: `resources/js/titan/operations.js`
- Modify: `public/css/titan-operations.css`
- Modify: `public/js/titan-operations.js`
- Create: `resources/views/titan/components/approval-card.blade.php`
- Create: `resources/views/titan/components/confidence-card.blade.php`
- Create: `resources/views/titan/components/conflict-centre.blade.php`
- Create: `tests/Architecture/OperationalUiRouteContractTest.php`
- Create: `tests/Feature/UI/OperationalShellTest.php`

**Interfaces:**
- Consumes: real WorkCore read models, Interaction Engine session state, approvals, confidence explanations and offline sync status.
- Produces: consistent mobile, tablet and desktop shell.

- [ ] Keep a persistent chat input available throughout normal company operations.
- [ ] Remove duplicate assistant navigation when the persistent chat surface is present.
- [ ] Keep Settings behind the top-right gear and reserve primary navigation for operational domains.
- [ ] Add a hamburger/overflow menu for secondary and administrative links.
- [ ] Connect dashboard and operations cards to real read models; remove static success states and dead controls.
- [ ] Add accessible loading, empty, offline, pending-sync, conflict, approval, error and recovery states.
- [ ] Keep cleaner/field-worker views stable and task-focused; keep manager views analytical and exception-focused.
- [ ] Verify responsive layouts at 375px, 768px, 1024px and 1440px widths.
- [ ] Commit with `feat: unify responsive Titan Zero operating shell`.

**Exit gate:** Every visible control resolves to a real route/action; mobile, tablet and desktop share one information architecture; offline/conflict/approval states are visible and accessible.

---

### Task 9: Provider, Connector and Channel Adapters

**Goal:** Activate external services through provider-neutral, fail-closed and tenant-scoped adapters.

**Files:**
- Modify: `app/Titan/Intelligence/Providers/TitanIntelligenceServiceProvider.php`
- Modify: `config/titan_intelligence.php`
- Modify: `config/services.php`
- Create: `app/Titan/Intelligence/Providers/ProviderRegistry.php`
- Create: `app/Titan/Intelligence/Connections/ConnectionHealthService.php`
- Create: `app/Titan/Intelligence/Webhooks/SignedWebhookVerifier.php`
- Create: `tests/Feature/Providers/ProviderFailureIsolationTest.php`
- Create: `tests/Feature/Providers/WebhookReplayProtectionTest.php`
- Create: `tests/Feature/Providers/VaultCredentialIsolationTest.php`

**Interfaces:**
- Consumes: Titan Vault secret references, capability registry, queue system and WorkCore/Titan read-action gateways.
- Produces: AI providers, Gmail/Outlook, Google Drive/Calendar, Slack, WhatsApp, Telegram, Messenger, Instagram, Twilio, Maps/Places, object storage and payment-observation adapters.

- [ ] Define provider capabilities, limits, retry policy, circuit-breaker state and health reporting.
- [ ] Store only Vault reference IDs in connection records.
- [ ] Sign and timestamp outbound callbacks; validate signatures, timestamps, nonce/replay state and company mapping for inbound webhooks.
- [ ] Treat provider data as observations until a governed WorkCore action accepts it.
- [ ] Redact prompts, tokens, credentials and sensitive evidence from normal logs.
- [ ] Add sandbox tests for success, timeout, rate limit, malformed response, duplicate webhook and revoked credential paths.
- [ ] Commit with `feat: govern external provider and channel adapters`.

**Exit gate:** No provider secret appears in source, URLs, logs or browser bundles; duplicate/replayed webhooks are rejected; provider outages do not corrupt interaction or WorkCore state.

---

### Task 10: Security, Privacy, Compliance and Titan Rewind

**Goal:** Close critical security paths and add temporal correction without raw database rollback.

**Files:**
- Create: `app/Titan/Rewind/Contracts/RewindPlanRepository.php`
- Create: `app/Titan/Rewind/Domain/RewindPlan.php`
- Create: `app/Titan/Rewind/Services/RewindPlanner.php`
- Create: `app/Titan/Rewind/Services/RewindExecutor.php`
- Create: `app/Titan/Rewind/Providers/TitanRewindServiceProvider.php`
- Create: `tests/Feature/Security/TenantBoundaryAttackTest.php`
- Create: `tests/Feature/Security/DelegationEscalationTest.php`
- Create: `tests/Feature/Security/ApprovalTokenExpiryTest.php`
- Create: `tests/Feature/Rewind/RewindCompensationTest.php`
- Create: `docs/security/THREAT_MODEL.md`
- Create: `docs/security/DATA_RETENTION_AND_EXPORT.md`

**Interfaces:**
- Consumes: immutable audit records, domain events, action registry, current permissions and confirmation policies.
- Produces: explainable compensating-action plans and signed rewind receipts.

- [ ] Threat-model authentication, active-company selection, delegated agents, file access, webhooks, provider callbacks, offline commands, approvals and administrative routes.
- [ ] Test mass assignment, arbitrary class/action resolution, path traversal, SSRF/unrestricted URLs, unsafe uploads, replay and sensitive-log exposure.
- [ ] Add data export, retention, deletion and legal-hold rules with company scope.
- [ ] Build Rewind from event history and registered compensating actions; never delete audit history or directly restore database snapshots as an application action.
- [ ] Re-evaluate current permission, authority and confirmation before executing a rewind plan.
- [ ] Record the before state, intended compensation, actual result and unresolved residue.
- [ ] Commit with `feat: add security assurance and governed Rewind`.

**Exit gate:** Zero unresolved critical findings; approval tokens expire and are scope-bound; Rewind cannot bypass current permissions or erase history.

---

### Task 11: Performance, Reliability and Observability

**Goal:** Make runtime behaviour measurable and safe under retries, concurrency and provider failure.

**Files:**
- Modify: `config/queue.php`
- Modify: `config/cache.php`
- Modify: `config/logging.php`
- Modify: `app/Domains/WorkCore/System/Outbox/DatabaseOutboxPublisher.php`
- Create: `app/Titan/Observability/ExecutionTrace.php`
- Create: `app/Titan/Observability/SensitivePayloadRedactor.php`
- Create: `tests/Feature/Reliability/ConcurrentActionIdempotencyTest.php`
- Create: `tests/Feature/Reliability/OutboxRecoveryTest.php`
- Create: `tests/Performance/CriticalReadModelBudgetTest.php`
- Create: `docs/operations/OBSERVABILITY_AND_ALERTS.md`

**Interfaces:**
- Consumes: action receipts, interaction traces, provider failures, queue jobs, outbox messages and database timings.
- Produces: redacted structured logs, metrics, traces and alert thresholds.

- [ ] Add correlation IDs spanning conversation, interaction session, action, event, outbox message and provider call.
- [ ] Redact credentials, prompts containing protected data, private evidence and approval tokens.
- [ ] Add safe retry/backoff policies and dead-letter diagnostics for long-running interactions and provider calls.
- [ ] Test concurrent delivery of identical idempotency keys and outbox recovery after process termination.
- [ ] Define performance budgets for active-company resolution, operations dashboard reads, interaction advancement and offline bootstrap.
- [ ] Record queue depth, failure rate, sync conflicts, provider latency, abstention rate, approval wait and action outcome metrics.
- [ ] Commit with `perf: add reliability and observability gates`.

**Exit gate:** Duplicate concurrent actions remain single-effect; failed jobs retain diagnostic context without sensitive payloads; critical read/interaction paths meet documented budgets.

---

### Task 12: Release Candidate, Deployment and Production Acceptance

**Goal:** Produce a signed, reproducible release from Git rather than another untracked archive.

**Files:**
- Modify: `DEPLOYMENT.md`
- Modify: `docker-compose.yml`
- Modify: `bin/titan-build`
- Modify: `bin/titan-verify-connected`
- Create: `docs/operations/QUEUE_AND_SCHEDULER_RUNBOOK.md`
- Create: `docs/operations/BACKUP_RESTORE_RUNBOOK.md`
- Create: `docs/operations/INCIDENT_RESPONSE.md`
- Create: `docs/releases/v0.8.0-rc1.md`
- Create: `.github/workflows/release.yml`

**Interfaces:**
- Consumes: all prior pass gates and connected verification outputs.
- Produces: signed Git tag, checksummed artifacts, SBOM, migration plan, deployment manifest and rollback evidence.

- [ ] Run full PHP syntax lint, Composer validation, package discovery, Laravel boot, route/event/schedule listing, Pest, architecture tests, tenant/permission/authority/confidence/offline tests, npm CI/build, JSON/YAML parsing, duplicate class/route scans, secret scan and unsafe-eval/direct-model-write scans.
- [ ] Run fresh SQLite and PostgreSQL migrations, rollback and re-migration.
- [ ] Test queue workers, scheduler, broadcasting, storage, signed file access and provider sandbox connections in staging.
- [ ] Perform backup, restore and disaster-recovery drills against staging data.
- [ ] Generate SBOM, checksums, build manifest and release notes from the exact candidate commit.
- [ ] Deploy a canary, monitor defined health/error/business metrics, then promote or roll back using the documented runbook.
- [ ] Tag the accepted commit `v0.8.0-rc1`; create production tag only after canary acceptance.
- [ ] Commit with `release: prepare Titan Zero v0.8.0 release candidate`.

**Exit gate:** A clean environment builds solely from Git plus documented secrets; all required checks pass; backup/restore and rollback are proven; artifacts correspond exactly to the signed tag.

---

## Stop Conditions

Stop the current pass and open a defect record when any of these occur:

- A second authority for users, companies, permissions, conversations, CRM, finance, settings or storage is discovered.
- An operational write bypasses WorkCore.
- Company context can be influenced by untrusted request data.
- A retry can duplicate a business effect.
- A provider credential reaches source, logs, URLs or browser bundles.
- An offline command is accepted without server-side revalidation.
- A migration would destroy or silently overwrite existing data.
- A critical security test fails.
- The branch cannot be rebuilt from a clean checkout.

## Completion Definition

The upgrade is complete only when all twelve pass PRs are merged into `agent/v070-upgrade-base`, the complete connected verifier passes on the resulting commit, the release candidate succeeds in staging, the final source is merged to `main`, and the release artifacts are generated from the signed `main` tag.