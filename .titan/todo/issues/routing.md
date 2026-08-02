# Routing Issues

## ROUTE-001 — Generic app routes are bound to a legacy template registry

### Current State

The extension exposes generic Titan app list, show, install and manifest routes backed by `TitanRegistry`, which currently loads vertical template directories rather than a five-application platform registry.

### Required Changes

Preserve route shapes where practical but change their authority to the canonical application registry. Expose vertical templates through a separate endpoint or nested resource. Add contract tests for exactly five platform applications.

### Why

The current route names can be reused, but their returned concept is incorrect for the new architecture.

### Risk

High. Existing clients may expect old slugs and manifest structures.

### Priority

Critical

### Dependencies

Application registry, template registry, Titan controller, manifests and API tests.

### Estimated Work

Medium

### Completion Status

Pending

---

## ROUTE-002 — Legacy application slugs require explicit compatibility rules

### Current State

Stored configurations, deep links and PWA code may reference `titan-dispatch`, `titan-front-desk`, `titan-sprout` and other retired top-level app slugs.

### Required Changes

Create a versioned slug migration map. At minimum:

- `titan-dispatch` → `titan-go` with dispatch workflow;
- `titan-front-desk` → `titan-desk`;
- `titan-sprout` → `titan-launch` with Sprout engine workflow.

Map remaining legacy app slugs to application/workflow pairs or mark them deliberately unsupported. Return deprecation metadata and avoid silent semantic changes.

### Why

Deleting old slugs would break links, saved builders, offline state and integrations.

### Risk

High. Some old app names represent multiple functions and require product-level mapping decisions.

### Priority

Critical

### Dependencies

Application ownership matrix, navigation, PWA, WorkCore manifests and persisted configuration migration.

### Estimated Work

Medium

### Completion Status

Pending

---

## ROUTE-003 — Live route collision validation remains outstanding

### Current State

A previous extension audit explicitly deferred `artisan route:list` and duplicate route-name validation until installation in the authoritative Laravel host. The extension and host each register chatbot-related routes.

### Required Changes

Run host boot and route inventory before implementation routes are merged. Capture path, method, name, middleware and controller for all chatbot/Titan endpoints. Add an automated collision check.

### Why

Static source inspection cannot prove that provider discovery and route groups do not collide at runtime.

### Risk

High if untested; low implementation risk.

### Priority

High

### Dependencies

Runnable host environment, installed extensions, Laravel route cache and provider discovery.

### Estimated Work

Small

### Completion Status

Pending

---

## ROUTE-004 — Public and authenticated application endpoints need separate policies

### Current State

The provider exposes authenticated Titan app routes and a public app-list route. The current registry mixes application discovery with installable templates.

### Required Changes

Define which five-app metadata is public, tenant-scoped or administrator-only. Do not expose internal tool lists, permissions or WorkCore mappings through public manifests.

### Why

Titan Hub may need public/customer entry points, while Titan Zero and internal application configuration require stronger authorization.

### Risk

Medium. Existing frontend bootstrap may depend on public metadata.

### Priority

High

### Dependencies

Application registry serializers, route middleware, permission model and frontend bootstrap.

### Estimated Work

Medium

### Completion Status

Pending
