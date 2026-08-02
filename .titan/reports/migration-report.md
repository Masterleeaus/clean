# Five-Application Chatbot Migration Report

## Pass

Pass 1 — Scoped static audit and implementation backlog

## Result

Pass 1 is complete. No production code was changed.

## Audited Areas

- extension identity and provider registration;
- duplicate chatbot trees;
- current application/template registries;
- builder and navigation configuration;
- operational workspace UI;
- Titan AI request, intent and orchestration path;
- WorkCore application bridge and legacy mappings;
- chatbot communication records and sync ownership;
- PWA manifest, service worker and offline runtime;
- route surfaces and host dependencies;
- policy and permission propagation;
- Interaction Engine integration boundary.

## Current Capability Summary

### Present and reusable

- template/schema-driven shell;
- distinct app navigation and prompt definitions;
- five-tier Titan AI runtime;
- governed tool execution and review path;
- WorkCore bridge with per-app tool allowlists;
- local-first PWA modules;
- device registration and revocation;
- idempotent sync operations;
- conflict tracking and tombstones;
- privacy-aware service worker;
- permissions snapshot and Laravel policies;
- generative UI and operational screen renderer.

### Missing or structurally incomplete

- one authoritative chatbot extension tree;
- exactly five platform application definitions;
- separation of platform application and vertical template;
- Titan Launch and Titan Desk as canonical applications;
- Titan Sprout as an internal Launch engine;
- Dispatch merged into Titan Go;
- full application execution context;
- application-aware intent routing;
- five-app WorkCore policies;
- explicit Interaction Engine adapter;
- communication identity to WorkCore customer boundary;
- application-aware PWA links and offline operations;
- role/application permission intersection;
- report and settings ownership metadata.

## Proposed Canonical Lifecycle

1. Titan Launch creates and grows the business.
2. Titan Desk receives and qualifies enquiries.
3. WorkCore stores authoritative operational records.
4. Titan Zero governs, schedules and administers the business.
5. Titan Go dispatches and completes operational work.
6. Titan Hub enables customer self-service and relationship management.

## Required Legacy Mapping

| Legacy identity | Canonical application | Workflow/module disposition |
|---|---|---|
| titan-zero | Titan Zero | core administration |
| titan-go | Titan Go | field operations |
| titan-dispatch | Titan Go | dispatch workflow |
| titan-front-desk | Titan Desk | communications/intake |
| titan-hub | Titan Hub | customer self-service |
| titan-sprout | Titan Launch | internal Sprout engine |
| titan-marketing | Titan Launch | growth workflow |
| titan-social | Titan Launch | social growth workflow |
| titan-money | Titan Zero / Hub | finance administration and customer payment views |
| titan-teams | Titan Zero / Go | workforce administration and field team views |
| titan-analytics | owning apps / Titan Zero | app reports and cross-platform executive reports |
| titan-locker | Titan Zero / Go | inventory/assets workflows |
| titan-office | Titan Zero | administration/documents workflows |
| titan-quality | Titan Zero / Go | quality/compliance workflows |

## Implementation Recommendation

The next pass should resolve extension authority before editing application schemas:

1. trace marketplace/provider discovery for both chatbot trees;
2. compare the trees and identify unique files;
3. nominate `app/Extensions/Chatbot` as authoritative if runtime evidence confirms it;
4. prevent duplicate provider/route/migration registration;
5. add tests proving only one extension boots;
6. only then introduce the canonical five-application registry.

## Backlog Produced

- `.titan/todo/issues/architecture.md`
- `.titan/todo/issues/chatbot.md`
- `.titan/todo/issues/navigation.md`
- `.titan/todo/issues/ai-runtime.md`
- `.titan/todo/issues/interaction-engine.md`
- `.titan/todo/issues/workcore.md`
- `.titan/todo/issues/offline.md`
- `.titan/todo/issues/pwa.md`
- `.titan/todo/issues/routing.md`
- `.titan/todo/issues/permissions.md`
- `.titan/todo/issues/ui.md`

## Limitations

This was a static GitHub source audit. Runtime claims remain unverified until the branch is checked out in a Laravel-capable environment and the host application is booted. The audit does not mark route, migration, queue, browser, WorkCore or Interaction Engine integration tests as passed.
