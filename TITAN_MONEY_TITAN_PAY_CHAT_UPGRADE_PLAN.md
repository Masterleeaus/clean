# Titan Money, Titan Pay and Chat-First Operations Upgrade Plan

**Repository:** `Masterleeaus/clean`  
**Working branch:** `agent/titan-money-pay-chat-upgrade`  
**Stable base:** `main`  
**Prepared:** 29 July 2026, Australia/Sydney  
**Primary staged source:** `TitanZero-Meetup-TitanMoney-TitanPay-v0.5.0-FULL`

## 1. Goal

Integrate the verified Titan Money, Titan Pay and chat-first job-completion workflow into the clean Titan Zero application without replacing the MagicAI host, duplicating WorkCore, creating a second chatbot, or allowing AI code to write operational records directly.

The finished path must support:

```text
User chat or voice
    -> Titan Zero intent and orchestration
    -> WorkCore governed job action
    -> immutable job.completed event
    -> Titan Money invoice agent
    -> Titan Pay collection session and QR code
    -> customer app / Channels / SMS / email / optional voice
    -> verified payment reconciliation
    -> receipt and overdue follow-up
```

## 2. Authority boundaries

These boundaries are mandatory throughout every pass.

| Capability | Authority |
|---|---|
| Host application, accounts, subscription shell and platform administration | MagicAI host |
| Conversation, channels, voice, presence and collaboration UI | Titan Zero Chatbot / Meetup-derived communication layer |
| Customers, properties, jobs, work orders, tasks, scheduling, workforce and operational state | WorkCore |
| Financial documents, receivables, credits, payments, allocations and financial audit | Titan Money |
| Collection sessions, PayID, bank transfer, cash, PayPal card checkout, gateway evidence and reconciliation | Titan Pay |
| Reasoning, intent recognition, planning and agent delegation | Titan Zero and the five-tier AI system |
| Secrets and protected payment evidence | Titan Vault or host secret references |
| Historical correction and reversible audit | Titan Rewind / canonical audit layer |

No AI controller, chatbot endpoint, provider callback or agent may modify WorkCore tables directly. WorkCore must validate permissions, execute the action and emit the business event.

## 3. Source policy

The clean repository remains authoritative. Staged sources are evidence and donor code until dispositioned.

- Do not overwrite the host `composer.json`, routes, authentication, tenancy, user model or service providers blindly.
- Do not copy a second Laravel application shell into runtime.
- Do not retain duplicate `Finance`, `ZeroPay` or `InvoixPro` authorities.
- Preserve only `TitanMoney` and `TitanPay` names for the new bounded contexts.
- Preserve existing repository-specific fixes and extension registrations.
- Do not commit `.env`, credentials, provider keys, `vendor/`, `node_modules/`, generated builds, runtime uploads, logs or caches.
- Licensed source must remain in this private repository.

## 4. Prepared branch layout

```text
/
├── TITAN_MONEY_TITAN_PAY_CHAT_UPGRADE_PLAN.md
├── TITAN_MONEY_TITAN_PAY_BRANCH_STATUS.md
├── SOURCE_PROVENANCE_TITAN_MONEY_TITAN_PAY.md
├── source-packs/
│   └── titan-money-titan-pay-v0.5.0/
│       ├── README.md
│       └── extracted source files
├── docs/integration/titan-money-titan-pay/
├── app/Domains/TitanMoney/                 # final native bounded context
├── app/Domains/TitanPay/                   # final native bounded context
├── app/Domains/WorkCore/                   # existing authority; extend, do not duplicate
└── app/Extensions/TitanZeroChatbot/        # existing conversation surface
```

The staged source directory is not runtime code. Files move into runtime only after collision review, adaptation and tests.

## 5. Upgrade passes

### Pass 0 — Branch and provenance baseline

- Record source archive names, hashes, versions and file counts.
- Capture the current `main` commit and branch start point.
- Inventory existing WorkCore, chatbot, finance, payment, agent and channel code.
- Generate a path-collision ledger between the clean base and v0.5.0.
- Mark every donor file `retain`, `adapt`, `replace`, `reference`, `reject` or `already present`.

**Gate:** no runtime merge begins until the collision and ownership ledgers exist.

### Pass 1 — Host compatibility and dependency reconciliation

- Compare Laravel, PHP, Composer, Node, Vite, queue and scheduler assumptions.
- Reconcile v0.5.0 dependencies against the Laravel 10 MagicAI host.
- Preserve the host lockfiles and add only verified missing packages.
- Remove any stale lockfile or package requirement that cannot install cleanly.
- Register domain providers through the host’s existing provider/module conventions.

**Gate:** Composer validation, autoload discovery and host boot must succeed before domain wiring.

### Pass 2 — Canonical company and tenancy bridge

- Map MagicAI account/team context to WorkCore company membership.
- Enforce company, branch and workspace context before model binding.
- Replace v0.5.0 assumptions about standalone users or companies with canonical host identities.
- Add cross-company isolation tests for every finance and payment endpoint.

**Gate:** no cross-company customer, invoice, payment, collection session or gateway access is possible.

### Pass 3 — WorkCore job-completion contract

- Inspect the canonical WorkCore job/work-order service and action interfaces.
- Implement a narrow `WorkCoreJobGateway` adapter against the real WorkCore API.
- Complete jobs only through WorkCore validation, permissions and transactions.
- Require checklists, evidence, final quantities and variations where configured.
- Emit an idempotent billable event after successful completion.

**Gate:** repeated chat commands cannot complete or invoice the same job twice.

### Pass 4 — Titan Money bounded-domain integration

- Port precise minor-unit money, GST profiles, invoice numbering and lifecycle services.
- Use WorkCore customers, properties, jobs and service lines through projections or stable references.
- Preserve immutable issued snapshots and document hashes.
- Wire credit notes, allocations, outbox events, audit context and agent attribution.
- Remove duplicate host or WorkCore invoice implementations only after migration mapping is proven.

**Gate:** only Titan Money can own issued invoice and receivable state.

### Pass 5 — Titan Pay bounded-domain integration

- Port secure collection sessions, hashed public tokens and QR generation.
- Present payment methods in the required order:
  1. PayID
  2. Bank transfer
  3. Cash
  4. Credit/debit card through PayPal
- Show PayID and bank transfer only when protected company details exist.
- Verify PayPal events server-side; browser returns never confirm payment.
- Keep evidence private and resolve credentials through Titan Vault or host secret references.

**Gate:** verified allocation remains the only path that can mark an invoice paid.

### Pass 6 — Chat-first Titan Zero tool routing

- Integrate job-completion intent with the existing chatbot, not a parallel chat controller.
- Reuse the current conversation, user, company, device and correlation context.
- Resolve ambiguous jobs conversationally.
- Reject questions, negations and future intentions as execution commands.
- Return structured action cards containing invoice details, payment link and QR code.
- Keep text and voice on the same governed execution pipeline.

**Gate:** “I completed job TZ-1042” can execute; “Is it complete?” and “I have not completed it” cannot.

### Pass 7 — Invoice agent and approval governance

- Register auto-invoice tools with the canonical five-tier AI/agent runtime.
- Apply company automation policies, authority limits and source eligibility rules.
- Auto-issue only within delegated limits.
- Create durable human approval requests for exceptions and high-value invoices.
- Record agent, user, company, conversation, correlation and reason on every action.

**Gate:** scheduled agents have no unrestricted permissions and no anonymous audit actions.

### Pass 8 — Customer delivery through Channels

- Convert Titan Money outbox events into canonical channel delivery jobs.
- Deliver to the customer app/internal Channels first when available.
- Add SMS, email and optional voice/WhatsApp adapters through configured providers.
- Record actual provider acceptance and delivery receipts.
- Create internal exceptions when no customer route succeeds.
- Never mark “handed off” as “delivered” without a receipt or accepted internal message.

**Gate:** invoice delivery has a traceable terminal state: delivered, failed, expired or requires intervention.

### Pass 9 — Receivables and late-invoice follow-up

- Retain due-date, 3-day, 7-day, 14-day and 30-day stages.
- Make stage progression forward-only and idempotent.
- Stop reminders for paid, voided, written-off, disputed or collection-hold invoices.
- Reuse or safely rotate collection sessions rather than generating unlimited links.
- Require approval for final notices and automated voice escalation by default.
- Respect timezone, quiet hours, consent and opt-out preferences.

**Gate:** rerunning queues or schedulers cannot send duplicate or backward-moving notices.

### Pass 10 — Offline and PWA integration

- Compare the current chatbot PWA with Pass 12 and preserve the stronger implementation.
- Queue job-completion intent, invoice drafts, cash receipts and payment claims safely offline.
- Do not cache secrets, private invoices or sensitive provider responses in the service worker.
- Preserve unsynchronised operations and expose conflict states.
- Reconcile device-created UUIDs and server versions through WorkCore sync contracts.

**Gate:** offline work survives restart and synchronises without silent overwrite.

### Pass 11 — Five-tier AI and Interaction Engine integration

- Register Titan Money and Titan Pay tools through the canonical agent/interaction registries.
- Avoid duplicating WorkCore or chatbot orchestration.
- Apply confidence, approval and escalation policies to financial actions.
- Expose deterministic action schemas and structured results for generative UI.
- Ensure Interaction Engine workflows call domain services rather than database models.

**Gate:** every AI-triggered mutation follows the same service and permission path as a human action.

### Pass 12 — UI integration

- Add Titan Money and Titan Pay to the existing navigation and role-aware menus.
- Build native MagicAI/Titan Zero pages rather than retaining the donor application shell.
- Add invoice, payment, automation, claim, gateway and reconciliation views.
- Add chat action cards, QR display, approval prompts and delivery status.
- Keep mobile, tablet and desktop layouts aligned with the Titan Zero design language.

**Gate:** no duplicate dashboard, authentication shell or standalone customer portal remains.

### Pass 13 — Data migration and compatibility

- Build dry-run importers for any existing finance, ZeroPay or InvoixPro data.
- Treat legacy “paid” labels as claims requiring reconciliation, never verified truth.
- Preserve source IDs, hashes and exception reports.
- Make migrations safe for both fresh installs and upgrades.
- Move host-wide migrations out of bounded-domain directories.

**Gate:** import is idempotent and reruns do not duplicate lines, payments or events.

### Pass 14 — Security hardening

- Audit public routes, route-model binding, uploads, callbacks and webhook URLs.
- Add SSRF controls, replay protection, signature verification and rate limits.
- Keep financial files private with expiring authorised access.
- Remove default application keys, debug defaults and synchronous production queues.
- Scan for plaintext secrets, hidden provisioning, destructive installers and executable ZIP imports.

**Gate:** security regression tests cover tenant isolation, payment authority, file privacy and webhook verification.

### Pass 15 — Test and build restoration

- Regenerate deterministic Composer and npm lockfiles from the clean host.
- Run migrations against disposable MySQL and SQLite test databases where supported.
- Run unit, feature, integration, security, concurrency and end-to-end tests.
- Build frontend assets and test the PWA manifest/service worker.
- Verify scheduler, queues, mail, Channels, SMS and PayPal sandbox flows.

**Gate:** no completion claim without fresh full-suite evidence.

### Pass 16 — End-to-end acceptance workflow

Prove the complete scenario:

1. A worker tells Titan Zero a job is complete.
2. Titan Zero resolves the correct WorkCore job.
3. WorkCore validates and completes the job.
4. Titan Money generates and issues the invoice under policy.
5. Titan Pay creates the secure collection session and QR.
6. The customer receives the invoice through the preferred available channel.
7. The customer pays by PayID, bank transfer, cash or PayPal card.
8. Titan Pay verifies and reconciles the payment.
9. Titan Money allocates it and issues a receipt.
10. Follow-up stops immediately.

**Gate:** audit records connect conversation, agent, WorkCore job, invoice, payment session, provider evidence, allocation and receipt.

### Pass 17 — Release preparation

- Produce deployment and rollback guides.
- Document required environment variables without values.
- Produce a source manifest, migration manifest and event catalogue.
- Create a cumulative full build and a minimal delta from clean `main`.
- Verify both packages independently.
- Open a draft pull request only after branch checks are green.

## 6. Required test groups

- Money, GST, discounts, rounding and invoice numbering
- Invoice lifecycle and immutable snapshots
- WorkCore job completion and billable-event idempotency
- Agent authority and human approvals
- PayID, bank, cash and PayPal collection flows
- Signed webhook and receipt callbacks
- Payment claim, allocation and concurrency races
- Tenant isolation and permission enforcement
- QR token expiry, revocation and reuse
- Customer Channels, SMS, email and voice fallback
- Follow-up stage progression and stop conditions
- Offline queue, sync conflicts and retry behaviour
- Chat intent positive, negative, ambiguous and repeated cases
- Full job-to-payment end-to-end workflow

## 7. Definition of ready

This branch is ready for implementation when:

- the root plan, branch status and provenance files are committed;
- v0.5.0 is available as extracted staged source, not merged blindly;
- existing host and WorkCore files remain untouched by branch preparation;
- source hashes and file counts are recorded;
- collision and ownership ledgers are the first implementation deliverables;
- `main` remains unchanged and usable as the rollback base.
