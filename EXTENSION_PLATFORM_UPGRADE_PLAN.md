# Titan Zero Extension Platform Upgrade Plan

## Objective

Transform the uploaded MagicAI provider extensions into a coherent Titan Zero platform layer while preserving the original source archives for audit, comparison, and selective extraction.

## Source set

- perplexity
- open-router
- multi-model
- model-council
- openai-realtime-chat
- midjourney
- nano-banana
- see-dream-v4

## Initial findings

1. The extensions use a recurring MagicAI structure: `extension.json`, service provider, controllers/services, settings views, and optional migrations/config.
2. Several extensions duplicate provider registration, settings persistence, route bootstrapping, webhook handling, image polling, model selection, and plan-entitlement UI.
3. The image extensions substantially overlap and should converge behind one provider-neutral image runtime.
4. OpenRouter, MultiModel, and Model Council overlap around model catalogues, routing, parallel execution, and synthesis.
5. Perplexity currently exposes only settings/provider selection and lacks a full research workflow.
6. OpenAI Realtime provides a useful session bridge but lacks an abstract voice provider layer, durable session state, interruption handling, tool orchestration, transcript persistence, and privacy controls.
7. Confirmed packaging/code defects include:
   - Midjourney webhook returns success before executing its implementation, making the remaining code unreachable.
   - Midjourney webhook image-empty condition is reversed before download.
   - Midjourney service contains a hard-coded request-catcher webhook and secret.
   - OpenAI Realtime ships a sample-data migration with an empty rollback.
   - SeeDream V4 view implementation is fully commented out and still references Nano Banana identifiers.
   - SeeDream V4 and Nano Banana register the same Fal webhook path/controller concept, creating collision risk.
   - OpenRouter contains a misspelled `System/Moldes` directory and unfinished modal TODOs.
   - Nano Banana has an unimplemented uninstall method.
   - `.DS_Store` files are included in source packages.
   - Large provider-specific Blade scripts duplicate request, polling, loading, error, and result-rendering logic.

## Target platform modules

### 1. Titan AI Gateway

Consolidate OpenRouter and shared provider mechanics into a provider-neutral gateway.

Capabilities:
- provider registry and health checks
- capability-aware routing
- policy filters for privacy, residency, cost, latency, quality, and offline availability
- fallback and retry chains
- per-tenant and BYO-key credentials
- normalized requests, streaming, errors, usage, and billing metadata
- model catalogue sync rather than hard-coded enums only

### 2. Titan Multi-Model Workspace

Transform MultiModel from model switching into coordinated execution.

Capabilities:
- parallel and sequential model runs
- reusable orchestration templates
- shared conversation/message UUIDs
- cancellation, partial failure, replay, and deterministic run records
- comparison views and structured output contracts

### 3. Titan Council Runtime

Refactor Model Council's large monolithic service into bounded services.

Proposed components:
- CouncilRequestValidator
- CouncilAccessPolicy
- CouncilModelSelector
- CouncilExecutionCoordinator
- ProviderRequestFactory
- CouncilResultParser
- CouncilAgreementAnalyzer
- CouncilConfidenceScorer
- CouncilSynthesisService
- CouncilPersistenceService
- CouncilSseEmitter

Upgrades:
- explicit voting and ranking
- disagreement and minority-report preservation
- confidence calibration
- evidence/citation comparison
- specialist councils
- retry and degraded-consensus modes
- benchmark and outcome feedback

### 4. Titan Research Studio

Transform Perplexity into a research workspace rather than a single-provider selector.

Capabilities:
- pluggable search/research providers
- citation capture and source provenance
- claim-to-source mapping
- source deduplication and contradiction detection
- timeline and entity extraction
- internal knowledge and uploaded document retrieval adapters
- saved research runs and evidence bundles

### 5. Titan Voice Runtime

Transform OpenAI Realtime Chat into a provider-neutral realtime interaction layer.

Capabilities:
- ephemeral-session broker
- durable transcript and event model
- interruption and barge-in handling
- voice activity detection settings
- tool execution and approval policies
- multi-speaker and device context
- privacy modes and transcript retention controls
- fallback to local/device speech services when available

### 6. Titan Creative Studio / Image Engine

Merge Midjourney, Nano Banana, and SeeDream V4 behind common contracts.

Core contracts:
- ImageProviderInterface
- ImageGenerationRequest
- ImageGenerationResult
- ImageJobRepository
- ImageWebhookVerifier
- ImageAssetStorage
- ImageCapabilityRegistry

Capabilities:
- text-to-image, image-to-image, edit, variation, upscaling, background removal
- provider-specific capability forms generated from metadata
- normalized async jobs and webhook states
- signed webhooks with replay protection
- polling fallback
- prompt enhancement and brand/style presets
- asset library, lineage, provenance, and cost metadata

## Multi-step implementation sequence

### Phase 0 — Preserve and inventory

- Store untouched extracted sources under `source/extensions-original/`.
- Generate machine-readable inventory and checksums.
- Remove packaging noise only from transformed copies, never from originals.
- Map routes, settings keys, service-container bindings, migrations, models, and view injection points.

### Phase 1 — Defect stabilization

- Fix unreachable Midjourney webhook logic and reversed image condition.
- remove hard-coded webhook endpoint/secret and use signed configurable callbacks.
- replace sample realtime migration with intentional schema or remove it.
- repair SeeDream identifiers and restore executable UI/runtime code.
- resolve Fal webhook route collisions.
- implement uninstall/cleanup contracts.
- remove `.DS_Store` and misspelled placeholder paths from transformed code.
- add request validation, authorization, rate limits, timeouts, retries, and structured logging.

### Phase 2 — Shared extension foundation

Create shared infrastructure for:
- extension registration and lifecycle
- encrypted credential storage
- provider configuration
- entitlement checks
- provider health and capability metadata
- normalized API errors
- audit events
- async jobs and webhook verification
- usage and cost records

### Phase 3 — AI gateway and model catalogue

- adapt OpenRouter first
- move model metadata from UI enums into registry/catalogue records
- add provider adapters for existing MagicAI engines
- implement policy-based routing and fallback
- preserve direct-provider selection as an explicit override

### Phase 4 — Multi-model and council refactor

- establish run/message contracts
- split ModelCouncilService into bounded services
- connect MultiModel and Council to the AI Gateway
- add consensus, disagreement, confidence, and evidence scoring
- create orchestration presets

### Phase 5 — Research Studio

- wrap Perplexity as one research provider
- add evidence/citation persistence
- add research-run state machine
- support multiple source adapters and synthesis through Council

### Phase 6 — Creative Studio

- implement common image contracts
- migrate Nano Banana, SeeDream, and Midjourney into provider adapters
- replace duplicate Blade scripts with one reusable frontend controller
- add job state machine and secured webhooks
- retain provider-specific controls through capability schemas

### Phase 7 — Voice Runtime

- abstract realtime provider session creation
- add transcript/event persistence and privacy policies
- integrate tool routing through Interaction Engine / WorkCore adapters
- add interruption, reconnection, and fallback behavior

### Phase 8 — Product integration

- consolidate menus/settings into workspace-level screens
- expose tenant/user/device controls
- integrate plan entitlements
- add dashboard telemetry for health, cost, latency, failure, and quality
- add feature flags and migration compatibility

### Phase 9 — Verification and release

- architecture and namespace tests
- route-collision tests
- migration up/down tests
- provider contract tests with fakes
- webhook signature/replay tests
- streamed response tests
- browser tests for settings and workspaces
- upgrade/uninstall/rollback tests
- security review for keys, SSRF, downloaded assets, logs, and callback URLs

## Branch working layout

```
/
├── EXTENSION_PLATFORM_UPGRADE_PLAN.md
├── source/
│   └── extensions-original/
│       ├── perplexity/
│       ├── open-router/
│       ├── multi-model/
│       ├── model-council/
│       ├── openai-realtime-chat/
│       ├── midjourney/
│       ├── nano-banana/
│       └── see-dream-v4/
├── docs/
│   └── extension-platform/
│       ├── inventory.md
│       ├── defect-register.md
│       ├── duplication-map.md
│       └── target-architecture.md
└── workbench/
    ├── shared-extension-foundation/
    ├── ai-gateway/
    ├── council-runtime/
    ├── research-studio/
    ├── voice-runtime/
    └── creative-studio/
```

## Immediate branch rules

- Original extracted sources are immutable reference material.
- All transformed code is developed outside `source/extensions-original`.
- No provider secret may be committed.
- No duplicated route or setting key may be introduced without a compatibility alias and deprecation plan.
- Existing MagicAI behavior remains available until replacement paths pass parity tests.
