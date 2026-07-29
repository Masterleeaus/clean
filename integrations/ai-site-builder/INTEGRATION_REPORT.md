# AI Site Builder Bridge Integration Report

## Scope

Repository: `Masterleeaus/clean`

Feature branch: `agent/ai-site-builder-bridge`

Base branch: `main`

No merge or pull request has been created.

## Integration boundary

The site builder remains a separately deployable React/Vite/Supabase application under `external/ai-site-builder/`. MagicAI exposes it through a native optional integration extension.

MagicAI/Titan Zero owns:

- active-company context
- user and agent permissions
- project correlation
- one-time launch sessions
- audit and callback records

The builder owns:

- project source
- generated applications
- remote build jobs
- build artifacts

WorkCore remains the sole authority for operational business records. Builder code receives bounded context and cannot directly update customers, properties, jobs, bookings, quotes, invoices, or other WorkCore entities.

## Implemented bridge

### MagicAI

- `AiSiteBuilderBridge` integration extension
- WorkCore tenant and delegated-permission checks
- company-scoped project and build correlations
- signed builder client
- signed, replay-protected, idempotent callbacks
- project/build/event/nonce persistence
- launch, status, build, and webhook routes
- integration management UI

### AI site builder

- Titan bridge Supabase migration
- HMAC-SHA256 canonical signing utilities
- one-time launch-session issue and consumption
- authenticated ownership transfer on session consumption
- Titan context banner and builder session hook
- company/project correlation metadata
- signed MagicAI status callbacks
- dedicated Codemagic webhook secret
- Supabase row-level security for subsequent access

## Security controls

- Five-minute signature timestamp tolerance
- One-time nonce replay prevention
- Fifteen-minute, one-use launch sessions
- Signed-in builder user required before ownership transfer
- Company-scoped external identifiers
- Idempotent callback event IDs
- HTTPS required outside localhost
- Raw WorkCore context is not persisted in browser local storage
- Secret-bearing `.env` and private-key formats are rejected from imported builder source
- Stale `dist/`, `vendor/`, and `node_modules/` are not imported

## Verification evidence

The branch patch bundle was reconstructed from its committed chunks and produced SHA-256:

`e20d5dcfb3f4268af27befdca1cb3259dbc9f0458a3f60e114d899dd406a10a4`

Verification performed against fresh MagicAI and builder extractions:

- patch ZIP integrity: passed
- WorkCore compatibility gates: passed
- MagicAI extension files installed: 20
- PHP syntax lint: passed
- builder bridge migration and functions installed: passed
- secret-file scan: passed
- provider registration: exactly one
- environment block: exactly one
- second installer run: passed without duplicate registration
- integration contract and HMAC canonical vector: passed in the implementation workspace
- JSON and Codemagic YAML parsing: passed
- TypeScript syntax diagnostic gate: passed

A complete Vite production build was not completed because the available package mirror did not contain `zustand@5.0.11`. Stale donor build output was deliberately excluded rather than presented as a verified integrated build.

## Source handling

The user-supplied site-builder archive contained a populated `.env`. The raw archive is therefore not stored in Git history. The branch importer downloads the source from the supplied MEGA URL, tests the archive, removes secrets and generated dependencies, and commits only the sanitised source.

## Required deployment validation

Before considering a future merge:

1. Run the branch importer manually from GitHub Actions.
2. Configure distinct shared and webhook secrets.
3. Apply Laravel and Supabase migrations.
4. Deploy the builder and Supabase functions.
5. Test company isolation and delegated permissions.
6. Test launch-token expiry and one-use consumption.
7. Test callback replay rejection and idempotency.
8. Run the full MagicAI and builder test/build suites with their normal dependency registries.
