# Titan Zero Source Baseline Audit

## Scope

This audit describes the source baseline used by `agent/gpt56-titan-zero-upgrade-workbench`. It does not claim that Laravel or all imported extensions boot successfully. Its purpose is to make the imported source inventory deterministic before provider, route, migration and runtime repairs begin.

## Source authority

- MagicAI is the host application at the repository root.
- WorkCore is authoritative at `app/Domains/WorkCore`.
- The cumulative Titan Zero chatbot is at `app/Extensions/Chatbot`.
- `EXTENSIONS_IMPORT_MANIFEST.json` is the canonical imported-extension inventory.
- `tools/titan-zero-audit/baseline.php` generates `storage/app/audits/source-baseline.json` without modifying application code.

## Verified baseline

| Check | Result |
|---|---:|
| Required host files | Pass |
| Canonical extension directories | 104 |
| Top-level extension manifests | 104 |
| Missing Composer path repositories | 0 |
| Missing npm file dependencies after repair | 0 |
| Files larger than 1 MiB | 10 |
| Duplicate PHP symbols requiring review | 150 |

## Dependency repair

The imported `package.json` and `package-lock.json` referenced `rt-client-0.4.7.tgz`, but the tarball was absent from every available project archive. No application code imported `rt-client`. The active realtime implementation imports `resources/views/default/js/components/realtime-frontend/nativeRTClient.js`, an in-repository native WebSocket replacement. The stale package and lockfile entries were therefore removed; no runtime implementation was replaced.

All five local Composer path repositories exist and contain their own `composer.json` files.

## Duplicate symbol classification

The audit found 150 duplicate fully qualified PHP symbols:

- **82 canonical WorkCore collisions:** classes under `app/Domains/WorkCore` are repeated inside `app/Extensions/Chatbot/System/TitanAI/workcore-runtime/native-runtime`. The chatbot copy is compatibility-only and must remain unable to shadow the host domain.
- **68 extension/module collisions:** standalone extension classes are repeated under chatbot-bundled TitanAI modules or personas, including AIAgent tool packages and AiPersona. These require provider and autoload qualification before any extension is enabled.

The audit reports these collisions but does not delete or rename source. Removal requires route, provider, event, migration, queue and dynamic-resolution tracing in later passes.

## Oversized assets

Ten source assets exceed 1 MiB. They are videos and images in BlogPilot, SocialMediaAgent, FashionStudio, CreativeSuite and AIImagePro. They are not PHP-code defects, but they should be considered during repository, deployment and frontend performance hardening.

## Commands

```bash
php tools/titan-zero-audit/baseline.php
php tests/Architecture/SourceBaselineTest.php
php tests/Architecture/DependencyInputTest.php
npm pkg get scripts dependencies devDependencies
```

When Composer and the required PHP test extensions are available, run the same tests through the project test runner:

```bash
composer validate --strict
php artisan test tests/Architecture/SourceBaselineTest.php tests/Architecture/DependencyInputTest.php
```
