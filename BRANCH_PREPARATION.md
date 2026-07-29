# Branch Preparation

## Branch

`agent/titan-zero-pwa-upgrade`

## Purpose

This branch is the isolated integration workspace for upgrading the existing Titan Zero chatbot PWA. `main` remains untouched until changes are reviewed.

## Prepared source layout

- MagicAI host remains at repository root.
- WorkCore remains under `app/Domains/WorkCore`.
- Interaction Engine is installed as `packages/titanzero/interaction-engine`.
- The canonical chatbot PWA is installed under `app/Extensions/Chatbot`.
- Donor source is quarantined under `tools/donor-sources` and is not autoloaded or bundled.
- The Extension SDK is under `tools/titan-zero-extension-sdk`.

## Initial verification commands

```bash
composer validate --no-check-publish
find app packages -type f -name '*.php' -print0 | xargs -0 -n1 php -l
php packages/titanzero/interaction-engine/tests/run.php
node --test app/Extensions/Chatbot/tests/**/*.test.js
bash scripts/titan-zero/verify-source.sh
```

Dependency-backed Laravel boot, migration, Vite, and browser tests require `composer install` and `npm ci`.
