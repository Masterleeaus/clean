# Titan Zero Clean Integration Workspace

Private integration repository for the MagicAI host, WorkCore operational domain, Titan Zero Chatbot/PWA, Interaction and Wizard runtimes, five-tier intelligence, extensions and supporting services.

> [!IMPORTANT]
> **Every agent must stop here before changing code.** Read this file, [`AGENTS.md`](AGENTS.md), [`.titan/README.md`](.titan/README.md), [`docs/README.md`](docs/README.md), every canonical document relevant to the task and current source evidence. Claude or any architecture-control agent must also read [`.titan/MANDATE.md`](.titan/MANDATE.md) in full.

## Required agent start sequence

1. Confirm the repository, current branch and base commit.
2. Read `AGENTS.md` in full.
3. Read `.titan/README.md` in full.
4. If acting as Claude or an architecture authority, read `.titan/MANDATE.md` in full.
5. Read `.titan/documentation/status/current.md`.
6. Read `docs/README.md` in full.
7. Read the current upgrade plan, authority map, tenancy/trust model and documentation policy.
8. Read all current documentation relevant to the subsystem being changed.
9. Review historical or reference material only to recover unique information; it is not automatically authoritative.
10. Compare the intended change with current source, tests, routes, providers, migrations, registries and runtime wiring.
11. Work from an approved branch created from `integration/current-main-reconciliation`.
12. Update `/docs` and `/.titan/documentation` for their respective audiences as part of the same change.
13. Record tests run, tests not run, remaining risks, decisions, lessons and superseded documents in the pull request.

An agent that has not completed this reading and source check must not modify the repository.

## Branch and merge rule

- `main` is the verified source baseline.
- `integration/current-main-reconciliation` is the shared coordination base.
- New work uses focused `reconcile/<scope>` or approved upgrade branches from that base.
- Old agent branches are frozen evidence. Preserve them, but port only unique, verified deltas.
- Never merge an obsolete branch wholesale, use unrelated-history merges, force-push shared branches or overwrite newer source with larger older copies.
- Agents do not merge their own pull requests.

## Architecture boundaries

- **Humans:** final authority for business goals, strategic architecture, destructive changes and production releases.
- **MagicAI host:** authentication, users, company and membership lifecycle, subscriptions, platform billing, provider configuration and application shell.
- **WorkCore:** sole authority for operational business records, rules, permissions and mutations.
- **Titan Zero:** intent, planning, orchestration, governance and delegation.
- **Interaction Engine:** interaction and wizard governance, clarification, evidence, approval preparation and command preparation. Source is present, but connected host activation must be proven before it is described as active.
- **Chatbot/PWA:** conversations, presentation, device storage, drafts, offline state, outbox and synchronisation UX.
- **Titan Vault:** credentials and protected configuration.
- **Titan Money/payment surfaces:** WorkCore-governed operational finance, separate from MagicAI platform subscription billing.

No AI agent, chatbot controller, PWA repository, integration callback or extension may create a parallel operational write path around WorkCore.

## Two documentation systems

The repository intentionally maintains two complementary documentation trees:

### `/docs`

The canonical human project-documentation library. It contains accepted architecture, governance, implementation plans, audits, provenance, setup guidance, reference material and historical records.

### `/.titan/documentation`

The Titan Agent OS documentation layer. It contains agent onboarding, generated system views, current status, progress, decisions, reviews, learning, dashboards, visualisations, history and the Project Chronicle.

Do not copy the same manually maintained document into both trees. Link or derive Agent OS views from canonical `/docs` sources and record source commit and freshness.

## Documentation rules

All long-form project documentation belongs under [`docs/`](docs/). Agent OS-native documentation belongs under [`.titan/documentation/`](.titan/documentation/). The repository root retains only:

- this `README.md`, the repository entry point;
- `AGENTS.md`, the mandatory working agreement;
- recognised licences and machine configuration/build manifests that conventionally remain at root.

When work changes architecture, behaviour, contracts, setup, migrations, APIs, security, UI, providers or deployment:

- update the relevant canonical document in `/docs`;
- update Agent OS status, decisions, reviews, lessons or generated views in `/.titan/documentation` when applicable;
- mark superseded guidance and move it to `docs/archive/` only after unique information is preserved;
- place audits and evidence in `docs/audits/`;
- place source origins and checksums in `docs/provenance/`;
- place implementation plans in `docs/plans/`;
- place generated catalogues and disposition records in `docs/inventory/` or Agent OS generated output under `.titan/documentation/system/` according to ownership;
- never manually edit generated Agent OS system documentation after a generator exists;
- never describe planned, source-present or partially wired functionality as operational without evidence.

## Titan Agent OS

`.titan/` is the governed engineering operating layer. It defines the Constitution, metadata schemas, Claude mandate, agent onboarding, source registries, status and future control/execution/intelligence boundaries.

The current Agent OS is a **v1.0 bootstrap**. It does not yet prove autonomous planning, continuous World Model generation, self-healing, background scheduling, automatic trust scoring or unsupervised architectural evolution.

## Safety and quality

- This repository contains licensed/private source and must remain private.
- Never commit `.env`, live credentials, private keys, tokens, user data, runtime caches, `vendor/` or `node_modules/`.
- Preserve tenant, actor, device, correlation and causation context across HTTP, queues, offline storage, sync and domain events.
- Use failing tests before repairs and run the smallest relevant checks after each coherent change.
- Report unavailable tests as **not run**; never claim unverified success.
- Do not delete code or documentation solely because it appears unused. Trace providers, routes, events, dynamic resolution, queues, schedulers, migrations, frontend imports and compatibility contracts first.

## Current entry points

- [Titan Agent OS](.titan/README.md)
- [Claude Architecture Authority mandate](.titan/MANDATE.md)
- [Agent OS current status](.titan/documentation/status/current.md)
- [Agent working agreement](AGENTS.md)
- [Canonical project documentation](docs/README.md)
- [Current upgrade plan](docs/plans/CURRENT_UPGRADE_PLAN.md)
- [Authority map](docs/architecture/TITAN_ZERO_AUTHORITY_MAP.md)
- [Tenancy, trust and action execution](docs/architecture/TENANCY_TRUST_AND_ACTION_EXECUTION.md)
- [Documentation reconciliation status](docs/DOCUMENTATION_RECONCILIATION_STATUS.md)
