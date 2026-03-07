const mysql = require("mysql2/promise");
const bcrypt = require("bcryptjs");

const host = process.env.MYSQL_HOST || "localhost";
const user = process.env.MYSQL_USER || "root";
const password = process.env.MYSQL_PASSWORD || "";
const database = process.env.MYSQL_DATABASE || "portuhub";
const port = parseInt(process.env.MYSQL_PORT || "3306", 10);

const defaultUsername = process.env.ADMIN_USERNAME || "admin";
const defaultPassword = process.env.ADMIN_PASSWORD || "@PORTUHUB2026";

async function seed() {
  let conn;
  try {
    conn = await mysql.createConnection({ host, user, password, port });
    await conn.query(`CREATE DATABASE IF NOT EXISTS \`${database}\``);
    await conn.changeUser({ database });

    const [users] = await conn.query(
      "SELECT id FROM admin_users WHERE username = ?",
      [defaultUsername]
    );
    if (users.length > 0) {
      console.log(`Admin user "${defaultUsername}" already exists.`);
      return;
    }
    const hash = await bcrypt.hash(defaultPassword, 10);
    await conn.query(
      "INSERT INTO admin_users (username, password_hash) VALUES (?, ?)",
      [defaultUsername, hash]
    );
    console.log(`Created admin user: ${defaultUsername} / (password you set in ADMIN_PASSWORD)`);
  } catch (err) {
    console.error("Seed failed:", err.message);
    process.exit(1);
  } finally {
    if (conn) await conn.end();
  }
}

seed();
