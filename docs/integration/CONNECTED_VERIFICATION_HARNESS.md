# Connected Verification Harness

## Purpose

Version 0.7.0 adds the reproducible environment required to perform the final Laravel, database, worker and frontend acceptance run without changing Titan Zero's authority model.

## Added surfaces

- `docker/php/Dockerfile`: PHP 8.4 CLI, Composer 2, Node 22 and required PHP extensions.
- `docker-compose.yml`: application, queue worker, scheduler, PostgreSQL, Node development server and verification profile.
- `bin/titan-preflight`: command, extension, writable-directory and production-key checks.
- `bin/titan-verify-offline`: all dependency-free Titan and WorkCore regression suites, namespace scans, migration scans, JavaScript syntax and full PHP lint.
- `bin/titan-verify-connected`: Composer validation, dependency installation, Laravel package discovery, SQLite migration/rollback/remigration, route and schedule inspection, queue smoke, framework tests, optional PostgreSQL migration and Vite production build.
- `.github/workflows/titan-verify.yml`: connected CI with PHP 8.4, Node 22 and PostgreSQL 17.
- `tools/titan_migration_order.php`: verifies explicit and inferred foreign-key targets exist before use.
- `tools/titan_route_provider_scan.php`: verifies App classes imported by routes and bootstrap provider files resolve to source files.

## Environment safety changes

- `.env.example` no longer contains a reusable `APP_KEY`.
- Local verification defaults to SQLite, private local files, the database queue and log broadcasting.
- AI and provider secret variables were removed from `.env.example`; credentials remain Titan Vault records.
- `.env.testing` uses in-memory SQLite and array-backed cache/session/queue services.
- `.env.verification` uses a file-backed SQLite database so separate migration and rollback commands inspect the same database.
- Testing keys are deterministic test-only values that decode to exactly 32 bytes; they must never be used in production.

## Connected command

```bash
bash bin/titan-verify-connected
```

To add PostgreSQL migration verification:

```bash
TITAN_VERIFY_POSTGRES=1 \
TITAN_PG_HOST=127.0.0.1 \
TITAN_PG_DATABASE=titan \
TITAN_PG_USERNAME=titan \
TITAN_PG_PASSWORD='<secret>' \
bash bin/titan-verify-connected
```

## Sandbox limitation

The build sandbox used for this release has no outbound package-registry access, no Composer binary, no Composer cache and no required XML/cURL/mbstring PHP extensions. The harness itself was verified statically and through dependency-free tests, but Composer installation, Laravel boot, live migrations, Pest, Vite and Docker image construction could not be executed in that sandbox.

No production-readiness claim is made until the connected verifier completes successfully in an environment with registry access or preloaded dependency caches.
