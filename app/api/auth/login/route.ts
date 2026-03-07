import { NextRequest, NextResponse } from "next/server";
import { setAdminSession } from "@/lib/auth";

export async function POST(request: NextRequest) {
  const body = await request.json();
  const { username, password } = body || {};
  if (!username || typeof username !== "string" || !password || typeof password !== "string") {
    return NextResponse.json({ error: "Username and password required" }, { status: 400 });
  }
  const ok = await setAdminSession(username.trim(), password);
  if (!ok) {
    return NextResponse.json({ error: "Invalid username or password" }, { status: 401 });
  }
  return NextResponse.json({ success: true });
}
