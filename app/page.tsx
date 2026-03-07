"use client";

import { useEffect, useState } from "react";
import Link from "next/link";

interface Product {
  id: string;
  name: string;
  description: string;
  price: number;
  imageUrls: string[];
  stock: number;
  createdAt: string;
}

function productPrimaryImage(p: Product & { imageUrl?: string }): string {
  if (p.imageUrls?.length) return p.imageUrls[0];
  if (p.imageUrl) return p.imageUrl;
  return "/placeholder.png";
}

export default function HomePage() {
  const [newItems, setNewItems] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch("/api/products/new")
      .then((res) => res.json())
      .then((data) => {
        setNewItems(Array.isArray(data) ? data : []);
      })
      .catch(() => setNewItems([]))
      .finally(() => setLoading(false));
  }, []);

  return (
    <div className="container">
      <section className="hero">
        <h1>Welcome to PortuHub</h1>
        <p>Discover the latest products. Simple shopping, fast delivery.</p>
        <Link href="/products" className="btn btn-primary">
          View all products
        </Link>
      </section>

      <section className="new-arrivals-section" style={{ marginBottom: "3rem" }}>
        <h2 className="section-title">New arrivals</h2>
        {loading ? (
          <p style={{ color: "#525252" }}>Loading…</p>
        ) : newItems.length === 0 ? (
          <p style={{ color: "#525252" }}>No products yet. Add some in Admin.</p>
        ) : (
          <div className="carousel">
            <div className="carousel-track">
              {newItems.map((item) => (
                <Link key={item.id} href={`/products#${item.id}`} className="carousel-item">
                  <img
                    src={productPrimaryImage(item)}
                    alt={item.name}
                  />
                  <div className="carousel-item-content">
                    <h3>{item.name}</h3>
                    <p>{item.description.slice(0, 60)}{item.description.length > 60 ? "…" : ""}</p>
                    <span className="price">₱{item.price.toLocaleString("en-PH", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                  </div>
                </Link>
              ))}
            </div>
          </div>
        )}
      </section>
    </div>
  );
}
