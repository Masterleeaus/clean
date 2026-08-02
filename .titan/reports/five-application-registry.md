# Five-Application Registry Report

## Pass

Pass 3 — Canonical Application Registry

## Date

2026-08-02

## Decision

Titan now has exactly five canonical customer-facing platform applications:

1. Titan Zero
2. Titan Go
3. Titan Launch
4. Titan Desk
5. Titan Hub

`app/Extensions/Chatbot/System/TitanShell/PlatformApplicationRegistry.php` is the canonical application registry.

## Separation of Concerns

The migration separates:

- platform applications;
- vertical and industry templates;
- WorkCore modules;
- internal engines.

The existing `TitanRegistry` remains available for vertical/business template compatibility. It is no longer the source of truth for the top-level application list.

Titan Sprout remains in the repository as an internal engine/template source for Titan Launch. It is not a sixth platform application.

## Legacy Slug Mapping

| Legacy slug | Canonical application |
|---|---|
| `titan-dispatch` | Titan Go |
| `titan-front-desk` | Titan Desk |
| `titan-sprout` | Titan Launch |
| `titan-marketing` | Titan Launch |
| `titan-social` | Titan Launch |
| `titan-money` | Titan Zero |
| `titan-teams` | Titan Zero |
| `titan-analytics` | Titan Zero |
| `titan-locker` | Titan Zero |
| `titan-office` | Titan Zero |
| `titan-quality` | Titan Zero |

Unknown vertical/template slugs are not force-mapped to a platform application. Their individual template schemas remain resolvable through the compatibility template layer.

## Active Surface Changes

### Builder

The shell builder receives only the five canonical application schemas and displays a dynamic application count.

### Titan API

`/api/v2/titan/apps` now returns:

- `applications`: five canonical application definitions;
- `templates`: compatibility alias of the five application definitions;
- `vertical_templates`: retained template catalogue;
- `legacy_slug_map`: explicit migration map;
- `workcore_apps`: canonical five-app WorkCore projections;
- `legacy_workcore_apps`: retained pre-migration WorkCore manifests.

### Operational Runtime

The operational runtime contains five application profiles and canonicalises legacy application slugs before selecting a profile. Browser events now carry both:

- `application`: canonical field;
- `template`: compatibility field.

### Schemas

New first-class schemas were added for:

- Titan Launch;
- Titan Desk.

The published `TemplateSchemas/index.json` now identifies only the five platform applications. The original individual legacy and vertical schema files remain present for compatibility and migration work.

## WorkCore Boundary

This pass does not move operational records into the chatbot extension. WorkCore remains the business record authority. Titan Launch and Titan Desk schemas declare requested workflows and read models, but their full WorkCore tool consolidation remains scheduled for the later WorkCore migration pass.

## Verification Added

`tests/Feature/TitanArchitecture/FiveApplicationRegistryTest.php` checks:

- exactly five canonical slugs;
- legacy slug resolution;
- canonical schema listing;
- legacy schema migration;
- exclusion of engines/modules/templates from the application list;
- five-entry published index;
- removal of the hard-coded 14-app builder label;
- exactly five operational runtime profiles.

## Remaining Work

- Propagate active application through the canonical execution context.
- Consolidate WorkCore manifests into five application policies.
- Update permissions, AI routing, offline records and PWA deep links.
- Decide when the retained legacy template catalogue can be physically archived or removed.
