# PortuHub

E-commerce app with admin auth and product CRUD. Built with **Laravel 12**, **Vue 3**, and **Tailwind CSS**.

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL

## Setup

1. **Copy environment and configure database**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Edit `.env`: set `DB_CONNECTION=mysql`, `DB_DATABASE=portuhub`, `DB_USERNAME`, `DB_PASSWORD`. (Laravel uses `DB_*` env vars, not `MYSQL_*`.)

2. **Run migrations**
   ```bash
   php artisan migrate
   ```

3. **Seed admin user**
   ```bash
   php artisan db:seed
   ```
   Default login: `ADMIN_USERNAME` / `ADMIN_PASSWORD` from `.env` (e.g. `admin` / `@PORTUHUB2026`).

4. **Install frontend dependencies and build**
   ```bash
   npm install
   npm run build
   ```
   For development: `npm run dev` (Vite dev server).

5. **Run the app**
   ```bash
   php artisan serve
   ```
   Open http://localhost:8000. For dev assets, run `npm run dev` in another terminal.

## Structure

- **Backend:** Laravel API routes under `/api` (auth: login, logout, me; products: CRUD).
- **Frontend:** Vue 3 SPA with Vue Router; Tailwind for styles. Single blade view `resources/views/app.blade.php` mounts the Vue app.
- **Auth:** Cookie-based admin session (`admin_session`). No Laravel Sanctum or Jetstream.

## Routes

- `/` – Home (new arrivals)
- `/products` – Product list
- `/admin` – Admin login and product management

## Deploy on Render

This repo includes a [Render Blueprint](https://render.com/docs/blueprint-spec) (`render.yaml`) that provisions a **Web Service** (Docker) and a **PostgreSQL** database.

1. Push the repo to GitHub/GitLab.
2. In [Render Dashboard](https://dashboard.render.com/) → **New** → **Blueprint**, connect the repo and apply `render.yaml`.
3. When prompted, set secrets:
   - `ADMIN_USERNAME` – admin login name
   - `ADMIN_PASSWORD` – admin password
4. Wait for the first deploy (build runs `composer install`, `npm run build`, then migrations + seed on start).
5. Open the service URL (e.g. `https://portuhub.onrender.com`). Health check: `/up`.

**Notes**

- Render free web services spin down after inactivity; the first request may take ~30s.
- Production uses built Vite assets (`public/build`); no separate Vite process on Render.
- Local Docker Compose still uses MySQL; Render uses Postgres (`DB_CONNECTION=pgsql`).
- Optional: deploy manually as **Web Service → Docker** with root `Dockerfile` and the same env vars as in `render.yaml`.

**Troubleshooting: database connection errors on Render**

| Error | Fix |
|-------|-----|
| `mysql` @ `127.0.0.1:3306` | Remove local MySQL env vars; link Postgres; set `DB_CONNECTION=pgsql`. |
| `pgsql` @ `127.0.0.1:5432` | Postgres is **not linked**. `DB_HOST` must be Render’s internal host (`dpg-…`), not `127.0.0.1`. |
| `DATABASE_URL is not set` | Link the database or set `DB_HOST` + credentials from the Postgres dashboard. |

In **Environment** → **Add from database** → select your Postgres instance. That injects `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`. Then redeploy.

Required: `DB_CONNECTION=pgsql`, linked Postgres vars, `APP_KEY`, `ADMIN_USERNAME`, `ADMIN_PASSWORD`.
