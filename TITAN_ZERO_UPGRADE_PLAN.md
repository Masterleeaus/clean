# Titan Zero Integrated Platform Upgrade Plan

> **For agentic workers:** REQUIRED SUB-SKILL: use `superpowers:subagent-driven-development` or `superpowers:executing-plans` to implement this plan task-by-task. Track every step with the checkboxes below and stop at each review gate.

**Goal:** Turn the imported MagicAI v10.91, WorkCore, Titan Zero Chatbot PWA, five-tier AI runtime, Interaction/Wizard Engine code and extension collection into one bootable, testable, tenant-safe and device-first Titan Zero application.

**Architecture:** MagicAI remains the host SaaS and UI shell. WorkCore under `app/Domains/WorkCore` is the authoritative operational domain. The chatbot remains a MagicAI extension under `app/Extensions/Chatbot`, while reusable Interaction Engine logic must converge into a bounded domain rather than remain duplicated inside the chatbot. Extensions are discovered through validated manifests and enabled progressively; they must not all boot unconditionally.

**Tech stack:** PHP 8.2, Laravel 10, Pest/PHPUnit 10, Livewire 3, Blade, Vite 7, React 19, Alpine.js, Tailwind CSS 3, IndexedDB/PWA service workers, queues, Laravel broadcasting, Composer and npm.

## Global constraints

- Preserve the imported MagicAI v10.91 host application and database compatibility.
- WorkCore is authoritative for customers, jobs, tasks, quotes, invoices, workforce, property operations and operational records.
- Do not create permanent `source`, `integration`, `merge`, `legacy-copy` or parallel application directories.
- Do not allow embedded chatbot WorkCore code to shadow `App\Domains\WorkCore`.
- Use tenant, user and device identifiers on local records, sync operations, queues and authorization decisions.
- Never cache secrets, API keys, sensitive API responses or unsynchronised private records in the service-worker cache.
- Never delete unsynchronised device data automatically.
- Preserve backward compatibility with existing MagicAI routes and extension registration until replacement tests pass.
- Do not activate all imported extensions simultaneously. Every extension must pass manifest, provider, route, migration and dependency checks before enablement.
- Use small, focused commits. Every implementation task must include a failing test, the minimal repair, passing validation and a review checkpoint.
- `main` is not a work branch. All changes for this programme remain on `agent/gpt56-titan-zero-upgrade-workbench` until reviewed.

---

## Baseline and source authority

### Verified branch baseline

- [x] Working branch created: `agent/gpt56-titan-zero-upgrade-workbench`.
- [x] Branch restored to verified source commit `a76eee53af7b72b9f740adb3fa757b3f4d527bd6`.
- [x] MagicAI application source is present at repository root.
- [x] WorkCore is present at `app/Domains/WorkCore`.
- [x] Titan Zero Chatbot is present at `app/Extensions/Chatbot`.
- [x] Extension import manifest is present at `EXTENSIONS_IMPORT_MANIFEST.json`.
- [x] The manifest records 104 canonical extension directories.

### Current architectural observations

1. `composer.json` targets PHP `^8.2` and Laravel `^10.0`, with Pest and PHPUnit available.
2. `package.json` uses Vite 7, React 19, Alpine.js, Tailwind CSS and the existing Blade asset pipeline.
3. `config/app.php` registers `App\Domains\WorkCore\WorkCoreServiceProvider` directly.
4. `app/Providers/ExtensionServiceProvider.php` currently lists only a chatbot provider and does not yet provide manifest-driven discovery.
5. `app/Extensions/Chatbot/System/ChatbotServiceProvider.php` contains the PWA, generative UI, five-tier AI compatibility maps, WorkCore adapters, team chat and Titan AI providers. It is powerful but overloaded and must be decomposed without breaking the extension.
6. The chatbot correctly prevents its embedded legacy WorkCore runtime from shadowing the canonical host WorkCore domain when the host provider exists.
7. The extension import is a source inventory, not proof that all 104 extensions can boot together.

---

# Programme sequence

The programme is divided into nine independently reviewable passes. A pass is complete only when its acceptance gate passes.

## Pass 1 — Reproducible baseline and repository health

**Outcome:** A developer can clone the branch, verify source provenance and run deterministic preflight checks without booting every extension.

### Task 1.1: Record baseline inventory

**Files:**
- Create: `tools/titan-zero-audit/baseline.php`
- Create: `tests/Architecture/SourceBaselineTest.php`
- Create: `docs/audits/source-baseline.md`

**Produces:** A machine-readable inventory of required roots, extension manifests, duplicate namespaces, oversized files and missing local Composer path packages.

- [ ] Write a failing Pest test asserting the existence of `artisan`, `composer.json`, `package.json`, `app/Domains/WorkCore/WorkCoreServiceProvider.php`, `app/Extensions/Chatbot/extension.json` and `EXTENSIONS_IMPORT_MANIFEST.json`.
- [ ] Run `php artisan test tests/Architecture/SourceBaselineTest.php` and confirm it fails only for unimplemented inventory assertions.
- [ ] Implement `tools/titan-zero-audit/baseline.php` using `RecursiveDirectoryIterator`; output JSON to `storage/app/audits/source-baseline.json` without modifying application files.
- [ ] Add assertions comparing top-level `app/Extensions/*/extension.json` folders to the 104 canonical manifest entries.
- [ ] Run `php tools/titan-zero-audit/baseline.php` and `php artisan test tests/Architecture/SourceBaselineTest.php`.
- [ ] Commit: `chore: add reproducible source baseline audit`.

### Task 1.2: Validate dependency inputs before installation

**Files:**
- Create: `tests/Architecture/DependencyInputTest.php`
- Modify only after a failing test: `composer.json`, `package.json`

- [ ] Assert every Composer `path` repository exists and contains its own `composer.json`.
- [ ] Assert `rt-client-0.4.7.tgz` exists before npm installation.
- [ ] Run `composer validate --strict` and `npm pkg get scripts dependencies devDependencies`.
- [ ] Repair malformed or missing local dependency references only when the required package is present elsewhere in the imported source.
- [ ] Run `composer validate --strict` and the dependency input test again.
- [ ] Commit: `fix: validate local dependency inputs`.

**Pass 1 acceptance gate**

```bash
composer validate --strict
php tools/titan-zero-audit/baseline.php
php artisan test tests/Architecture/SourceBaselineTest.php tests/Architecture/DependencyInputTest.php
```

---

## Pass 2 — Laravel boot and provider graph repair

**Outcome:** Laravel boots with WorkCore and the chatbot disabled, then with each enabled independently, without duplicate bindings or fatal class errors.

### Task 2.1: Establish controlled feature flags

**Files:**
- Create: `config/titan-zero.php`
- Create: `tests/Feature/Bootstrap/TitanZeroFeatureFlagTest.php`
- Modify: `config/app.php`

**Required flags:**

```php
return [
    'workcore_enabled' => env('TITAN_ZERO_WORKCORE_ENABLED', true),
    'chatbot_enabled' => env('TITAN_ZERO_CHATBOT_ENABLED', false),
    'interaction_engine_enabled' => env('TITAN_ZERO_INTERACTION_ENGINE_ENABLED', false),
    'extension_discovery_enabled' => env('TITAN_ZERO_EXTENSION_DISCOVERY_ENABLED', false),
];
```

- [ ] Write tests for the four enablement combinations needed during staged repair.
- [ ] Move direct provider activation behind explicit configuration without removing the existing provider classes.
- [ ] Verify `php artisan about`, `php artisan config:clear` and `php artisan route:list` under each supported combination.
- [ ] Commit: `feat: add staged Titan Zero boot flags`.

### Task 2.2: Repair the extension provider graph

**Files:**
- Modify: `app/Providers/ExtensionServiceProvider.php`
- Create: `app/Support/Extensions/ExtensionManifest.php`
- Create: `app/Support/Extensions/ExtensionCatalog.php`
- Create: `app/Support/Extensions/ExtensionProviderResolver.php`
- Create: `tests/Feature/Extensions/ExtensionProviderDiscoveryTest.php`

**Interfaces:**

```php
final readonly class ExtensionManifest
{
    public function __construct(
        public string $directory,
        public string $name,
        public string $version,
        public ?string $provider,
        public bool $enabled,
    ) {}
}

interface ExtensionProviderResolver
{
    /** @return list<class-string<\Illuminate\Support\ServiceProvider>> */
    public function resolveEnabledProviders(): array;
}
```

- [ ] Write failing tests for invalid JSON, absent provider classes, duplicate extension names, duplicate provider classes and disabled extensions.
- [ ] Implement catalog scanning without executing extension code during discovery.
- [ ] Register providers only after validation and only for explicitly enabled packages.
- [ ] Keep a compatibility fallback for the existing chatbot registration until its dedicated test passes.
- [ ] Commit: `feat: add validated extension provider discovery`.

### Task 2.3: Add provider collision diagnostics

**Files:**
- Create: `app/Console/Commands/TitanZeroProviderAuditCommand.php`
- Create: `tests/Feature/Console/TitanZeroProviderAuditCommandTest.php`

- [ ] Detect duplicate route names, middleware aliases, container bindings, event listeners and migration class names before enabling an extension.
- [ ] Output Green, Amber or Red status for each extension.
- [ ] Ensure the command never mutates extension state.
- [ ] Commit: `feat: add extension provider collision audit`.

**Pass 2 acceptance gate**

```bash
php artisan about
php artisan titan-zero:provider-audit
php artisan route:list
php artisan test tests/Feature/Bootstrap tests/Feature/Extensions tests/Feature/Console/TitanZeroProviderAuditCommandTest.php
```

---

## Pass 3 — WorkCore host integration and tenancy

**Outcome:** WorkCore boots as the canonical operational domain with host adapters, tenant-safe route binding and deterministic capability registration.

### Task 3.1: Prove WorkCore boot contracts

**Files:**
- Create: `tests/Feature/WorkCore/WorkCoreBootTest.php`
- Create: `tests/Feature/WorkCore/WorkCoreBindingTest.php`
- Inspect and modify only when tests prove a defect: `app/Domains/WorkCore/WorkCoreServiceProvider.php`, `app/Domains/WorkCore/Config/workcore.php`

- [ ] Assert every configured contract resolves to a concrete class.
- [ ] Assert every enabled WorkCore module provider exists.
- [ ] Assert migrations, commands and routes load once.
- [ ] Assert WorkCore API and web routes are disabled unless their explicit flags are enabled.
- [ ] Commit: `test: lock WorkCore boot contracts`.

### Task 3.2: Replace null host adapters deliberately

**Files:**
- Create: `app/Integration/WorkCore/MagicAiMenuAdapter.php`
- Create: `app/Integration/WorkCore/MagicAiNotificationAdapter.php`
- Create: `app/Integration/WorkCore/MagicAiToolBridge.php`
- Create: `app/Providers/TitanZeroWorkCoreHostServiceProvider.php`
- Create: `tests/Feature/WorkCore/WorkCoreHostAdapterTest.php`

- [ ] Write tests proving menu entries, notifications and AI tools remain tenant-scoped.
- [ ] Bind the host adapters after the canonical WorkCore provider, without changing WorkCore domain contracts.
- [ ] Ensure host failures degrade safely and do not corrupt WorkCore actions.
- [ ] Commit: `feat: wire WorkCore to MagicAI host adapters`.

### Task 3.3: Enforce tenant-safe record resolution

**Files:**
- Create: `tests/Feature/WorkCore/TenantRouteBindingTest.php`
- Inspect: `app/Domains/WorkCore/Routes/api.php`, `app/Domains/WorkCore/Routes/web.php`
- Modify affected route bindings, policies and query scopes only after a failing cross-tenant test.

- [ ] Test a user cannot resolve another tenant’s customer, job, invoice, property or workforce record by numeric ID or UUID.
- [ ] Test queued WorkCore actions restore tenant, user and correlation context.
- [ ] Test idempotency keys cannot be reused across tenants.
- [ ] Commit: `fix: enforce WorkCore tenant boundaries`.

**Pass 3 acceptance gate**

```bash
php artisan workcore:diagnose
php artisan workcore:schema-preflight
php artisan test tests/Feature/WorkCore
```

---

## Pass 4 — Interaction Engine convergence

**Outcome:** Interaction, wizard, authority, confidence and execution logic has one canonical bounded domain and no active parallel implementation.

### Task 4.1: Inventory engine implementations

**Files:**
- Create: `tools/titan-zero-audit/interaction-engine-map.php`
- Create: `docs/audits/interaction-engine-map.md`
- Create: `tests/Architecture/InteractionEngineOwnershipTest.php`

- [ ] Locate runtime engines, wizard state, authority controls, confidence grading, policy evaluation, events, DTOs and adapters across WorkCore and chatbot directories.
- [ ] Classify each implementation as canonical, adapter, compatibility shim, duplicate or dead candidate.
- [ ] Fail the architecture test when two active classes claim the same contract or route.
- [ ] Commit: `chore: map Interaction Engine ownership`.

### Task 4.2: Establish the canonical domain

**Files:**
- Create or consolidate into: `app/Domains/InteractionEngine`
- Create: `app/Domains/InteractionEngine/InteractionEngineServiceProvider.php`
- Create: `tests/Feature/InteractionEngine/InteractionEngineBootTest.php`

**Boundaries:**

```text
app/Domains/InteractionEngine/
├── Contracts/
├── Application/
├── Domain/
├── Infrastructure/
├── Providers/
├── Config/
├── Database/Migrations/
└── Tests/
```

- [ ] Move only validated canonical code; retain temporary namespace shims for known consumers.
- [ ] Keep WorkCore as the operational authority and make the Interaction Engine call WorkCore through contracts/adapters.
- [ ] Keep chatbot-specific presentation and device adapters inside the chatbot extension.
- [ ] Add architecture tests preventing the Interaction Engine from depending on Blade controllers or PWA storage implementations.
- [ ] Commit: `refactor: establish canonical Interaction Engine domain`.

### Task 4.3: Wire confidence and authority controls

**Files:**
- Create: `tests/Feature/InteractionEngine/AuthorityDecisionTest.php`
- Create: `tests/Feature/InteractionEngine/ConfidencePipelineTest.php`
- Modify canonical engine services only after the tests fail.

- [ ] Prove Green decisions may proceed, Amber decisions request confirmation, and Red decisions are blocked or escalated.
- [ ] Persist the decision, actor, tenant, device, reason, confidence and correlation identifiers.
- [ ] Ensure offline actions cannot silently bypass authority rules during later synchronisation.
- [ ] Commit: `feat: enforce Interaction Engine authority pipeline`.

**Pass 4 acceptance gate**

```bash
php artisan test tests/Architecture/InteractionEngineOwnershipTest.php tests/Feature/InteractionEngine
```

---

## Pass 5 — Chatbot, five-tier AI and offline PWA

**Outcome:** The chatbot extension boots through a narrow provider, uses canonical WorkCore and Interaction Engine contracts, and works device-first without leaking secrets.

### Task 5.1: Decompose the chatbot provider

**Files:**
- Modify: `app/Extensions/Chatbot/System/ChatbotServiceProvider.php`
- Create focused providers under: `app/Extensions/Chatbot/System/Providers/`
- Create: `tests/Feature/Chatbot/ChatbotProviderGraphTest.php`

**Target providers:**

```text
ChatbotCoreServiceProvider.php
ChatbotRoutesServiceProvider.php
ChatbotPwaServiceProvider.php
ChatbotTitanAiServiceProvider.php
ChatbotGenerativeUiServiceProvider.php
ChatbotTeamChatServiceProvider.php
```

- [ ] Write tests ensuring each provider has one responsibility and is registered once.
- [ ] Preserve extension routes, views, migrations, policies and public asset publishing.
- [ ] Move custom autoload compatibility into a dedicated, documented bridge and remove mappings as Composer-compatible namespaces become available.
- [ ] Commit: `refactor: split chatbot service provider responsibilities`.

### Task 5.2: Wire the five-tier AI runtime

**Files:**
- Inspect and repair: `app/Extensions/Chatbot/System/TitanAI`
- Create: `tests/Feature/Chatbot/TitanAiExecutionPathTest.php`

- [ ] Trace a request from HTTP/controller to Tier 0 orchestration, Tier 1 manager, Tier 2 assistant/specialist, Tier 3 action agent, governance, WorkCore action and generative UI response.
- [ ] Prove every registered manager, assistant and action agent is reachable through a declared capability.
- [ ] Reject duplicate or orphaned agent registrations.
- [ ] Commit: `fix: wire five-tier AI execution path`.

### Task 5.3: Harden offline data and synchronisation

**Files:**
- Inspect and repair: `app/Extensions/Chatbot/resources/pwa/chatbot-pwa`
- Create: `tests/browser/chatbot-offline.spec.ts`
- Create: `tests/Feature/Chatbot/ChatbotSyncContractTest.php`

- [ ] Test offline conversation creation, draft persistence, local search, outbox retry, conflict state and recovery after reconnect.
- [ ] Assert client-generated UUIDs and tenant/user/device identifiers are included in every local mutation.
- [ ] Assert credentials and sensitive responses never enter Cache Storage.
- [ ] Assert failed synchronisation never deletes unsynchronised local records.
- [ ] Commit: `fix: harden chatbot offline sync`.

**Pass 5 acceptance gate**

```bash
php artisan test tests/Feature/Chatbot
npx playwright test tests/browser/chatbot-offline.spec.ts
```

---

## Pass 6 — Extension catalogue and progressive enablement

**Outcome:** The 104 imported extension folders are visible, classified and testable, while only compatible packages can be enabled.

### Task 6.1: Build the extension health catalogue

**Files:**
- Create: `app/Support/Extensions/ExtensionHealthReport.php`
- Create: `app/Console/Commands/TitanZeroExtensionAuditCommand.php`
- Create: `tests/Feature/Extensions/ExtensionHealthAuditTest.php`

**Health states:** `green`, `amber`, `red`, `quarantined`.

- [ ] Validate manifest schema, provider class, PHP syntax, migration uniqueness, route uniqueness, configuration, assets and required Composer/npm packages.
- [ ] Generate `storage/app/audits/extensions.json` and a Markdown summary.
- [ ] Default unverified extensions to disabled or quarantined.
- [ ] Commit: `feat: add progressive extension health catalogue`.

### Task 6.2: Test extension families in batches

**Families:** host/base, AI providers, AIChatPro, agent/channel, creative/media, chatbot/channel, payments/integrations and experimental.

- [ ] Enable one family per test process.
- [ ] Run provider boot, route list, migration dry run and focused smoke tests.
- [ ] Record failures against the exact extension and dependency.
- [ ] Promote only passing extensions from Amber/Red to Green.
- [ ] Commit one family at a time using `fix: qualify <family> extensions`.

**Pass 6 acceptance gate**

```bash
php artisan titan-zero:extension-audit --format=json
php artisan test tests/Feature/Extensions
```

---

## Pass 7 — Build Web Apps interface integration

**Outcome:** Titan Zero presents one coherent, responsive business operating interface instead of disconnected dashboards and chat surfaces.

### Task 7.1: Map existing UI routes and templates

**Files:**
- Create: `docs/audits/ui-route-map.md`
- Create: `tests/browser/navigation-shell.spec.ts`

- [ ] Map MagicAI header, sidebar/menu, chatbot shell, WorkCore pages, extension pages and settings surfaces.
- [ ] Identify duplicate navigation entries and routes that bypass tenant or capability checks.
- [ ] Preserve the persistent top chat bar, operational workspace, gear settings control and responsive hamburger navigation requested for Titan Zero.
- [ ] Commit: `docs: map Titan Zero interface surfaces`.

### Task 7.2: Establish a shared Titan Zero shell

**Files:**
- Create focused Blade/React components under the existing theme conventions; do not start a parallel frontend application.
- Create: `tests/browser/titan-zero-shell.spec.ts`

- [ ] Implement the shared header, persistent chat bar, primary workspace and contextual navigation.
- [ ] Use WorkCore capability metadata to decide which operational links appear.
- [ ] Use extension catalogue health to hide quarantined extension links.
- [ ] Validate desktop, tablet and mobile layouts with Playwright screenshots.
- [ ] Commit: `feat: add shared Titan Zero application shell`.

### Task 7.3: Connect generative UI to operational actions

**Files:**
- Inspect and repair: `app/Extensions/Chatbot/System/GenerativeUI`
- Create: `tests/Feature/Chatbot/GenerativeUiWorkCoreActionTest.php`
- Create: `tests/browser/generative-ui-actions.spec.ts`

- [ ] Validate every generated component specification before rendering.
- [ ] Require authority/confirmation metadata for state-changing WorkCore actions.
- [ ] Provide accessible loading, offline, error, conflict and success states.
- [ ] Commit: `feat: connect generative UI to governed WorkCore actions`.

**Pass 7 acceptance gate**

```bash
npm run build
npx playwright test tests/browser/navigation-shell.spec.ts tests/browser/titan-zero-shell.spec.ts tests/browser/generative-ui-actions.spec.ts
```

---

## Pass 8 — Security, privacy and operational resilience

**Outcome:** Tenant isolation, BYO credentials, queues, files, webhooks and offline storage meet Titan Zero’s privacy-first requirements.

### Task 8.1: Credential and secret boundary audit

**Files:**
- Create: `tests/Security/SecretBoundaryTest.php`
- Create: `docs/audits/secret-boundaries.md`

- [ ] Find API keys, OAuth tokens, webhook secrets, encryption material and provider credentials.
- [ ] Ensure secrets are encrypted at rest or remain in environment/secret stores.
- [ ] Ensure logs, queues, browser storage and service-worker caches do not expose secret values.
- [ ] Commit: `security: enforce secret boundaries`.

### Task 8.2: Authorization and webhook audit

**Files:**
- Create: `tests/Security/TenantAuthorizationMatrixTest.php`
- Create: `tests/Security/WebhookVerificationTest.php`

- [ ] Test owner, administrator, dispatcher, worker, customer and unauthenticated access across representative WorkCore and chatbot actions.
- [ ] Verify signatures, replay protection and tenant resolution for inbound webhooks.
- [ ] Ensure rate limits and idempotency apply before side effects.
- [ ] Commit: `security: harden authorization and webhooks`.

### Task 8.3: Queue and failure recovery

**Files:**
- Create: `tests/Feature/Resilience/QueueContextTest.php`
- Create: `tests/Feature/Resilience/OutboxRecoveryTest.php`

- [ ] Test tenant/user/device/correlation context survives queue serialization.
- [ ] Test retries do not duplicate invoices, messages, bookings or payments.
- [ ] Test poison messages move to a reviewable failed state.
- [ ] Commit: `fix: harden queue and outbox recovery`.

**Pass 8 acceptance gate**

```bash
php artisan test tests/Security tests/Feature/Resilience
```

---

## Pass 9 — CI, release and deployment readiness

**Outcome:** Every merge candidate produces evidence that backend, frontend, PWA and selected extensions work together.

### Task 9.1: Add continuous integration

**Files:**
- Create: `.github/workflows/titan-zero-ci.yml`
- Create: `.github/pull_request_template.md`

**CI jobs:**

1. Composer validation and PHP syntax.
2. Pint check.
3. Pest architecture/unit tests.
4. Laravel integration tests using an isolated database.
5. npm clean install and Vite production build.
6. Playwright browser tests.
7. Extension health audit.
8. Source and migration collision audit.

- [ ] Make each job independently diagnosable.
- [ ] Upload test logs and browser screenshots only; never upload `.env`, databases or credentials.
- [ ] Commit: `ci: add Titan Zero validation pipeline`.

### Task 9.2: Produce release evidence

**Files:**
- Create: `docs/releases/RELEASE_CHECKLIST.md`
- Create: `tools/release/build-release.sh`

- [ ] Build release archives only from a tagged, verified commit.
- [ ] Exclude `.git`, `.env`, logs, caches, test databases, node modules and local credentials.
- [ ] Generate SHA-256 checksums, migration inventory and extension health report.
- [ ] Perform install, upgrade and rollback rehearsals.
- [ ] Commit: `chore: add reproducible release packaging`.

**Pass 9 acceptance gate**

```bash
composer test
composer test:lint
npm run build
php artisan titan-zero:provider-audit
php artisan titan-zero:extension-audit
npx playwright test
```

---

# Review gates and completion definition

## Per-pass review gate

A pass cannot be marked complete until:

- The relevant tests fail before implementation and pass afterward.
- No new duplicate provider, route, migration or namespace owner is introduced.
- Tenant and authorization tests cover state-changing behaviour.
- Documentation names the canonical owner and compatibility shims.
- The commit contains only files belonging to that pass.

## Final completion definition

Titan Zero is ready for a release candidate when:

1. Laravel boots cleanly with production configuration.
2. WorkCore diagnoses and schema preflight pass.
3. The canonical Interaction Engine has no active competing implementation.
4. The chatbot works online and offline, with safe conflict recovery.
5. The five-tier AI path reaches governed WorkCore actions and generative UI.
6. All enabled extensions are Green; Amber, Red and quarantined extensions remain disabled.
7. Desktop, tablet and mobile browser tests pass.
8. Tenant isolation, credentials, webhooks, queue context and idempotency tests pass.
9. CI passes from a clean checkout.
10. A release archive can be reproduced from the tagged commit with matching SHA-256 checksums.

# Execution order

Use this order strictly:

```text
Pass 1 → Pass 2 → Pass 3 → Pass 4 → Pass 5 → Pass 6 → Pass 7 → Pass 8 → Pass 9
```

Do not begin broad UI redesign before Passes 1–6 establish a bootable and governed backend. Do not enable every extension to “see what breaks”; qualify extensions progressively through the health catalogue.
