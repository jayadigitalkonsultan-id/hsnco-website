#!/bin/sh
set -eu
umask 0027
cd /app

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY is required. Supply the existing application key through EasyPanel runtime environment." >&2
    exit 1
fi

mkdir -p storage/app/public storage/app/private storage/framework/cache/data \
    storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
if [ ! -w storage ] || [ ! -w bootstrap/cache ]; then
    echo "Laravel storage/cache are not writable by UID 1000. Check volume ownership." >&2
    exit 1
fi

# Build caches only at runtime, after environment secrets are injected.
# No key generation, dependency installation or database migration here.
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction

exec "$@"
