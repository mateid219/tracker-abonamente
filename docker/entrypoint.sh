#!/bin/sh
# Runs before every app/reverb container start.
#  - creates (once) and loads the application key
#  - on the "app" container: waits for PostgreSQL, runs migrations and seeds the demo account
#  - caches config/routes/views for speed
set -e

cd /var/www/html

as_www() { su-exec www-data "$@"; }

chown -R www-data:www-data storage bootstrap/cache

# Application key: generated on first start, kept in the storage volume.
KEY_FILE=storage/app/.app_key
if [ -z "$APP_KEY" ]; then
    if [ ! -s "$KEY_FILE" ]; then
        echo "base64:$(head -c 32 /dev/urandom | base64)" > "$KEY_FILE"
        chown www-data:www-data "$KEY_FILE"
        chmod 600 "$KEY_FILE"
        echo "Generated a new application key."
    fi
    APP_KEY="$(cat "$KEY_FILE")"
    export APP_KEY
fi

if [ "$CONTAINER_ROLE" = "app" ]; then
    echo "Waiting for the database..."
    until as_www php artisan migrate --force --no-interaction; do
        sleep 2
    done
    as_www php artisan db:seed --force --no-interaction
fi

as_www php artisan config:cache --no-interaction
as_www php artisan route:cache --no-interaction
as_www php artisan view:cache --no-interaction

# php-fpm starts as root and runs its workers as www-data;
# everything else (Reverb, artisan commands) runs as www-data.
if [ "$1" = "php-fpm" ]; then
    exec "$@"
fi
exec su-exec www-data "$@"
