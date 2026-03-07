import { NextResponse } from "next/server";
import { getNewestProducts } from "@/lib/products";

export async function GET() {
  const products = await getNewestProducts(12);
  return NextResponse.json(products);
}
