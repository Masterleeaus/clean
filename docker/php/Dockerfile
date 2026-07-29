FROM node:22-bookworm-slim AS node_runtime
FROM php:8.4-cli-bookworm

ARG UID=1000
ARG GID=1000

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git unzip \
        libcurl4-openssl-dev libicu-dev libonig-dev libzip-dev \
        libpq-dev libsqlite3-dev libxml2-dev \
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath curl dom gd intl mbstring pcntl \
        pdo_pgsql pdo_sqlite simplexml xml xmlwriter zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=node_runtime /usr/local/bin/node /usr/local/bin/node
COPY --from=node_runtime /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s ../lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s ../lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

RUN groupadd --gid "${GID}" titan \
    && useradd --uid "${UID}" --gid titan --create-home --shell /bin/bash titan

WORKDIR /var/www/html
COPY docker/php/entrypoint.sh /usr/local/bin/titan-entrypoint
RUN chmod +x /usr/local/bin/titan-entrypoint

USER titan
ENTRYPOINT ["titan-entrypoint"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
