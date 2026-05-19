#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

on_render=false
if [ -n "${RENDER:-}" ] || [ -n "${RENDER_SERVICE_ID:-}" ]; then
  on_render=true
fi

# Never use a local .env on Render — it overrides platform env vars.
if $on_render || [ -n "${DATABASE_URL:-}" ] || [ -n "${DB_URL:-}" ] || [ -n "${DB_HOST:-}" ]; then
  rm -f .env .env.local .env.production
fi

if [ -n "${DATABASE_URL:-}" ] && [ -z "${DB_URL:-}" ]; then
  export DB_URL="${DATABASE_URL}"
fi

# Map Render Postgres standard vars when linking a database in the dashboard.
if [ -z "${DB_HOST:-}" ] && [ -n "${PGHOST:-}" ]; then
  export DB_HOST="${PGHOST}"
  export DB_PORT="${PGPORT:-5432}"
  export DB_DATABASE="${PGDATABASE:-${DB_DATABASE:-}}"
  export DB_USERNAME="${PGUSER:-${DB_USERNAME:-}}"
  export DB_PASSWORD="${PGPASSWORD:-${DB_PASSWORD:-}}"
fi

export DB_CONNECTION="${DB_CONNECTION:-pgsql}"

# Prefer discrete host/port from a linked database; avoid empty DB_URL forcing 127.0.0.1.
if [ -n "${DB_HOST:-}" ] && [ "${DB_HOST}" != "127.0.0.1" ] && [ "${DB_HOST}" != "localhost" ]; then
  export DB_PORT="${DB_PORT:-5432}"
  export DB_SSLMODE="${DB_SSLMODE:-require}"
  unset DB_URL DATABASE_URL || true
elif [ -n "${DB_URL:-}" ]; then
  unset DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD DB_SOCKET || true
elif $on_render; then
  echo "ERROR: Postgres is not configured for this service." >&2
  echo "  Render → Environment → Link your Postgres database (use Internal connection)," >&2
  echo "  or set DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD." >&2
  exit 1
fi

if $on_render; then
  php -r '
    $url = getenv("DB_URL") ?: "";
    $host = getenv("DB_HOST") ?: "";
    if ($url !== "") {
      $p = parse_url($url);
      $host = $p["host"] ?? $host;
    }
    if ($host === "" || $host === "127.0.0.1" || $host === "localhost") {
      fwrite(STDERR, "ERROR: Database host is \"$host\". Link Postgres to this web service and redeploy.\n");
      exit(1);
    }
    fwrite(STDERR, "INFO: Database host: $host\n");
  '
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
