> [!IMPORTANT]
> **Historical record — not current implementation guidance.** This document is retained for provenance because it describes an earlier branch, source version, import, or completed upgrade pass. Use `docs/README.md` and `docs/plans/CURRENT_UPGRADE_PLAN.md` for current guidance.

# Titan Zero Wizard Upgrade Plan

## Purpose

Upgrade the supplied `wizard-integration-v1.0` prototype into a production-grade Titan Zero setup system without creating parallel AI, WorkCore, governance, tenancy, credential, or offline runtimes.

## Working rules

- Preserve `main`; all work remains on `agent/wizard-upgrade` until reviewed.
- Treat the imported archive as reference and donor code, not trusted production code.
- Extend canonical repository services, contracts, models, policies, routes, providers, UI shell, and tests.
- Keep activation state truthful: configuration, validation, provisioning, activation, and runtime health are separate states.
- Scope every record and lookup by tenant/company, user, role, and device where applicable.
- Keep BYO provider credentials in the encrypted device vault unless an explicit server-side connector requires encrypted server custody.
- Use client-generated UUIDs and idempotent commands for offline-created configuration.
- Do not write directly around WorkCore authorities or five-tier AI governance.

## Phase 0 — Repository orientation and import isolation

1. Inventory the clean repository architecture, packages, extension discovery, tenancy model, authentication and authorization, frontend shell, WorkCore contracts, five-tier AI runtime, local vault, IndexedDB/outbox, and test framework.
2. Import the supplied source under `imports/wizard-integration-v1.0/` unchanged.
3. Add an import manifest with archive checksum, file list, audit classification, and known defects.
4. Identify canonical destination paths before moving or adapting any source.

**Exit criteria:** donor source is reproducible, isolated, checksummed, and no production path has been changed.

## Phase 1 — Package and runtime repair

1. Resolve extension/module manifests and service-provider registration.
2. Create canonical route files and middleware groups.
3. Correct Blade namespaces and shared-layout resolution.
4. Repair Phone Agent view/controller path drift.
5. Add missing configuration and feature flags.
6. Verify referenced models, relations, connectors, migrations, and user-model extensions exist.
7. Replace manual installation instructions with automated package discovery or explicit bootstrap registration.

**Tests:** package boot, route discovery, view rendering, migration loading, provider registration.

## Phase 2 — Wizard state and form wiring

1. Replace disconnected Alpine state with typed wizard state objects.
2. Wire trigger selections, tool IDs, channels, workflow steps, transfer targets, consent messages, and transfer keywords end-to-end.
3. Apply template defaults rather than merely storing template IDs.
4. Enforce provider/model compatibility.
5. Add per-step validation and nested error mapping.
6. Clear stale errors and preserve accessible focus/error summaries.
7. Make save-draft, validate, test, provision, activate, pause, and archive distinct commands.
8. Wrap aggregate creation in transactions and add idempotency keys.

**Tests:** browser/component tests, request validation, service transaction rollback, duplicate-submit protection.

## Phase 3 — Tenant security, authorization, and privacy

1. Replace `auth()->check()` authorization with policies and capability checks.
2. Scope templates, tools, knowledge collections, channels, and phone resources to the current tenant/company.
3. Prevent cross-tenant IDs from attaching through validation or service resolution.
4. Replace raw exception output with safe public errors and structured internal logs.
5. Encrypt any server-custodied provider credentials; hide them from serialization and logs.
6. Integrate device-vault references for BYO keys.
7. Add audit events, rate limiting, CSRF/session protections, webhook signature validation, and replay prevention.
8. Define explicit memory scopes: device, user, team, company, and approved shared knowledge—never ambiguous platform-global scope.

**Tests:** policy matrix, tenant isolation, IDOR regression, secret serialization, log redaction, webhook replay.

## Phase 4 — Titan Zero five-tier AI integration

1. Map wizard choices onto canonical Tier 0–4/5 runtime contracts and registries.
2. Configure manager, assistant, specialist, action-agent, and governance relationships through existing authorities.
3. Resolve tools through the canonical capability registry rather than free-form checkbox IDs.
4. Create capability grants with read/write/action boundaries.
5. Register model/provider routing through the five-tier orchestration layer.
6. Record confidence, risk, approval, execution, and receipt policies.
7. Remove or adapt parallel `AssistantBlueprint` and `AIAgentWorkflow` paths when canonical equivalents exist.

**Tests:** registry resolution, delegation graph, capability denial, confidence/approval routing, execution receipts.

## Phase 5 — WorkCore integration

1. Discover canonical WorkCore domains and commands available to the selected vertical and role.
2. Present only capabilities that are actually wired and authorized.
3. Separate read, propose, approve, and execute permissions.
4. Route all CRM, job, task, quote, invoice, scheduling, dispatch, customer, and staff writes through WorkCore application services.
5. Add dry-run previews and before/after operation summaries.
6. Store WorkCore references without duplicating authoritative records.

**Tests:** command authorization, aggregate ownership, rollback, audit receipts, domain boundary enforcement.

## Phase 6 — Device-first and offline-first wizard

1. Persist wizard drafts in IndexedDB with tenant, user, device, schema version, and client UUID.
2. Encrypt sensitive local fields through the device vault.
3. Add outbox commands, retry, conflict states, resumable progress, and schema migration.
4. Keep credentials out of service-worker caches and ordinary browser storage.
5. Add offline validation using cached registries while marking server-dependent checks as pending.
6. Reconcile drafts safely when connectivity returns.

**Tests:** offline creation, reload recovery, encrypted storage, retry/idempotency, conflict preservation, cache exclusion.

## Phase 7 — Phone and channel runtime

1. Implement provider adapters behind canonical interfaces.
2. Verify phone-number ownership and provider credentials before provisioning.
3. Provision signed inbound webhooks and health checks.
4. Persist IVR flows, transfer destinations, consent policy, recording policy, escalation keywords, and business hours.
5. Add call queues, events, logs, transcripts, redaction, retention, deletion, legal hold, and access controls.
6. Add Australian defaults (`en-AU`, `Australia/Sydney`, `+61`) derived from tenant/device settings rather than hard-coded.
7. Mark an agent live only after successful provisioning and runtime health verification.

**Tests:** provider contract tests, signed webhook fixtures, transfer routing, recording authorization, retention jobs, activation failure.

## Phase 8 — Titan Zero shell and generative UI

1. Embed the wizard in the canonical Titan Zero shell with persistent chat, workspace, hamburger navigation, gear settings, and sync state.
2. Allow conversational creation and editing through governed generative UI actions.
3. Render role- and vertical-aware steps.
4. Show exact effects of each capability and approval setting.
5. Add deployment readiness, unresolved checks, and runtime health panels.
6. Meet keyboard, screen-reader, contrast, responsive, and reduced-motion requirements.

**Tests:** responsive UI, accessibility checks, generated-action authorization, state synchronization between chat and form.

## Phase 9 — Verification and release

1. Run PHP linting, static analysis, formatting, frontend linting/type checks, unit, feature, integration, browser, security, and architecture tests.
2. Add migration rollback and upgrade-path tests.
3. Test clean install and installation into the current Titan Zero application.
4. Produce a defect closure matrix mapping every original audit finding to code and tests.
5. Generate a release manifest, checksums, installation notes, rollback procedure, and known limitations.
6. Open a draft pull request only after the branch is internally coherent and tests provide evidence.

## Initial audit gates

The following defects must remain release-blocking until verified closed:

- unresolved Blade namespace/layout paths
- Phone Agent controller/view mismatch
- discarded triggers, tools, workflow steps, consent messages, transfer keywords, or IVR targets
- UI claiming deployment while records remain drafts
- plaintext or serializable provider credentials
- authentication-only authorization
- unscoped `exists` validation and cross-tenant attachment risk
- raw exception leakage
- missing transactions and idempotency
- absent WorkCore/five-tier AI/offline integration
- non-operational phone provisioning and compliance controls

## Proposed branch layout

```text
/TITAN_ZERO_WIZARD_UPGRADE_PLAN.md
/imports/wizard-integration-v1.0/       # immutable donor snapshot
/docs/wizard-upgrade/                   # audit, mappings, decisions, closure matrix
/tests/...                              # added beside canonical repository tests
<canonical application paths>          # implementation only after orientation
```
