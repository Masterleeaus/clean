# Titan Zero Host Development Requirements

## Runtime

- PHP 8.2 or 8.3 for the supported MagicAI v10.91/Laravel 10 host.
- Composer 2.7 or newer.
- Node.js 20 LTS with npm 10 for frontend work.
- MySQL 8 or MariaDB 10.6+ for the hosted application.
- SQLite with PDO SQLite for isolated test execution.

## Required PHP extensions

`curl`, `fileinfo`, `intl`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `pdo_sqlite`, `sodium`, `tokenizer`, `xml`, and `zip`.

## Development defaults

- Cache: `array` in tests; Redis or database cache in hosted environments.
- Queue: `sync` only in tests; Redis/database queue workers in hosted environments.
- Session: `array` in tests; Redis/database/file according to deployment topology.
- Interaction Engine cloud AI: disabled unless a company explicitly configures an approved provider.
- Interaction Engine authority: default deny in every environment.

## Clean-clone setup

```bash
cp .env.example .env
composer install --no-interaction --prefer-dist
php artisan key:generate
npm ci
php artisan about
php artisan route:list --path=interaction
php artisan test --filter=HostBootTest
```

Do not commit `.env`, credentials, generated application keys, `vendor`, `node_modules`, runtime cache, or user uploads.
