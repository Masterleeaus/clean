# Titan Money + Titan Pay Branch Status

**Branch:** `agent/titan-money-pay-chat-upgrade`  
**Base:** `main`  
**Prepared:** 30 July 2026, Australia/Sydney  
**Status:** Ready for Pass 0 discovery and collision analysis

## Purpose

Provide an isolated integration branch for the Titan Money, Titan Pay and chat-first job-completion upgrade while keeping the clean MagicAI + WorkCore + Titan Zero Chatbot base authoritative.

## Completed preparation

- The branch contains current `main` in its ancestry and is not behind the clean base.
- WorkCore remains the sole authority for operational records and job completion.
- Titan Zero remains the conversation, intent and orchestration layer.
- Titan Money is the proposed authority for canonical financial documents and receivables.
- Titan Pay is the proposed authority for collection sessions, payment methods, QR codes, gateway evidence and reconciliation.
- The verified v0.5.0 source archive was imported by GitHub Actions after SHA-256, ZIP integrity, wrapper and exact 404-file checks.
- The extracted archive is staged under `source-packs/titan-money-titan-pay-v0.5.0/` with one additional `STAGED_SOURCE_NOTICE.md` file.
- The staged source is not loaded by Composer or Laravel.
- No runtime host file was replaced during branch preparation.
- Temporary source-import workflows were removed after successful import.
- Bootstrap issue `#21` closed automatically after the import completed.

## Branch controls

- `TITAN_MONEY_TITAN_PAY_CHAT_UPGRADE_PLAN.md`
- `SOURCE_PROVENANCE_TITAN_MONEY_TITAN_PAY.md`
- `SOURCE_IMPORT_TITAN_MONEY_TITAN_PAY.json`
- `AGENTS.md`
- `docs/integration/titan-money-titan-pay/README.md`
- `tools/titan-money-pay-inventory.php`
- `tools/verify-titan-money-pay-branch.php`

## First implementation task

Generate and review these evidence artifacts before moving staged code into runtime:

1. `docs/integration/titan-money-titan-pay/base-inventory.json`
2. `docs/integration/titan-money-titan-pay/staged-source-inventory.json`
3. `docs/integration/titan-money-titan-pay/path-collision-ledger.md`
4. `docs/integration/titan-money-titan-pay/authority-ownership-matrix.md`
5. `docs/integration/titan-money-titan-pay/dependency-compatibility.md`
6. `docs/integration/titan-money-titan-pay/security-regression-register.md`

Run:

```bash
php tools/verify-titan-money-pay-branch.php
php tools/titan-money-pay-inventory.php
```

## Branch rules

- Do not merge the staged Laravel shell over the MagicAI host.
- Do not create a second WorkCore, chatbot, company model or authentication system.
- Do not allow AI, chat, webhook or channel code to write WorkCore tables directly.
- Do not copy secrets, installed dependencies, generated builds, logs, caches or user uploads into runtime.
- Do not mark invoices paid from browser return URLs.
- Do not expose protected payment evidence publicly.
- Use test-first, cumulative passes and preserve working host behaviour.

## Ready condition

Satisfied. The root plan, provenance, machine-readable manifest, agent instructions, preparation tools and verified extracted source are present. Runtime integration has not started.
