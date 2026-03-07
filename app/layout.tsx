import type { Metadata, Viewport } from "next";
import "./globals.css";
import ConditionalNav from "./ConditionalNav";

export const metadata: Metadata = {
  title: "PortuHub – Simple Ecommerce",
  description: "Browse and shop the latest products.",
};

export const viewport: Viewport = {
  width: "device-width",
  initialScale: 1,
  maximumScale: 5,
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet" />
      </head>
      <body>
        <ConditionalNav>{children}</ConditionalNav>
      </body>
    </html>
  );
}
