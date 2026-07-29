# 01. System Doctrine

## 1. System intent

The goal is not to bolt AI onto Worksuite. The goal is to turn Worksuite into a **coordinated operating system for service businesses** where:
- the database remains the canonical operational ledger
- modules own business state and workflows
- AI interprets, plans, proposes, and supervises
- PWAs and mobile devices become specialized surfaces over the same governed system
- device nodes perform as much work locally as possible
- the server coordinates, audits, syncs, and enforces tenant-safe rules

This creates a product that feels like a business runs through one living system rather than through separate dashboards, apps, and disconnected automations.

## 2. Core product belief

The most valuable software for service businesses will not be a static dashboard.
It will be a **distributed operational intelligence system** with:
- conversational control
- modular workflow depth
- multiple role-specific surfaces
- device-local execution where possible
- AI approval and safety gates
- a persistent operational memory that survives channels and devices

## 3. Design laws

### 3.1 Business logic belongs in modules and services
Laravel guidance strongly supports keeping controllers thin and shifting validation, authorization, and business logic into form requests, actions, and services. That principle should become a platform rule across Worksuite. Controllers should coordinate requests, not own operational logic.

### 3.2 Modules are the business organs
The Worksuite core should provide tenancy, auth, permissions, settings, package wiring, and common infrastructure. Modules should own domain state and workflows: jobs, sites, dispatch, money, documents, customer messaging, etc.

### 3.3 AI is supervisory, not a hidden side effect
AI should not silently mutate critical business records. It should:
- interpret intent
- gather context
- propose actions
- request approval when confidence/risk demands it
- call approved tools
- write auditable traces

### 3.4 PWAs are first-class products, not responsive leftovers

Each surface is a role-specific operating node over the same tenant-safe system. The platform ships as **9 named nodes**:

| Node | Role | Type |
|---|---|---|
| **Titan Pro** | Owner / Director command centre | Filament Admin Panel |
| **Ground Zero** | Dispatcher / Operations real-time control | Filament Panel |
| **Titan Go** | Field operator / Cleaner on-site execution | PWA (mobile-first) |
| **Zero Fuss** | Customer self-service portal | PWA |
| **Titan Zero** | AI orchestration surface + embedded AI in all nodes | Chat + API |
| **ZeroPay** | Financial engine — invoicing, payments, cashflow | PWA |
| **Titan Studio** | Growth, marketing, lead funnel | Filament Panel |
| **Titan Solo** | Single-operator simplified mode | PWA |
| **Titan Hello** | Omni-channel receptionist + lead intake | Background system |

These are not separate businesses. They are coordinated nodes over the same tenant-safe backend modules. See `docs/dashboards/00-node-architecture.md` for the full mapping.

### 3.5 Devices are nodes
A phone, tablet, desktop, kiosk, or browser session should be treated as a node with:
- identity
- capabilities
- sync state
- optional local model/runtime
- local cache and task queue
- offline behavior

### 3.6 Tenant boundary is sacred
Every operational module must be scoped by `company_id` as the tenant boundary. `user_id` is actor/ownership context, not the tenant boundary. No AI workflow, API endpoint, queue job, or local sync process is allowed to cross tenant scope.

### 3.7 The system must degrade gracefully
The system should continue functioning when:
- internet is poor
- AI provider is unavailable
- queue backlog exists
- mobile device is offline
- channel provider fails
- one subsystem is stale

That requires deliberate local caching, replay, sync envelopes, and background recovery.

## 4. System personality

The system should feel:
- conversational
- operational
- calm
- explainable
- fast
- modular
- auditable
- privacy-preserving

It should not feel like:
- a black box automation toy
- a noisy chatbot wrapper
- a CRUD app with AI buttons scattered around it

## 5. Engineering doctrine adapted from the Laravel sources

### 5.1 Thin controllers
Use form requests, actions, DTOs, and services so controllers remain orchestration layers.

### 5.2 Clear route architecture
Use named routes, route groups, middleware groups, resource controllers where appropriate, and API/web separation. This is critical once the system spans admin, user, PWA, API, and node surfaces.

### 5.3 Service-container driven design
Use interfaces and provider bindings so AI providers, sync transports, channel drivers, local/remote execution paths, and model routers can be swapped cleanly.

### 5.4 Performance by default
Prefer eager loading, select only needed columns, remove dead packages, cache aggressively where safe, queue long-running work, and monitor query behavior.

### 5.5 Testability is architecture
The PDFs reinforce that clean separation improves testing. In this system, tests must exist at:
- module feature level
- tenant-boundary/security level
- workflow level
- API level
- sync/offline replay level
- AI approval boundary level

## 6. Product doctrine

### 6.1 Chat is the entry layer, not the only layer
Conversation should be the top interaction surface, but structured interfaces must appear when the task demands precision.

### 6.2 Every action should be reducible to a tool call
If the AI can do something, there should be a module-owned tool/API contract for it.

### 6.3 Every tool call must be explainable
The system should be able to say:
- what it saw
- what it inferred
- what rule/tool it used
- what it changed
- what it refused to do

### 6.4 Memory must be scoped
Memory is not one blob. The system needs:
- company memory
- user preference memory
- site/job memory
- channel memory
- device/node memory
- AI operational memory

### 6.5 The platform must be buildable by agents
Structure, contracts, manifests, route conventions, and module interfaces must be explicit enough that GitHub Copilot or other agents can build safely without improvising the architecture.

## 7. Zero Philosophy

Every product decision is governed by the Zero Philosophy. The platform must deliver:

- **Zero missed calls** — Titan Hello answers every inbound, every channel, always
- **Zero unanswered messages** — Titan Zero triages, responds, escalates
- **Zero surprise bills** — transparent pricing, no platform transaction fees, no AI markup
- **Zero vendor lock-in** — BYO AI API keys, BYO payment gateway, data portability
- **Zero AI data resale** — company data never trains third-party models
- **Zero code forks** — vertical specialisation via overlay config, not separate codebases
- **Zero hidden complexity** — Titan Solo proves the platform can run a business in 3 taps

> Titan BOS. Zero BS.

See `docs/philosophy/00-zero-philosophy.md` for the full doctrine.

---

## 8. Vertical Overlay System

The platform serves 19 verticals across 4 tiers — without forking code. Each vertical is a config overlay that injects:

1. Terminology translation (industry-native UI language)
2. Workflow lifecycle model (job state machine per vertical)
3. Compliance layer (mandatory gates per vertical)
4. Checklist engine (vertical-specific task lists)
5. Artefact generator (auto-produced documents per vertical)
6. AI training layer (Titan Zero becomes a vertical expert)

See `docs/dashboards/vertical-overlay-architecture.md`, `docs/dashboards/vertical-registry.md`, and `docs/vertical-ai-training-architecture.md`.

---

## 9. Pricing Model

The platform is priced on a **node activation + crew seat** model, not per-transaction or per-AI-call.

| Plan | Target | Price |
|---|---|---|
| Solo | 1-person operator | ~$79/mo |
| Grow | 2–10 crew | ~$199/mo |
| Pro | 10+ crew, all 9 nodes | ~$399/mo |
| Enterprise | Multi-location / franchise | Custom |

Verticals are included at all tiers — not an upsell. BYO payment gateway and BYO AI key mean no transaction fees and no AI markup.

---

## 10. Final doctrine sentence

Titan BOS is a **tenant-safe, AI-supervised, vertically-specialised, modular business operating system** with 9 purpose-built node surfaces, where modules own business state, Titan Zero owns AI interpretation and orchestration, and nodes execute locally wherever possible.
