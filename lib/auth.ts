import { cookies } from "next/headers";
import bcrypt from "bcryptjs";
import { randomBytes } from "crypto";
import { query, queryOne } from "./db";

const ADMIN_COOKIE = "admin_session";
const SESSION_DAYS = 7;

interface AdminUser {
  id: number;
  username: string;
  password_hash: string;
}

interface SessionRow {
  admin_user_id: number;
  expires_at: Date;
}

export async function isAdmin(): Promise<boolean> {
  const cookieStore = await cookies();
  const token = cookieStore.get(ADMIN_COOKIE)?.value;
  if (!token) return false;
  try {
    const row = await queryOne<SessionRow>(
      "SELECT admin_user_id, expires_at FROM sessions WHERE token = ? AND expires_at > NOW()",
      [token]
    );
    return !!row;
  } catch {
    return false;
  }
}

export async function setAdminSession(username: string, password: string): Promise<boolean> {
  if (!username || !password) return false;
  try {
    const user = await queryOne<AdminUser>(
      "SELECT id, username, password_hash FROM admin_users WHERE username = ?",
      [username]
    );
    if (!user || !(await bcrypt.compare(password, user.password_hash))) return false;

    const token = randomBytes(32).toString("hex");
    const expiresAt = new Date();
    expiresAt.setDate(expiresAt.getDate() + SESSION_DAYS);
    await query(
      "INSERT INTO sessions (admin_user_id, token, expires_at) VALUES (?, ?, ?)",
      [user.id, token, expiresAt.toISOString().slice(0, 19).replace("T", " ")]
    );

    const cookieStore = await cookies();
    cookieStore.set(ADMIN_COOKIE, token, {
      httpOnly: true,
      secure: process.env.NODE_ENV === "production",
      sameSite: "lax",
      maxAge: 60 * 60 * 24 * SESSION_DAYS,
      path: "/",
    });
    return true;
  } catch {
    return false;
  }
}

export async function clearAdminSession(): Promise<void> {
  const cookieStore = await cookies();
  const token = cookieStore.get(ADMIN_COOKIE)?.value;
  if (token) {
    try {
      await query("DELETE FROM sessions WHERE token = ?", [token]);
    } catch {
      // ignore
    }
  }
  cookieStore.delete(ADMIN_COOKIE);
}
