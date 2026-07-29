# Titan Zero Current Upgrade Plan

> [!NOTE]
> **Current coordination baseline:** `integration/current-main-reconciliation`, created from repository `main` at `e565d7594e062c6705be9747bee0bd6081beb137`. Agents preserve old branches as evidence, but port only unique, verified deltas onto fresh `reconcile/<scope>` branches. Old branches are not merged wholesale.

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

See `docs/architecture/TITAN_ZERO_AUTHORITY_MAP.md` and `docs/architecture/TENANCY_TRUST_AND_ACTION_EXECUTION.md`.

## Verified current-state findings

1. The host is Laravel 10 and PHP 8.2.
2. `App\Providers\TitanZeroServiceProvider` stages WorkCore and Chatbot providers through feature flags.
3. WorkCore is enabled by default; Chatbot and Interaction Engine flags default to disabled.
4. WorkCore provides scoped tenant/operation contexts and a governed `BusinessActionDispatcher` with entitlement, permission, confirmation, idempotency, audit and domain-event controls.
5. The Interaction Engine package source exists under `packages/titanzero/interaction-engine` and contains a provider and tests.
6. The root `composer.json` currently does not register or require that package.
7. `interaction_engine_enabled` is stored by `TitanZeroFeatureFlags`, but `coreProviderClassNames()` does not currently register the Interaction Engine provider.
8. The repository contains parallel/embedded Chatbot and WorkCore runtime copies that require classification before deletion or activation.

The Interaction Engine is therefore **source-present but not yet proven active in the host**.

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

**Exit criteria**

- No live `.env`, private key or embedded transport key is tracked.
- No workflow can rewrite `main` or create orphan history.
- Every active agent branch starts from the coordination base.
- Basic repository validation runs automatically.

### Phase 1 — Boot, dependencies and provider graph

1. Validate `composer.json`, local path repositories and lockfiles.
2. Confirm every referenced local package exists.
3. Register `packages/titanzero/interaction-engine` deliberately or record a different canonical destination.
4. Wire `interaction_engine_enabled` to exactly one loadable provider.
5. Run PSR-4/autoload and PHP syntax validation.
6. Boot Laravel under staged combinations: host only, WorkCore only, Chatbot only, Interaction Engine only and approved combinations.
7. Verify config caching, package discovery, provider counts and route registration.
8. Catalogue missing classes, duplicate symbols and dependency conflicts.

**Exit criteria**

- `composer install` succeeds from a clean checkout.
- Laravel boots without container-resolution failures.
- WorkCore, Chatbot and Interaction Engine activate independently through explicit flags.
- No provider, route or class is registered twice.

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
8. Add cross-company isolation and privilege-escalation tests.

**Exit criteria**

- One host identity source and one WorkCore operational context are used everywhere.
- Cross-company access fails closed across web, API, queue, AI and sync paths.

### Phase 4 — Provider, adapter and module wiring

1. Audit every service-container binding.
2. Replace production null adapters with explicit MagicAI adapters where required.
3. Verify menu, notification, storage, payment, messaging, calendar and geocoding contracts.
4. Classify each WorkCore module as enabled, optional, dormant, compatibility-only or obsolete.
5. Disable unused verticals by manifest/config rather than premature deletion.
6. Ensure retained modules register providers, migrations, routes, permissions and UI entries exactly once.

**Exit criteria**

- No required production interface resolves to an unintended null implementation.
- Every enabled module is reachable, entitled and authorised.

### Phase 5 — Route and API consolidation

1. Generate and analyse the complete route table.
2. Remove duplicate names and method/URI collisions.
3. Standardise WorkCore operational API prefixes and response envelopes.
4. Apply consistent authentication, tenant, capability and exception middleware.
5. Keep controllers thin and move decisions into application actions.
6. Version device sync and AI tool endpoints.
7. Require signed, replay-protected external callbacks.

**Exit criteria**

- No duplicate route names or method/URI pairs exist.
- All operational APIs enforce identity, tenant and capability context.

### Phase 6 — Database and migration repair

1. Build a table, model and migration ownership map.
2. Detect duplicate table creation and altered historical migrations.
3. Validate foreign-key types, indexes, tenant keys and UUID strategy.
4. Reconcile host users/companies with WorkCore membership/context tables.
5. Mark Rewind and legacy models as canonical, compatibility-only or obsolete.
6. Test fresh install and upgrade from the latest supported schema.

**Exit criteria**

- Fresh and upgrade migrations succeed.
- No table has competing ownership without an explicit compatibility layer.

### Phase 7 — Interaction Engine and five-tier intelligence

1. Select one canonical Interaction Engine runtime and remove active parallel registration.
2. Separate Titan Zero orchestration from WorkCore business authority.
3. Consolidate agent, tool, memory, usage and governance registries.
4. Route Chatbot, offline and hosted AI execution through Interaction Engine clarification/approval and WorkCore actions.
5. Keep confidence separate from permission, entitlement and confirmation.
6. Enforce idempotency, authorisation, audit and domain events.
7. Add local/offline and cloud execution parity tests.

**Exit criteria**

- Exactly one Interaction Engine provider and one tool/action catalogue are active.
- AI cannot bypass WorkCore policy, confirmation or audit controls.

### Phase 8 — PWA and offline alignment

1. Match IndexedDB schemas to canonical WorkCore DTOs and versions.
2. Standardise UUID, company, actor and device fields.
3. Validate vault encryption, outbox retry, conflict handling and background sync.
4. Prevent secrets and sensitive responses entering service-worker caches.
5. Add local schema migrations and safe recovery.
6. Test offline create/update/delete and later reconciliation through WorkCore.

**Exit criteria**

- Offline operations reconcile without loss, duplication, false success or cross-tenant leakage.

### Phase 9 — UI, menus and product surfaces

1. Wire qualified WorkCore modules into the MagicAI shell.
2. Keep the persistent Chatbot input as the primary assistant surface.
3. Use menus for operational domains rather than duplicate assistant links.
4. Place settings behind the header gear and appropriate secondary navigation.
5. Verify role-specific mobile, tablet and desktop layouts.
6. Remove dead links and unreachable screens.

**Exit criteria**

- Every enabled feature has a reachable, authorised UI path.
- No placeholder menu or disconnected dashboard remains.

### Phase 10 — Security, quality and release hardening

1. Add secret scanning, dependency auditing and static analysis.
2. Test uploads, SSRF, path traversal, XSS, CSRF, SQL injection and mass-assignment controls.
3. Review queue, scheduler, webhook and provider failure behaviour.
4. Add observability for actions, sync, AI runs and tenant violations.
5. Produce deployment, rollback and disaster-recovery documentation.
6. Generate a verified release archive and checksums.

**Exit criteria**

- CI is green.
- No unresolved critical/high security finding remains.
- The release installs from a clean environment and can be rolled back safely.

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
