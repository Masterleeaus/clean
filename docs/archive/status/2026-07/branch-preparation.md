> [!IMPORTANT]
> **Historical record — not current implementation guidance.** This document is retained for provenance because it describes an earlier branch, source version, import, or completed upgrade pass. Use `docs/README.md` and `docs/plans/CURRENT_UPGRADE_PLAN.md` for current guidance.

# Branch Preparation

## Branch

`agent/titan-zero-pwa-upgrade`

## Purpose

This branch is the isolated integration workspace for upgrading the existing Titan Zero chatbot PWA. Upgrade source remains isolated from `main` until an explicit source-promotion decision is made.

## Prepared source layout

- MagicAI host remains at repository root.
- WorkCore remains under `app/Domains/WorkCore`.
- Interaction Engine is installed as `packages/titanzero/interaction-engine`.
- The canonical chatbot PWA is installed under `app/Extensions/Chatbot`.
- Mobile-builder donor source is quarantined under `tools/donor-sources/mobile-builder` and is not autoloaded or bundled.
- Root agent rules, authority documentation, provenance, source verification, and permanent read-only CI are present.

## Initial verification commands

```bash
python3 tests/contracts/titan_zero_source_verification_test.py
composer validate --no-check-publish --no-check-lock
find app packages -type f -name '*.php' -print0 | xargs -0 -n1 php -l
php packages/titanzero/interaction-engine/tests/run.php
node --test app/Extensions/Chatbot/tests/**/*.test.js
bash scripts/titan-zero/verify-source.sh
```

Dependency-backed Laravel boot, migration, Vite, and browser tests require `composer install` and `npm ci` and are scheduled for Pass 1.

## Promotion note

This work branch is a clean source snapshot with separate history from the current `main` branch. Do not attempt an ordinary merge without first choosing an explicit source-promotion or rebase strategy.
