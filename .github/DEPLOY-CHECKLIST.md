# Why aren’t my changes showing on the live site?

Use this checklist so updates go from your machine to the live site.

## 1. Push your changes to GitHub

The live site only updates when new code is on GitHub and a deploy runs.

- Commit: `git add .` then `git commit -m "Your message"`
- Push: `git push origin main` (or `master` if that’s your default branch)

If you only commit locally and never push, the live site will not change.

## 2. Push to the right branch

The workflow runs only when you push to **main** or **master**.

- Check your branch: `git branch`
- If you’re on another branch (e.g. `develop`), either:
  - Merge into `main` and push, or
  - Push to `main`: `git push origin your-branch:main`

## 3. Check GitHub Actions

- Open your repo on GitHub → **Actions**
- Click the latest **“Publish to Live Site”** run
- Confirm it completed successfully (green check). If it failed, open the run and read the error (e.g. missing `RENDER_DEPLOY_HOOK` secret)

## 4. Check your host (e.g. Render)

- Log in to Render (or whatever host you use)
- Open your service → **Events** or **Deploys**
- After each push, a new deploy should appear and finish without errors
- If the deploy fails, fix the error shown in the host’s logs (e.g. build or env issues)

## 5. Cache and hard refresh

Sometimes the site updated but your browser shows the old version.

- Hard refresh: **Ctrl+Shift+R** (Windows/Linux) or **Cmd+Shift+R** (Mac)
- Or try an incognito/private window, or another browser

---

**Short version:** Commit → push to `main` (or `master`) → check Actions and your host both succeeded → hard refresh the live site.
