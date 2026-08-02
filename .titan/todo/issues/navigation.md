# Navigation Issues

## NAV-001 — Builder and schema index expose 14 top-level applications

### Current State

The shell builder loads every `TemplateSchema` entry and displays a hard-coded “14 apps” badge. The schema index contains Titan Zero, Titan Go, Titan Hub, Titan Dispatch, Titan Money, Titan Teams, Titan Analytics, Titan Front Desk, Titan Marketing, Titan Social, Titan Sprout, Titan Locker, Titan Office and Titan Quality.

### Required Changes

Expose only the five canonical platform applications in the app selector. Move vertical selection into a separate template/business configuration control. Implement an explicit legacy mapping rather than deleting old schemas immediately.

### Why

Users currently see modules and engines as peer applications, which conflicts with the new lifecycle and ownership model.

### Risk

High. Saved builder configurations may reference old slugs and customised navigation arrays.

### Priority

Critical

### Dependencies

Application registry, template registry, builder persistence, legacy slug resolver and UI tests.

### Estimated Work

Large

### Completion Status

Pending

---

## NAV-002 — Titan Dispatch, Front Desk and Sprout need controlled migration

### Current State

Titan Dispatch, Titan Front Desk and Titan Sprout each have their own schema, navigation, prompts, WorkCore mappings and operational screen profile.

### Required Changes

- Merge Titan Dispatch navigation and workflows into Titan Go.
- Rename and expand Titan Front Desk into Titan Desk.
- Move Titan Sprout navigation and generation workflows under Titan Launch.
- Preserve old URLs and persisted slugs through aliases, redirects or configuration migration.

### Why

These are the three clearest direct ownership changes required by the five-application architecture.

### Risk

High. Blind removal would strand saved chatbot configurations and deep links.

### Priority

Critical

### Dependencies

Route compatibility, schema migration, WorkCore manifests, PWA deep links, AI prompts and builder UI.

### Estimated Work

Large

### Completion Status

Pending

---

## NAV-003 — Global and application settings are not separated

### Current State

`TemplateNavigation` exposes the same broad settings list—including AI providers, privacy, device security, offline sync, WorkCore, permissions, notifications and channels—through each resolved shell.

### Required Changes

Classify settings as global, application-specific, device-specific or user-specific. Surface global business/platform settings through Titan Zero. Keep only application-specific settings in Titan Go, Launch, Desk and Hub. Device-local privacy, vault and offline controls may remain universally accessible where required for safety.

### Why

The new architecture assigns global settings to Titan Zero and prevents each application from presenting itself as a separate platform administration surface.

### Risk

Medium. Hiding privacy or device controls too aggressively could reduce offline recoverability.

### Priority

High

### Dependencies

Settings registry, application registry, role/permission filtering and frontend shell.

### Estimated Work

Medium

### Completion Status

Pending

---

## NAV-004 — Navigation rendering is not visibly permission-filtered at the inspected boundary

### Current State

The app drawer renders the resolved schema drawer directly. The scanned Blade boundary does not show filtering by effective user, role or application permissions.

### Required Changes

Add a central navigation policy/filter that receives the canonical application context and removes inaccessible items before rendering. Client-side hiding must not replace server-side authorization.

### Why

The five applications target different actors, including managers, field workers, reception staff and customers.

### Risk

High if navigation reveals sensitive functions; medium implementation risk because authorization may exist elsewhere and must not be duplicated.

### Priority

High

### Dependencies

Permission resolver, role context, application registry, policy tests and UI rendering.

### Estimated Work

Medium

### Completion Status

Pending
