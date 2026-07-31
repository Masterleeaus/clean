# Titan Zero deployment and connected verification

This application requires PHP 8.2 or newer, Composer 2, Node 22, npm, and either SQLite or PostgreSQL. Production deployment should use PostgreSQL.

## Fast connected verification

```bash
cp .env.example .env
composer install --no-interaction --prefer-dist
php artisan key:generate
bash bin/titan-verify-connected
```

The verifier performs Composer validation, Laravel package discovery, fresh migrations, a rollback/remigrate cycle, route and schedule inspection, one queue-worker pass, framework tests, standalone Titan tests, static namespace and migration scans, and the Vite production build.

## Docker verification

```bash
cp .env.example .env
# Generate a unique host key, or leave APP_KEY blank and generate inside the app container.
docker compose build
docker compose run --rm app composer install --no-interaction --prefer-dist
docker compose run --rm app php artisan key:generate
docker compose --profile verify run --rm verify
```

Start the application and workers:

```bash
docker compose up app queue scheduler postgres node
```

## PostgreSQL

Set these production values through the deployment secret manager, not source control:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=postgres.example.internal
DB_PORT=5432
DB_DATABASE=titan
DB_USERNAME=titan_app
DB_PASSWORD=<secret>
```

Use a dedicated database role with only the permissions the application needs. Run migrations as a controlled release step:

```bash
php artisan migrate --force
```

## Required production controls

- Generate a unique `APP_KEY`; never reuse the testing key.
- Keep `APP_DEBUG=false`.
- Store AI, payment, map, connector and telephony credentials in Titan Vault.
- Keep `FILESYSTEM_DISK=local` or another private signed-access disk.
- Run at least one queue worker and the scheduler continuously.
- Configure Pusher or another reviewed broadcaster before changing `BROADCAST_CONNECTION` from `log`.
- Terminate TLS at the load balancer or web server.
- Back up PostgreSQL and private files together and test restoration.
- Run security, tenancy and provider-sandbox tests before accepting real customer data or payments.

## Frontend dependency lock

The supplied source does not contain `package-lock.json`. The first connected `npm install` creates it. Review and commit that generated lockfile before a production release, then use `npm ci` in CI and deployment.

## Commands

```bash
bash bin/titan-preflight
bash bin/titan-build
bash bin/titan-verify-connected
php tools/titan_migration_order.php
php tools/titan_route_provider_scan.php
```
