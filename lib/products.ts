import { query, queryOne } from "./db";
import type { Product, ProductInput } from "./types";

interface ProductRow {
  id: string;
  name: string;
  description: string;
  price: number;
  image_urls: string[] | null;
  stock: number;
  created_at: Date;
}

function rowToProduct(r: ProductRow): Product {
  let imageUrls: string[] = ["/placeholder.png"];
  if (r.image_urls != null) {
    if (Array.isArray(r.image_urls)) imageUrls = r.image_urls.length ? r.image_urls : imageUrls;
    else if (typeof r.image_urls === "string") {
      try {
        const parsed = JSON.parse(r.image_urls);
        imageUrls = Array.isArray(parsed) && parsed.length ? parsed : imageUrls;
      } catch {
        // keep default
      }
    }
  }
  return {
    id: r.id,
    name: r.name,
    description: r.description ?? "",
    price: Number(r.price),
    imageUrls,
    stock: Number(r.stock),
    createdAt: r.created_at instanceof Date ? r.created_at.toISOString() : String(r.created_at),
  };
}

export async function getProducts(): Promise<Product[]> {
  try {
    const rows = await query<ProductRow[]>("SELECT id, name, description, price, image_urls, stock, created_at FROM products ORDER BY created_at DESC");
    return (rows || []).map(rowToProduct);
  } catch {
    return [];
  }
}

export async function getProductById(id: string): Promise<Product | null> {
  try {
    const row = await queryOne<ProductRow>(
      "SELECT id, name, description, price, image_urls, stock, created_at FROM products WHERE id = ?",
      [id]
    );
    return row ? rowToProduct(row) : null;
  } catch {
    return null;
  }
}

export async function getNewestProducts(limit: number): Promise<Product[]> {
  try {
    const rows = await query<ProductRow[]>(
      "SELECT id, name, description, price, image_urls, stock, created_at FROM products ORDER BY created_at DESC LIMIT ?",
      [limit]
    );
    return (rows || []).map(rowToProduct);
  } catch {
    return [];
  }
}

export async function addProduct(input: ProductInput): Promise<Product> {
  const id = `p_${Date.now()}_${Math.random().toString(36).slice(2, 9)}`;
  const imageUrls = Array.isArray(input.imageUrls) && input.imageUrls.length > 0
    ? input.imageUrls
    : ["/placeholder.png"];
  await query(
    "INSERT INTO products (id, name, description, price, image_urls, stock) VALUES (?, ?, ?, ?, ?, ?)",
    [id, input.name, input.description, input.price, JSON.stringify(imageUrls), input.stock]
  );
  const product = await getProductById(id);
  if (!product) throw new Error("Failed to read product after insert");
  return product;
}

export async function updateProduct(id: string, updates: Partial<ProductInput>): Promise<Product | null> {
  const current = await getProductById(id);
  if (!current) return null;

  const name = updates.name !== undefined ? updates.name : current.name;
  const description = updates.description !== undefined ? updates.description : current.description;
  const price = updates.price !== undefined ? updates.price : current.price;
  const stock = updates.stock !== undefined ? updates.stock : current.stock;
  const imageUrls = updates.imageUrls !== undefined
    ? (updates.imageUrls.length > 0 ? updates.imageUrls : ["/placeholder.png"])
    : current.imageUrls;

  await query(
    "UPDATE products SET name = ?, description = ?, price = ?, image_urls = ?, stock = ? WHERE id = ?",
    [name, description, price, JSON.stringify(imageUrls), stock, id]
  );
  return getProductById(id);
}

export async function updateStock(id: string, stock: number): Promise<Product | null> {
  return updateProduct(id, { stock });
}

export async function deleteProduct(id: string): Promise<boolean> {
  try {
    const existed = await getProductById(id);
    if (!existed) return false;
    await query("DELETE FROM products WHERE id = ?", [id]);
    return true;
  } catch {
    return false;
  }
}
