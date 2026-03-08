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
