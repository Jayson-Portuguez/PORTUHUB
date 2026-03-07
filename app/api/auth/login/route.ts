import { NextRequest, NextResponse } from "next/server";
import { setAdminSession } from "@/lib/auth";

export async function POST(request: NextRequest) {
  const body = await request.json();
  const { password } = body || {};
  if (!password || typeof password !== "string") {
    return NextResponse.json({ error: "Password required" }, { status: 400 });
  }
  const ok = await setAdminSession(password);
  if (!ok) {
    return NextResponse.json({ error: "Invalid password" }, { status: 401 });
  }
  return NextResponse.json({ success: true });
}
