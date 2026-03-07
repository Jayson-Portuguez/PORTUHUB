export interface Product {
  id: string;
  name: string;
  description: string;
  price: number;
  /** Multiple image URLs; first is primary. Legacy: imageUrl still supported when reading. */
  imageUrls: string[];
  stock: number;
  createdAt: string;
}

/** Legacy shape in stored JSON (single image) */
export interface ProductLegacy {
  imageUrl?: string;
  imageUrls?: string[];
}

export type ProductInput = Omit<Product, "id" | "createdAt">;

/** Get display image URLs from a product (supports legacy imageUrl). */
export function getProductImageUrls(p: Product & Partial<ProductLegacy>): string[] {
  if (p.imageUrls?.length) return p.imageUrls;
  if (p.imageUrl) return [p.imageUrl];
  return ["/placeholder.png"];
}
