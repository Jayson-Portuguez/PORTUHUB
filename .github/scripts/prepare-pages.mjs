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

const outDir = 'public_html';
fs.mkdirSync(outDir, { recursive: true });
fs.writeFileSync(`${outDir}/index.html`, html);
fs.writeFileSync(`${outDir}/404.html`, html);

// Copy build assets so live site in public_html can serve them
const buildSrc = 'public/build';
const buildDest = `${outDir}/build`;
if (fs.existsSync(buildSrc)) {
  fs.mkdirSync(buildDest, { recursive: true });
  for (const name of fs.readdirSync(buildSrc)) {
    const src = `${buildSrc}/${name}`;
    const dest = `${buildDest}/${name}`;
    if (fs.statSync(src).isDirectory()) {
      copyDirSync(src, dest);
    } else {
      fs.copyFileSync(src, dest);
    }
  }
}

function copyDirSync(src, dest) {
  fs.mkdirSync(dest, { recursive: true });
  for (const name of fs.readdirSync(src)) {
    const s = `${src}/${name}`;
    const d = `${dest}/${name}`;
    if (fs.statSync(s).isDirectory()) copyDirSync(s, d);
    else fs.copyFileSync(s, d);
  }
}
