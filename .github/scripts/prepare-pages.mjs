import fs from 'fs';

const manifestPath = fs.existsSync('public/build/.vite/manifest.json')
  ? 'public/build/.vite/manifest.json'
  : 'public/build/manifest.json';

const manifest = fs.existsSync(manifestPath)
  ? JSON.parse(fs.readFileSync(manifestPath, 'utf8'))
  : {};

// Prefer the explicit JS app entry, but gracefully fall back to any JS entry.
const jsEntry =
  manifest['resources/js/app.js'] ||
  Object.values(manifest).find((v) => v?.isEntry && typeof v.file === 'string' && v.file.endsWith('.js')) ||
  null;

// Prefer the explicit CSS app entry, but fall back to css on the JS entry.
const cssEntry =
  manifest['resources/css/app.css'] ||
  jsEntry ||
  Object.values(manifest)[0] ||
  null;

const base = '/';
const js = jsEntry?.file ? base + 'build/' + jsEntry.file : '';
const css = (cssEntry?.css || []).map((c) => base + 'build/' + c);
const linkTags = css.map((c) => `<link rel="stylesheet" href="${c}">`).join('\n  ');
const scriptTag = js ? `<script type="module" src="${js}"></script>` : '';

const html = `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PortuHub</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  ${linkTags}
</head>
<body>
  <div id="app"></div>
  ${scriptTag}
</body>
</html>`;

fs.mkdirSync('deploy', { recursive: true });
fs.writeFileSync('deploy/index.html', html);
fs.writeFileSync('deploy/404.html', html);
