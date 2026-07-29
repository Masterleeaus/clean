> [!IMPORTANT]
> **Historical record — not current implementation guidance.** This document is retained for provenance because it describes an earlier branch, source version, import, or completed upgrade pass. Use `docs/README.md` and `docs/plans/CURRENT_UPGRADE_PLAN.md` for current guidance.

# Titan Zero Creative Extensions Upgrade Plan

Status: Active implementation plan
Branch: `agent/creative-extension-upgrade-workspace`
Scope: Six supplied creative extensions plus Titan Zero Extension SDK, MagicAI/WorkCore integration references, and selected donor modules.

## Operating rules

1. Preserve the original extensions as immutable provenance sources.
2. Record verified behaviour separately from proposed conversions.
3. Do not let creative extensions write operational records directly; all business mutations must pass through WorkCore services, permissions, transactions, audit and domain events.
4. Reuse existing extension service providers, routes, queues, permissions, settings and UI patterns before introducing parallel systems.
5. Convert by duplication only after namespace, migration, route, config, translation, asset and permission isolation is proven.
6. Every conversion requires install, upgrade, rollback and collision tests.

## Source set

- Creative Suite — 111 files
- Creative Suite Annotations — 17 files
- Creative Suite AI Template — 10 files
- Advanced Image — 47 files
- AI Image Pro — 151 files
- AI Photoshoot — 95 files

Verified extracted total: 431 files.

## Target platform shape

The six extensions remain specialised products but converge on shared contracts:

- Creative Suite: canvas/document workspace
- Creative Suite Annotations: region-aware AI editing add-on
- Creative Suite AI Template: queued template-generation add-on
- Advanced Image: multi-provider image tool runtime
- AI Image Pro: generation, publishing and media-library workflow
- AI Photoshoot: guided product/background/studio workflow

Shared capabilities to extract or introduce:

- Creative asset identity and version history
- Provider capability registry and routing
- AI job lifecycle, progress, retry, cancellation and failover
- Prompt/preset registry
- Media storage and derivative handling
- Tenant, permission and audit context
- WorkCore document/evidence adapters
- Extension capability discovery

## Five implementation scans

### Scan 1 — Provenance and structural inventory

- Normalise archive paths.
- Record checksums, file counts and top-level roots.
- Inventory extension manifests, providers, routes, migrations, models, jobs, services, views and assets.
- Detect bundled vendor code, generated assets, secrets and unsupported binaries.

Deliverables:

- `docs/creative-audit/01-source-inventory.md`
- `docs/creative-audit/source-inventory.json`
- `docs/creative-audit/checksums.sha256`

### Scan 2 — Runtime wiring and dependency graph

Trace each extension from service-provider registration through:

- boot/register methods
- route and middleware registration
- menu and permission hooks
- controller/request/service/job/model flow
- queue dispatch and polling
- settings and provider credentials
- frontend entry points and compiled assets

Classify every component as reachable, conditionally reachable, orphaned, duplicated or externally coupled.

Deliverables:

- `docs/creative-audit/02-runtime-wiring.md`
- `docs/creative-audit/dependency-map.mmd`

### Scan 3 — Data, AI, security and media pipeline

Audit:

- migration collisions and rollback safety
- tenancy scoping and ownership checks
- queue idempotency and retry behaviour
- webhook authentication
- provider timeout, cost and failure handling
- file validation, MIME trust and path traversal
- prompt injection and unsafe remote input
- public sharing tokens and access control
- image metadata/privacy leakage
- storage cleanup and orphaned derivatives

Deliverables:

- `docs/creative-audit/03-data-ai-security.md`
- prioritised defect register

### Scan 4 — Direct upgrades and duplicate-and-convert candidates

Direct upgrades:

- unify provider contracts
- introduce a shared creative asset contract
- standardise job state and progress events
- add provider capability/cost/latency metadata
- add WorkCore document and evidence adapters
- add semantic tagging and provenance metadata
- add tenant-aware audit events

Candidate conversions, created from copies only after isolation tests:

1. AI Photoshoot → Real Estate Visual Studio
2. AI Photoshoot → Site and Asset Inspector
3. AI Photoshoot → Vehicle/Equipment Inspector
4. AI Image Pro → Titan Media Library
5. AI Image Pro → Brand Asset Manager
6. Advanced Image → Visual Intelligence Tools
7. Creative Suite → Campaign Studio
8. Creative Suite → Presentation and Report Studio

Conversions must change product purpose, namespace, route prefix, database tables, config keys, permissions, translations, public assets and extension identifiers. A renamed copy with shared tables is not acceptable.

Deliverables:

- `docs/creative-audit/04-upgrades-and-conversions.md`
- one selected conversion scaffold with collision tests

### Scan 5 — Integration, verification and release

- Wire selected shared contracts into the clean MagicAI/WorkCore base.
- Validate extension install/uninstall/upgrade paths.
- Run PHP syntax, route, migration, container, queue and frontend build checks.
- Verify that creative outputs can attach to WorkCore documents/evidence without bypassing WorkCore authority.
- Produce release notes and rollback guidance.

Deliverables:

- `docs/creative-audit/05-release-readiness.md`
- verified implementation commits

## Initial implementation order

1. Commit source inventory and architecture decisions.
2. Audit extension manifests and service providers.
3. Build the runtime dependency map.
4. Extract shared contracts without changing behaviour.
5. Add WorkCore adapters behind interfaces.
6. Select the lowest-risk conversion and duplicate it with full namespace/data isolation.
7. Add automated collision and installation tests.
8. Continue with higher-value conversions only after the first conversion passes.

## First conversion selection gate

The first conversion will be chosen after Scan 3 using:

- lowest coupling to upstream MagicAI internals
- clearest reusable workflow
- smallest migration collision surface
- strongest Titan Zero business value
- easiest measurable acceptance tests

Current leading candidate: AI Photoshoot → Real Estate Visual Studio, subject to code-level verification.

## Definition of done

A pass is complete only when its findings are grounded in source paths and runtime traces. A conversion is complete only when original and converted extensions can be installed together, use independent identifiers and data, pass automated checks, and preserve WorkCore authority boundaries.
