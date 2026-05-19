#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

# Render Postgres provides DATABASE_URL; Laravel reads DB_URL.
if [ -n "${DATABASE_URL:-}" ] && [ -z "${DB_URL:-}" ]; then
  export DB_URL="${DATABASE_URL}"
fi

# Public URL for links, cookies, and asset URLs.
if [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
  export APP_URL="${RENDER_EXTERNAL_URL}"
fi

if [ -z "${APP_KEY:-}" ]; then
  php artisan key:generate --force
fi

php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}" --no-reload
