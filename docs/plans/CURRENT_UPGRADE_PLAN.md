# Titan Zero Current Upgrade Plan

> [!NOTE]
> **Current coordination baseline:** `integration/current-main-reconciliation`, fast-forwarded to repository `main` at `fa607d769a4f72ba287801b027cc42dcf56aa549`. Agents preserve old branches as evidence, but port only unique, verified deltas onto fresh `reconcile/<scope>` branches. Old branches are not merged wholesale.

## Purpose

Convert the cumulative MagicAI, WorkCore, Chatbot/PWA, Interaction/Wizard, five-tier intelligence and extension source into one secure, bootable, testable, tenant-safe and device-first Titan Zero application.

The governing rule is simple: preserve real functionality, establish one authority for each responsibility, and remove duplicate or unreachable implementations only after references and runtime wiring have been verified.

## Canonical authority

- **MagicAI host:** authentication, users, companies, memberships, subscriptions, platform billing, provider configuration and application shell.
- **WorkCore:** operational records, business permissions, governed actions, transactions, domain events, operational audit and synchronisation contracts.
- **Titan Zero intelligence:** intent, planning, orchestration, delegation, confidence and model/tool selection.
- **Interaction Engine:** interaction and wizard state, clarification, evidence, abstention, approval preparation and governed command preparation.
- **Chatbot/PWA:** conversations, channels, presentation, generative UI, device storage, drafts, offline state, outbox and sync experience.
- **Titan Money/payment surfaces:** operational finance and payment lifecycle under WorkCore governance, separate from MagicAI platform billing.

See:

- `docs/architecture/TITAN_ZERO_AUTHORITY_MAP.md`
- `docs/architecture/TENANCY_TRUST_AND_ACTION_EXECUTION.md`
- `docs/architecture/INTERACTION_WIZARD_AND_FIVE_TIER_INTELLIGENCE.md`
- `docs/architecture/PWA_OFFLINE_AND_CHATBOT_EXTENSION_ARCHITECTURE.md`

## Verified current-state findings

1. The host is Laravel 10 and PHP 8.2.
2. `App\Providers\TitanZeroServiceProvider` stages WorkCore and Chatbot providers through feature flags.
3. WorkCore is enabled by default; Chatbot and Interaction Engine flags default to disabled.
4. WorkCore provides scoped tenant/operation contexts and a governed `BusinessActionDispatcher` with entitlement, permission, confirmation, idempotency, audit and domain-event controls.
5. The canonical Interaction Engine package source exists under `packages/titanzero/interaction-engine` and contained 386 files before duplicate-path cleanup, including its provider, routes, migrations, tests, offline runtime and engine library.
6. A metadata-only duplicate package root under `packages/titan-zero/interaction-engine` was removed after its unique metadata and conflict were recorded.
7. The root `composer.json` currently does not register or require the canonical Interaction Engine package.
8. `interaction_engine_enabled` is stored by `TitanZeroFeatureFlags`, but `coreProviderClassNames()` does not currently register the Interaction Engine provider.
9. WorkCore Wizards is a separate operational-domain module under canonical WorkCore and must not be conflated with the universal Interaction Engine.
10. The TitanAI trees under `app/Extensions/Chatbot` and `app/Extensions/TitanZeroChatbot` contained 864 byte-identical files at Pass 3 inventory time.
11. Full Pass 4 comparison found 1,541 byte-identical files, one divergent provider and six primary-only Titan Train files across the complete extension trees.
12. `app/Extensions/Chatbot` is the canonical intended PWA extension; the secondary tree remains frozen compatibility/reference material pending focused source reconciliation.
13. The primary provider feature-gates WorkCore/TitanAI integration, while the secondary provider registers those integrations unconditionally and must not be activated.
14. Each extension tree contains 93 migrations and 40 provider-like files, so parallel discovery would create a serious duplicate-registration risk.
15. The PWA source contains IndexedDB version 5, an AES-256-GCM device vault, service-worker safeguards, outbox/conflict state, sync inbox and a cursor-based sync engine.
16. The generic outbox stores headers and bodies directly, so queued payloads require a no-secrets guarantee or encryption before production release.
17. The device ID is stored in localStorage and must be treated as an identifier only; the server must bind device trust to authenticated tenant and actor context.
18. Embedded Chatbot WorkCore server code remains compatibility/reference-only and must not shadow `app/Domains/WorkCore`.

The Interaction Engine is therefore **source-present but not yet proven active in the host**. Pass 3 established its canonical package path and removed the empty competing package root; connected activation remains implementation work.

The PWA is **source-present with substantial offline foundations**, but production readiness is not proven until duplicate-extension activation, queued-payload secrecy, device trust, IndexedDB upgrades and tenant-safe sync are verified end to end.

## Delivery phases

### Phase 0 — Repository safety and coordination

**Status:** Active reconciliation and stabilisation.

1. Use `integration/current-main-reconciliation` as the shared base.
2. Create isolated `reconcile/<scope>` branches.
3. Freeze old agent branches and port only verified unique deltas.
4. Keep the repository private because it contains licensed source.
5. Remove tracked runtime secrets and destructive import/sanitiser workflows.
6. Rotate keys exposed in historical commits.
7. Record every repair pass in focused commits and draft PRs.
8. Require agents to read the root README, `AGENTS.md`, `docs/README.md` and relevant canonical documents before changing source.
9. Require documentation changes under `docs/` in the same branch as material implementation changes.

**Exit criteria**

- No live `.env`, private key or embedded transport key is tracked.
- No workflow can rewrite `main` or create orphan history.
- Every active agent branch starts from the coordination base.
- Basic repository validation runs automatically.
- No new project plans, status reports, audits or architecture notes are added to the repository root.

### Phase 1 — Boot, dependencies and provider graph

1. Validate `composer.json`, local path repositories and lockfiles.
2. Confirm every referenced local package exists.
3. Register `packages/titanzero/interaction-engine` deliberately or record a different canonical destination.
4. Wire `interaction_engine_enabled` to exactly one loadable provider.
5. Ensure only `app/Extensions/Chatbot/System/ChatbotServiceProvider.php` can be selected by the active host/extension graph.
6. Prevent `app/Extensions/TitanZeroChatbot` from independently registering providers, routes or migrations.
7. Run PSR-4/autoload and PHP syntax validation.
8. Boot Laravel under staged combinations: host only, WorkCore only, Chatbot only, Interaction Engine only and approved combinations.
9. Verify config caching, package discovery, provider counts and route registration.
10. Catalogue missing classes, duplicate symbols and dependency conflicts.

**Exit criteria**

- `composer install` succeeds from a clean checkout.
- Laravel boots without container-resolution failures.
- WorkCore, Chatbot and Interaction Engine activate independently through explicit flags.
- No provider, route, migration or class is registered twice.

### Phase 2 — Canonical WorkCore boundary

1. Treat `app/Domains/WorkCore` as the canonical server-side WorkCore implementation.
2. Inventory PHP WorkCore copies embedded under Chatbot runtimes.
3. Compare duplicate files and preserve unique capabilities.
4. Move valid server functionality into canonical WorkCore.
5. Retain only device contracts and client runtime code in the Chatbot extension.
6. Add architecture tests preventing a second active Laravel WorkCore domain.
7. Scan AI, PWA, integration and extension code for direct operational model writes.

**Exit criteria**

- One active server-side WorkCore domain exists.
- All operational mutations use registered WorkCore actions.
- Chatbot/PWA code consumes WorkCore contracts/APIs rather than carrying a second backend.

### Phase 3 — Identity, tenant and trust consolidation

1. Keep host user/company/membership lifecycle authoritative.
2. Select one normalised WorkCore tenant-context contract.
3. Separate request-scoped context from serialisable queue/offline snapshots.
4. Standardise company, actor, device, channel, correlation and causation identifiers.
5. Consolidate tenant resolvers and company-scoping traits.
6. Verify route binding, queues, schedules, AI tools and offline sync restore the same context.
7. Establish one device/channel trust-state model.
8. Bind PWA device registration to authenticated tenant and actor context; never trust a localStorage device ID as proof.
9. Add cross-company isolation and privilege-escalation tests.

**Exit criteria**

- One host identity source and one WorkCore operational context are used everywhere.
- Cross-company access fails closed across web, API, queue, AI and sync paths.
- Device revocation and tenant switching invalidate inappropriate local and server sessions.

### Phase 4 — Provider, adapter and module wiring

1. Audit every service-container binding.
2. Replace production null adapters with explicit MagicAI adapters where required.
3. Verify menu, notification, storage, payment, messaging, calendar and geocoding contracts.
4. Classify each WorkCore module and extension as enabled, optional, dormant, compatibility-only or obsolete.
5. Disable unused verticals and extensions by manifest/config rather than premature deletion.
6. Ensure retained modules register providers, migrations, routes, permissions and UI entries exactly once.
7. Preserve the primary Chatbot provider’s feature-aware WorkCore/TitanAI registration.
8. Reject the secondary provider’s unconditional WorkCore/TitanAI registration behaviour.

**Exit criteria**

- No required production interface resolves to an unintended null implementation.
- Every enabled module is reachable, entitled and authorised.
- Only one Chatbot provider and one Chatbot migration tree can activate.

### Phase 5 — Route and API consolidation

1. Generate and analyse the complete route table.
2. Remove duplicate names and method/URI collisions.
3. Standardise WorkCore operational API prefixes and response envelopes.
4. Apply consistent authentication, tenant, capability and exception middleware.
5. Keep controllers thin and move decisions into application actions.
6. Version device sync and AI tool endpoints.
7. Require signed, replay-protected external callbacks.
8. Verify `/api/v2/chatbot` device, bootstrap, push, pull and acknowledgement endpoints derive tenant/actor authority on the server.

**Exit criteria**

- No duplicate route names or method/URI pairs exist.
- All operational APIs enforce identity, tenant and capability context.
- A device cannot pull, push or acknowledge another tenant’s records.

### Phase 6 — Database and migration repair

1. Build a table, model and migration ownership map.
2. Detect duplicate table creation and altered historical migrations.
3. Validate foreign-key types, indexes, tenant keys and UUID strategy.
4. Reconcile host users/companies with WorkCore membership/context tables.
5. Mark Rewind and legacy models as canonical, compatibility-only or obsolete.
6. Map the 93 Chatbot migration files and prove only the canonical extension loads them.
7. Test fresh install and upgrade from the latest supported server schema.
8. Test IndexedDB upgrades from every supported prior device-database version without data loss.

**Exit criteria**

- Fresh and upgrade migrations succeed.
- No table has competing ownership without an explicit compatibility layer.
- IndexedDB upgrades preserve unsynchronised records, attachments and conflicts.

### Phase 7 — Interaction Engine and five-tier intelligence

1. Retain `packages/titanzero/interaction-engine` as the canonical universal runtime; the metadata-only competing package root has been removed.
2. Select one coherent host activation model—explicit feature-gated registration or Composer auto-discovery—and load the provider exactly once.
3. Separate Titan Zero orchestration from WorkCore business authority.
4. Consolidate agent, tool, memory, usage and governance registries.
5. Keep only one active Chatbot TitanAI tree and one canonical WorkCore authority; port unique deltas before removing compatibility copies.
6. Route Chatbot, offline and hosted AI execution through Interaction Engine clarification/approval and WorkCore actions.
7. Keep confidence separate from permission, entitlement and confirmation.
8. Enforce idempotency, authorisation, audit and domain events.
9. Add local/offline and cloud execution parity tests.

**Exit criteria**

- Exactly one Interaction Engine provider and one tool/action catalogue are active.
- AI cannot bypass WorkCore policy, confirmation or audit controls.

### Phase 8 — PWA and offline alignment

1. Retain `app/Extensions/Chatbot` as the canonical PWA extension and keep the secondary tree disabled until focused removal evidence exists.
2. Match IndexedDB schemas to canonical WorkCore DTOs and versions.
3. Standardise UUID, company, actor and device fields on local records and queued operations.
4. Validate vault encryption, inactivity/logout locking, explicit reset behaviour, outbox retry, conflict handling and background sync.
5. Prove queued headers and bodies contain no secrets or encrypt sensitive payloads before IndexedDB persistence.
6. Prevent secrets, authenticated pages and sensitive responses entering service-worker caches.
7. Add local schema migration, quota recovery and rollback handling.
8. Test offline create/update/delete and later reconciliation through WorkCore.
9. Verify device trust, revocation, multi-user browser isolation and tenant-switch behaviour.
10. Verify push-before-pull, cursor partitioning, durable sync inbox and acknowledgement ownership.
11. Remove the secondary extension only after provider, route, migration, asset, registry, installer, updater and rollback checks pass.

**Exit criteria**

- Offline operations reconcile without loss, duplication, false success or cross-tenant leakage.
- Service-worker caches contain only approved public shell assets.
- No secret or unrestricted sensitive payload is persisted unencrypted in the outbox.
- Unsynchronised records and conflicts survive upgrades and failures.
- Only the canonical Chatbot extension is active.

### Phase 9 — UI, menus and product surfaces

1. Wire qualified WorkCore modules into the MagicAI shell.
2. Keep the persistent Chatbot input as the primary assistant surface.
3. Use menus for operational domains rather than duplicate assistant links.
4. Place settings behind the header gear and appropriate secondary navigation.
5. Verify role-specific mobile, tablet and desktop layouts.
6. Preserve the six primary-only Titan Train files and test their workspace integration.
7. Remove dead links and unreachable screens.

**Exit criteria**

- Every enabled feature has a reachable, authorised UI path.
- No placeholder menu or disconnected dashboard remains.
- PWA install, update and offline fallback behave consistently across supported layouts.

### Phase 10 — Security, quality and release hardening

1. Add secret scanning, dependency auditing and static analysis.
2. Test uploads, SSRF, path traversal, XSS, CSRF, SQL injection and mass-assignment controls.
3. Review queue, scheduler, webhook and provider failure behaviour.
4. Add observability for actions, sync, AI runs and tenant violations.
5. Audit outbox payloads, service-worker caching, notification navigation and device registration.
6. Produce deployment, rollback and disaster-recovery documentation.
7. Generate a verified release archive and checksums.

**Exit criteria**

- CI is green.
- No unresolved critical/high security finding remains.
- The release installs from a clean environment and can be rolled back safely.
- Secondary extension removal or quarantine is proven reversible.

## Working method

Each pass must:

1. state its scope;
2. separate confirmed defects, probable defects and architecture risks;
3. modify the smallest coherent file set;
4. add or update tests for changed behaviour;
5. run relevant validation;
6. state tests not run;
7. record rejected source and remaining risks;
8. update the documentation and next-pass handoff.

## Branch and PR rule

```text
main
└── integration/current-main-reconciliation
    ├── reconcile/documentation
    ├── reconcile/repository-safety
    ├── reconcile/workcore
    ├── reconcile/interaction-engine
    ├── reconcile/offline-pwa
    └── reconcile/<other-independent-scope>
```

Reconciliation PRs target `integration/current-main-reconciliation`. Only the integration coordinator merges them. The coordination branch returns to `main` only after connected release gates pass.
