# Titan Zero Meetup + WorkCore

This repository is the integrated Titan Zero application base built from the supplied Laravel Meetup chat application.

## Architecture

- **Meetup host:** authentication, companies, memberships, chat, realtime channels, private attachments and user interface.
- **Titan Zero:** conversational orchestration, governed tool routing, company-scoped AI configuration, the native Titan Intelligence runtime, and the native Titan Creative & Marketing runtime.
- **WorkCore:** first-party operational domain for CRM, premises, jobs, scheduling, workforce, assets, inventory, documents, evidence, assurance, property agreements, occupancies, accommodation, finance, ZeroPay reconciliation, trust accounting, forms, support, supply, fleet and repairs.
- **Titan Maps Intelligence:** optional provider and business discovery extension, disabled by default.

WorkCore is not an extension. It is loaded as an authoritative domain provider from `app/Domains/WorkCore`.

## Requirements

- PHP 8.2 or later
- Composer
- Laravel-supported database; verify both SQLite and PostgreSQL for deployment targets
- Node.js and npm
- Queue worker for background jobs
- Broadcasting provider for realtime presence and conversations

## Connected-environment setup

```bash
cp .env.example .env
composer install --no-interaction --prefer-dist
php artisan key:generate
bash bin/titan-verify-connected
```

Docker-based verification is documented in [`DEPLOYMENT.md`](DEPLOYMENT.md). The connected verifier runs Laravel package discovery, migration and rollback cycles, route and schedule inspection, a queue-worker smoke pass, framework tests, optional PostgreSQL migrations and the Vite production build.

Configure the application URL, database, queue, broadcasting and mail services in `.env`. Do not place company AI or Maps credentials in ordinary settings; authorised users store them through Titan Vault.

## Verification available without Composer

```bash
bash bin/titan-verify-offline
php tools/titan_verify.php
php tools/titan_namespace_scan.php
php tools/titan_migration_order.php
php tools/titan_route_provider_scan.php
php tests/Standalone/WorkCoreAssurance/run.php
php tests/Standalone/TitanAI/run.php
php tests/Standalone/TitanMapsIntelligence/run.php
php tests/Standalone/TitanIntelligence/domain.php
php tests/Architecture/TitanIntelligencePersistenceContractTest.php
php tests/Architecture/TitanIntelligenceRuntimeContractTest.php
php tests/Architecture/TitanIntelligenceHostSurfaceTest.php
php tests/Standalone/TitanCreative/domain.php
php tests/Standalone/TitanCreative/persistence.php
php tests/Standalone/TitanCreative/runtime.php
php tests/Standalone/TitanCreative/host.php
php tests/Standalone/TitanCreative/reconciliation.php
node --check public/js/chat-app.js
node --check public/js/titan-operations.js
```

## Company and operations flow

1. Register or log in.
2. Select or create a company.
3. Open **Titan Operations**.
4. Configure a company AI provider if authorised.
5. Use chat with Titan Zero inside the active company context.
6. Create WorkCore operational records through governed actions.
7. Query registered WorkCore read models through the protected API or Titan Zero.
8. Register evidence and run inspections, findings, corrective actions, risks and incidents through the shared WorkCore domains.
9. Create premise-linked party roles, agreements and occupancies through governed WorkCore actions.
10. Manage accommodation availability, reservations, stays, housekeeping and operational folio charges.
11. Create quotes and invoices through WorkCore Finance; reconcile observed payments through ZeroPay.
12. Use separately permissioned trust ledgers for client money; never mix trust and operating funds.
13. Create company-scoped folders, canvases, memories, skills and agents through registered Titan Intelligence capabilities.
14. Configure provider, connector and voice references through Titan Vault; adapters remain disabled until explicitly implemented and enabled.
15. Create governed brand systems, campaigns, creative projects, generation jobs, approvals, publication plans, newsletters, SEO briefs and analytics observations through Titan Creative & Marketing.
16. Enable compatible optional extensions deliberately through the extension registry.

## Security boundaries

- Active company context is resolved from authenticated memberships.
- Request body company identifiers are not tenant authority.
- AI and external provider credentials are encrypted in Titan Vault.
- Chat attachments and document binaries use private storage.
- WorkCore writes pass through governed actions, permissions, confirmation, idempotency and audit.
- WorkCore reads pass through registered definitions, entitlement, permission and bounded-pagination checks.
- Evidence sign-off requires a valid checksum and active evidence state.
- Extensions cannot write WorkCore operational tables directly.
- Titan Intelligence share tokens are persisted only as hashes.
- Agent tools must exist in registered Titan or WorkCore registries.
- Intelligence summaries expose counts only and never memory content, prompts, transcripts, share tokens or provider credentials.
- Creative and marketing summaries expose lifecycle counts only; prompts, private asset references, generated content and provider metadata remain private.
- External creative, publishing and social adapters remain disabled until a provider-specific implementation is configured and verified.

## Documentation

- [Authority and provenance](docs/integration/AUTHORITY_AND_PROVENANCE.md)
- [WorkCore repair report](docs/integration/WORKCORE_REPAIR_REPORT.md)
- [BOS assurance and evidence delta](docs/integration/BOS_ASSURANCE_EVIDENCE_DELTA.md)
- [Property and accommodation delta](docs/integration/PROPERTY_ACCOMMODATION_DELTA.md)
- [Finance, ZeroPay and trust-accounting delta](docs/integration/FINANCE_ZEROPAY_TRUST_DELTA.md)
- [Base App and AI extension reconciliation](docs/integration/BASE_AI_EXTENSION_RECONCILIATION.md)
- [Marketing and Creative reconciliation](docs/integration/MARKETING_CREATIVE_RECONCILIATION.md)
- [Connected verification harness](docs/integration/CONNECTED_VERIFICATION_HARNESS.md)
- [Deployment guide](DEPLOYMENT.md)
- [Application directory summary](APP_DIRECTORY_SUMMARY.md)
- [Complete application directory tree](APP_DIRECTORY_TREE.txt)
- [Meetup host adapters](docs/integration/MEETUP_HOST_ADAPTERS.md)
- [Extension registry](docs/integration/EXTENSION_REGISTRY.md)
- [Remaining work](docs/integration/REMAINING_WORK.md)
- [Build report](BUILD_REPORT.md)

## Current release status

Version `0.7.0` is the deployment-hardened source checkpoint. The complete source-reconciliation sequence remains intact and a reproducible Docker/CI verification harness now covers Laravel boot, SQLite/PostgreSQL migration cycles, framework tests, queue/scheduler smoke checks and Vite compilation. The supplied sandbox could not install external dependencies, so the connected verifier must still complete successfully before production deployment.
