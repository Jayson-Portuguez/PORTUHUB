/** @type {import('next').NextConfig} */
const nextConfig = {
  output: 'export',
  images: {
    remotePatterns: [],
    unoptimized: true,
  },
};

module.exports = nextConfig;
