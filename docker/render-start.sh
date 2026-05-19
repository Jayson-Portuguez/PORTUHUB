#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

# Never use a local .env on Render — it overrides platform env vars (e.g. mysql @ 127.0.0.1).
if [ -n "${RENDER:-}" ] || [ -n "${RENDER_SERVICE_ID:-}" ] || [ -n "${DATABASE_URL:-}" ]; then
  rm -f .env .env.local .env.production
fi

# Render Postgres: DATABASE_URL → Laravel DB_URL + pgsql driver.
if [ -n "${DATABASE_URL:-}" ]; then
  export DB_URL="${DATABASE_URL}"
  export DB_CONNECTION=pgsql
  unset DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD DB_SOCKET || true
elif [ -n "${RENDER:-}" ] || [ -n "${RENDER_SERVICE_ID:-}" ]; then
  echo "ERROR: DATABASE_URL is not set. Link a Render Postgres database or set DB_URL + DB_CONNECTION=pgsql." >&2
  exit 1
fi

if [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
  export APP_URL="${RENDER_EXTERNAL_URL}"
fi

if [ -z "${APP_KEY:-}" ]; then
  php artisan key:generate --force
fi

php artisan config:clear
php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}" --no-reload
