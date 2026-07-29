# Titan Zero Interaction/Wizard Engine Multi-Pass Upgrade Plan

Status: Working branch implementation plan  
Target repository: `Masterleeaus/clean`  
Working branch: `agent/interaction-wizard-cumulative-upgrade`  
Date: 2026-07-29

## Mission

Create one production-grade Interaction/Wizard Engine inside the MagicAI + WorkCore application while preserving a device-compatible offline runtime in the Titan Zero Chatbot PWA. Consolidate every valid cumulative wizard phase without allowing duplicate runtimes, shadow WorkCore domains, direct model writes, or unsafe offline replay.

## Architectural boundaries

1. MagicAI is the Laravel host and owns authentication, SaaS tenancy, billing and extension loading.
2. WorkCore owns operational records, validation, permissions, transactions, audit and domain events.
3. The Interaction Engine owns intent, wizard execution, context, planning, confidence, explanations, memory and orchestration.
4. The PWA owns UI, device storage, offline state and sync queues; it does not become a second WorkCore authority.
5. Operational writes must use typed WorkCore commands or registered business actions.
6. AI-generated definitions may not execute arbitrary PHP, JavaScript, SQL or unregistered tools.

## Pass 0 — Repository and source baseline

Establish source layout, archive hashes, extraction roots, root documentation, ignore rules, validation scripts and a no-loss source manifest.

## Pass 1 — Deep version inventory

Scan every Interaction Engine and Wizard phase. Compare namespaces, contracts, providers, routes, migrations, registries and tests. Classify files as canonical, merge, adapter, reference, obsolete or dangerous duplicate.

## Pass 2 — Canonical package placement

Install one server runtime at `packages/titan-zero/interaction-engine`, normalise Composer metadata, provider discovery, configuration, routes and migrations, and remove standalone-app assumptions.

## Pass 3 — Runtime contract consolidation

Consolidate execution context, runtime result, pipeline and middleware contracts. Define one lifecycle, legal transitions, versioning, correlation IDs, causation IDs and optimistic concurrency.

## Pass 4 — Wizard definition compiler

Create one schema for metadata, steps, branching, validation, commands, approvals, offline policy and UI schema. Compile PHP, JSON, YAML, database and extension definitions into immutable runtime definitions.

## Pass 5 — Registry and extension integration

Merge wizard, capability, tool, validator, resolver and UI registries. Add semantic versioning, collision detection, dependency checks and Extension SDK integration.

## Pass 6 — Tenant, actor and device context

Resolve MagicAI user, company, branch, workspace, device, conversation, role, permissions and authority limits. Fail closed when tenant or actor identity is absent.

## Pass 7 — WorkCore query gateway

Add typed, tenant-scoped read gateways for customers, properties, jobs, workers, schedules, quotes, invoices, payments, compliance and knowledge. Remove raw table traversal from the engine.

## Pass 8 — WorkCore command gateway

Map wizard actions to canonical WorkCore business actions and typed commands. Complete finance mappings through Titan Money or ZeroPay where required. Add idempotency, validation, audit metadata and event correlation.

## Pass 9 — Authority and approvals

Consolidate observe, recommend, prepare, approval-required, delegated and user-only modes. Add monetary, record, tenant, time, device and sensitive-action limits.

## Pass 10 — Memory truth lifecycle

Consolidate working, episodic, semantic and procedural memory with provenance, scope, expiry, contradiction, correction, supersession, deletion and tombstones.

## Pass 11 — Calibrated confidence and abstention

Score intent, entity resolution, context completeness, source freshness, contradictions, risk and network state. Add clarification, confirmation, abstention and escalation policies.

## Pass 12 — Cognitive events and outcome learning

Record intent, wizard selection, confidence, questions, corrections, commands, results and business outcomes. Keep learning scoped, governed and auditable.

## Pass 13 — Offline device runtime

Implement the TypeScript subset in the PWA. Sync compiled definitions rather than PHP source. Support local state, validation, safe transitions, attachments, queued commands, conflicts and tombstones while preserving the host-boundary fix.

## Pass 14 — Definition distribution and integrity

Add signed manifests, checksums, runtime compatibility, tenant filtering, delta downloads, revocation and rollback.

## Pass 15 — Generative UI wiring

Bind runtime step types to typed PWA components for questions, confirmations, approvals, entities, checklists, evidence, warnings, conflicts and completion summaries.

## Pass 16 — WorkCore onboarding migration

Split onboarding into reusable company, service, territory, workforce, scheduling, finance, compliance, branding, privacy and activation wizards without losing questions or rules.

## Pass 17 — Operational wizard library

Deliver tested CRM, quoting, jobs, workforce, finance and compliance wizard packs with permissions, commands, events and offline policy.

## Pass 18 — AI and Titan Sprout generation

Add proposed-definition, schema validation, capability validation, simulation, generated tests, human approval and publication stages. Generated workflows remain data definitions, not executable code.

## Pass 19 — Security and adversarial audit

Test cross-tenant access, forged approvals, prompt and tool injection, replay, out-of-order sync, stale definitions, malicious extensions and device impersonation.

## Pass 20 — Performance and production hardening

Add caching, queue tuning, indexes, event batching, incremental sync, storage quotas, telemetry, health checks and dead-letter recovery.

## Pass 21 — No-loss legacy removal

Compare final source against every phase. Remove shadow providers, duplicate registries, routes, migrations, direct-write fallbacks and abandoned placeholders. Keep compatibility adapters only where proven necessary.

## Pass 22 — Final validation and release

Run Composer, PHP lint, static analysis, Laravel boot, routes, migrations, unit, integration, tenancy, end-to-end, TypeScript, PWA, offline, replay, conflict, signature and extension tests. Deliver cumulative app, canonical package, updated PWA, delta, migrations, definition library, architecture matrices, reports, manifests and SHA-256 checksums.

## Immediate execution order

The first cycle completes Passes 0–3 and stops for an evidence-based checkpoint before WorkCore command expansion. This prevents further capability from being built on unresolved duplicate contracts.
