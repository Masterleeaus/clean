# Titan Zero Meetup + Titan Money + Titan Pay v0.4.0 Audit

**Audit date:** 27 July 2026  
**Scope:** v0.3.0 full source, Titan Money, Titan Pay, agent automation, Meetup chat, host authentication/settings, migrations, routes, storage, scheduling and package drift.

## Outcome

The audit found and repaired verified security, payment-integrity, tenancy, concurrency, schema and naming drift. Static and standalone regression checks are green. A full Laravel runtime, database migration and browser build could not be executed because Composer dependencies, `vendor/`, `node_modules`, a configured test database and provider sandbox credentials are unavailable in this environment.

This package is therefore **statically repaired and structurally verified**, not yet production-certified.

## Critical repairs

- Removed unauthenticated cache/storage maintenance routes.
- Replaced mutable-email platform administrator authority with an explicit `is_platform_admin` flag and provisioning command.
- Prevented AI messages from targeting conversations the user cannot access.
- Enforced company tenancy and owner governance for conversations and participants.
- Moved chat attachments to private authenticated delivery and added a migration command for legacy public files.
- Added schema compatibility for `ai_assistant` conversations and nullable AI message senders.
- Prevented donor InvoixPro `paid` labels from becoming verified Titan Money payments.
- Preserved the rule that only verified payment allocation can mark an invoice paid.

## High-severity repairs

- Added exact minor-unit parsing and capped line discounts at line gross.
- Added row locks to invoice numbering, payment allocation, payment claims, agent approvals and lifecycle transitions.
- Prevented invalid webhook payloads from reserving valid provider event IDs.
- Added PayPal capture while retaining signed webhook authority.
- Added Stripe and PayPal provider idempotency keys and reusable collection initiation.
- Cancelled stale payment links when another session settles the invoice.
- Capped pending payment claims across all collection sessions for the same invoice.
- Added amount, currency, merchant connection and company checks to reconciliation.
- Retained duplicate/overpayments as verified unallocated funds for review instead of losing or double-allocating them.
- Added transactional outbox row claiming to prevent concurrent publication.
- Added immutable invoice document hash verification and fail-closed missing/tampered snapshots.
- Refreshed customer and business snapshots at issue time.
- Added explicit agent actor/correlation context for scheduled automation.

## Automation repairs

- Invoice Agent consumes idempotent billable events and follows company authority limits.
- Recurring invoices enter the same governed billable-event pipeline.
- Receivables Agent uses forward-only due/overdue stages and does not backfill old notices.
- Due-today reminders no longer mark invoices overdue.
- Final/escalated notices require approval by default.
- Outbox delivery records close only after delivery or Titan Channels handoff.
- Optional AI Agent extension registration fails closed when dependencies are absent.
- Agent APIs and stored results no longer expose raw exception messages.

## Host and tenancy repairs

- Company context is resolved before implicit route-model binding.
- Malformed correlation/causation headers cannot break ULID persistence.
- User discovery, broadcasting and conversation reuse are company scoped.
- Group mutation/deletion is owner governed.
- Authentication is rate limited and passwords strengthened.
- AI provider errors are logged internally without exposing provider response bodies.
- Session encryption, Sydney timezone, database queues and blank application-key defaults are documented in `.env.example`.
- Host-wide chat, settings and platform-admin migrations were moved out of the Titan Money domain into `database/migrations`.

## Currency and reporting repairs

- One currency-aware minor-unit formatter is used throughout Titan Money and Titan Pay views.
- Automated follow-up messages format the actual invoice currency.
- Titan Money dashboard totals are grouped by currency rather than combining unlike currencies.
- Titan Pay verified collection totals are grouped by currency.

## Verification performed

- PHP syntax scan across application, bootstrap, config, migrations, routes, resources, tests and tools.
- Eight standalone regression/structural suites:
  - core money/GST/webhook invariants
  - agent authority and follow-up stages
  - v0.4 regressions
  - chat security and tenancy
  - host authentication/settings/broadcasting
  - concurrency and lifecycle integrity
  - Titan Money/Titan Pay structural invariants
  - automation provider/route/scheduler/outbox wiring
- JavaScript parse checks for `public/js/chat-app.js` and `public/js/pwa.js`.
- Duplicate-class, namespace, retired-name, direct-paid-write and public state-changing route scans.
- Full and delta ZIP integrity tests.
- Delta application against untouched v0.3.0 and byte-for-byte source-tree comparison.

## Deployment requirements

Run in a controlled deployment environment:

```bash
composer install
composer validate
php artisan optimize:clear
php artisan migrate --force
php artisan platform:admin admin@example.com
php artisan chat:migrate-private-attachments
php artisan chat:migrate-private-attachments --commit
php artisan route:list
php artisan test
npm install
npm run build
```

Production also requires queue workers, scheduler execution, Titan Vault integration, Titan Channels listeners and Stripe/PayPal sandbox-to-live verification.

## Known limitations

- Composer is unavailable here, so the Composer content hash and Laravel package discovery could not be freshly verified. Regenerate `composer.lock` in the deployment environment.
- `package-lock.json` is absent. Offline generation failed because npm package metadata was not cached. Generate and commit it before production deployment.
- Laravel/Pest, migrations, queue workers, mail delivery, broadcasting and route binding were not runtime-tested against a database.
- Stripe and PayPal endpoints were not exercised with sandbox credentials or live signed webhooks.
- DomPDF is optional; without it Titan Money stores immutable HTML invoice documents.
- SMS, WhatsApp and internal-channel delivery require the host Titan Channels listener.
- The optional AI Agent action classes refer to extension contracts that are intentionally absent in the base package; the bridge checks all dependencies before registration.
- The conversation compatibility migration is intentionally one-way where exact rollback would destroy AI conversation data.
