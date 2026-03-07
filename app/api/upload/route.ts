import { NextRequest, NextResponse } from "next/server";
import { promises as fs } from "fs";
import path from "path";
import { isAdmin } from "@/lib/auth";

export async function POST(request: NextRequest) {
  const admin = await isAdmin();
  if (!admin) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }
  const body = await request.json();
  const { data } = body || {}; // base64 data URL
  if (!data || typeof data !== "string") {
    return NextResponse.json({ error: "Missing image data" }, { status: 400 });
  }
  const match = data.match(/^data:image\/(\w+);base64,(.+)$/);
  if (!match) {
    return NextResponse.json({ error: "Invalid image data" }, { status: 400 });
  }
  const ext = match[1] === "jpeg" ? "jpg" : match[1];
  const base64 = match[2];
  const dir = path.join(process.cwd(), "public", "uploads");
  await fs.mkdir(dir, { recursive: true });
  const filename = `img_${Date.now()}.${ext}`;
  const filepath = path.join(dir, filename);
  await fs.writeFile(filepath, Buffer.from(base64, "base64"));
  return NextResponse.json({ url: `/uploads/${filename}` });
}
