# Phase 03 Delta — Canonical Five-Application Registry

## Date

2026-08-02

## Branch

`feature/titan-five-app-pass3-registry`

## Objective

Replace the active 14-app shell model with five canonical Titan platform applications while retaining explicit compatibility for legacy app slugs and separate vertical templates.

## Canonical Applications

- Titan Zero
- Titan Go
- Titan Launch
- Titan Desk
- Titan Hub

## Production Files Added

- `app/Extensions/Chatbot/System/TitanShell/PlatformApplicationRegistry.php`
- `app/Extensions/Chatbot/resources/titan-apps/TemplateSchemas/titan-launch.json`
- `app/Extensions/Chatbot/resources/titan-apps/TemplateSchemas/titan-desk.json`
- `app/Extensions/Chatbot/resources/titan-apps/TemplateSchemas/legacy-index-v1.json`

## Production Files Modified

- `app/Extensions/Chatbot/System/TitanShell/TemplateSchema.php`
- `app/Extensions/Chatbot/System/Http/Controllers/Api/TitanController.php`
- `app/Extensions/Chatbot/resources/titan-apps/TemplateSchemas/index.json`
- `app/Extensions/Chatbot/resources/views/home/edit-window/edit-steps/titan-shell-builder.blade.php`
- `app/Extensions/Chatbot/resources/js/titan-operational-screens.js`
- `app/Extensions/Chatbot/public/vendor/chatbot/js/titan-operational-screens.js`
- `app/Extensions/Chatbot/extension.json`

## Tests Added

- `tests/Feature/TitanArchitecture/FiveApplicationRegistryTest.php`

## Documentation Added

- `.titan/reports/five-application-registry.md`
- `.titan/deltas/phase03.md`

## Behaviour Changes

1. The top-level application registry contains exactly five entries.
2. The builder lists only those five applications.
3. The Titan API returns five canonical applications.
4. Titan Launch and Titan Desk have first-class schemas.
5. Legacy app slugs resolve to their canonical application.
6. The operational runtime selects from five profiles.
7. Browser events carry canonical `application` context while preserving the legacy `template` field.
8. The extension manifest no longer advertises a 14-app shell.
9. The previous complete schema index is retained as a migration archive rather than deleted.

## Compatibility Preserved

- Existing legacy application schema files remain in place.
- Existing vertical and industry templates remain accessible through `TitanRegistry`.
- The former complete schema catalogue is preserved byte-for-byte in `legacy-index-v1.json`.
- `templates` remains an API compatibility alias for the canonical application list.
- Previous WorkCore manifests remain available under `legacy_workcore_apps`.
- Legacy application slugs are mapped rather than abruptly rejected.
- Browser events retain the existing `template` field alongside canonical `application` context.

## Database Changes

None.

## Migration Changes

None.

## Route Changes

No route names or paths were changed.

## Verification Status

The following independent scratch checks were executed successfully using PHP 8.4 and Node.js 22:

- PHP syntax validation for `PlatformApplicationRegistry.php`;
- PHP syntax validation for `TemplateSchema.php`;
- PHP syntax validation for `TitanController.php`;
- registry contract execution confirming exactly five canonical slugs;
- legacy mapping execution for Titan Sprout and Titan Front Desk;
- canonical schema resolution and five-schema count;
- JavaScript syntax validation for the operational runtime;
- JavaScript contract check confirming exactly five operational profiles;
- JSON parsing and slug-order validation for the five-app index;
- JSON parsing for Titan Launch and Titan Desk schemas.

The GitHub connector cannot execute the complete checked-out Laravel application. Required CI commands remain:

```bash
php artisan test tests/Feature/TitanArchitecture/FiveApplicationRegistryTest.php
php artisan test app/Extensions/Chatbot/tests/Feature/TitanShell/TitanOperationalScreensContractTest.php
node app/Extensions/Chatbot/tests/js/titan-production-readiness.test.js
```

## Deferred

- Canonical execution context envelope.
- AI and Interaction Engine application-aware routing.
- Five-app WorkCore manifest consolidation.
- Offline and PWA application-context persistence.
- Full navigation and permissions ownership refinement.
