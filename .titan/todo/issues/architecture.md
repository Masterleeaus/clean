# Architecture Issues

## ARCH-001 — Parallel authoritative chatbot extension trees

### Current State

`app/Extensions/Chatbot` and `app/Extensions/TitanZeroChatbot` contain parallel copies of the same extension. Multiple inspected files are byte-identical, share the same extension identity, and declare the same `App\Extensions\Chatbot` namespaces. The primary `Chatbot` provider has newer feature-flagged WorkCore registration while the parallel provider retains unconditional registration, proving the trees have already begun to drift.

### Required Changes

Declare one authoritative chatbot extension. The current evidence favours `app/Extensions/Chatbot` because it contains the newer feature-flag boundary. Quarantine, remove, or convert `TitanZeroChatbot` into an explicit compatibility package that cannot independently register providers, routes, migrations or classes. Add an architecture test that fails when duplicate extension identities or authoritative namespaces are discovered.

### Why

Two independently discoverable trees using the same namespaces can cause unpredictable autoloading, duplicated migrations/routes/providers and fixes being applied to only one copy.

### Risk

Critical. Removing the wrong discovery path could affect installed-extension metadata or deployment scripts. A reference and registration scan is required before deletion.

### Priority

Critical

### Dependencies

Marketplace extension discovery, Composer/autoload configuration, deployment packaging, provider registration, migration loading and extension upgrade scripts.

### Estimated Work

Large

### Completion Status

Pending

---

## ARCH-002 — Platform applications are conflated with templates and modules

### Current State

The extension description advertises a 14-app shell. `TitanRegistry` loads vertical template directories, while `TemplateSchema` and the builder expose 14 top-level application identities. The same concept currently represents platform applications, vertical templates, operational modules and internal engines.

### Required Changes

Separate three concepts:

1. five canonical platform applications;
2. vertical/business templates;
3. internal WorkCore modules and engines.

Create one canonical platform application registry for Titan Zero, Titan Go, Titan Launch, Titan Desk and Titan Hub. Retain vertical templates in a separate registry. Reclassify old app identities through an explicit migration map.

### Why

Without separate ownership layers, navigation, permissions, AI context, settings, reports, WorkCore tools and PWA behaviour cannot reliably determine whether a slug identifies an application, workflow, vertical or module.

### Risk

High. Existing chatbot records may persist legacy template slugs and UI configuration.

### Priority

Critical

### Dependencies

Application registry, template schemas, builder UI, navigation, PWA, WorkCore manifests, AI routing and persisted chatbot configuration.

### Estimated Work

Large

### Completion Status

Pending

---

## ARCH-003 — Canonical context boundary is incomplete

### Current State

The AI request and orchestrator carry tenant, user, device, conversation, template, workflow and permission snapshots. They do not define one authoritative envelope for active application, role, WorkCore entity context, conversation context, AI context or offline state.

### Required Changes

Introduce a typed application execution context shared by the HTTP controller, Titan AI runtime, WorkCore bridge, offline client and Interaction Engine adapter. Context creation must be centralised and validated before workflow execution.

### Why

Application-aware behaviour cannot be safely inferred from optional template strings or arbitrary payload fields.

### Risk

High. Changing request contracts without compatibility handling could break existing PWA clients.

### Priority

Critical

### Dependencies

Titan AI request DTO, execution controller, orchestrator, Interaction Engine adapter, WorkCore bridge, PWA runtime and permission resolver.

### Estimated Work

Large

### Completion Status

Pending

---

## ARCH-004 — Host and extension chatbot route surfaces overlap

### Current State

The scoped extension registers `/api/v2/chatbot`, `/api/v2/titan` and sync/AI routes. A host-level `app/Providers/ChatbotServiceProvider.php` separately exposes `chatbot-api` and chatbot asset routes. The previous audit did not run a live route collision check.

### Required Changes

Document the authority of each route surface, preserve required compatibility endpoints, and add an executable route-name/path collision test in the host application before migration routes are added.

### Why

Adding another five-app route family without resolving current ownership would increase fragmentation and collision risk.

### Risk

High. External embeds or older clients may depend on legacy paths.

### Priority

High

### Dependencies

Host application provider, extension provider, frontend clients, embed scripts and API consumers.

### Estimated Work

Medium

### Completion Status

Pending
