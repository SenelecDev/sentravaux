# syntax=docker/dockerfile:1

# ---- Assets frontend (Vite) ----
FROM node:20-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci --ignore-scripts 2>/dev/null || npm install
COPY vite.config.js postcss.config.js tailwind.config.js* ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# ---- Application PHP ----
FROM php:8.2-fpm-bookworm AS app

ARG INSTALL_OCI8=true
ENV ORACLE_DIR=/opt/oracle/instantclient \
    LD_LIBRARY_PATH=/opt/oracle/instantclient

RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libldap2-dev libxml2-dev libonig-dev libaio1 wget \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql mysqli zip gd ldap bcmath opcache exif pcntl

COPY docker/php/install-oracle.sh /tmp/install-oracle.sh
COPY docker/oracle/ /tmp/oracle/
RUN chmod +x /tmp/install-oracle.sh \
    && if [ "$INSTALL_OCI8" = "true" ]; then /tmp/install-oracle.sh || true; fi \
    && rm -rf /tmp/install-oracle.sh

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
COPY . .

COPY --from=assets /app/public/build ./public/build

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    $(php -m 2>/dev/null | grep -qi oci8 || echo '--ignore-platform-req=ext-oci8') \
    && php artisan package:discover --ansi || true \
    && composer dump-autoload --optimize

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-sentravaux.ini
COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm"]
