# Titan Money + Titan Pay Branch Status

**Branch:** `agent/titan-money-pay-chat-upgrade`  
**Base:** `main`  
**Prepared:** 29 July 2026, Australia/Sydney

## Purpose

Prepare an isolated integration branch for the Titan Money, Titan Pay and chat-first job-completion upgrade while keeping the clean MagicAI + WorkCore + Titan Zero Chatbot base intact.

## Current state

- `main` is the stable rollback and integration base.
- WorkCore remains the sole authority for operational records and job completion.
- Titan Zero remains the conversation, intent and orchestration layer.
- Titan Money will own canonical financial documents and receivables.
- Titan Pay will own collection sessions, payment methods, QR codes, gateway evidence and reconciliation.
- The v0.5.0 application source is staged under `source-packs/titan-money-titan-pay-v0.5.0/` for controlled comparison; it is not loaded by Composer or Laravel.
- No runtime host file is intentionally replaced during branch preparation.

## First implementation task

Create these evidence files before moving staged code into runtime:

1. `docs/integration/titan-money-titan-pay/base-inventory.md`
2. `docs/integration/titan-money-titan-pay/staged-source-inventory.md`
3. `docs/integration/titan-money-titan-pay/path-collision-ledger.md`
4. `docs/integration/titan-money-titan-pay/authority-ownership-matrix.md`
5. `docs/integration/titan-money-titan-pay/dependency-compatibility.md`
6. `docs/integration/titan-money-titan-pay/security-regression-register.md`

## Branch rules

- Do not merge the staged Laravel shell over the MagicAI host.
- Do not create a second WorkCore, chatbot, company model or authentication system.
- Do not allow AI, chat, webhook or channel code to write WorkCore tables directly.
- Do not copy `.env`, secrets, `vendor`, `node_modules`, generated builds, logs, caches or user uploads.
- Do not mark invoices paid from browser return URLs.
- Do not expose protected payment evidence publicly.
- Use test-first, cumulative passes and preserve working host behaviour.

## Ready condition

The branch is ready for implementation after the extracted source, root plan and provenance record are present and the source-import workflow has completed successfully.
