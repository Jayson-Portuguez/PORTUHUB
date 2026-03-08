# Deploy to GitHub Pages with your GoDaddy domain

The workflow **Deploy to GitHub Pages (GoDaddy domain)** (`.github/workflows/deploy-godaddy.yml`) builds your Next.js site as a **static export** and deploys it to **GitHub Pages**. You can then use your GoDaddy domain (e.g. **portuhub.shop**).

## Important: GitHub Pages is static only

- GitHub Pages serves only **static files** (HTML, CSS, JS). It does **not** run Node.js, API routes, or a database.
- This workflow **temporarily excludes** your `app/api` folder during the build so the static export succeeds.
- On the live site, **product lists, login, and admin will not work** because there is no backend. The pages will load, but `/api/*` requests will 404.
- To have a **full working app** (MySQL, auth, products API), you need a Node.js host (e.g. Vercel, Railway, or a VPS) and would point your GoDaddy domain there instead.

## 1. Enable GitHub Pages

1. In your repo: **Settings** → **Pages**.
2. Under **Build and deployment**, set **Source** to **GitHub Actions**.

## 2. Connect your GoDaddy domain

1. In **Settings** → **Pages**, in the **Custom domain** field enter your GoDaddy domain (e.g. `portuhub.shop`), then **Save**.
2. Keep the **CNAME** file in the repo root with the same domain (e.g. `portuhub.shop`) so the build knows your custom domain.

## 3. Point GoDaddy DNS to GitHub Pages

In **GoDaddy** → your domain → **DNS** (or **Manage DNS**):

- **For root domain (`portuhub.shop`):**  
  Add **A** records pointing to GitHub’s IPs:
  - `185.199.108.153`
  - `185.199.109.153`
  - `185.199.110.153`
  - `185.199.111.153`

- **For `www` (optional):**  
  Add a **CNAME** record:
  - Name: `www`
  - Value: `YOUR_USERNAME.github.io` (replace with your GitHub username)

Save and wait a few minutes (up to 48 hours for full propagation).

## 4. Run the workflow

- **Automatic:** Push to the `main` branch → the workflow runs and deploys.
- **Manual:** **Actions** tab → **Deploy to GitHub Pages (GoDaddy domain)** → **Run workflow**.

Your site will be available at your GitHub Pages URL and at your GoDaddy domain once DNS is set up.
