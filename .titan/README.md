# Titan Agent OS

`.titan/` is the governed engineering operating layer for the Titan Zero repository. It defines architectural rules, planning and review contracts, agent onboarding, machine-readable registries, runtime boundaries, project status and long-term engineering memory.

> [!IMPORTANT]
> **Every architecture-control agent starts here.** Claude must read [`MANDATE.md`](MANDATE.md) in full. Every worker agent must read the root [`README.md`](../README.md), [`AGENTS.md`](docs/AGENTS.md), [`docs/README.md`](../docs/README.md), this file and [`documentation/agents/START-HERE.md`](documentation/agents/START-HERE.md) before changing the repository.

## Current maturity

Titan Agent OS is in **v1.0 bootstrap**. The directory currently establishes contracts, structure, documentation sources, schemas and onboarding. It does **not** yet prove autonomous planning, continuous World Model generation, self-healing, background scheduling, automatic trust scoring or unsupervised architectural evolution.

Planned, source-present, partially wired and operational are different states. Agents must report the evidence-supported state precisely.

## Two documentation systems

The repository deliberately maintains two complementary documentation trees:

1. [`/docs`](../docs/README.md) — canonical human-authored project documentation, architecture, governance, plans, audits, provenance and historical/reference material.
2. [`/.titan/documentation`](documentation/README.md) — Agent OS documentation for AI onboarding, generated system views, progress, status, decisions, reviews, learning, dashboards, visualisations and the Project Chronicle.

The two trees must not become competing sources of truth. `.titan` documents declare whether they are authored, generated, derived or reference material and identify canonical `/docs` sources where applicable.

## Layer map

| Layer | Responsibility | Executes application work? |
|---|---|---:|
| `kernel/` | Constitution, contracts, capabilities, actions, workflows, policies, roles, validators and schemas | No |
| `intent/` | Goals, constraints, ambiguity and intent contracts | No |
| `control-plane/` | Planning, decomposition, dispatch, scheduling, governance, review, simulation and trust | No direct business-code execution |
| `execution-plane/` | Worker agents, manifests, tasks, queues, mailboxes and execution state | Yes, through approved plans and providers |
| `intelligence/` | World Model, knowledge graph, memory, reasoning, dependency/capability graphs and learning | No direct business-code execution |
| `integration/` | Provider and adapter contracts for GitHub, web builders, deployment, CLI and external systems | Through declared actions |
| `runtime/` | Transient plans, events, artifacts, results, logs and temporary state | Runtime only |
| `observability/` | Metrics, health, audit, activity, alerts and analytics | No |
| `evolution/` | Observe, propose, simulate, validate, review, adopt, monitor and retire | Only through approved governance |
| `documentation/` | Human, agent and system documentation; status; decisions; learning; Chronicle | No |
| `developer/` | Executable developer-experience assets, generators, scaffolds and validation tools | Tooling only |

## Non-negotiable repository authorities

- **Humans:** final authority for business goals, strategic architecture and production releases.
- **MagicAI host:** authentication, platform users, company membership lifecycle, subscriptions and platform shell.
- **WorkCore:** sole authority for operational business records, permissions, governed actions and mutations.
- **Titan Zero:** intent, orchestration, planning and delegation.
- **Interaction Engine:** interaction state, clarification, evidence, approvals and governed command preparation.
- **Chatbot/PWA:** conversations, presentation, device storage, offline state, outbox and synchronisation experience.
- **Titan Vault:** credentials and protected configuration.

No agent, extension, UI, PWA adapter or provider may create a parallel operational write path around WorkCore.

## Start sequence

1. Read [`MANDATE.md`](MANDATE.md) if acting as Claude or an architecture authority.
2. Read the root repository onboarding documents.
3. Read [`documentation/status/current.md`](documentation/status/current.md).
4. Read the canonical `/docs` documents relevant to the task.
5. Inspect current source and tests; never rely on documentation alone.
6. Create or use an approved isolated branch from the coordination baseline.
7. Plan, simulate and validate before implementation when the change is architectural or cross-domain.
8. Update `/docs` and `.titan/documentation` where their respective audiences require it.
9. Record evidence, tests run, tests not run, risks, decisions and lessons.

## Key entry points

- [Claude Architecture Authority mandate](MANDATE.md)
- [Agent OS documentation layer](documentation/README.md)
- [Worker-agent onboarding](documentation/agents/START-HERE.md)
- [Current status](documentation/status/current.md)
- [Kernel constitution](kernel/constitution/README.md)
- [Registry guidance](registry/README.md)
- [Canonical project documentation](../docs/README.md)
