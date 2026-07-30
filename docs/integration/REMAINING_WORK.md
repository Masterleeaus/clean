# Remaining Work

## Required before production deployment

### Install and boot the Laravel application

Composer is not available in the current sandbox and the supplied source contains no `vendor` directory. A connected build environment must run Composer installation, Laravel boot checks, route inspection, migrations and the framework test suite.

### Execute database verification

Run the complete migration sequence against fresh SQLite and PostgreSQL databases, then test rollback, foreign keys, indexes and company isolation with real Eloquent queries.

### Build frontend assets

The sandbox has Node.js and npm, but dependencies were not cached and external package access was unavailable. Install packages in a connected environment and run the Vite production build.

### Configure realtime services

Set Pusher or a compatible Laravel broadcasting service, then verify company-scoped presence, conversation channels, typing events, read receipts and queued broadcasts.

### Configure queues and scheduled work

Use a production queue driver and scheduler for WorkCore outbox processing, Maps searches, retries and notifications. Verify idempotency during worker restarts.

### Enable Titan Maps deliberately

Create or update the extension registry record, store a licensed provider key in Titan Vault, configure provider restrictions and run live integration tests. The extension is intentionally disabled by default.

### Migrate legacy public attachments

New message attachments use private storage and signed downloads. Existing files created by the original app may still reside on the public disk and need an authorised migration into company-scoped private paths.

## Connected acceptance run remaining

Version `0.7.0` provides the Docker, CI, preflight, migration-order, route/provider and connected-verification harness. The remaining acceptance gate is execution of `bin/titan-verify-connected` in an environment that can install Composer/npm dependencies and reach the configured PostgreSQL and provider sandboxes. No additional source-reconciliation pass is planned.

## Product depth still to build

- Full CRUD interfaces for WorkCore records beyond the current operational summary, action API and read API.
- Jurisdiction-specific residential tenancy notices, bonds, owner statements, statutory trust reporting and rent-ledger workflows.
- Accommodation channel-manager connections, dynamic pricing, owner/guest portals and regulated reporting.
- Rich document/evidence upload, preview, annotation and inspection-authoring interfaces over the new canonical domains.
- Assurance automation for recurring inspections, escalation, regulatory registers and scheduled compliance reporting.
- Live ZeroPay provider adapters, payment initiation, bank feeds and provider-sandbox reconciliation.
- Jurisdiction-specific trust-account, bond, owner-statement, rent and statutory reporting packs.
- Offline-first local database, delta sync and conflict resolution.
- Titan Rewind temporal correction and rollback.
- Full notification channel integrations.
- Live voice-provider adapters, telephony, streaming transcription and device workflows.
- Deep analytics, route optimisation and mapping visualisation.
- Reconciled NDIS and regulated-care execution modules.

## Security and operational validation

- Penetration test tenant switching, signed downloads, extension routes and AI tool confirmation.
- Establish data retention, backup, key rotation and incident response policies.
- Replace external CDN dependencies where offline operation or strict content security policy is required.
- Add provider-specific terms, attribution and storage controls for every external data source.
- Verify logs contain no secrets or cross-company data.

## Source reconciliation status

Base App and AI extension packages were reconciled in `0.5.0`. Marketing and Creative packages were reconciled in `0.6.0`. Deployment and connected-verification tooling was added in `0.7.0`. Original donor code remains outside runtime autoload; only clean native implementations entered the application.

Optional provider, connector, storage, channel, voice, image, video, audio, newsletter, SEO and social adapters remain disabled until each adapter receives provider-specific implementation, credential, scope, terms, failure-mode and integration testing.
