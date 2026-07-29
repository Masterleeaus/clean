# Extension Duplication and Transformation Map

## Shared structures

| Repeated structure | Extensions | Transformation target |
|---|---|---|
| Service-provider registration | All eight | Shared extension lifecycle foundation |
| Settings forms and global settings keys | All eight | Typed provider configuration with encrypted scoped credentials |
| Provider/model selection | Perplexity, OpenRouter, MultiModel, Model Council | Titan AI Gateway and model catalogue |
| Parallel model execution and response comparison | MultiModel, Model Council | Multi-model run coordinator and Council Runtime |
| Async image jobs, polling, webhooks, asset storage | Midjourney, Nano Banana, SeeDream V4 | Titan Creative Studio / Image Engine |
| Large generator-specific Blade scripts | Midjourney, Nano Banana, SeeDream V4 | Reusable frontend image-job controller |
| Plan-entitlement partials | MultiModel, Model Council | Shared entitlement policy and UI component |
| Provider HTTP request construction | OpenRouter, Model Council, Midjourney, realtime | Provider adapters, normalized transport, retry and telemetry |

## Transform rather than merely duplicate

### OpenRouter → Titan AI Gateway

Retain the provider adapter and settings compatibility. Replace the thin request-forwarding service with routing policies, health checks, fallback chains, capability metadata, cost/latency accounting, and tenant/user BYO-key resolution.

### MultiModel → Titan Multi-Model Workspace

Retain shared message UUID compatibility. Add durable run records, parallel/sequential orchestration, cancellation, partial-failure handling, replay, structured comparison, and reusable execution templates.

### Model Council → Titan Council Runtime

Preserve stored council responses and existing chat integration. Split the monolithic service into execution, provider request, parsing, agreement, confidence, synthesis, persistence, access-policy, and SSE components. Add minority reports rather than forcing false consensus.

### Perplexity → Titan Research Studio

Retain Perplexity as one provider. Add research runs, source/citation persistence, claim-source mapping, contradiction detection, provenance, source deduplication, timeline extraction, and adapters for public and internal knowledge.

### OpenAI Realtime Chat → Titan Voice Runtime

Retain OpenAI realtime session support. Introduce provider-neutral sessions, transcripts, interruption handling, tool invocation and approvals, reconnection, retention controls, device context, and optional local speech fallback.

### Midjourney + Nano Banana + SeeDream V4 → Titan Creative Studio

Retain each provider's unique capabilities as adapters. Consolidate jobs, callbacks, polling, asset storage, prompt enhancement, variations, editing, upscaling, provider metadata, lineage, and brand/style presets.

## Code that should not be copied forward unchanged

- hard-coded callback URLs or secrets
- unverified generic webhook routes
- global unscoped API credentials
- copied DOM IDs and function names from another provider
- fully commented feature implementations
- sample migrations and empty rollbacks
- monolithic multi-thousand-line orchestration services
- repeated polling and rendering scripts
- `.DS_Store`, misspelled placeholder directories, and unused `.gitkeep` scaffolding
