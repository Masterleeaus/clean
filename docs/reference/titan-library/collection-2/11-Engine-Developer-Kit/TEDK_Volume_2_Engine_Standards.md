# TEDK Volume 2: Engine Standards
## The Authoritative Framework for Building Engines in Titan BOS

**Status:** Canonical - Automation Runtime  
**Version:** 1.0  
**Last Updated:** 2026  
**Audience:** Engine developers, module architects, automation specialists

⸻

## Table of Contents

1. [What is an Engine?](#what-is-an-engine)
2. [Engine Architecture & Lifecycle](#engine-architecture--lifecycle)
3. [Module Structure](#module-structure)
4. [Engine Manifest (module.json)](#engine-manifest-modulejson)
5. [Registration & Discovery](#registration--discovery)
6. [Engine Permissions Model](#engine-permissions-model)
7. [Engine Settings & Configuration](#engine-settings--configuration)
8. [Health Checks & Telemetry](#health-checks--telemetry)
9. [Engine Lifecycle Patterns](#engine-lifecycle-patterns)
10. [Reusable Engine Templates](#reusable-engine-templates)

---

## What is an Engine?

An **Engine** is a domain-specific runtime that orchestrates business workflows and coordinates operational state.

### Engine vs Module

| Aspect | Module (Legacy) | Engine (New) |
|---|---|---|
| Purpose | Feature package | Business domain runtime |
| Scope | UI features + models | Complete domain orchestration |
| Responsibilities | CRUD operations | Workflows, automation, coordination |
| Communication | Direct imports | Signals and events only |
| AI Integration | None | First-class (tools, agents, decision) |
| Surfaces | Admin only | Multiple role-specific nodes |
| Lifecycle | Static | Dynamic, workflow-driven |

### Engine Goals

Every engine should:
- **Turn CRUD into operation** — Transform raw data into coordinated workflows
- **Own its domain completely** — No cross-engine logic bleeding
- **Emit clear signals** — Other engines react, they don't initiate
- **Provide AI tools** — Make domain actions available to AI orchestration
- **Support multiple surfaces** — Admin, field operator, customer, AI, API
- **Enable offline operation** — Key actions work without connectivity
- **Be auditable** — Every change is traceable and reviewable
- **Scale with tenancy** — Tenant isolation is mandatory

---

## Engine Architecture & Lifecycle

### Full Engine Structure

```
Modules/{EngineNam}/
├─ module.json                    ← Engine manifest (required)
├─ version.txt
├─ README.md
├─ CHANGELOG.md
│
├─ Config/                         ← Configuration
│  ├─ config.php                   (settings schema and defaults)
│  ├─ features.php                 (feature gates)
│  ├─ permissions.php              (capabilities this engine owns)
│  ├─ navigation.php               (sidebar/menu entries)
│  ├─ package.php                  (feature packages)
│  └─ ai.php                       (AI tool configuration)
│
├─ Providers/                      ← Service registration
│  ├─ {EngineNam}ServiceProvider.php
│  ├─ RouteServiceProvider.php     (HTTP routes)
│  ├─ EventServiceProvider.php     (Events/listeners)
│  ├─ FilamentServiceProvider.php  (Admin integration)
│  └─ ModuleBootServiceProvider.php (Startup sequence)
│
├─ Routes/                         ← HTTP endpoints
│  ├─ web.php                      (Web HTML routes)
│  ├─ api.php                      (API v1 endpoints)
│  ├─ admin.php                    (Admin panel routes)
│  ├─ user.php                     (User/customer routes)
│  └─ pwa/                         (PWA routes by node)
│      ├─ go.php                   (Field operator: Titan Go)
│      ├─ studio.php               (Marketing: Titan Studio)
│      ├─ pay.php                  (Finance: ZeroPay)
│      └─ ...
│
├─ Database/                       ← Schema
│  ├─ Migrations/
│  ├─ Seeders/
│  └─ factories/
│
├─ Models/                         ← Domain entities
│  ├─ Job.php
│  ├─ Customer.php
│  └─ ...
│
├─ Services/                       ← Business logic
│  ├─ JobService.php
│  ├─ DispatchService.php
│  └─ ...
│
├─ Actions/                        ← Atomic operations
│  ├─ CreateJobAction.php
│  ├─ DispatchJobAction.php
│  └─ ...
│
├─ Events/                         ← Domain signals
│  ├─ JobCreated.php
│  ├─ JobDispatched.php
│  └─ ...
│
├─ Listeners/                      ← Event reactions
│  ├─ SendJobNotification.php
│  ├─ UpdateDispatchState.php
│  └─ ...
│
├─ Jobs/                           ← Queued work
│  ├─ ProcessJobPaymentJob.php
│  ├─ SendJobCompletionSurvey.php
│  └─ ...
│
├─ Workflows/                      ← Multi-step processes
│  ├─ JobLifecycleWorkflow.php
│  ├─ DisputeResolutionWorkflow.php
│  └─ ...
│
├─ Http/                           ← HTTP layer
│  ├─ Controllers/
│  │  ├─ Api/
│  │  ├─ Web/
│  │  └─ Admin/
│  ├─ Requests/                    (validation)
│  └─ Resources/                   (API response shaping)
│
├─ Filament/                       ← Admin UI
│  ├─ Pages/
│  ├─ Resources/
│  └─ Widgets/
│
├─ Manifests/                      ← Discovery declarations
│  ├─ ai_tools.json                (AI-callable tools)
│  ├─ signals_manifest.json        (Domain signals)
│  ├─ lifecycle_manifest.json      (State machine)
│  ├─ cms_manifest.json            (CMS rendering)
│  ├─ omni_manifest.json           (Channel integration)
│  ├─ api_manifest.json            (API contracts)
│  └─ permissions_manifest.json    (Capabilities)
│
└─ Tests/                          ← Quality assurance
   ├─ Feature/
   ├─ Unit/
   ├─ Integration/
   └─ Support/
```

### Engine Lifecycle

```
1. Discovery Phase
   └─ Platform finds module.json
   └─ Platform validates manifest schema
   └─ Platform checks dependencies

2. Boot Phase
   ├─ ServiceProviders register services
   ├─ Routes load
   ├─ Events/listeners wired
   ├─ Filament integration loads
   └─ Database migrations check (auto-migrate or manual)

3. Registration Phase
   ├─ Platform discovers engine capabilities
   ├─ AI tools are indexed
   ├─ Signals are registered
   ├─ Permissions are granted
   ├─ Navigation entries appear
   └─ Settings are available

4. Runtime Phase
   ├─ Workflows execute
   ├─ Events emit signals
   ├─ AI tools are invoked
   ├─ Queue jobs process
   └─ Sync envelopes flow

5. Observability Phase
   ├─ Health checks run
   ├─ Metrics collected
   ├─ Logs structured
   ├─ Diagnostics available
   └─ Doctor tool inspects
```

---

## Module Structure

### Strict Structure Rules

Every engine follows this structure. Deviation requires architectural review.

#### Config/ — Configuration & Declaration

**config.php** — Settings schema, defaults, feature flags
```php
return [
    'domain' => 'jobs',
    'version' => '1.0.0',
    
    // Settings that admins can configure
    'settings' => [
        'max_jobs_per_crew' => env('JOBS_MAX_PER_CREW', 8),
        'dispatch_timeout_minutes' => env('JOBS_DISPATCH_TIMEOUT', 30),
        'auto_assign_enabled' => env('JOBS_AUTO_ASSIGN', false),
    ],
    
    // Feature gates
    'features' => [
        'ai_dispatch' => true,
        'offline_dispatch' => true,
        'bulk_operations' => true,
    ],
];
```

**permissions.php** — Capabilities this engine owns
```php
return [
    'jobs.view' => 'View jobs',
    'jobs.create' => 'Create jobs',
    'jobs.edit' => 'Edit jobs',
    'jobs.delete' => 'Delete jobs',
    'jobs.dispatch' => 'Dispatch jobs',
    'jobs.approve_dispatch' => 'Approve job dispatch',
];
```

**navigation.php** — Sidebar/menu entries by role
```php
return [
    'admin' => [
        [
            'label' => 'Jobs',
            'icon' => 'heroicon-o-briefcase',
            'route' => 'admin.jobs.index',
            'children' => [
                ['label' => 'All Jobs', 'route' => 'admin.jobs.index'],
                ['label' => 'Pending Dispatch', 'route' => 'admin.jobs.pending'],
            ],
        ],
    ],
    'operator' => [
        ['label' => 'My Jobs', 'route' => 'operator.jobs.assigned'],
    ],
];
```

**ai.php** — AI tool configuration
```php
return [
    'tools' => [
        'dispatch_job' => [
            'class' => DispatchJobTool::class,
            'requires_approval' => true,
            'risk_class' => 'critical',
        ],
        'suggest_alternatives' => [
            'class' => SuggestAlternativesTool::class,
            'requires_approval' => false,
            'risk_class' => 'low',
        ],
    ],
];
```

#### Routes/ — HTTP Endpoints

**web.php** — HTML forms and views
```php
Route::middleware('auth.tenant')->group(function () {
    Route::resource('jobs', JobController::class);
    Route::post('jobs/{id}/dispatch', [JobController::class, 'dispatch'])
        ->name('jobs.dispatch');
});
```

**api.php** — REST API endpoints
```php
Route::prefix('api/v1')->middleware('api.token.tenant')->group(function () {
    Route::apiResource('jobs', JobApiController::class);
    Route::post('jobs/{id}/dispatch', [JobApiController::class, 'dispatch']);
});
```

**pwa/go.php** — Field operator mobile (Titan Go)
```php
Route::middleware('auth.device.tenant')->prefix('pwa/go')->group(function () {
    Route::get('/jobs/assigned', [MobileJobController::class, 'assigned']);
    Route::post('/jobs/{id}/complete', [MobileJobController::class, 'complete']);
    Route::post('/jobs/{id}/report-issue', [MobileJobController::class, 'reportIssue']);
});
```

#### Services/ — Business Logic

**Never put business logic in controllers.**

```php
namespace Modules\Jobs\Services;

class JobService
{
    public function __construct(
        private JobRepository $jobs,
        private WorkflowEngine $workflows,
    ) {}
    
    public function dispatch(Job $job, array $data): Job
    {
        // Validate tenancy
        $this->validateTenant($job);
        
        // Apply business rules
        if (!$this->canDispatch($job)) {
            throw new CannotDispatchException('Job not ready');
        }
        
        // Perform state transition
        $job->update(['status' => 'dispatched']);
        
        // Emit signal
        event(new JobDispatched($job));
        
        // Trigger downstream workflows
        $this->workflows->transition('job_lifecycle', $job->id, 'dispatch');
        
        return $job->refresh();
    }
}
```

#### Actions/ — Atomic Operations

**One action, one responsibility.**

```php
namespace Modules\Jobs\Actions;

class DispatchJobAction
{
    public function __construct(
        private WorkflowEngine $workflows,
        private SignalDispatcher $signals,
    ) {}
    
    public function execute(Job $job, Crew $crew): void
    {
        // One atomic operation
        $job->update([
            'crew_id' => $crew->id,
            'status' => 'dispatched',
            'dispatched_at' => now(),
        ]);
        
        // Emit signal (causes workflows to react)
        $this->signals->emit('job_dispatched', [
            'job_id' => $job->id,
            'crew_id' => $crew->id,
        ]);
    }
}
```

#### Events & Listeners — Reactive Patterns

**Events signal domain state changes. Listeners react.**

```php
namespace Modules\Jobs\Events;

class JobDispatched
{
    public function __construct(public Job $job) {}
}
```

```php
namespace Modules\Jobs\Listeners;

class SendJobToMobileListener
{
    public function __construct(private OmniBridge $omni) {}
    
    public function handle(JobDispatched $event): void
    {
        // React to job dispatch signal
        $this->omni->send('sms', [
            'to' => $event->job->crew->phone,
            'message' => "Job {$event->job->id} assigned to you",
        ]);
    }
}
```

Register in EventServiceProvider:
```php
protected $listen = [
    JobDispatched::class => [
        SendJobToMobileListener::class,
        UpdateDispatchDashboard::class,
    ],
];
```

#### Workflows/ — Multi-Step Processes

**Turn sequences into automatable workflows.**

```php
namespace Modules\Jobs\Workflows;

class JobLifecycleWorkflow
{
    public function definition(): array
    {
        return [
            'name' => 'job_lifecycle',
            'initial' => 'created',
            'states' => [
                'created',
                'quoted',
                'approved',
                'scheduled',
                'dispatched',
                'in_progress',
                'completed',
                'invoiced',
            ],
            'transitions' => [
                'create' => ['created' => 'created'],
                'quote' => ['created' => 'quoted'],
                'approve' => ['quoted' => 'approved'],
                'schedule' => ['approved' => 'scheduled'],
                'dispatch' => ['scheduled' => 'dispatched'],
                'start' => ['dispatched' => 'in_progress'],
                'complete' => ['in_progress' => 'completed'],
                'invoice' => ['completed' => 'invoiced'],
            ],
            'guards' => [
                'dispatch' => CanDispatchGuard::class,
                'complete' => CanCompleteGuard::class,
            ],
        ];
    }
}
```

---

## Engine Manifest (module.json)

Every engine requires a `module.json` manifest. This declares the engine's identity and capabilities without requiring runtime introspection.

### Required Fields

```json
{
  "name": "Jobs Engine",
  "key": "jobs",
  "description": "Orchestrates job creation, dispatch, execution, and completion",
  "version": "1.0.0",
  "type": "engine",
  
  "author": "Titan Team",
  "license": "proprietary",
  
  "requires": {
    "platform": ">=1.0.0",
    "laravel": ">=11.0"
  },
  
  "dependencies": [
    "platform/core",
    "modules/customers"
  ],
  
  "provides": {
    "domain": "jobs",
    "models": [
      "Modules\\Jobs\\Models\\Job",
      "Modules\\Jobs\\Models\\Crew"
    ],
    "services": [
      "job_service",
      "dispatch_service"
    ],
    "events": [
      "job_created",
      "job_dispatched",
      "job_completed"
    ]
  },
  
  "capabilities": [
    "jobs.view",
    "jobs.create",
    "jobs.edit",
    "jobs.delete",
    "jobs.dispatch",
    "jobs.approve_dispatch"
  ],
  
  "surfaces": [
    "admin",           // Filament admin
    "operator",        // Operator filament
    "field",           // Titan Go PWA
    "api",             // REST API
    "ai"               // AI agent tools
  ],
  
  "workflows": [
    {
      "key": "job_lifecycle",
      "states": ["created", "quoted", "approved", "scheduled", 
                 "dispatched", "in_progress", "completed", "invoiced"],
      "requires_approval": ["dispatch", "invoice"]
    }
  ],
  
  "ai_tools": [
    {
      "key": "dispatch_job",
      "name": "Dispatch Job to Crew",
      "description": "Dispatch a scheduled job to an available crew",
      "requires_approval": true,
      "risk_class": "critical"
    },
    {
      "key": "suggest_alternatives",
      "name": "Suggest Alternative Times",
      "description": "Suggest alternative times for a customer if requested time unavailable",
      "requires_approval": false,
      "risk_class": "low"
    }
  ],
  
  "features": {
    "offline_dispatch": {
      "enabled": true,
      "description": "Allow field operators to dispatch jobs offline"
    },
    "ai_optimization": {
      "enabled": true,
      "description": "Use AI to optimize crew assignments"
    }
  },
  
  "settings": {
    "max_jobs_per_crew": {
      "type": "integer",
      "default": 8,
      "label": "Maximum jobs per crew per day"
    },
    "auto_assign_enabled": {
      "type": "boolean",
      "default": false,
      "label": "Enable automatic crew assignment"
    }
  },
  
  "database": {
    "tables": ["jobs", "job_assignments", "crew"],
    "migrations": "database/migrations"
  },
  
  "permissions": "config/permissions.php",
  "navigation": "config/navigation.php",
  "ai": "config/ai.php",
  
  "manifests": {
    "ai_tools": "manifests/ai_tools.json",
    "signals": "manifests/signals_manifest.json",
    "lifecycle": "manifests/lifecycle_manifest.json",
    "cms": "manifests/cms_manifest.json",
    "omni": "manifests/omni_manifest.json",
    "api": "manifests/api_manifest.json",
    "pwa": "manifests/pwa_contract.json"
  }
}
```

### Manifest Validation Rules

- `name` — Human-readable, 2-50 characters
- `key` — Machine name, lowercase, no spaces, globally unique
- `version` — Semantic versioning (MAJOR.MINOR.PATCH)
- `requires` — Platform and Laravel versions
- `dependencies` — Other engines this requires
- `surfaces` — Which surfaces this engine provides
- `workflows` — State machines defined by this engine
- `ai_tools` — Tools available to AI orchestration
- `features` — Feature gates for experimental functionality

---

## Registration & Discovery

### Bootstrap Sequence

When the platform boots, it:

```
1. Load manifest from every engine
2. Validate manifest schema
3. Check dependencies are available
4. Register ServiceProviders
5. Load routes in order
6. Wire events/listeners
7. Load Filament integration
8. Discover capabilities
9. Index AI tools
10. Validate permissions
11. Run health checks (non-blocking)
```

### ServiceProvider Registration

Every engine has a primary ServiceProvider:

```php
namespace Modules\Jobs\Providers;

class JobsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind services into container
        $this->app->singleton(JobService::class, function ($app) {
            return new JobService(
                $app->make(JobRepository::class),
                $app->make(WorkflowEngine::class),
            );
        });
    }
    
    public function boot(): void
    {
        // Load configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'jobs');
        
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/pwa/go.php');
        
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        
        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'jobs');
        
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config' => config_path('jobs'),
        ], 'jobs-config');
    }
}
```

### Capability Discovery

Platform automatically discovers what each engine can do:

```php
// From manifest
app(CapabilityResolver::class)->for('jobs');
// Returns: ['jobs.view', 'jobs.create', 'jobs.edit', 'jobs.delete', 'jobs.dispatch', ...]

// From AI tools
app(ToolRegistry::class)->for('jobs');
// Returns: [DispatchJobTool, SuggestAlternativesTool, ...]

// From workflows
app(WorkflowRegistry::class)->for('jobs');
// Returns: [JobLifecycleWorkflow, DisputeResolutionWorkflow, ...]
```

---

## Engine Permissions Model

Engines declare capabilities they own. Platform enforces them.

### Permission Declaration (config/permissions.php)

```php
return [
    // Capability => Label
    'jobs.view' => 'View jobs',
    'jobs.create' => 'Create jobs',
    'jobs.edit' => 'Edit jobs',
    'jobs.delete' => 'Delete jobs',
    'jobs.dispatch' => 'Dispatch jobs',
    'jobs.approve_dispatch' => 'Approve job dispatch (critical)',
    'jobs.bulk_delete' => 'Bulk delete jobs',
    'jobs.export' => 'Export job data',
];
```

### Permission Enforcement

```php
// Gate check
if (!Gate::allows('jobs.dispatch', $job)) {
    throw AuthorizationException('Not authorized to dispatch');
}

// In authorization policy
class JobPolicy
{
    public function dispatch(User $user, Job $job): bool
    {
        return $user->can('jobs.dispatch') &&
               $job->tenant_id === $user->tenant_id;
    }
}

// In request
class DispatchJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('jobs.dispatch', $this->job);
    }
}
```

### Permission Precedence

1. **Explicit deny** — Denied at any level, deny everywhere
2. **Role-based** — Assigned via role
3. **Context-based** — Specific resource authorization (policy)
4. **Feature gate** — Check feature flags

---

## Engine Settings & Configuration

### Configuration Sources (Priority Order)

1. **Environment variables** — Override everything
2. **Database settings** (per-tenant) — Runtime settings
3. **config/{module}.php** — Application defaults
4. **module.json** — Engine declarations

### Settings Schema

Define in `config/settings.php`:

```php
return [
    'dispatch' => [
        'max_jobs_per_crew' => [
            'type' => 'integer',
            'min' => 1,
            'max' => 100,
            'default' => 8,
            'label' => 'Maximum jobs per crew per day',
            'help' => 'Prevents crew overallocation',
        ],
        'dispatch_window_minutes' => [
            'type' => 'integer',
            'default' => 30,
            'label' => 'Dispatch notification window',
            'help' => 'Minutes before scheduled time to notify crew',
        ],
        'auto_assign_enabled' => [
            'type' => 'boolean',
            'default' => false,
            'label' => 'Enable automatic crew assignment',
        ],
    ],
    'notifications' => [
        'dispatch_channel' => [
            'type' => 'select',
            'options' => ['sms', 'whatsapp', 'push', 'email'],
            'default' => 'sms',
            'label' => 'Preferred dispatch notification channel',
        ],
    ],
];
```

### Settings Access

```php
// Tenant-scoped setting
$max_per_crew = SettingService::for($tenant)->get('jobs.dispatch.max_jobs_per_crew');

// Override for specific tenant
SettingService::for($tenant)->set('jobs.dispatch.max_jobs_per_crew', 12);

// Fallback to config default
$value = config('jobs.dispatch.max_jobs_per_crew', 8);
```

---

## Health Checks & Telemetry

### Engine Health Checks

Register in ServiceProvider:

```php
app(HealthRegistry::class)->register('jobs', [
    new DatabaseTableExists('jobs'),
    new MigrationsPending('jobs'),
    new QueueHealthCheck(),
    new ApiEndpointHealthCheck(),
]);
```

### Custom Health Checks

```php
namespace Modules\Jobs\Health;

class DispatchTimelinessHealthCheck extends HealthCheck
{
    public function name(): string
    {
        return 'jobs.dispatch.timeliness';
    }
    
    public function check(): HealthStatus
    {
        $late = Job::where('status', 'dispatched')
            ->where('scheduled_at', '<', now()->subHours(2))
            ->count();
        
        if ($late > 10) {
            return HealthStatus::failing(
                "Too many jobs dispatched late: {$late}"
            );
        }
        
        return HealthStatus::ok();
    }
}
```

### Telemetry Metrics

```php
// Register metrics
app(MetricsCollector::class)->register('jobs', [
    'total_jobs_created',
    'jobs_dispatched',
    'average_dispatch_time_minutes',
    'crew_utilization_percent',
    'cancellation_rate',
]);

// Emit metrics
app(MetricsCollector::class)->increment('jobs.created');
app(MetricsCollector::class)->gauge('jobs.crew_utilization', 85.5);
```

---

## Engine Lifecycle Patterns

### Creation Workflow

```php
// Trigger: User submits form
$job = Job::create($validated);

// Fire event (immediate reactions)
event(new JobCreated($job));

// Emit signal (platform-level reactions, workflows)
app(SignalDispatcher::class)->emit('job_created', [
    'job_id' => $job->id,
    'customer_id' => $job->customer_id,
]);

// Queue long-running work
SendJobCreationConfirmation::dispatch($job);

// Trigger workflow state machine
app(WorkflowEngine::class)->initialize('job_lifecycle', $job->id);
```

### Approval Workflow

```php
// Suggest an action
$proposal = [
    'action' => 'dispatch_job',
    'job_id' => $job->id,
    'crew_id' => $crew->id,
    'confidence' => 0.95,
];

// If high confidence, execute auto-mode
if ($proposal['confidence'] > 0.9) {
    app(DispatchJobService::class)->dispatch($job, $crew);
} else {
    // Request approval
    app(ApprovalCoordinator::class)->request(
        actor: $user,
        action: $proposal['action'],
        subject: $job,
        reasoning: "Confidence {$proposal['confidence']}: Best available crew",
        mode: 'review_queue'
    );
}
```

### Compensation & Rollback

```php
// Define compensation (what to do if workflow fails)
class JobDispatchedCompensation
{
    public function __invoke(Job $job): void
    {
        // Reverse the dispatch
        $job->update(['status' => 'scheduled', 'crew_id' => null]);
        
        // Notify stakeholders
        event(new JobDispatchCancelled($job));
        
        // Emit signal for platform handling
        app(SignalDispatcher::class)->emit('job_dispatch_cancelled', [
            'job_id' => $job->id,
        ]);
    }
}

// Attach to workflow
$workflow->compensation('dispatch', JobDispatchedCompensation::class);
```

---

## Reusable Engine Templates

### Pattern 1: CRUD + Approval

For engines managing resources with approval gates:

```php
// Models
- Resource (main entity)
- ResourceApproval (approval tracking)

// Workflows
- creation_workflow (create → pending → approved → active)
- deletion_workflow (active → deletion_pending → deleted)

// Services
- CreateResourceService (create with initial state)
- ApproveResourceService (move to approved)
- DeleteResourceService (with compensation)

// AI Tools
- suggest_{resource} (AI proposes new resource)
- approve_{resource} (AI approves pending)
```

### Pattern 2: Event-Driven Cascade

For engines orchestrating multiple downstream actions:

```php
// Model
- Event (main record)

// Lifecycle
- created → listeners queue jobs

// Jobs
- NotifyStakeholders
- UpdateRelatedRecords
- TriggerDownstreamWorkflows

// Signals
- Emit when complete (other engines react)
```

### Pattern 3: State Machine Workflow

For engines with complex multi-step processes:

```php
// Workflow Definition
- States: initial → pending → processing → completed
- Guards: canTransition() checks
- Compensation: rollback on failure

// Stores runtime state
- current_state
- state_history
- timestamp_of_transition

// Supports approval
- requires_approval for critical transitions
```

### Pattern 4: AI-Orchestrated Automation

For engines that AI systems frequently control:

```php
// Service Layer
- Service::suggest($context): Proposal
- Service::execute(Proposal): Result

// AI Tools
- tool_{action} (defined in ai_tools.json)
- risk_class: critical, high, medium, low
- requires_approval: true/false

// Audit
- Every execution logged with AI reasoning
```

---

## Engine Implementation Checklist

Before declaring an engine complete:

- [ ] `module.json` complete and validated
- [ ] ServiceProvider registers all services
- [ ] All routes load (web, api, pwa, admin)
- [ ] Migrations create all tables
- [ ] Models defined and relationships correct
- [ ] Services own all business logic
- [ ] Controllers are thin (route → service)
- [ ] Actions are atomic (one responsibility)
- [ ] Events/listeners defined (reactive patterns)
- [ ] Workflows defined (state machines)
- [ ] Permissions declared (capabilities)
- [ ] Settings schema defined
- [ ] Navigation entries configured
- [ ] AI tools declared (manifest + classes)
- [ ] Manifests generated (all 7 types)
- [ ] Filament integration present
- [ ] Health checks registered
- [ ] Tests exist (feature, unit, integration)
- [ ] README documents domain and workflows
- [ ] CHANGELOG tracks versions
- [ ] No direct module-to-module imports
- [ ] All signals via dispatcher (not events)
- [ ] Tenant boundary validated everywhere

---

## Common Mistakes to Avoid

### ❌ Mistake 1: Business Logic in Controllers

```php
// Wrong
public function dispatch(Request $request)
{
    $job = Job::find($request->job_id);
    $crew = Crew::find($request->crew_id);
    
    if ($crew->jobs_today >= 8) {
        return error('Crew overalloc');
    }
    
    $job->update(['crew_id' => $crew->id]);
    return success();
}

// Right
public function dispatch(Request $request, DispatchService $service)
{
    $job = $service->dispatch(
        $request->job_id,
        $request->crew_id
    );
    return response()->json($job);
}
```

### ❌ Mistake 2: Direct Module-to-Module Imports

```php
// Wrong
use Modules\Customers\Models\Customer;

// Right
$customer = app(CustomerRepository::class)->find($id);
```

### ❌ Mistake 3: Ignoring Tenant Boundary

```php
// Wrong
Job::where('status', 'pending')->get();

// Right
Job::whereTenantId($tenant_id)
    ->where('status', 'pending')
    ->get();
```

### ❌ Mistake 4: Emitting Events Instead of Signals

```php
// Wrong
event(new JobDispatched($job));

// Right
app(SignalDispatcher::class)->emit('job_dispatched', [
    'job_id' => $job->id,
    'crew_id' => $job->crew_id,
]);
```

### ❌ Mistake 5: Custom Settings Without Schema

```php
// Wrong
config('jobs.max_per_crew');  // magic string, no schema

// Right
SettingService::for($tenant)->get('jobs.dispatch.max_jobs_per_crew');
```

---

## Summary

Engines are the building blocks of Titan BOS. Each engine:
- **Owns a business domain completely**
- **Emits clear signals** for platform and other engines
- **Provides AI tools** for orchestration
- **Supports multiple surfaces** (admin, field, customer, API)
- **Is tenant-scoped everywhere**
- **Is fully auditable and testable**

Follow the module structure. Use the manifests for discovery. Emit signals, not events. Keep controllers thin. Own your domain.

---

**Status:** ✓ TEDK Volume 2 Complete  
**Version:** 1.0  
**Authority:** Engine Development Standard  
**Use:** Reference for all Engine development

