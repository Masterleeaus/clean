# Agent Instructions — Titan Money + Titan Pay Chat Upgrade

## Scope

This branch integrates Titan Money, Titan Pay and chat-first job completion into the existing MagicAI + WorkCore + Titan Zero Chatbot application.

Read these files before modifying runtime code:

1. `TITAN_MONEY_TITAN_PAY_CHAT_UPGRADE_PLAN.md`
2. `TITAN_MONEY_TITAN_PAY_BRANCH_STATUS.md`
3. `SOURCE_PROVENANCE_TITAN_MONEY_TITAN_PAY.md`
4. `WorkCore Technical Architecture Specification.txt` when available in project references
5. Existing WorkCore and extension documentation in the repository

## Non-negotiable authority boundaries

- MagicAI is the host application and platform shell.
- WorkCore owns operational records and is the only writer for customers, properties, jobs, work orders, tasks and job status.
- Titan Zero owns intent recognition, orchestration and agent delegation; it does not write operational tables.
- Titan Money owns canonical invoices, receivables, credits, payments, allocations and financial audit.
- Titan Pay owns collection sessions, payment methods, QR codes, gateway events, settlement evidence and reconciliation.
- Titan Channels/chat owns customer communication, delivery state, voice and collaboration.
- Secrets and private evidence must use the host vault/protected storage boundary.

## Staged source rule

`source-packs/titan-money-titan-pay-v0.5.0/` is donor/reference source. It must never be added wholesale to Composer autoloading or copied over the repository root.

For every staged file used, record one disposition:

- `retain-existing`
- `adapt-into-host`
- `move-to-native-domain`
- `reference-only`
- `reject-security`
- `reject-architecture`
- `duplicate`

## Required first deliverables

Create before broad implementation:

- `docs/integration/titan-money-titan-pay/base-inventory.md`
- `docs/integration/titan-money-titan-pay/staged-source-inventory.md`
- `docs/integration/titan-money-titan-pay/path-collision-ledger.md`
- `docs/integration/titan-money-titan-pay/authority-ownership-matrix.md`
- `docs/integration/titan-money-titan-pay/dependency-compatibility.md`
- `docs/integration/titan-money-titan-pay/security-regression-register.md`

## Development rules

- Use test-first changes for features and fixes.
- Preserve company, branch, workspace, user, agent, device, conversation, correlation and causation context.
- No state-changing GET routes.
- No browser redirect may confirm payment.
- No payment screenshot alone may mark an invoice paid.
- Issued invoice snapshots are immutable.
- Payment and gateway events are idempotent and append-only.
- Do not send duplicate or backward-moving collection notices.
- Do not expose financial evidence through public storage.
- Do not create a second authentication, company, chatbot, WorkCore or finance authority.
- Do not commit secrets, `.env`, installed dependencies, generated builds, caches, logs or user uploads.

## Verification gates

Before claiming a pass complete, run all checks available for that pass and record exact output. Final acceptance requires:

- Composer validation and installation
- deterministic lockfiles
- Laravel boot and route discovery
- disposable-database migrations
- unit, feature, security and concurrency tests
- frontend build
- scheduler and queue checks
- PayPal/provider sandbox verification
- customer channel delivery receipts
- complete chat -> WorkCore job completion -> Titan Money invoice -> Titan Pay QR/payment -> allocation/receipt workflow

Keep this branch isolated until those gates are green.
