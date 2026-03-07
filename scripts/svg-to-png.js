const sharp = require("sharp");
const fs = require("fs");
const path = require("path");

const svgPath = path.join(__dirname, "..", "public", "placeholder.svg");
const pngPath = path.join(__dirname, "..", "public", "placeholder.png");

const svg = fs.readFileSync(svgPath);
sharp(svg)
  .png()
  .toFile(pngPath)
  .then(() => console.log("Created public/placeholder.png"))
  .catch((err) => {
    console.error(err);
    process.exit(1);
  });
