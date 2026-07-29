# TEDK Volume 1: Platform Constitution
## The Authoritative Platform Architecture Standard for Titan BOS

**Status:** Canonical - Foundation Layer  
**Version:** 1.0  
**Last Updated:** 2026  
**Audience:** Architecture leads, platform designers, framework maintainers, all developers

---

## Table of Contents

1. [Preamble: The Zero Philosophy](#preamble-the-zero-philosophy)
2. [System Intent & Core Belief](#system-intent--core-belief)
3. [Architectural Invariants](#architectural-invariants)
4. [Design Rules & Boundaries](#design-rules--boundaries)
5. [TitanCore Platform Kernel](#titancore-platform-kernel)
6. [Layering & Dependency Model](#layering--dependency-model)
7. [Engineering Doctrine](#engineering-doctrine)
8. [Design Laws](#design-laws)
9. [Key Principles by Domain](#key-principles-by-domain)

---

## Preamble: The Zero Philosophy

Titan BOS is guided by a single operating principle:

> **Remove friction instead of adding features.**

This is not marketing. This is architectural doctrine. Every design decision, every boundary, every contract must answer: *Does this reduce or increase operational friction?*

### Zero Definition

| Principle | Meaning | Implication for Architecture |
|---|---|---|
| **Zero hidden pricing** | Subscription only, transparent costs | Avoid feature gating, complex entitlement logic |
| **Zero AI token traps** | BYO API keys or local inference | No vendor lock-in; AI must be swappable |
| **Zero vendor lock-in** | User owns data, keys, integrations | Export everything, leave anytime |
| **Zero forced integrations** | Every connection is opt-in | Modular architecture, no hard dependencies |
| **Zero workflow duplication** | One engine, many surfaces | Platform is single source of truth |
| **Zero data resale** | Platform orchestrates, never harvests | User data ownership model |
| **Zero platform dependency** | Business runs through Titan, not because Titan forces it | Clean contracts, no hidden invasive instrumentation |
| **Zero learning curve friction** | AI guides rather than documentation | Interface clarity, operational intelligence |

### System Personality

The system should feel:
- **Conversational** — Natural language is the primary input layer
- **Operational** — Every tool is for doing business, not displaying dashboards
- **Calm** — Reduces noise, not amplifies it
- **Explainable** — Users understand why AI made a decision
- **Fast** — Local execution, smart caching, offline resilience
- **Modular** — Swappable engines, no monolithic core
- **Auditable** — Every change is traced and reviewable
- **Privacy-preserving** — User owns everything, platform orchestrates

It should NOT feel like:
- A black box automation toy
- A noisy chatbot wrapper around existing dashboards
- A CRUD app with AI buttons scattered on it

---

## System Intent & Core Belief

### Intent

The goal is not to add features to a service business dashboard.

The goal is to transform service business operations into **a coordinated operating system** where:

- The **database** remains the canonical operational ledger
- **Modules** own business state and workflows (jobs, dispatch, money, customers)
- **AI** interprets, plans, proposes, and supervises
- **PWAs and mobile devices** are specialized surfaces over the same governed system
- **Device nodes** perform as much work locally as possible
- **The server** coordinates, audits, syncs, and enforces tenant-safe rules

This creates a product that feels like a business **runs through one living system** rather than through separate dashboards, apps, and disconnected automations.

### Core Belief

The most valuable software for service businesses will not be a static dashboard.

It will be a **distributed operational intelligence system** with:
- Conversational control
- Modular workflow depth
- Multiple role-specific surfaces (9 named nodes)
- Device-local execution where possible
- AI approval and safety gates
- Persistent operational memory that survives channels and devices

---

## Architectural Invariants

These principles are **non-negotiable**. They define what Titan BOS is.

### I1: Tenant Boundary is Sacred

Every operational module must be scoped by `company_id` as the tenant boundary.

- `user_id` is actor/ownership context, not the tenant boundary
- No AI workflow, API endpoint, queue job, or local sync process is allowed to cross tenant scope
- Tenant-unsafe code is a **platform violation**
- Every cross-module operation must explicitly validate tenant boundary

**Application:**
```php
// ✓ Correct: Tenant-scoped
Job::whereTenantId($tenant_id)->where('status', 'pending')->get();

// ✗ Wrong: No tenant check
Job::where('status', 'pending')->get();

// ✗ Wrong: User boundary, not tenant
Job::whereUserId($user_id)->get();
```

### I2: Business Logic Lives in Modules & Services

Controllers coordinate requests. They do not own operational logic.

- **Validation logic** → Form Requests or Service validators
- **Business rules** → Services and Actions
- **Domain state transitions** → Module services
- **Controllers** → Orchestration only (route handler → service → response)

This ensures AI, batch jobs, webhooks, and other non-HTTP entry points can safely invoke the same business logic.

### I3: AI is Supervisory, Not a Hidden Side Effect

AI never silently mutates critical business records.

- **AI interprets** user intent
- **AI gathers** context from system state
- **AI proposes** actions with reasoning
- **AI requests** approval when confidence or risk demands it
- **AI executes** only approved tools
- **AI logs** every action with auditable traces

The human remains the decision-maker. AI is the intelligent assistant.

### I4: One Engine, Many Surfaces

The platform runs as a single coordinated system with **9 named operating nodes**, each role-specific but sharing the same backend:

| Node | Role | Type | Interface |
|---|---|---|---|
| **Titan Pro** | Owner/Director command centre | Filament Admin | Web |
| **Ground Zero** | Dispatcher/Operations real-time control | Filament Panel | Web |
| **Titan Go** | Field operator/Cleaner on-site execution | PWA | Mobile |
| **Zero Fuss** | Customer self-service portal | PWA | Mobile/Web |
| **Titan Zero** | AI orchestration surface + embedded intelligence | Chat + API | Conversational |
| **ZeroPay** | Financial engine — invoicing, payments, cashflow | PWA | Mobile/Web |
| **Titan Studio** | Growth, marketing, lead funnel | Filament Panel | Web |
| **Titan Solo** | Single-operator simplified mode | PWA | Mobile |
| **Titan Hello** | Omni-channel receptionist + lead intake | Background | Webhook |

**Implication:** Platform must not have surface-specific state. All surfaces query the same backend state.

### I5: Devices Are Nodes with Identity

A phone, tablet, desktop, kiosk, or browser session is a **node** with:
- Identity and capabilities
- Sync state and conflict resolution
- Optional local model/runtime (TitanEdge)
- Local cache and task queue
- Offline behavior and replay capability

The server treats device nodes as first-class participants in workflows, not just HTTP clients.

### I6: Modules Own Domain, Platform Owns Runtime

Strict separation of concerns:

| Platform Owns | Modules Own |
|---|---|
| Tenancy & tenant boundary | Domain data (jobs, customers, money) |
| Identity & authentication | Domain actions (schedule, dispatch, invoice) |
| Permissions & RBAC | Domain workflows & approvals |
| Core infrastructure | Domain events & state transitions |
| AI orchestration & gateway | Domain-specific AI tools |
| Signal dispatch & audit | Domain-specific notifications |
| Sync & offline runtime | Domain-specific reporting |
| Health & observability | Domain-specific metrics |
| PWA shell & navigation | Domain-specific UI surfaces |

Modules do NOT:
- Create users or manage identity
- Control tenancy
- Define global permissions
- Manage core infrastructure
- Run without platform services

Platform does NOT:
- Own business domain state
- Define domain workflows
- Create domain-specific AI prompts
- Emit domain-specific events
- Define domain metrics
- Own domain reporting

### I7: The System Degrades Gracefully

The system continues functioning when:
- Internet is poor or missing
- AI provider is unavailable
- Queue backlog exists
- Mobile device is offline
- Channel provider fails
- One subsystem is stale

This requires:
- Deliberate local caching strategies
- Replay and recovery mechanisms
- Sync envelopes for offline capture
- Fallback execution paths (local models, degraded UI)
- Background recovery and eventual consistency
- Timeout and circuit breaker patterns

---

## Design Rules & Boundaries

### Rule D1: Platform vs Module vs Filament

Three distinct layers, each with clear responsibilities:

#### Platform Layer
**What:** The shared runtime, governance, and orchestration infrastructure

**Owns:**
- Core bootstrapping and service registry
- Tenancy and identity resolution
- Permissions and capability gates
- Module discovery and lifecycle
- Signal dispatch and audit trail
- AI model routing and execution gateway
- Sync and offline runtime
- Communications infrastructure (channels, templates, routing)
- Automation engines (workflows, triggers, approvals)
- Health checks and observability
- PWA shell and navigation

**Does NOT own:**
- Domain business logic
- Domain data models
- Domain workflows
- Domain-specific AI tools
- Domain metrics or reporting

#### Module Layer
**What:** Business domain engines that implement industry-specific workflows

**Owns:**
- Domain data models (Jobs, Customers, etc.)
- Domain business rules and validation
- Domain actions and state transitions
- Domain workflows and approvals
- Domain-specific tools and capabilities
- Domain events and signals
- Domain-specific AI agents
- Domain API contracts

**Does NOT own:**
- Identity or authentication
- Tenancy enforcement
- Global permissions
- Core infrastructure
- Signal routing or audit
- AI model management

#### Filament Layer
**What:** Admin and operator control surfaces

**Owns:**
- Admin panel layout and resources
- Operator approval interfaces
- Dashboard composition
- Admin-specific workflows (setting configuration, policy definitions)
- Control surface permissions enforcement

**Does NOT own:**
- Business domain state
- Module data models
- Platform infrastructure
- User-facing surfaces (those are PWAs)

### Rule D2: Public SDK vs Internal Platform

#### Public SDK (Safe to Use)
- Event contracts and DTOs
- Signal envelopes and gates
- AI tool contracts
- Facade interfaces and helpers
- Navigation and permission helpers
- Sync and offline contracts
- Published interfaces in `Illuminate\Contracts\*` style

#### Internal Platform (Do Not Use Directly)
- TitanCore service implementations
- Private provider implementations
- Internal registry manipulation
- Direct AI model calls (use gateway instead)
- Direct database queries (use module services instead)

**Rule:** If it's not in the public SDK contract, don't depend on it. It will change.

### Rule D3: Layering & Dependency Flow

```
Modules → Platform Public SDK → Platform Services
         ↗                           ↑
AI / PWA / Filament → Platform API contracts → Platform
                    ↗              ↑
                   Signal Contracts Database
```

**Strict Rule:**
- Modules depend on Platform, not vice versa
- Platform does not import Module code
- All cross-module communication flows through Platform (signals, events, API)
- Direct module-to-module dependencies are forbidden
- Circular dependencies of any kind are forbidden

### Rule D4: Naming Conventions Reflect Architecture

**Namespaces:**
```
Platform\Core\*              — Platform kernel
Platform\Modules\*           — Module registry and lifecycle
Platform\Permissions\*       — Permission and capability system
Platform\Signals\*           — Signal dispatch and contracts
Platform\Ai\*                — AI orchestration
Platform\Communications\*    — Channel and message routing
Platform\Workflows\*         — State machines and approvals
Platform\Sync\*              — Device sync and offline
Platform\Observability\*     — Health and telemetry

Module\{Domain}\*            — Domain-specific modules (e.g., Module\Jobs\*)
Module\{Domain}\Services\*   — Business logic
Module\{Domain}\Models\*     — Data models
Module\{Domain}\Actions\*    — Discrete operations
Module\{Domain}\Tools\*      — AI-callable capabilities
Module\{Domain}\Events\*     — Domain events
Module\{Domain}\Jobs\*       — Queued work
Module\{Domain}\Api\*        — Module API contracts
Module\{Domain}\Filament\*   — Admin surfaces

Filament\{Surface}\*         — Surface-specific Filament (Admin, Operator)
```

**Routes:**
```
api/v1/{module}/...          — Module API
admin/...                    — Admin Filament
operator/...                 — Operator Filament
pwa/{node}/...               — PWA by node (go, studio, pay, etc.)
```

**Events/Jobs:**
```
{Module}\{Domain}\Events\{Event}Created
{Module}\{Domain}\Jobs\{Action}Job
{Module}\{Domain}\Notifications\{Type}Notification
```

---

## TitanCore Platform Kernel

The minimum viable platform infrastructure required for all systems to function.

### Kernel Responsibilities

```
TitanCore provides:

├─ Bootstrap & Registry
│  ├─ Service container bindings
│  ├─ Module discovery & lifecycle
│  ├─ Manifest loading & validation
│  └─ Provider initialization order
│
├─ Identity & Tenancy
│  ├─ Current actor resolution (user/device/AI)
│  ├─ Tenant context lookup
│  ├─ Trust context establishment
│  └─ Cross-tenant boundary validation
│
├─ Permissions & Capabilities
│  ├─ Permission cache & gates
│  ├─ Capability lookup from modules
│  ├─ Policy hooks and enforcement
│  └─ Role mapping and precedence
│
├─ Signal System
│  ├─ Signal intake & validation
│  ├─ Signal approval gates
│  ├─ Signal dispatch to listeners
│  ├─ Signal audit trail
│  └─ Signal replay for recovery
│
├─ AI Orchestration
│  ├─ Provider abstraction layer
│  ├─ Model routing decisions
│  ├─ Context packing
│  ├─ Tool registry & invocation
│  ├─ Token/cost tracking
│  └─ Execution gateway
│
├─ Automation & Workflows
│  ├─ Workflow state machine runtime
│  ├─ Guard evaluation
│  ├─ Approval coordination
│  ├─ Retry and recovery logic
│  └─ Compensation patterns
│
├─ Communications
│  ├─ Channel provider abstraction
│  ├─ Message template rendering
│  ├─ Inbound/outbound routing
│  ├─ Delivery tracking
│  └─ Consent & compliance
│
├─ Sync & Offline
│  ├─ Device envelope normalization
│  ├─ Conflict resolution
│  ├─ Replay and recovery
│  ├─ Local cache consistency
│  └─ Event backfeed
│
├─ Observability
│  ├─ Health check registry
│  ├─ Metrics collection
│  ├─ Diagnostic tools
│  ├─ Structured logging
│  └─ Telemetry pipeline
│
└─ PWA & Surface
   ├─ Shell contract
   ├─ Navigation registry
   ├─ Surface permissions
   └─ Offline manifest
```

### Core Services Published by Platform

```php
// Identity & Tenancy
app(ActorResolver::class)      // Current user/device/AI
app(TenantContext::class)       // Tenant scope
app(TrustContext::class)        // Trust signals

// Permissions
app(PermissionGate::class)      // Capability checking
app(CapabilityResolver::class)  // Discovery

// Signals
app(SignalDispatcher::class)    // Emit signals
app(SignalApprovalGate::class)  // Approval workflow

// AI
app(AiGateway::class)           // Model invocation
app(ModelRouter::class)         // Model selection
app(ToolRegistry::class)        // Tool lookup

// Workflows & Automation
app(WorkflowEngine::class)      // State machine
app(ApprovalCoordinator::class) // Approval flow
app(RetryEngine::class)         // Retry strategy

// Communications
app(OmniBridge::class)          // Channel abstraction
app(TemplateRenderer::class)    // Message rendering

// Sync
app(SyncEngine::class)          // Device sync
app(EnvelopeNormalizer::class)  // Sync protocol

// Observability
app(HealthRegistry::class)      // Health checks
app(MetricsCollector::class)    // Metrics
app(TitanDoctor::class)         // Diagnostics
```

### Invariant: Direct Platform Access is Forbidden

Modules must not:
```php
// ✗ Wrong: Direct AI model call
OpenAI::models()->list();

// ✓ Correct: Through platform gateway
app(AiGateway::class)->invoke('prompt', ['model' => 'sonnet']);

// ✗ Wrong: Direct channel send
Mail::send($view, $data, function ($m) { ... });

// ✓ Correct: Through platform bridge
app(OmniBridge::class)->send('email', ['to' => '...']);

// ✗ Wrong: Direct signal emit
event(new JobCreated($job));

// ✓ Correct: Through signal dispatcher
app(SignalDispatcher::class)->emit('job_created', ['job_id' => $id]);
```

**Why?** The platform needs to interpose for:
- Tenancy validation
- Audit and compliance
- Cost tracking
- Fallback handling
- Observability
- Governance

---

## Layering & Dependency Model

### Architectural Layers

```
Layer 6: User Surfaces
         ├─ Filament Admin (Titan Pro, Ground Zero, Titan Studio)
         ├─ PWAs (Titan Go, Zero Fuss, ZeroPay, Titan Solo)
         ├─ Omni Chat (Titan Hello)
         └─ APIs (External integrations)

Layer 5: Module Engines
         ├─ Job Management Module
         ├─ Customer Module
         ├─ Financial Module
         ├─ Communication Module
         ├─ Custom Vertical Modules
         └─ (Modules own domain workflows, events, tools)

Layer 4: Platform Services
         ├─ Signal Dispatch & Approval
         ├─ Workflow & Automation Runtime
         ├─ AI Orchestration & Gateway
         ├─ Communications Bridge
         ├─ Sync & Offline Runtime
         └─ Permissions & Identity

Layer 3: Platform Contracts & Facades
         ├─ Public SDK Interfaces
         ├─ Event/Signal DTOs
         ├─ AI Tool Contracts
         ├─ CMS Render Points
         ├─ PWA Shell Contracts
         └─ Health/Telemetry Contracts

Layer 2: Core Kernel
         ├─ Service Container & Registry
         ├─ Tenancy Resolver
         ├─ Module Lifecycle
         ├─ Manifest System
         └─ Bootstrap Sequence

Layer 1: Foundation (Laravel)
         ├─ HTTP / Queue / Scheduler
         ├─ Database & Migrations
         ├─ Authentication (at tenancy boundary)
         ├─ Configuration
         └─ Service Providers
```

### Dependency Rules

**Allowed:**
- Layer 5 → Layer 4: Modules use Platform Services
- Layer 5 → Layer 3: Modules use Public SDK
- Layer 4 → Layer 2: Platform uses Kernel
- Layer 4 → Layer 3: Platform implements Contracts
- Layer 3 → Layer 1: Contracts use Foundation

**Forbidden:**
- Layer 4 → Layer 5: Platform must not import Module code
- Layer 5 → Layer 5: Direct Module-to-Module imports
- Circular dependencies of any kind
- Layer 6 (Surfaces) directly importing business logic (must go through services)

**Principle:** The dependency graph must be a strict hierarchy. No cycles.

---

## Engineering Doctrine

These engineering practices ensure architecture resilience and developer productivity.

### E1: Thin Controllers, Fat Services

**Controllers** remain thin orchestration layers:
```php
class JobController
{
    public function store(StoreJobRequest $request, DispatchJobService $service)
    {
        // Validation happens in form request
        // Business logic happens in service
        $job = $service->dispatch($request->validated());
        return redirect()->route('jobs.show', $job);
    }
}
```

**Business logic** lives in Services and Actions:
```php
class DispatchJobService
{
    public function dispatch(array $data): Job
    {
        $job = Job::create($data);
        $this->validateTenantBoundary($job);
        $this->applyBusinessRules($job);
        $this->emitSignal($job);
        return $job;
    }
}
```

**AI, batch jobs, webhooks** all invoke the same service:
```php
// HTTP request
$service->dispatch($data);

// AI tool call
app(DispatchJobService::class)->dispatch($data);

// Queue job
DispatchJobService::dispatch($data);

// Webhook receiver
app(DispatchJobService::class)->dispatch($webhookData);
```

### E2: Clear Route Architecture

Routes define system topology. Route naming and structure must be explicit:

```php
// Web routes
Route::middleware('auth.tenant')->group(function () {
    Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
    Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');
});

// API routes (v1)
Route::prefix('api/v1')->middleware('api.token.tenant')->group(function () {
    Route::apiResource('jobs', JobApiController::class);
});

// Filament panels separate
Route::middleware('filament.admin')->group(function () {
    // Admin surfaces via Filament
});

// PWA routes
Route::prefix('pwa/go')->middleware('auth.device')->group(function () {
    Route::get('/jobs', [MobileJobController::class, 'index']);
});
```

**Rules:**
- Use named routes everywhere (never hardcoded URLs)
- Use route groups for middleware and prefix management
- Separate web/API/admin/PWA routes
- Use resource controllers where CRUD applies
- Prefix versions for APIs

### E3: Service Container Driven Design

Never hardcode implementations. Always use the container:

```php
// ✗ Wrong
$mailer = new SmtpMailer();
$mailer->send($message);

// ✓ Correct
app(OmniBridge::class)->send('email', $message);

// ✓ Also correct (with interface)
$this->mailer->send($message);

// In provider:
$this->app->bind(Mailer::class, SmtpMailer::class);
// Later, swap implementations:
$this->app->bind(Mailer::class, LocalMailer::class);
```

This allows:
- Swapping AI providers
- Switching channel drivers
- Local vs remote execution paths
- Fake implementations for testing
- Feature flagging

### E4: Performance by Default

- **Eager load** relationships predictably
- **Select only needed** columns
- **Remove dead** packages and unused features
- **Cache aggressively** where safe (Redis, file, memory)
- **Queue long-running** work (not HTTP request)
- **Monitor query** behavior (use tools, not guesses)
- **Index database** for known access patterns
- **Profile before optimizing** (collect data first)

### E5: Testability is Architecture

Testing is not an afterthought. Architecture must support:

- **Module feature tests** — Database state, workflows, domain logic
- **Tenant boundary tests** — Ensure tenant isolation, verify access control
- **Workflow tests** — State machine transitions, approval gates, compensation
- **API tests** — Contract validation, error handling, versioning
- **Sync/offline tests** — Replay, conflict resolution, device state
- **AI approval tests** — Tool invocation, safety gates, audit trails
- **Architecture tests** — Dependency rules, namespace boundaries

Each test type requires different setup. Make the system testable by making it modular.

---

## Design Laws

These design laws are extracted from architectural principles and serve as guardrails.

### L1: Business Logic Belongs in Modules and Services

Controllers coordinate requests. Services and Actions own business logic.

**Validation → Form Requests**
```php
class StoreJobRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'site_id' => 'required|exists:sites,id',
            'scheduled_at' => 'required|date|after:now',
        ];
    }
}
```

**Business Rules → Services**
```php
class DispatchJobService
{
    public function canDispatch(Job $job): bool
    {
        return $job->hasAssignedCrew() &&
               $job->siteIsAccessible() &&
               $job->customIsNotBlacklisted();
    }
}
```

**Controllers → Orchestration**
```php
public function store(StoreJobRequest $request, DispatchJobService $service)
{
    $job = $service->dispatch($request->validated());
    return response()->json($job, 201);
}
```

### L2: Modules are the Business Organs

The platform provides infrastructure. Modules provide business value.

**Platform provides:**
- Tenancy
- Authentication
- Permissions
- Settings
- Observability
- AI orchestration
- Signal dispatch

**Modules provide:**
- Domain data models
- Domain workflows
- Domain AI agents
- Domain events
- Domain reporting
- Domain integrations

### L3: AI is Supervisory, Never Hidden

AI augments human decision-making. It does not replace human judgment on critical operations.

**Supervisory AI Pattern:**
```php
// AI interprets intent
$intent = app(AiGateway::class)->analyze($userMessage);

// AI gathers context
$context = $this->buildContext($intent);

// AI proposes action
$proposal = app(AiGateway::class)->propose($intent, $context);

// If confidence < threshold OR risk > threshold:
// AI requests approval
$approval = app(ApprovalCoordinator::class)->request($proposal);

// Wait for human approval
if (!$approval->approved()) {
    return response()->json(['status' => 'awaiting_approval']);
}

// Execute approved action
$result = $service->execute($proposal);

// Log decision trail
app(SignalDispatcher::class)->emit('job_dispatched', [
    'user_intent' => $intent,
    'ai_proposal' => $proposal,
    'approval_required' => true,
    'execution' => $result,
]);
```

### L4: PWAs are First-Class Products

Each surface is a role-specific operating node over the same governed system.

- Titan Pro (Admin): Full control, configuration, reporting
- Ground Zero (Dispatcher): Real-time dispatch, rapid decisions
- Titan Go (Field): On-site execution, offline capability
- Zero Fuss (Customer): Self-service access, limited scope
- Titan Zero (AI): Conversational control, orchestration
- ZeroPay (Finance): Invoicing, payment processing
- Titan Studio (Marketing): Growth, lead management
- Titan Solo (Solo Operator): Simplified mode, single person
- Titan Hello (Receptionist): Omni-channel intake

**Not separate businesses. Same backend. Different roles.**

### L5: Devices are First-Class Nodes

A mobile device, tablet, or kiosk is a participant in the system with:
- Identity
- Capabilities
- Sync state
- Conflict resolution
- Offline behavior
- Task queue

The server treats devices as peers, not just HTTP clients.

### L6: Tenant Boundary is Sacred

This is the nuclear option of architecture.

Every single piece of code must respect `company_id` as the tenant boundary:
- No cross-tenant queries
- No cross-tenant workflows
- No cross-tenant AI context
- No cross-tenant sync
- No cross-tenant signals

**Tenant boundary violations are critical bugs**, not edge cases.

### L7: The System Must Degrade Gracefully

Assume the network will fail. Assume AI will be unavailable. Assume queues will back up.

**Design for graceful degradation:**
- **Offline first** — Local cache, local execution, sync when possible
- **Fallback paths** — Local LLMs when cloud unavailable, simplified UI when features missing
- **Circuit breakers** — Stop calling failed services, fail fast
- **Retry with backoff** — Respect service limits and degradation
- **Eventual consistency** — Dont require immediate sync
- **Supervisor trees** — Process crash restarts without losing state

---

## Key Principles by Domain

### Signals & Events

**What:** The nervous system of the platform. Signals represent operational state changes.

**Principles:**
- Signals flow FROM modules TO platform (never from platform TO modules)
- Every signal has a canonical shape defined in contracts
- Signals can be approved, routed, logged, and replayed
- Signals trigger workflows, automations, and notifications
- Signals are immutable; they represent what happened, not what should happen

**Pattern:**
```php
// Module emits signal
app(SignalDispatcher::class)->emit('job_dispatched', [
    'job_id' => $job->id,
    'crew_ids' => $job->crew_ids,
    'site_id' => $job->site_id,
    'scheduled_at' => $job->scheduled_at,
]);

// Platform handles signal
SignalDispatcher::listen('job_dispatched', function ($signal) {
    // Trigger notification to customer
    // Update dispatch state
    // Log audit trail
    // Invoke AI context updates
});
```

### Workflows & Approvals

**What:** State machine runtime for multi-step business processes.

**Principles:**
- Every workflow is a state machine (states + transitions + guards)
- Approval gates protect critical transitions
- Guardrails evaluate before transition (cost, tenant, permission)
- Compensation defines rollback behavior
- Workflows are auditable and resumable

### AI & Model Routing

**What:** Model abstraction and routing for tenant-safe AI execution.

**Principles:**
- Never call AI providers directly; use AiGateway
- Model selection is deterministic (based on task type and context)
- Context packing normalizes prompt structure
- Tool invocation goes through ToolRegistry
- Every AI decision is logged with decision factors

### Communications & Omni

**What:** Unified interface over multiple channels (SMS, WhatsApp, Email, Push, Voice).

**Principles:**
- Every channel speaks the same message shape (OmniBridge)
- Routing is deterministic (channel preference → fallback → human review)
- Delivery is tracked and retried
- Inbound messages are normalized and routed to appropriate handlers
- Consent and compliance is enforced per channel

### Sync & Offline

**What:** Device node synchronization and offline-first design.

**Principles:**
- Devices hold local cache of their relevant data
- Changes are captured in envelopes (delta, not full sync)
- Conflicts are resolved deterministically (server wins, or custom resolution)
- Devices can replay operations when offline
- Eventually consistent; full sync occurs periodically

---

## Architecture Decision: Modules-as-Engines

Titan BOS moved from a module-era architecture to an **engine-based architecture**.

### What Changed

| Module Era | Engine Era |
|---|---|
| Modules = feature packages | Engines = business domain runtimes |
| Modules added features to dashboard | Engines coordinate business workflows |
| Module-to-module imports allowed | Cross-engine communication via signals only |
| Monolithic core | Platform kernel + pluggable engines |
| Database-centric | Signal-centric |

### Why This Matters

**Engines enable:**
- Clear separation of domain logic
- Parallel development (teams own engines)
- Testable workflows
- AI-orchestrated automation
- Extensibility without core changes
- Role-specific surfaces (PWAs)

### Migration Path

Existing modules are being converted to engine model:
1. Extract domain logic into service layer
2. Define engine.json manifest
3. Emit signals instead of events
4. Register tools instead of controllers
5. Define AI contracts
6. Build role-specific PWA surfaces

---

## Summary: Architecture DNA

Titan BOS architecture is defined by these 7 facts:

1. **Zero Friction** — Every design removes operational stress
2. **Tenant Boundary** — Sacred, enforced everywhere
3. **Signal-Driven** — Modules emit, platform routes, surfaces consume
4. **One Backend, Many Surfaces** — 9 nodes over same governed system
5. **AI is Supervisory** — Proposes, requests approval, executes, logs
6. **Devices are Nodes** — Offline-first, sync when possible
7. **Engines not Modules** — Domain runtimes, not feature packages

Every code change should validate against this DNA.

---

## Glossary

| Term | Definition |
|---|---|
| **Platform** | Shared runtime providing tenancy, identity, permissions, signals, AI, workflows, communications, sync |
| **Engine** | Business domain runtime (Jobs, Customers, Finance) that owns state, workflows, and AI agents |
| **Module** | (Legacy) Feature package; now called Engine |
| **TitanCore** | The platform kernel providing bootstrap, registry, manifest system |
| **Signal** | Event emitted by engine representing state change; can be approved, routed, audited |
| **Workflow** | State machine defining multi-step process with guards and approvals |
| **Tool** | AI-callable capability provided by engine with manifest and safety gates |
| **Node** | Role-specific surface (Titan Pro, Titan Go, Zero Fuss, etc.) |
| **Omni Bridge** | Platform abstraction unifying SMS, WhatsApp, Email, Push, Voice into single interface |
| **Tenant** | Company boundary; all data scoped by company_id |
| **Actor** | Current user, device, or AI requesting action |
| **Envelope** | Serialized sync packet (device-to-server or server-to-device) |
| **Sync** | Device cache synchronization using envelopes |

---

## Next Steps

This constitution establishes the architectural foundation. Each subsequent volume implements specific domains:

- **Volume 2:** Engine Standards (structure, lifecycle, registration)
- **Volume 3:** TitanSDK & Public Contracts (safe consumption)
- **Volume 4:** Platform Studios (governance surfaces)
- **Volume 5:** Filament & UI Standards (admin interface)
- **Volume 6:** Workflow & Automation (state machines)
- **Volume 7:** AI & Model Standards (provider abstraction)
- **Volume 8:** Communications & Omni (channel routing)
- **Volume 9:** UI & Dashboards (9-node surfaces)
- **Volume 10:** Enterprise & Ops (health, telemetry, deployment)
- **Volume 11:** Developer Standards (coding, testing, quality)
- **Volume 12:** Blueprint Library (templates and starters)

---

**Status:** ✓ TEDK Volume 1 Complete  
**Version:** 1.0  
**Authority:** Platform Architecture Standard  
**Use:** Reference for all Titan BOS development
