# Titan Zero Agent Working Agreement

This repository contains licensed/private source and must remain private.

## Mandatory reading and documentation duty

Before changing code or documentation, every agent must read:

1. the root `README.md` in full;
2. this `AGENTS.md` file in full;
3. `docs/README.md` in full;
4. every canonical document linked from `docs/README.md`;
5. every current document in the subject cluster affected by the work;
6. relevant archive and reference documents when recovering unique requirements or provenance.

An agent must not rely on a branch name, ZIP filename, document title such as `final` or `canonical`, or a previous chat summary without comparing it with current source, tests and accepted authority boundaries.

When work changes architecture, behaviour, contracts, APIs, migrations, setup, security, UI, providers, deployment or runtime status, the agent must update or add documentation under `docs/` in the same branch. Do not add project documentation to the repository root. Historical material moves to `docs/archive/` only after unique information has been preserved.

## Single coordination base

The approved shared base is:

```text
integration/current-main-reconciliation
```

That branch is created from the latest verified `main`. Every active agent must create a new, focused `reconcile/<scope>` branch from it. Old agent branches are preserved as evidence only; they are not rebased or merged wholesale.

Each agent must:

1. freeze and push its old branch;
2. compare it with the coordination base;
3. identify work already present, unique work, conflicts and obsolete work;
4. port only the unique, verified delta to a fresh reconciliation branch;
5. open a draft PR targeting `integration/current-main-reconciliation`;
6. record tests run, tests not run and rejected source;
7. update the relevant current documents under `docs/`.

Do not commit upgrade work directly to `main`.

## Canonical ownership

### MagicAI host

Owns platform authentication, users, company and membership lifecycle, active-company selection, subscriptions, platform billing, provider configuration, queues, notifications and the application shell.

### WorkCore

`app/Domains/WorkCore` is the sole authority for operational business records, business permissions, governed mutations, transactions, domain events, operational audit and server-side synchronisation contracts.

The host supplies identity and tenant membership context. WorkCore validates and consumes that context for operational access; it does not become a second account or subscription authority.

### Interaction Engine

Owns interaction definitions, sessions, transitions, clarification, confidence, evidence, abstention, approval preparation and wizard execution. It may prepare or dispatch governed commands, but it may not mutate operational records outside WorkCore actions.

The canonical package source exists under `packages/titanzero/interaction-engine`, but activation must not be claimed until root dependency registration, provider activation, Laravel boot and route tests pass.

### Titan Zero intelligence

Owns intent, planning, orchestration, delegation, model/tool selection, governance and memory coordination. Confidence never grants permission. AI may propose actions only through the governed Interaction Engine and WorkCore action boundary.

### Chatbot and PWA

`app/Extensions/Chatbot` owns presentation, conversations, channels, generative UI, device storage, local drafts, offline state, outbox and synchronisation UX. It must not own canonical operational data or host identity.

Embedded PHP WorkCore copies are compatibility/reference material and must never shadow `App\Domains\WorkCore`.

### Titan Money and payment surfaces

Operational finance, settlement and reconciliation remain governed WorkCore capabilities. MagicAI subscription and platform billing remain separate. Payment providers and UI surfaces may observe or initiate payment flows but may not become invoice or ledger authority.

### Extensions

Extensions add optional capabilities. They must not replace identity, tenancy, permissions, messaging authority, WorkCore authority or the credential vault.

## Governed operational mutation path

```text
Surface or channel
→ host authentication and tenant membership
→ Titan Zero / Interaction Engine proposal
→ WorkCore BusinessActionDispatcher
→ tenant + actor context check
→ entitlement check
→ permission check
→ explicit confirmation when required
→ idempotency reservation/replay
→ transactional handler
→ domain events + audit
→ result + outbox/synchronisation
```

No UI, chatbot, AI agent, PWA adapter, integration or extension may bypass this path for operational mutations.

## Non-negotiable rules

1. Preserve tenant, actor, device, correlation and causation identifiers through HTTP, queues, offline storage, sync and domain events.
2. Treat company/tenant identity and user/actor identity as different concepts.
3. Never cache credentials, provider secrets or sensitive API responses in service-worker Cache Storage.
4. Never automatically delete unsynchronised device records.
5. Never activate all imported extensions at once; use validated manifests and progressive qualification.
6. Do not introduce permanent parallel application, WorkCore, identity, tenant or permission systems.
7. Do not delete apparently unused code until routes, providers, events, dynamic resolution, scheduled jobs, queues, migrations and frontend imports have been traced.
8. Keep compatibility shims explicit, documented and covered by tests.
9. Use failing tests before behavioural repairs and run the smallest relevant test set after each change.
10. Record unavailable validation honestly as `not run`.
11. Do not merge to the coordination base without authority, tenancy, security and regression evidence.
12. Only the integration coordinator merges reconciliation PRs.
13. Never describe planned, source-present or partially wired functionality as operational without connected evidence.
14. Never add a new root-level plan, status report, architecture note, audit or provenance document; use the governed `docs/` tree.

## Shared-file locks

Coordinator approval is required before changing shared bootstrap surfaces such as:

- `composer.json` and lockfiles;
- `package.json` and lockfiles;
- `config/app.php`, `config/titan-zero.php` and provider registries;
- `routes/*`;
- `database/migrations/*`;
- `.github/workflows/*`;
- global capability, extension and navigation registries;
- service-worker entry points.

## Required validation hierarchy

```text
PHP syntax → Composer validation → architecture tests → focused tests
→ Laravel boot/provider/route checks → frontend build → browser/offline tests
→ extension health → tenant-isolation/security checks → release verification
```

## Current documentation

- Repository entry point: `README.md`
- Documentation index and placement rules: `docs/README.md`
- Coordination and upgrade plan: `docs/plans/CURRENT_UPGRADE_PLAN.md`
- Authority map: `docs/architecture/TITAN_ZERO_AUTHORITY_MAP.md`
- Trust and action model: `docs/architecture/TENANCY_TRUST_AND_ACTION_EXECUTION.md`
- Interaction, Wizard and five-tier model: `docs/architecture/INTERACTION_WIZARD_AND_FIVE_TIER_INTELLIGENCE.md`
- Documentation reconciliation status: `docs/DOCUMENTATION_RECONCILIATION_STATUS.md`

Historical plans are retained under `docs/archive/` and are not current implementation instructions. Reference doctrine under `docs/reference/titan-library/` is source material, not automatic runtime authority.
