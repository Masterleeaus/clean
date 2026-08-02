# PWA Issues

## PWA-001 — PWA manifest represents one generic Titan Zero Chatbot shell

### Current State

The manifest identifies the installable application as “Titan Zero Chatbot”, starts at `/chatbot?source=pwa`, uses root scope, and exposes generic Chat, Help and New Conversation shortcuts.

### Required Changes

Keep one shared installable conversational shell unless product testing proves separate PWAs are necessary. Rename it to represent the Titan platform chatbot and add application-aware deep links/shortcuts for Titan Zero, Go, Launch, Desk and Hub. Restore the last authorised active application on launch.

### Why

Five duplicated PWAs would also duplicate service workers, caches, local databases and upgrade behaviour. One application-aware shell better matches the shared runtime architecture.

### Risk

Medium. Manifest identity changes may affect installed shortcuts and update behaviour.

### Priority

High

### Dependencies

Application registry, route/deep-link strategy, frontend context store and product naming decision.

### Estimated Work

Medium

### Completion Status

Pending

---

## PWA-002 — Service worker precache references legacy application shell assets

### Current State

The service worker safely avoids sensitive/API caching and precaches the operational shell, WorkCore local-first modules, settings and generative UI. The operational screen asset currently embeds the 14-app profile list.

### Required Changes

Preserve the privacy and cache-integrity controls. Version the cache when the five-app shell lands, migrate local state, and verify all new registry/context assets are included. Avoid caching authenticated application data in Cache Storage.

### Why

Cached legacy scripts can keep the old application model active after server deployment.

### Risk

High for stale installed clients; low architectural risk if existing update/rollback mechanics are retained.

### Priority

High

### Dependencies

Operational screen refactor, cache version, local database migration, PWA readiness tests and deployment asset publication.

### Estimated Work

Medium

### Completion Status

Pending

---

## PWA-003 — Notification and launch targets are not application-aware

### Current State

Push notifications default to `/chatbot?source=notification`, and shortcut/launch behaviour does not include a canonical application ID or workflow target.

### Required Changes

Use validated deep links carrying application and safe workflow/record references. On launch, resolve permissions and fall back to an authorised application home rather than trusting notification data.

### Why

A field assignment should open Titan Go, an intake message should open Titan Desk, and a customer booking update should open Titan Hub.

### Risk

Medium. Notification payloads are external inputs and must be sanitised.

### Priority

Medium

### Dependencies

Deep-link router, application context resolver, notification service and permission policy.

### Estimated Work

Medium

### Completion Status

Pending

---

## PWA-004 — Application switching and offline retention policy are not centrally governed

### Current State

Each template schema contains its own offline records, packs, retention and conflict rules. There is no canonical five-app policy layer above these template definitions.

### Required Changes

Define base offline policy per canonical application and allow vertical/workflow schemas to extend it without replacing security, conflict or authority rules.

### Why

Vertical templates should configure data needs but should not redefine platform security or record authority.

### Risk

Medium. Existing template-specific offline packs must be preserved through inheritance/mapping.

### Priority

High

### Dependencies

Application registry, template schema separation, local pack manager and sync policy tests.

### Estimated Work

Medium

### Completion Status

Pending
