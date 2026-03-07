import { NextRequest, NextResponse } from "next/server";
import { getProducts, addProduct } from "@/lib/products";
import { isAdmin } from "@/lib/auth";

export async function GET() {
  const products = await getProducts();
  return NextResponse.json(products);
}

export async function POST(request: NextRequest) {
  const admin = await isAdmin();
  if (!admin) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }
  const body = await request.json();
  const { name, description, price, imageUrls, stock } = body || {};
  if (!name || typeof description !== "string" || typeof price !== "number" || typeof stock !== "number") {
    return NextResponse.json(
      { error: "Missing or invalid: name, description, price, stock" },
      { status: 400 }
    );
  }
  const urls = Array.isArray(imageUrls) ? imageUrls.filter((u: unknown) => typeof u === "string") : [];
  const product = await addProduct({
    name: String(name),
    description,
    price: Number(price),
    imageUrls: urls.length > 0 ? urls : ["/placeholder.png"],
    stock: Number(stock),
  });
  return NextResponse.json(product);
}
