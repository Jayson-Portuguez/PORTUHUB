import { cookies } from "next/headers";

const ADMIN_COOKIE = "admin_session";
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || "@PORTUHUB2026";

export async function isAdmin(): Promise<boolean> {
  const cookieStore = await cookies();
  const token = cookieStore.get(ADMIN_COOKIE)?.value;
  return token === ADMIN_PASSWORD;
}

export async function setAdminSession(password: string): Promise<boolean> {
  if (password !== ADMIN_PASSWORD) return false;
  const cookieStore = await cookies();
  cookieStore.set(ADMIN_COOKIE, password, {
    httpOnly: true,
    secure: process.env.NODE_ENV === "production",
    sameSite: "lax",
    maxAge: 60 * 60 * 24 * 7, // 7 days
    path: "/",
  });
  return true;
}

export async function clearAdminSession() {
  const cookieStore = await cookies();
  cookieStore.delete(ADMIN_COOKIE);
}
