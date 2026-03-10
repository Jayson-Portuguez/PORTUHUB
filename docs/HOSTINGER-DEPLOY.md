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

## Quick checklist

- [ ] Database created and credentials in `.env`
- [ ] PORTUHUB files in `public_html/PORTUHUB` (or equivalent)
- [ ] Document root = `.../PORTUHUB/public`
- [ ] `.env` configured and `php artisan key:generate` run
- [ ] `composer install` and `php artisan migrate --force` run
- [ ] `public/build` present (from `npm run build` on PC or on server)
- [ ] `storage` and `bootstrap/cache` writable
