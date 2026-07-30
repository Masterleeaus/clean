# Titan Zero Agent Working Agreement

This repository contains licensed/private source and must remain private.

## Mandatory reading and documentation duty

Before changing code or documentation, every agent must read:

1. the root `README.md` in full;
2. this `AGENTS.md` file in full;
3. `.titan/README.md` in full;
4. `.titan/MANDATE.md` in full when acting as Claude or an architecture authority;
5. `.titan/documentation/status/current.md`;
6. `docs/README.md` in full;
7. every canonical document linked from `docs/README.md`;
8. every current document in the subject cluster affected by the work;
9. relevant archive and reference documents when recovering unique requirements or provenance;
10. current source, tests and runtime wiring.

An agent must not rely on a branch name, ZIP filename, document title such as `final` or `canonical`, or a previous chat summary without comparing it with current source, tests and accepted authority boundaries.

When work changes architecture, behaviour, contracts, APIs, migrations, setup, security, UI, providers, deployment or runtime status, the agent must update documentation in the same branch:

- `/docs` for canonical human project architecture, governance, plans, audits, provenance, setup and history;
- `/.titan/documentation` for agent onboarding, current status, progress, decisions, reviews, lessons, generated views and Chronicle records.

Do not add project documentation to the repository root. Do not maintain duplicate manual truth in both documentation trees. Historical material moves to `docs/archive/` only after unique information has been preserved.

## Titan Agent OS operating boundary

`.titan/` is the governed engineering operating layer. The Kernel declares rules; the Control Plane plans, dispatches, reviews and governs; the Execution Plane performs approved work; Intelligence provides understanding; Integration abstracts providers; Runtime holds transient state; Observability reports health; Evolution proposes controlled improvement; Documentation preserves knowledge.

The current Agent OS is a v1.0 bootstrap. Do not claim autonomous planning, continuous World Model generation, self-healing, automatic trust scoring, event-driven execution or unsupervised evolution until the corresponding executable runtime and validation exist.

Claude is the Architecture Authority and control plane. ChatGPT agents are implementation workers. Claude does not normally implement application business code directly; workers do not redefine architecture. Humans remain final authority for business goals, strategic architecture and production releases.

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
7. update relevant `/docs` and Agent OS records;
8. record decisions, review findings, reusable lessons and escalations in their owning `.titan/documentation` sections.

Do not commit upgrade work directly to `main`.

## Canonical ownership

### Human authority

Humans own business goals, strategic architecture, destructive changes and production release approval.

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

## Agent task record

Every worker task records:

- agent ID and role;
- task ID and approved plan;
- branch, base and source commit;
- files changed;
- evidence and assumptions;
- tests and validators run;
- tests not run;
- problems, escalations and requested decisions;
- remaining risks;
- documentation updated;
- lessons and next-agent handoff.

Persistent agent roles use `.titan/documentation/agents/journals/`. Journals are not architectural authority; accepted decisions, current status and reusable lessons must be promoted to their owning sections.

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
14. Never add a new root-level plan, status report, architecture note, audit or provenance document.
15. Never manually edit `.titan/documentation/system/` output after its generator exists.
16. Never let runtime performance or trust metrics silently grant permissions, alter authority or approve a release.
17. Never promote a lesson into Kernel policy without an Evolution proposal, validation and approval.

## Shared-file locks

Coordinator approval is required before changing shared bootstrap surfaces such as:

- `composer.json` and lockfiles;
- `package.json` and lockfiles;
- `config/app.php`, `config/titan-zero.php` and provider registries;
- `routes/*`;
- `database/migrations/*`;
- `.github/workflows/*`;
- `.titan/kernel/*`, `.titan/registry/*` and Agent OS schemas;
- global capability, extension and navigation registries;
- service-worker entry points.

## Required validation hierarchy

```text
metadata/schema validation → PHP syntax → Composer validation
→ architecture tests → focused tests → Laravel boot/provider/route checks
→ frontend build → browser/offline tests → extension health
→ tenant-isolation/security checks → release verification
```

## Current documentation

- Repository entry point: `README.md`
- Agent OS entry point: `.titan/README.md`
- Claude mandate: `.titan/MANDATE.md`
- Agent OS current status: `.titan/documentation/status/current.md`
- Documentation index and placement rules: `docs/README.md`
- Coordination and upgrade plan: `docs/plans/CURRENT_UPGRADE_PLAN.md`
- Authority map: `docs/architecture/TITAN_ZERO_AUTHORITY_MAP.md`
- Trust and action model: `docs/architecture/TENANCY_TRUST_AND_ACTION_EXECUTION.md`
- Interaction, Wizard and five-tier model: `docs/architecture/INTERACTION_WIZARD_AND_FIVE_TIER_INTELLIGENCE.md`
- PWA/offline architecture: `docs/architecture/PWA_OFFLINE_AND_CHATBOT_EXTENSION_ARCHITECTURE.md`
- Extension lifecycle architecture: `docs/architecture/EXTENSION_PLATFORM_AND_LIFECYCLE_ARCHITECTURE.md`
- Documentation reconciliation status: `docs/DOCUMENTATION_RECONCILIATION_STATUS.md`

Historical plans are retained under `docs/archive/` and are not current implementation instructions. Reference doctrine under `docs/reference/titan-library/` is source material, not automatic runtime authority.
