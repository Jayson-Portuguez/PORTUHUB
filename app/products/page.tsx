"use client";

import { useEffect, useState } from "react";

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

function getProductImages(p: Product & { imageUrl?: string }): string[] {
  if (p.imageUrls?.length) return p.imageUrls;
  if (p.imageUrl) return [p.imageUrl];
  return ["/placeholder.png"];
}

export default function ProductsPage() {
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedProduct, setSelectedProduct] = useState<Product | null>(null);

  useEffect(() => {
    fetch("/api/products")
      .then((res) => res.json())
      .then((data) => setProducts(Array.isArray(data) ? data : []))
      .catch(() => setProducts([]))
      .finally(() => setLoading(false));
  }, []);

  useEffect(() => {
    if (products.length === 0) return;
    const hash = typeof window !== "undefined" ? window.location.hash.slice(1) : "";
    if (hash) {
      const p = products.find((x) => x.id === hash);
      if (p) setSelectedProduct(p);
    }
  }, [products]);

  return (
    <div className="container" style={{ paddingBottom: "3rem" }}>
      <h1 className="section-title" style={{ marginTop: "0.5rem" }}>
        All products
      </h1>
      {loading ? (
        <p style={{ color: "#525252" }}>Loading…</p>
      ) : products.length === 0 ? (
        <p style={{ color: "#525252" }}>No products yet.</p>
      ) : (
        <div className="card-grid">
          {products.map((p) => (
            <article
              key={p.id}
              className="product-card product-card-clickable"
              id={p.id}
              onClick={() => setSelectedProduct(p)}
              role="button"
              tabIndex={0}
              onKeyDown={(e) => e.key === "Enter" && setSelectedProduct(p)}
            >
              <img
                src={productPrimaryImage(p)}
                alt={p.name}
              />
              <div className="product-card-body">
                <h3>{p.name}</h3>
                <p className="desc">{p.description}</p>
                <span className="price">₱{p.price.toLocaleString("en-PH", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                <p className="stock">In stock: {p.stock}</p>
              </div>
            </article>
          ))}
        </div>
      )}
      {selectedProduct && (
        <ProductDetailModal
          product={selectedProduct}
          onClose={() => setSelectedProduct(null)}
        />
      )}
    </div>
  );
}

function ProductDetailModal({ product, onClose }: { product: Product; onClose: () => void }) {
  const images = getProductImages(product);
  const [currentIndex, setCurrentIndex] = useState(0);

  const goPrev = () => setCurrentIndex((i) => (i <= 0 ? images.length - 1 : i - 1));
  const goNext = () => setCurrentIndex((i) => (i >= images.length - 1 ? 0 : i + 1));

  return (
    <div className="product-modal-overlay" onClick={onClose} role="dialog" aria-modal="true" aria-label="Product details">
      <div className="product-modal" onClick={(e) => e.stopPropagation()}>
        <button type="button" className="product-modal-close" onClick={onClose} aria-label="Close">×</button>
        <div className="product-modal-content">
          <div className="product-detail-carousel">
            <div className="product-detail-carousel-main">
              <img src={images[currentIndex]} alt={product.name} />
              {images.length > 1 && (
                <>
                  <button type="button" className="product-detail-carousel-btn product-detail-carousel-prev" onClick={goPrev} aria-label="Previous image">‹</button>
                  <button type="button" className="product-detail-carousel-btn product-detail-carousel-next" onClick={goNext} aria-label="Next image">›</button>
                </>
              )}
            </div>
            {images.length > 1 && (
              <div className="product-detail-carousel-dots">
                {images.map((_, i) => (
                  <button
                    key={i}
                    type="button"
                    className={`product-detail-carousel-dot ${i === currentIndex ? "active" : ""}`}
                    onClick={() => setCurrentIndex(i)}
                    aria-label={`Image ${i + 1}`}
                  />
                ))}
              </div>
            )}
          </div>
          <div className="product-detail-info">
            <h2 className="product-detail-name">{product.name}</h2>
            <p className="product-detail-price">₱{product.price.toLocaleString("en-PH", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
            <p className="product-detail-stock">In stock: {product.stock}</p>
            <div className="product-detail-description">
              <h3>Description</h3>
              <p>{product.description || "No description."}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
