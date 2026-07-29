# Titan Zero AI Site Builder Bridge

This integration is isolated on branch `agent/ai-site-builder-bridge`. It must remain unmerged until MagicAI and the separately installed builder pass configured integration tests.

## What this branch contains

- A branch-only importer for verified MagicAI v11 source and the user-supplied AI site-builder MEGA archive.
- Sanitisation rules that remove `.env`, private-key formats, `.git/`, `vendor/`, `node_modules/`, and stale `dist/` output before committing source.
- A checksum-pinned two-sided patch bundle under `integrations/ai-site-builder/patches/`.
- `integrations/ai-site-builder/apply.sh`, which applies the MagicAI extension and the builder bridge overlay.

The raw donor archive is deliberately not committed because it contains a populated `.env` file.

## Populate and apply

The branch workflow can be run manually from GitHub Actions: **Bootstrap MagicAI and AI Site Builder on isolated branch**.

The workflow:

1. Downloads and verifies MagicAI v11.
2. Downloads and tests the supplied AI site-builder archive.
3. Sanitises the builder source.
4. Places the builder at `external/ai-site-builder/`.
5. Runs:

```bash
bash integrations/ai-site-builder/apply.sh
```

The same command can be run locally after both source trees exist.

## Architecture

MagicAI/Titan Zero owns user authentication, active-company context, delegated permissions, launch sessions, project correlation, and audit records. The external React/Vite/Supabase builder owns generated projects, mobile builds, and artifacts. WorkCore supplies bounded company/service/property context and remains the only authority permitted to modify operational business records.

## Security contract

- HMAC-SHA256 signatures in both directions
- Five-minute timestamp tolerance
- One-time nonce replay protection
- Fifteen-minute, one-use launch sessions
- Builder authentication required before session consumption
- Supabase row-level security after ownership transfer
- Company-scoped identifiers
- Idempotent callback event IDs
- HTTPS outside localhost
- Dedicated Codemagic webhook secret
- Raw WorkCore context is not persisted in browser local storage

## MagicAI settings

```dotenv
AI_SITE_BUILDER_ENABLED=false
AI_SITE_BUILDER_BASE_URL=
AI_SITE_BUILDER_SUPABASE_URL=
AI_SITE_BUILDER_SUPABASE_SERVICE_ROLE_KEY=
AI_SITE_BUILDER_CLIENT_ID=magicai
AI_SITE_BUILDER_SHARED_SECRET=
AI_SITE_BUILDER_WEBHOOK_SECRET=
AI_SITE_BUILDER_TIMEOUT=15
```

## Builder settings

```dotenv
VITE_SUPABASE_PROJECT_ID=
VITE_SUPABASE_PUBLISHABLE_KEY=
VITE_SUPABASE_URL=
VITE_DEMO_MODE=false
VITE_TITAN_BRIDGE_ENABLED=true
VITE_TITAN_BRIDGE_FUNCTION=titan-bridge
TITAN_BRIDGE_CLIENT_ID=magicai
TITAN_BRIDGE_SHARED_SECRET=
TITAN_BRIDGE_USER_ID=
TITAN_SITE_BUILDER_PUBLIC_URL=
TITAN_MAGICAI_WEBHOOK_URL=
TITAN_MAGICAI_WEBHOOK_SECRET=
TITAN_ALLOWED_ORIGINS=
CODEMAGIC_WEBHOOK_SECRET=
```

Use matching shared and webhook secrets on both installations. Do not commit real values.

## Deployment order

1. Populate the branch sources and apply the patch.
2. Apply Supabase migration `20260730010000_add_titan_zero_bridge.sql`.
3. Deploy builder functions `titan-bridge`, `codemagic-webhook`, and `cloud-build-status`.
4. Build and deploy the React/Vite builder.
5. Configure the MagicAI extension secrets.
6. Run `php artisan migrate` in MagicAI.
7. Assign `integration.ai_site_builder.*` permissions.
8. Enable the extension.
9. Test session launch, project creation, build status, webhook replay rejection, and company isolation.

## Patch provenance

- Patch ZIP SHA-256: `e20d5dcfb3f4268af27befdca1cb3259dbc9f0458a3f60e114d899dd406a10a4`
- Sanitised integrated builder SHA-256: `a6190c90da374d0c973971d848f7095b0cb536b26a2a7429aca057283f617cb0`
- MagicAI bridge delta SHA-256: `6736453eac95f2d5b2689bf1295a051a9b6de012c2d21f06e2a19f3f09696374`
