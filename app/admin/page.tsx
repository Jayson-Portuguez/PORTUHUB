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

const IconEdit = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" /><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" /></svg>
);
const IconDelete = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden><polyline points="3 6 5 6 21 6" /><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /></svg>
);
const IconLogout = () => (
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /><polyline points="16 17 21 12 16 7" /><line x1="21" y1="12" x2="9" y2="12" /></svg>
);

type ProductModalMode = "add" | Product;

export default function AdminPage() {
  const [admin, setAdmin] = useState<boolean | null>(null);
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [loginError, setLoginError] = useState("");
  const [products, setProducts] = useState<Product[]>([]);
  const [productModal, setProductModal] = useState<ProductModalMode | null>(null);
  const [deletingId, setDeletingId] = useState<string | null>(null);

  useEffect(() => {
    fetch("/api/auth/me")
      .then((res) => res.json())
      .then((data) => setAdmin(data.admin === true))
      .catch(() => setAdmin(false));
  }, []);

  useEffect(() => {
    if (!admin) return;
    fetch("/api/products")
      .then((res) => res.json())
      .then((data) => setProducts(Array.isArray(data) ? data : []))
      .catch(() => setProducts([]));
  }, [admin]);

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoginError("");
    const res = await fetch("/api/auth/login", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ username, password }),
    });
    const data = await res.json();
    if (!res.ok) {
      setLoginError(data.error || "Login failed");
      return;
    }
    setAdmin(true);
  };

  const handleLogout = async () => {
    await fetch("/api/auth/logout", { method: "POST" });
    setAdmin(false);
    setUsername("");
    setPassword("");
  };

  const deleteProduct = async (id: string) => {
    if (!confirm("Delete this product? This cannot be undone.")) return;
    setDeletingId(id);
    try {
      const res = await fetch(`/api/products/${id}`, { method: "DELETE" });
      if (res.ok) setProducts((prev) => prev.filter((p) => p.id !== id));
    } finally {
      setDeletingId(null);
    }
  };

  const openAddModal = () => setProductModal("add");
  const openEditModal = (p: Product) => setProductModal(p);
  const closeModal = () => setProductModal(null);

  const onProductSaved = (updated: Product) => {
    const isAdd = productModal === "add";
    if (isAdd) setProducts((prev) => [updated, ...prev]);
    else setProducts((prev) => prev.map((p) => (p.id === updated.id ? updated : p)));
    closeModal();
  };

  if (admin === null) {
    return (
      <div className="container admin-layout">
        <p style={{ color: "#525252" }}>Checking access…</p>
      </div>
    );
  }

  if (!admin) {
    return (
      <div className="container">
        <div className="login-box">
          <h1>Admin login</h1>
          <form onSubmit={handleLogin}>
            {loginError && <div className="alert alert-error">{loginError}</div>}
            <div className="form-group">
              <label htmlFor="username">Username</label>
              <input
                id="username"
                type="text"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                placeholder="Admin username"
                autoComplete="username"
                autoFocus
              />
            </div>
            <div className="form-group">
              <label htmlFor="password">Password</label>
              <input
                id="password"
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="Password"
                autoComplete="current-password"
              />
            </div>
            <button type="submit" className="btn btn-primary" style={{ width: "100%" }}>
              Log in
            </button>
          </form>
        </div>
      </div>
    );
  }

  return (
    <>
      <header className="admin-top-header">
        <div className="container admin-top-header-inner">
          <a href="/" className="admin-top-logo" aria-label="PortuHub home">
            <img src="/logo.png" alt="PortuHub" className="admin-top-logo-img" />
          </a>
          <span className="admin-top-right">
            <h2 className="admin-heading-name" style={{ margin: 0, fontSize: "1.1rem", fontWeight: 600, color: "#0a0a0a" }}>Admin</h2>
            <button type="button" className="admin-icon-btn" onClick={handleLogout} title="Log out" aria-label="Log out">
              <IconLogout />
            </button>
          </span>
        </div>
      </header>
      <div className="container admin-layout">
        <div className="admin-header" style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: "1.5rem", flexWrap: "wrap", gap: "0.75rem" }}>
          <h1 style={{ margin: 0, fontSize: "1.5rem", fontWeight: 700 }}>List of products</h1>
          <button type="button" className="btn btn-primary" onClick={openAddModal}>
            Add product
          </button>
        </div>
      {products.length === 0 ? (
        <p style={{ color: "#525252" }}>No products yet. Click Add product to create one.</p>
      ) : (
        <ul style={{ listStyle: "none", margin: 0, padding: 0 }}>
          {products.map((p) => (
            <li key={p.id} style={{ borderBottom: "1px solid #e5e5e5" }}>
              <div
                className="admin-product-row"
                style={{
                  display: "flex",
                  alignItems: "center",
                  gap: "1rem",
                  padding: "0.75rem 0",
                  flexWrap: "wrap",
                }}
              >
                <img
                  className="admin-product-thumb"
                  src={productPrimaryImage(p)}
                  alt=""
                  style={{ width: 48, height: 48, objectFit: "cover", borderRadius: 8 }}
                />
                <span className="admin-product-name" style={{ flex: 1, fontWeight: 600, minWidth: 0 }}>{p.name}</span>
                <span style={{ fontSize: "0.9rem", color: "#737373", minWidth: "4rem" }}>Stock: {p.stock}</span>
                <span className="admin-product-actions" style={{ display: "flex", gap: "0.35rem", alignItems: "center" }}>
                  <button type="button" className="admin-icon-btn admin-icon-btn-edit" onClick={() => openEditModal(p)} title="Edit product" aria-label="Edit product">
                    <IconEdit />
                  </button>
                  <button type="button" className="admin-icon-btn admin-icon-btn-danger" disabled={deletingId === p.id} onClick={() => deleteProduct(p.id)} title="Delete product" aria-label="Delete product">
                    <IconDelete />
                  </button>
                </span>
              </div>
            </li>
          ))}
        </ul>
      )}
      {productModal !== null && (
        <ProductFormModal
          mode={productModal}
          onClose={closeModal}
          onSaved={onProductSaved}
        />
      )}
      </div>
    </>
  );
}

function ProductFormModal({
  mode,
  onClose,
  onSaved,
}: {
  mode: "add" | Product;
  onClose: () => void;
  onSaved: (p: Product) => void;
}) {
  const isAdd = mode === "add";
  const product = isAdd ? null : mode;

  const [name, setName] = useState(product?.name ?? "");
  const [description, setDescription] = useState(product?.description ?? "");
  const [price, setPrice] = useState(product != null ? String(product.price) : "");
  const [stock, setStock] = useState(product != null ? String(product.stock) : "0");
  const [existingUrls, setExistingUrls] = useState<string[]>(product?.imageUrls?.length ? [...product.imageUrls] : []);
  const [imageFiles, setImageFiles] = useState<File[]>([]);
  const [imagePreviews, setImagePreviews] = useState<string[]>([]);
  const [submitting, setSubmitting] = useState(false);
  const [message, setMessage] = useState<{ type: "error" | "success"; text: string } | null>(null);

  const onFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(e.target.files ?? []);
    if (!files.length) return;
    setImageFiles((prev) => [...prev, ...files]);
    files.forEach((file) => {
      const reader = new FileReader();
      reader.onload = () => setImagePreviews((prev) => [...prev, reader.result as string]);
      reader.readAsDataURL(file);
    });
    e.target.value = "";
  };

  const removeExisting = (index: number) => setExistingUrls((prev) => prev.filter((_, i) => i !== index));
  const removeNew = (index: number) => {
    setImageFiles((prev) => prev.filter((_, i) => i !== index));
    setImagePreviews((prev) => prev.filter((_, i) => i !== index));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setMessage(null);
    const numPrice = parseFloat(price);
    const numStock = parseInt(stock, 10);
    if (!name.trim() || isNaN(numPrice) || numPrice < 0 || isNaN(numStock) || numStock < 0) {
      setMessage({ type: "error", text: "Please fill name, valid price and stock." });
      return;
    }
    setSubmitting(true);
    try {
      const newUrls: string[] = [];
      for (const file of imageFiles) {
        const dataUrl = await new Promise<string>((resolve) => {
          const reader = new FileReader();
          reader.onload = () => resolve(reader.result as string);
          reader.readAsDataURL(file);
        });
        const uploadRes = await fetch("/api/upload", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ data: dataUrl }),
        });
        if (uploadRes.ok) {
          const { url } = await uploadRes.json();
          newUrls.push(url);
        }
      }
      const allUrls = [...existingUrls, ...newUrls];
      const imageUrls = allUrls.length > 0 ? allUrls : ["/placeholder.png"];

      if (isAdd) {
        const res = await fetch("/api/products", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            name: name.trim(),
            description: description.trim(),
            price: numPrice,
            stock: numStock,
            imageUrls,
          }),
        });
        const data = await res.json();
        if (!res.ok) {
          setMessage({ type: "error", text: data.error || "Failed to add product" });
          return;
        }
        onSaved(data);
      } else {
        const res = await fetch(`/api/products/${product!.id}`, {
          method: "PATCH",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            name: name.trim(),
            description: description.trim(),
            price: numPrice,
            stock: numStock,
            imageUrls,
          }),
        });
        const data = await res.json();
        if (!res.ok) {
          setMessage({ type: "error", text: data.error || "Failed to update product" });
          return;
        }
        onSaved(data);
      }
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="admin-modal-overlay" onClick={onClose} role="dialog" aria-modal="true" aria-labelledby="product-modal-title">
      <div className="admin-modal" onClick={(e) => e.stopPropagation()}>
        <div className="admin-modal-header">
          <h2 id="product-modal-title" style={{ margin: 0, fontSize: "1.25rem" }}>{isAdd ? "Add product" : "Edit product"}</h2>
          <button type="button" className="admin-modal-close" onClick={onClose} aria-label="Close">×</button>
        </div>
        <form onSubmit={handleSubmit} className="admin-modal-body">
          {message && <div className={`alert alert-${message.type}`}>{message.text}</div>}
          <div className="form-group">
            <label>Product images</label>
            <input type="file" accept="image/*" multiple onChange={onFileChange} />
            <div className="image-preview-list" style={{ marginTop: "0.5rem" }}>
              {existingUrls.map((url, i) => (
                <span key={url + i} className="image-preview-wrap">
                  <img src={url} alt="" className="image-preview-thumb" />
                  <button type="button" className="image-preview-remove" onClick={() => removeExisting(i)} aria-label="Remove">×</button>
                </span>
              ))}
              {imagePreviews.map((src, i) => (
                <span key={"new-" + i} className="image-preview-wrap">
                  <img src={src} alt="" className="image-preview-thumb" />
                  <button type="button" className="image-preview-remove" onClick={() => removeNew(i)} aria-label="Remove">×</button>
                </span>
              ))}
            </div>
          </div>
          <div className="form-group">
            <label>Name</label>
            <input value={name} onChange={(e) => setName(e.target.value)} placeholder="Product name" />
          </div>
          <div className="form-group">
            <label>Description</label>
            <textarea value={description} onChange={(e) => setDescription(e.target.value)} placeholder="Short description" />
          </div>
          <div style={{ display: "flex", gap: "1rem", flexWrap: "wrap" }}>
            <div className="form-group" style={{ flex: "1 1 120px" }}>
              <label>Price (₱)</label>
              <input type="number" step="0.01" min="0" value={price} onChange={(e) => setPrice(e.target.value)} placeholder="0.00" />
            </div>
            <div className="form-group" style={{ flex: "1 1 120px" }}>
              <label>Stock</label>
              <input type="number" min="0" value={stock} onChange={(e) => setStock(e.target.value)} placeholder="0" />
            </div>
          </div>
          <div className="admin-modal-footer">
            <button type="button" className="btn btn-ghost" onClick={onClose}>Cancel</button>
            <button type="submit" className="btn btn-primary" disabled={submitting}>
              {submitting ? "Saving…" : isAdd ? "Add product" : "Save changes"}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
