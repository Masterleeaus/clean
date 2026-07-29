#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache database

if [[ "${TITAN_AUTO_INSTALL:-0}" == "1" && ! -f vendor/autoload.php ]]; then
    composer install --no-interaction --prefer-dist
fi

if [[ ! -f .env && -f .env.example ]]; then
    cp .env.example .env
fi

exec "$@"
