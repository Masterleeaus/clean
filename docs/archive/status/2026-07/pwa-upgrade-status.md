> [!IMPORTANT]
> **Historical record — not current implementation guidance.** This document is retained for provenance because it describes an earlier branch, source version, import, or completed upgrade pass. Use `docs/README.md` and `docs/plans/CURRENT_UPGRADE_PLAN.md` for current guidance.

# Upgrade Status

## Current branch

`agent/titan-zero-pwa-upgrade`

## Pass 0 — branch preparation

- [x] Isolated branch created without modifying `main`.
- [x] Existing full MagicAI, WorkCore, Chatbot 6.9, and extension source selected as branch base.
- [x] Newer local Interaction Engine package imported and registered as a Composer path package.
- [x] Root fourteen-pass upgrade plan added.
- [x] Authority map, provenance, agent rules, verification script, and permanent read-only CI added.
- [x] AppForge, MobileKit, Vue website-builder, Flutter shell, and selected build/editor controller code quarantined under `tools/donor-sources/mobile-builder`.
- [x] Donor code confirmed non-runtime, non-autoloaded, and free of environment, private-key, signing-profile, and keystore files.
- [x] UTF-8 BOM JSON regression coverage added without weakening malformed-JSON rejection.
- [x] GitHub verification completed against the real branch: source contracts, strict JSON, private-key scan, 7,122 PHP syntax checks, Composer metadata, 24 Interaction Engine tests, donor quarantine contract, and shell syntax passed.
- [ ] Composer lock reconciliation, dependency-backed Laravel boot, migrations, Vite, and browser tests — scheduled for Pass 1.

## Prepared branch snapshot

- Source files: 12,312
- Source bytes: 167,323,961
- Quarantined mobile-builder donor files: 616
- Canonical PWA: `app/Extensions/Chatbot`
- WorkCore authority: `app/Domains/WorkCore`
- Interaction Engine: `packages/titanzero/interaction-engine`

## Known baseline warning

`composer.json` contains `titanzero/interaction-engine`, but `composer.lock` does not yet contain that package. This is explicitly scheduled for Pass 1 and must be resolved through Composer rather than manual lock-file editing.

## Next pass

Pass 1: host boot, Composer lock reconciliation, provider registration, migration preflight, and baseline integration tests.
