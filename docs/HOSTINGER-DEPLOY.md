# Deploy PortuHub to Hostinger (portuhub.shop)

Your domain shows the Hostinger placeholder because the app isn’t uploaded yet, or the document root isn’t pointing to Laravel’s `public` folder. Follow these steps.

---

## Step 1: Create a database

1. Log in to **Hostinger hPanel** → **Databases** → **MySQL Databases**.
2. **Create database** (e.g. name: `u123456789_portuhub`).
3. **Create user** (e.g. `u123456789_portuhub`) with a strong password.
4. **Add user to database** with **All privileges**.
5. Note down: **Database name**, **Username**, **Password**, **Host** (often `localhost`).

---

## Step 2: Upload your project

### Option A – File Manager (no SSH)

1. On your PC, open the PORTUHUB project folder.
2. **Exclude** before zipping: `node_modules`, `vendor`, `.git`.  
   (Or zip everything and delete those folders on the server after upload.)
3. Zip the whole **PORTUHUB** folder (so inside the zip there is one folder `PORTUHUB` with app, public, etc.).
4. In hPanel → **Files** → **File Manager**.
5. Go to `domains/portuhub.shop/public_html` (or the root folder Hostinger shows for portuhub.shop).
6. **Upload** the zip, then **Extract** it.  
   You should have: `public_html/PORTUHUB/app`, `public_html/PORTUHUB/public`, etc.

### Option B – SSH + Git

1. hPanel → **Advanced** → **SSH Access** → enable and note **host**, **user**, **port**.
2. On your PC:
   ```bash
   ssh uXXXXX@portuhub.shop -p 65002
   ```
3. On the server:
   ```bash
   cd ~/domains/portuhub.shop/public_html
   git clone https://github.com/YOUR_GITHUB_USERNAME/PORTUHUB.git
   cd PORTUHUB
   ```

---

## Step 3: Point the site to Laravel’s `public` folder

The placeholder appears because the site is still serving `public_html` (or similar). Laravel must be served from the **`public`** folder.

1. In hPanel go to **Websites** → select **portuhub.shop** → **Manage**.
2. Find **Domain** or **Document root** / **Website root**.
3. Change the document root from something like:
   - `public_html`  
   to:
   - `public_html/PORTUHUB/public`  
   (Use the exact path Hostinger shows; it might be `domains/portuhub.shop/public_html/PORTUHUB/public`.)
4. Save.

After this, `https://portuhub.shop` should hit Laravel. If you haven’t configured `.env` yet, you may see a Laravel error next (we fix that in Step 4).

---

## Step 4: Configure Laravel on the server

You need `.env` and dependencies on the server.

### If you have SSH

```bash
cd ~/domains/portuhub.shop/public_html/PORTUHUB   # adjust path if different

cp .env.example .env
nano .env   # or use File Manager to edit
```

In `.env` set at least:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://portuhub.shop`
- `DB_HOST=localhost` (or the host from Step 1)
- `DB_DATABASE=your_db_name`
- `DB_USERNAME=your_db_user`
- `DB_PASSWORD=your_db_password`

Then:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
chmod -R 775 storage bootstrap/cache
```

If the server has Node:

```bash
npm ci
npm run build
```

If not: run `npm run build` on your PC, then upload the `public/build` folder to the server (overwrite the one in `PORTUHUB/public/build`).

### If you only use File Manager

1. In **File Manager** go to `PORTUHUB` and copy `.env.example` to `.env`.
2. Edit `.env` (right‑click → Edit) and set the same values as above.
3. Use hPanel’s **Run PHP/Composer** (or similar) to run in the PORTUHUB folder:
   - `composer install --no-dev --optimize-autoloader`
   - `php artisan key:generate`
   - `php artisan migrate --force`
4. Set permissions on `storage` and `bootstrap/cache` to **755** (or **775** if available).
5. Build assets on your PC (`npm run build`) and upload the `public/build` folder to `PORTUHUB/public/build`.

---

## Step 5: Test

1. Open **https://portuhub.shop**.
2. You should see your PortuHub app (with styling and navigation), not the Hostinger “You Are All Set to Go!” page.
3. If you see a 500 error or blank page, check:
   - `storage/logs/laravel.log` on the server.
   - Permissions: `storage` and `bootstrap/cache` writable.
   - `.env` has correct DB credentials and `APP_KEY` is set after `php artisan key:generate`.

---

## Fix 403 Forbidden

If you see **403 Forbidden** after uploading, do these in order:

### 1. Confirm document root

The site **must** be served from the **`public`** folder inside PORTUHUB, not from `public_html` or from `PORTUHUB` itself.

- In hPanel → **Websites** → **portuhub.shop** → **Manage**.
- Find **Document root** / **Website root** / **Domain root**.
- It must be exactly the folder that **contains `index.php` and `.htaccess`** (i.e. Laravel’s `public` folder).  
  Examples that work:
  - `public_html/PORTUHUB/public`
  - `domains/portuhub.shop/public_html/PORTUHUB/public`
- If it’s still `public_html` or `PORTUHUB` (without `/public`), change it to `.../PORTUHUB/public`, save, and try again.

### 2. Check permissions (File Manager or SSH)

The web server must be able to read the `public` folder and `index.php`.

- **Folder** `public`: **755**
- **File** `public/index.php`: **644**
- **File** `public/.htaccess`: **644**

In File Manager: right‑click the file/folder → **Permissions** / **Change permissions** and set the above.

Also make sure Laravel can write to storage and cache:

- **Folders** `storage` and `bootstrap/cache`: **775** (and their subfolders).

### 3. Make sure `index.php` and `.htaccess` exist in the document root

In the folder that is set as document root you must have:

- `index.php` (Laravel’s front controller)
- `.htaccess` (rewrite rules)

If you only moved/copied part of the project, copy the full contents of your local **`public`** folder (including `.htaccess`) into that document root on the server.

### 4. If Hostinger won’t let you change document root (alternative)

Some plans don’t allow changing the document root; it stays as `public_html`. In that case:

1. Put the whole Laravel app **outside** `public_html` (e.g. in `domains/portuhub.shop/PORTUHUB` so you have `PORTUHUB/app`, `PORTUHUB/public`, etc.).
2. **Move everything inside `public`** (all files and folders) **into** `public_html`. So `public_html` will contain: `index.php`, `.htaccess`, `build/`, etc.
3. Edit **`public_html/index.php`** on the server. Change the two lines that require bootstrap and vendor so they point one level up to the Laravel root:
   - Change `__DIR__.'/../vendor/autoload.php'` to `__DIR__.'/../PORTUHUB/vendor/autoload.php'`
   - Change `__DIR__.'/../bootstrap/app.php'` to `__DIR__.'/../PORTUHUB/bootstrap/app.php'`  
   (Adjust `PORTUHUB` if your folder name is different; the idea is that from `public_html` you go up once to the parent, then into the Laravel root.)
4. In `public_html/index.php`, fix the maintenance path too: change `__DIR__.'/../storage/framework/maintenance.php'` to `__DIR__.'/../PORTUHUB/storage/framework/maintenance.php'`.

Then reload **https://portuhub.shop**. The 403 should go away if permissions are 755/644 as above.

---

## Fix HTTP 500 (Internal Server Error)

A **500 error** means the server is running Laravel but something is failing (missing config, DB, or permissions). Fix it step by step.

### 1. Check the real error in Laravel’s log

On the server, open:

- **`PORTUHUB/storage/logs/laravel.log`** (or `storage/logs/laravel.log` inside your project root).

Look at the **last few lines** after you load the page. You’ll see the real exception (e.g. "No application encryption key", "could not find driver", "Access denied for user", "file_put_contents failed").

- **File Manager:** go to `PORTUHUB` → `storage` → `logs` → open `laravel.log`, scroll to the bottom.
- **SSH:** `tail -50 ~/domains/portuhub.shop/public_html/PORTUHUB/storage/logs/laravel.log` (adjust path).

Use that message to decide what to fix below.

### 2. No application encryption key

If the log says something like "No application encryption key set":

- On the server, in the **PORTUHUB** folder, run:
  ```bash
  php artisan key:generate
  ```
- Or in **File Manager**: ensure `.env` exists and has a line like `APP_KEY=base64:...` (no empty value). If you have SSH or a “Run PHP” tool, run `php artisan key:generate` in the project root.

### 3. Database connection error

If the log says "could not find driver", "SQLSTATE", or "Access denied for user":

- In **hPanel** → **Databases** → **MySQL** → confirm the database and user exist and the user is assigned to the database.
- In **PORTUHUB/.env** on the server set:
  - `DB_CONNECTION=mysql`
  - `DB_HOST=localhost` (or the host Hostinger shows, e.g. `localhost` or an internal hostname)
  - `DB_PORT=3306`
  - `DB_DATABASE=...` (exact name from hPanel)
  - `DB_USERNAME=...` (exact user from hPanel)
  - `DB_PASSWORD=...` (exact password)
- Run migrations: `php artisan migrate --force` in the PORTUHUB folder.

### 4. Storage / bootstrap/cache not writable

If the log says "file_put_contents", "failed to open stream", or "Permission denied" for `storage` or `bootstrap/cache`:

- Set permissions so the web server can write:
  - **Folders** `storage` and `bootstrap/cache` (and everything inside): **775**.
- In File Manager: right‑click `storage` → Permissions → 775, and enable "Recurse into subdirectories". Do the same for `bootstrap/cache`.

### 5. Missing vendor or wrong PHP version

- If the log says "require vendor/autoload.php" or "Class not found", run on the server in **PORTUHUB**:
  ```bash
  composer install --no-dev --optimize-autoloader
  ```
- Laravel 12 needs **PHP 8.2+**. In hPanel → **Advanced** → **PHP Configuration** (or **Select PHP version**) choose **8.2** or **8.3** for the domain.

### 6. Turn on debug temporarily (only to see the error)

If you still get a blank 500 and the log is empty or hard to find:

- In **PORTUHUB/.env** on the server set:
  - `APP_DEBUG=true`
- Reload **https://portuhub.shop**. The page may show the real error (and stack trace). **Fix the issue**, then set `APP_DEBUG=false` again for security.

---

## Quick checklist

- [ ] Database created and credentials in `.env`
- [ ] PORTUHUB files in `public_html/PORTUHUB` (or equivalent)
- [ ] Document root = `.../PORTUHUB/public`
- [ ] `.env` configured and `php artisan key:generate` run
- [ ] `composer install` and `php artisan migrate --force` run
- [ ] `public/build` present (from `npm run build` on PC or on server)
- [ ] `storage` and `bootstrap/cache` writable
