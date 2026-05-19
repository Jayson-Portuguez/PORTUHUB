#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

on_render=false
if [ -n "${RENDER:-}" ] || [ -n "${RENDER_SERVICE_ID:-}" ]; then
  on_render=true
fi

# Never use a local .env on Render — it overrides platform env vars (e.g. mysql @ 127.0.0.1).
if $on_render || [ -n "${DATABASE_URL:-}" ] || [ -n "${DB_URL:-}" ]; then
  rm -f .env .env.local .env.production
fi

# Render Postgres: blueprint sets DB_URL; some setups use DATABASE_URL instead.
if [ -n "${DATABASE_URL:-}" ] && [ -z "${DB_URL:-}" ]; then
  export DB_URL="${DATABASE_URL}"
fi

if [ -n "${DB_URL:-}" ]; then
  export DB_CONNECTION="${DB_CONNECTION:-pgsql}"
  unset DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD DB_SOCKET || true
elif $on_render; then
  echo "ERROR: DB_URL is not set. Link a Render Postgres database to this service," >&2
  echo "       or add DB_URL (Internal Database URL) and DB_CONNECTION=pgsql in Environment." >&2
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
