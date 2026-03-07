"use client";

import { usePathname } from "next/navigation";

export default function ConditionalNav({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();
  const isAdmin = pathname === "/admin";

  if (isAdmin) {
    return (
      <div className="layout-wrap">
        <main className="main-wrap">{children}</main>
        <footer className="site-footer">
          <div className="container footer-inner">
            <div className="footer-brand">
              <a href="/" className="footer-logo" aria-label="PortuHub home">
                <img src="/logo.png" alt="PortuHub" className="footer-logo-img" />
              </a>
              <p className="footer-tagline">Simple shopping, fast delivery.</p>
            </div>
            <div className="footer-links">
              <a href="/">Home</a>
              <a href="/products">Products</a>
            </div>
            <div className="footer-bottom">
              <p className="footer-copy">&copy; {new Date().getFullYear()} PortuHub. All rights reserved.</p>
            </div>
          </div>
        </footer>
      </div>
    );
  }

  return (
    <div className="layout-wrap">
      <nav className="nav">
        <div className="container nav-inner">
          <a href="/" className="logo" aria-label="PortuHub home">
            <img src="/logo.png" alt="PortuHub" className="logo-img" />
          </a>
          <div className="nav-links">
            <a href="/">Home</a>
            <a href="/products">Products</a>
          </div>
        </div>
      </nav>
      <main className="main-wrap">{children}</main>
      <footer className="site-footer">
        <div className="container footer-inner">
          <div className="footer-brand">
            <a href="/" className="footer-logo" aria-label="PortuHub home">
              <img src="/logo.png" alt="PortuHub" className="footer-logo-img" />
            </a>
            <p className="footer-tagline">Simple shopping, fast delivery.</p>
          </div>
          <div className="footer-links">
            <a href="/">Home</a>
            <a href="/products">Products</a>
          </div>
          <div className="footer-bottom">
            <p className="footer-copy">&copy; {new Date().getFullYear()} PortuHub. All rights reserved.</p>
          </div>
        </div>
      </footer>
    </div>
  );
}
