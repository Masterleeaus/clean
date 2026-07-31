# Titan Zero Meetup + WorkCore Build Report

## Release

- Version: `0.7.0`
- Branch: `feature/connected-verification-v070`
- Base: verified integrated Meetup + WorkCore `0.6.0` package
- Release type: deployment hardening and connected-verification harness
- Date: 2026-07-27
- Default locale and currency: `en-AU`, `AUD`

The supplied `0.6.0` ZIP contained no Git metadata. It was imported unchanged into a fresh repository and committed before the `0.7.0` feature branch was created. No earlier branch history is claimed to exist inside the downloadable package.

## Scope

Version 0.7.0 adds no new business-record authority. Meetup remains the communication and UI host, WorkCore remains operational truth, Titan Zero remains the governed orchestration layer, Titan Vault remains credential authority, and provider adapters remain disabled until separately configured and tested.

This pass adds the environment and commands required to perform the final Laravel, database, worker and frontend acceptance run reproducibly.

## Deployment harness added

### Runtime image

`docker/php/Dockerfile` provides:

- PHP 8.4 CLI
- Composer 2
- Node.js 22 and npm
- PostgreSQL and SQLite PDO drivers
- cURL, DOM, SimpleXML, XML/XMLWriter, mbstring, intl, ZIP, PCNTL, BCMath and GD
- non-root `titan` runtime user

### Docker Compose

`docker-compose.yml` defines:

- `app`: Laravel application
- `queue`: durable queue worker
- `scheduler`: Laravel scheduler worker
- `postgres`: PostgreSQL 17 with health check
- `node`: Vite development service
- `verify`: one-shot connected acceptance profile

### Verification commands

- `bin/titan-preflight`: command, PHP extension, source-file, writable-directory and production-key checks
- `bin/titan-verify-offline`: complete dependency-free regression, namespace, migration, route/provider, JavaScript and PHP-lint suite
- `bin/titan-verify-connected`: Composer validation/install, Laravel package discovery, SQLite fresh migration, rollback/remigration, route and schedule inspection, queue smoke, framework tests, optional PostgreSQL migration and Vite build
- `bin/titan-build`: production-oriented dependency, migration, asset and Laravel cache build

### Static deployment tools

- `tools/titan_migration_order.php`: scans 48 migration files, 267 created tables and 815 explicit or inferred references
- `tools/titan_route_provider_scan.php`: verifies App classes imported by bootstrap and route files resolve to source files

### CI

`.github/workflows/titan-verify.yml` defines PHP 8.4, Node 22 and PostgreSQL 17 verification. It intentionally uses `npm install` until the first connected run generates `package-lock.json`; the generated lockfile must be reviewed and committed before production release.

## Environment safety changes

- Removed the reusable generated `APP_KEY` from `.env.example`.
- Changed local defaults from public storage to private `local` storage.
- Changed the queue default from synchronous execution to the durable database queue.
- Changed broadcasting to safe `log` mode until an approved provider is configured.
- Changed the local database default from credentialed MySQL to SQLite.
- Removed AI/provider secret environment variables; company credentials remain Titan Vault records.
- Added `.env.testing` with in-memory SQLite and array-backed services.
- Added `.env.verification` with file-backed SQLite for cross-command migration and rollback checks.
- Verified both test-only application keys decode to exactly 32 bytes.

## Dependency-free verification results

### Release verifier

```bash
php tools/titan_verify.php
```

Result: `4,901 passed, 0 failed`.

### Namespace scan

```bash
php tools/titan_namespace_scan.php
```

Result: `818 PHP files, 1,182 declarations, 679 App imports, 0 unresolved`.

### Deployment contracts

Result: `71 checks passed`.

### Migration and route/provider scans

- Migration order: `48 files, 267 tables, 815 references, 0 issues`
- Route/provider resolution: `7 files, 26 App classes, 0 unresolved`

### Existing subsystem regression

- Finance domain: `36 passed`
- Finance persistence: `83 passed`
- Finance runtime: `64 passed`
- Finance host: `15 passed`
- Property/Accommodation domain: `29 passed`
- Property/Accommodation persistence: `32 passed`
- Property/Accommodation runtime: `47 passed`
- Property/Accommodation host: `15 passed`
- Assurance/Evidence: `98 passed`
- Titan AI: `8 passed`
- Titan Intelligence domain: `24 passed`
- Titan Intelligence persistence: `106 passed`
- Titan Intelligence runtime: `127 passed`
- Titan Intelligence host: `35 passed`
- Titan Creative domain: `30 passed`
- Titan Creative persistence: `80 passed`
- Titan Creative runtime: `104 passed`
- Titan Creative host: `39 passed`
- Titan Creative donor reconciliation: `211 passed`
- Titan Maps Intelligence: `32 passed`

### Syntax

- `990` PHP files passed `php -l`.
- Vite source and public Operations JavaScript passed `node --check`.
- Docker Compose and GitHub workflow YAML parsed successfully.
- Shell scripts passed `bash -n`.

## Connected execution status

The sandbox cannot reach Composer, npm or Debian registries and contains no Composer cache or `vendor` directory. Its host PHP installation also lacks cURL, DOM, mbstring, PDO SQLite, PDO PostgreSQL, SimpleXML, XML and XMLWriter extensions. `bin/titan-preflight` correctly reported nine blocking issues: Composer plus those eight extension groups.

Because dependencies could not be installed, the following commands were not executed in this sandbox:

- `composer validate --strict`
- `composer install`
- Laravel package discovery and application boot
- Laravel route cache generation
- live SQLite migrations and rollback through Eloquent/Artisan
- live PostgreSQL migrations
- Pest and Laravel framework tests
- queue and scheduler Artisan smoke commands
- Vite dependency installation and production compilation
- Docker image construction

No claim is made that these connected gates passed. They are encoded in `bin/titan-verify-connected`, Docker Compose and CI for execution in a connected environment.

## Frontend lockfile boundary

The source package has no `package-lock.json`; none was invented manually. The first connected `npm install` will create it. Review and commit that lockfile, then replace CI/deployment installation with `npm ci` before production release.

## Packaging inventory

- Source files: `1,128`
- Directories represented: `323`
- PHP files: `990`
- JavaScript files: `14`
- Blade templates: `18`
- Laravel migration files: `61`
- WorkCore module directories: `24`
- Titan host subsystem directories: `11`

## Release assessment

`0.7.0` is the deployment-hardened source checkpoint. Source reconciliation and dependency-free verification are complete. One external acceptance gate remains: run `bash bin/titan-verify-connected` successfully in a connected build environment, generate and commit the npm lockfile, and complete provider/security sandbox validation before handling production customer data or payments.

## Laravel boot verification

Laravel boot was not executed in this network-isolated sandbox. The connected verifier performs package discovery, application boot through Artisan, route inspection and framework tests once dependencies are installed.

## Frontend build status

The Vite Frontend build was not executed in this sandbox because npm dependencies could not be installed. JavaScript syntax passed; `npm run build` remains a mandatory connected-verification gate.

## Quarantined sources

Original donor source folders, compiled binaries, decompilation output, `vendor`, `node_modules`, secrets, logs and generated caches remain excluded from runtime and release packaging.

## External code review

CodeRabbit CLI was not installed. Its official installer could not be reached because the sandbox could not resolve `cli.coderabbit.ai`, so no external AI code-review result is claimed for this release.
