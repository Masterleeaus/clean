# Target Extension Platform Architecture

```text
Titan Interaction Surface
        |
        +-- Research Studio
        +-- Multi-Model Workspace
        +-- Council Workspace
        +-- Voice Runtime
        +-- Creative Studio
        |
Titan Extension Application Layer
        |
        +-- Research Run Coordinator
        +-- Multi-Model Run Coordinator
        +-- Council Execution Coordinator
        +-- Realtime Session Coordinator
        +-- Image Job Coordinator
        |
Shared Provider Platform
        |
        +-- Provider Registry
        +-- Capability Catalogue
        +-- Credential Resolver
        +-- Policy Router
        +-- Normalized Transport
        +-- Usage / Cost / Latency Telemetry
        +-- Webhook Verification and Replay Guard
        +-- Audit Events
        |
Provider Adapters
        |
        +-- OpenRouter
        +-- Perplexity
        +-- OpenAI Realtime
        +-- Midjourney via PiAPI
        +-- Nano Banana via Fal
        +-- SeeDream via Fal
        +-- Existing MagicAI providers
```

## Boundaries

- Provider adapters translate external APIs only; they do not own product workflows.
- Coordinators own workflow state, retries, cancellation, persistence, and events.
- Policy routing is explicit and inspectable; direct provider selection remains available.
- Secrets are resolved at execution time and are never placed in source, logs, queues, browser markup, or service-worker cache.
- Original extension routes and setting keys are maintained through compatibility adapters until migration parity is verified.
- Interaction Engine and WorkCore integration occurs through ports/adapters rather than direct provider coupling.

## Required shared records

- provider definitions and capabilities
- scoped encrypted credentials
- provider health observations
- model catalogue entries
- execution runs and attempts
- usage/cost/latency metrics
- webhook deliveries and replay keys
- generated assets and lineage
- research sources and citations
- voice sessions, events, and retention policies
- council member responses, scores, disagreements, synthesis, and accepted result
