# syntax=docker/dockerfile:1
#
# One Dockerfile, two images:
#   --target app  PHP-FPM with Laravel (also used for the queue worker)
#   --target web  Nginx with the public/ files, forwards PHP requests to "app"

# ---------------------------------------------------------------------------
# 1. Front-end assets (CSS/JS built by Vite)
# ---------------------------------------------------------------------------
FROM node:22-alpine AS assets
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# ---------------------------------------------------------------------------
# 2. PHP dependencies (production only, no dev packages)
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --no-dev --optimize --no-scripts

# ---------------------------------------------------------------------------
# 3. Application image: PHP-FPM
# ---------------------------------------------------------------------------
FROM php:8.4-fpm-alpine AS app

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_pgsql intl zip bcmath opcache pcntl \
    && apk add --no-cache su-exec \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-app.ini"

WORKDIR /var/www/html

COPY --from=vendor /app ./
COPY --from=assets /app/public/build ./public/build

RUN chown -R www-data:www-data storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]

# ---------------------------------------------------------------------------
# 4. Web server image: Nginx
# ---------------------------------------------------------------------------
FROM nginx:1.27-alpine AS web

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY public /var/www/html/public
COPY --from=assets /app/public/build /var/www/html/public/build
