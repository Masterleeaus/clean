# Titan Zero Clean Integration Workspace

Private integration repository for the MagicAI host, WorkCore operational domain, Titan Zero Chatbot/PWA, Interaction and Wizard runtimes, five-tier intelligence, extensions and supporting services.

> [!IMPORTANT]
> **Every agent must stop here before changing code.** Read this file, then read [`AGENTS.md`](AGENTS.md), [`docs/README.md`](docs/README.md), every canonical document listed there, and every document in the subject cluster affected by the work. Do not rely on an old branch plan, archive filename or reference PDF as current authority.

## Required agent start sequence

1. Confirm the repository and current branch.
2. Read `AGENTS.md` in full.
3. Read `docs/README.md` in full.
4. Read the current upgrade plan, authority map, tenancy/trust model and documentation policy.
5. Read all current documentation relevant to the subsystem being changed.
6. Review historical or reference material only to recover unique information; it is not automatically authoritative.
7. Compare the intended change with current source, tests, routes, providers, migrations, registries and runtime wiring.
8. Work from an approved branch created from `integration/current-main-reconciliation`.
9. Add or update documentation in `docs/` as part of the same change.
10. Record tests run, tests not run, remaining risks and any superseded documents in the pull request.

An agent that has not completed this reading and source check must not modify the repository.

## Branch and merge rule

- `main` is the verified source baseline.
- `integration/current-main-reconciliation` is the shared coordination base.
- New work uses focused `reconcile/<scope>` or approved upgrade branches from that base.
- Old agent branches are frozen evidence. Preserve them, but port only unique, verified deltas.
- Never merge an obsolete branch wholesale, use unrelated-history merges, force-push shared branches or overwrite newer source with larger older copies.
- Agents do not merge their own pull requests.

## Architecture boundaries

- **MagicAI host:** authentication, users, company and membership lifecycle, subscriptions, platform billing, provider configuration and application shell.
- **WorkCore:** sole authority for operational business records, rules, permissions and mutations.
- **Titan Zero:** intent, planning, orchestration, governance and delegation.
- **Interaction Engine:** interaction and wizard governance, clarification, evidence, approval preparation and command preparation. Source is present, but connected host activation must be proven before it is described as active.
- **Chatbot/PWA:** conversations, presentation, device storage, drafts, offline state, outbox and synchronisation UX.
- **Titan Vault:** credentials and protected configuration.
- **Titan Money/payment surfaces:** WorkCore-governed operational finance, separate from MagicAI platform subscription billing.

No AI agent, chatbot controller, PWA repository, integration callback or extension may create a parallel operational write path around WorkCore.

## Documentation rules

All project documentation belongs under [`docs/`](docs/), except:

- this root `README.md`, which is the repository entry point;
- root `AGENTS.md`, which is the mandatory working agreement;
- recognised licences and machine configuration/build manifests that conventionally remain at the root.

When work changes architecture, behaviour, contracts, setup, migrations, APIs, security, UI, providers or deployment:

- update the relevant canonical document in `docs/`;
- create new documentation in the appropriate `docs/` section rather than the repository root;
- mark superseded guidance and move it to `docs/archive/` only after unique information is preserved;
- place audits and evidence in `docs/audits/`;
- place source origins and checksums in `docs/provenance/`;
- place implementation plans in `docs/plans/`;
- place generated catalogues and disposition records in `docs/inventory/`;
- never describe planned, source-present or partially wired functionality as operational without evidence.

Start with [`docs/README.md`](docs/README.md). It identifies the current canonical documents and explains the archive and reference-library status.

## Safety and quality

- This repository contains licensed/private source and must remain private.
- Never commit `.env`, live credentials, private keys, tokens, user data, runtime caches, `vendor/` or `node_modules/`.
- Preserve tenant, actor, device, correlation and causation context across HTTP, queues, offline storage, sync and domain events.
- Use failing tests before repairs and run the smallest relevant checks after each coherent change.
- Report unavailable tests as **not run**; never claim unverified success.
- Do not delete code or documentation solely because it appears unused. Trace providers, routes, events, dynamic resolution, queues, schedulers, migrations, frontend imports and compatibility contracts first.

## Current documentation entry points

- [`docs/README.md`](docs/README.md)
- [`docs/plans/CURRENT_UPGRADE_PLAN.md`](docs/plans/CURRENT_UPGRADE_PLAN.md)
- [`docs/architecture/TITAN_ZERO_AUTHORITY_MAP.md`](docs/architecture/TITAN_ZERO_AUTHORITY_MAP.md)
- [`docs/architecture/TENANCY_TRUST_AND_ACTION_EXECUTION.md`](docs/architecture/TENANCY_TRUST_AND_ACTION_EXECUTION.md)
- [`docs/governance/DOCUMENTATION_POLICY.md`](docs/governance/DOCUMENTATION_POLICY.md)
- [`docs/DOCUMENTATION_RECONCILIATION_STATUS.md`](docs/DOCUMENTATION_RECONCILIATION_STATUS.md)
