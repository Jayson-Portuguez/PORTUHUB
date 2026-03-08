# Deploy with your GoDaddy domain

This project uses the workflow **Deploy to production (GoDaddy domain)** (`.github/workflows/deploy-godaddy.yml`) to deploy to **Vercel**. You then point your GoDaddy domain to Vercel.

## 1. One-time setup

### A. Create a Vercel project

1. Go to [vercel.com](https://vercel.com) and sign in (e.g. with GitHub).
2. **Add New Project** → Import your `PORTUHUB` repo.
3. Add your **Environment Variables** (same as in `.env`): `MYSQL_*`, `NEXTAUTH_*`, etc., for Production.
4. Deploy once from the Vercel dashboard so the project exists.

### B. Get Vercel IDs and token

1. In Vercel: **Project Settings** → **General** → copy **Project ID**.
2. **Account Settings** → **General** → copy **Team/Org ID** (or use your user ID from the URL).
3. **Account Settings** → **Tokens** → create a token (e.g. “GitHub Actions”) and copy it.

### C. Add GitHub secrets

In your repo: **Settings** → **Secrets and variables** → **Actions** → **New repository secret**. Add:

| Name              | Value                    |
|-------------------|--------------------------|
| `VERCEL_TOKEN`    | Token from step B.3      |
| `VERCEL_ORG_ID`   | Team/Org ID from step B.2 |
| `VERCEL_PROJECT_ID` | Project ID from step B.1 |

## 2. Connect your GoDaddy domain in Vercel

1. In Vercel: your project → **Settings** → **Domains**.
2. Add your domain (e.g. `portuhub.shop` and `www.portuhub.shop`).
3. Vercel will show the DNS records you need (usually a CNAME or A records).

## 3. Point GoDaddy DNS to Vercel

In **GoDaddy** → your domain → **DNS** (or **Manage DNS**):

- **For root domain (`portuhub.shop`):**  
  Add an **A** record:  
  - Name: `@`  
  - Value: `76.76.21.21` (Vercel’s IP; confirm in Vercel’s Domains page if it changes.)

- **For `www`:**  
  Add a **CNAME** record:  
  - Name: `www`  
  - Value: `cname.vercel-dns.com`

Save and wait a few minutes (up to 48 hours for full propagation). Vercel will then serve your app on your GoDaddy domain.

## 4. Run the workflow

- **Automatic:** Push to the `main` branch → the workflow runs and deploys.
- **Manual:** Repo **Actions** tab → **Deploy to production (GoDaddy domain)** → **Run workflow**.

After it runs, your site will be live at your Vercel URL and at your GoDaddy domain once DNS is set up.
