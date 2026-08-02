# Titan Omni Chatbot Architecture Drift Report

## Audit Date

2026-08-02

## Branch

`feature/titan-five-app-architecture`

## Scope

Static, evidence-based inspection of chatbot-related code under `app/Extensions`, centred on:

- `app/Extensions/Chatbot`;
- the parallel `app/Extensions/TitanZeroChatbot` tree;
- adjacent chatbot extensions where they affect registration or duplication.

The canonical WorkCore domain and `packages/titanzero/interaction-engine` were treated as external dependencies and were not deeply audited in this pass.

## Target Architecture

The chatbot must become the shared, application-aware conversational interface for:

- Titan Zero;
- Titan Go;
- Titan Launch;
- Titan Desk;
- Titan Hub.

WorkCore owns operational truth. Titan AI owns interpretation, planning and worker routing. The Interaction Engine owns the shared execution pipeline. The offline runtime owns local-first operation and synchronisation without becoming a second source of business truth.

## Executive Finding

The extension already contains many of the required building blocks: schema-driven navigation, distinct operational screens, Titan AI orchestration, governed tool execution, WorkCore tool allowlists, device registration, idempotent sync, conflict tracking, PWA cache hardening, local WorkCore projections, permissions and feature flags.

The main problem is not missing capability. It is architectural convergence. The current build still models 14 top-level applications/templates, retains two parallel chatbot extension trees, uses template slugs as application identity, and propagates only a partial execution context.

## Confirmed Drift

### 1. Duplicate authoritative extension candidates

`app/Extensions/Chatbot` and `app/Extensions/TitanZeroChatbot` contain overlapping copies with the same extension identity and namespaces. Several inspected files are identical, while the service providers have already diverged: the primary tree has feature-flagged WorkCore registration and the parallel tree retains unconditional registration.

**Severity:** Critical

**Required direction:** Select one authoritative tree and stop independent discovery/boot of the other before substantive migration work.

### 2. Fourteen top-level shells remain encoded

The manifest describes a 14-app shell. The template schema index and builder expose 14 identities, including Titan Dispatch, Money, Teams, Analytics, Front Desk, Marketing, Social, Sprout, Locker, Office and Quality.

**Severity:** Critical

**Required direction:** Register exactly five platform applications and reclassify legacy identities as workflows, modules, reports, vertical tools or internal engines.

### 3. Registry naming and responsibility are misleading

`System/Titan/TitanRegistry.php` is described as an app registry but loads vertical template directories and emits PWA manifests. `TemplateSchema` separately supplies shell definitions. Neither is a canonical platform application registry with ownership, role, report, settings, context and workflow metadata.

**Severity:** Critical

**Required direction:** Separate platform application registry from vertical template registry. Reuse existing schema data through adapters/migration mapping.

### 4. Template slug is used as active application identity

The frontend resolves the operational shell from `titan_template` or `template_slug`, and client events use that slug as the app identifier.

**Severity:** Critical

**Required direction:** Persist `active_application` separately from `vertical_template`, with compatibility resolution for legacy data.

### 5. AI context does not satisfy the target context contract

The request DTO carries tenant, user, device, conversation, template, workflow and permission fields. It lacks explicit application, role, structured WorkCore context, conversation context summary, AI context and offline state. The orchestrator requires only tenant, user and device.

**Severity:** Critical

**Required direction:** Introduce one typed, validated context envelope propagated through AI, Interaction Engine, WorkCore and offline execution.

### 6. Intent routing is application-agnostic

Intent classification is deterministic and useful, but based on phrase matching without active application, selected workflow, record context or role.

**Severity:** High

**Required direction:** Retain deterministic definitions and add application-aware ranking and policy constraints.

### 7. WorkCore mappings encode the old application model

The WorkCore bridge loads ten legacy app manifests and checks tools by old app slug. The bridge itself is reusable and correctly delegates execution to the WorkCore runtime.

**Severity:** Critical

**Required direction:** Consolidate manifests into five application policies and keep WorkCore as final validator/writer.

### 8. Communication identities risk being treated as operational customers

The extension owns and syncs a customer-like record with contact/session/channel fields. This is legitimate for chat and intake, but its authority boundary is not explicit.

**Severity:** Critical

**Required direction:** Define it as a communication/intake identity and link or promote records through WorkCore CRM services.

### 9. Interaction Engine integration is not explicit in the scoped extension

A canonical Interaction Engine package exists elsewhere in the repository. No clear adapter to that package was evidenced inside the chatbot extension. Controllers, Titan AI and governed execution currently coordinate workflow behaviour directly.

**Severity:** High

**Required direction:** Inspect the package's public contract during implementation and add one adapter rather than copying or replacing the engine.

### 10. Offline runtime is strong but legacy-app aware

The service worker has good privacy and cache-integrity controls. Sync includes device ownership, idempotency, dependencies, transactions, version conflicts and tombstones. However, operational profiles and queued context remain tied to templates/legacy apps.

**Severity:** High

**Required direction:** Preserve the runtime and add canonical application/workflow context, legacy state migration and five-app profiles.

### 11. PWA identity and deep links are generic

The manifest represents a single “Titan Zero Chatbot” PWA with generic chat/help shortcuts. Notifications return to the generic chatbot route.

**Severity:** Medium

**Required direction:** Retain one shared PWA shell, make it application-aware and add authorised deep links rather than cloning the PWA five times.

### 12. Global settings and reports are not assigned to the new ownership model

Every template can expose broad settings, and Titan Analytics remains a top-level shell.

**Severity:** High

**Required direction:** Put global settings and cross-platform reporting in Titan Zero; keep operational reports and app-specific settings inside each owning application.

### 13. Route authority remains split between extension and host

The extension owns `/api/v2/chatbot` and `/api/v2/titan` routes while a host provider exposes legacy `chatbot-api` routes. Runtime collision checks have not been executed.

**Severity:** High

**Required direction:** Preserve compatibility, document authority and run an executable route collision audit before adding migration routes.

## Reusable Foundations

The migration should reuse, not replace:

- `ChatbotServiceProvider` feature flags and canonical WorkCore guard;
- schema-driven `TemplateNavigation` and `TemplateSchema` concepts;
- operational workspace renderer;
- Titan AI five-tier worker orchestration;
- governed tool executor and approval path;
- WorkCore bridge and tool allowlist mechanism;
- device registration and chatbot sync protocol;
- conflict, tombstone and idempotency handling;
- service-worker privacy controls and rollback cache;
- local WorkCore client/projection modules;
- existing policies, broadcast channel checks and permission snapshots;
- existing API routes through compatibility adapters.

## Runtime Verification Not Performed

This pass used repository source inspection through the GitHub connector. It did not execute:

- Laravel application boot;
- `artisan route:list`;
- provider/container resolution;
- migrations or rollback;
- queue workers;
- browser asset compilation;
- PHP/JavaScript test suites;
- live WorkCore writes;
- Interaction Engine package integration tests;
- external channel/provider handshakes.

These checks remain mandatory implementation gates and are recorded in the backlog.

## Recommended First Implementation Move

Before changing application schemas, resolve the duplicate extension authority problem. Building the five-app registry while both trees remain bootable would multiply every subsequent migration and allow immediate drift.
