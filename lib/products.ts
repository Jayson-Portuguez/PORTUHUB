import { promises as fs } from "fs";
import path from "path";
import type { Product, ProductInput, ProductLegacy } from "./types";

function normalizeProduct(p: Product & ProductLegacy): Product {
  const imageUrls = p.imageUrls?.length
    ? p.imageUrls
    : p.imageUrl
      ? [p.imageUrl]
      : ["/placeholder.png"];
  const { imageUrl: _u, ...rest } = p;
  return { ...rest, imageUrls } as Product;
}

const DATA_PATH = path.join(process.cwd(), "data", "products.json");

async function ensureDataDir() {
  const dir = path.dirname(DATA_PATH);
  await fs.mkdir(dir, { recursive: true });
}

export async function getProducts(): Promise<Product[]> {
  await ensureDataDir();
  try {
    const raw = await fs.readFile(DATA_PATH, "utf-8");
    const data = JSON.parse(raw);
    const list = Array.isArray(data) ? data : [];
    return list.map((p: Product & ProductLegacy) => normalizeProduct(p));
  } catch {
    return [];
  }
}

export async function getProductById(id: string): Promise<Product | null> {
  const products = await getProducts();
  const p = products.find((x) => x.id === id);
  return p ?? null;
}

export async function getNewestProducts(limit: number): Promise<Product[]> {
  const products = await getProducts();
  return [...products]
    .sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime())
    .slice(0, limit);
}

async function saveProducts(products: Product[]) {
  await ensureDataDir();
  await fs.writeFile(DATA_PATH, JSON.stringify(products, null, 2), "utf-8");
}

export async function addProduct(input: ProductInput): Promise<Product> {
  const products = await getProducts();
  const id = `p_${Date.now()}_${Math.random().toString(36).slice(2, 9)}`;
  const imageUrls = Array.isArray(input.imageUrls) && input.imageUrls.length > 0
    ? input.imageUrls
    : ["/placeholder.png"];
  const product: Product = {
    ...input,
    imageUrls,
    id,
    createdAt: new Date().toISOString(),
  };
  products.push(product);
  await saveProducts(products);
  return product;
}

export async function updateProduct(id: string, updates: Partial<ProductInput>): Promise<Product | null> {
  const products = await getProducts();
  const idx = products.findIndex((p) => p.id === id);
  if (idx === -1) return null;
  const next = { ...products[idx], ...updates } as Product;
  if (updates.imageUrls !== undefined) next.imageUrls = updates.imageUrls.length ? updates.imageUrls : ["/placeholder.png"];
  products[idx] = next;
  await saveProducts(products);
  return products[idx];
}

export async function updateStock(id: string, stock: number): Promise<Product | null> {
  return updateProduct(id, { stock });
}

export async function deleteProduct(id: string): Promise<boolean> {
  const products = await getProducts();
  const filtered = products.filter((p) => p.id !== id);
  if (filtered.length === products.length) return false;
  await saveProducts(filtered);
  return true;
}
