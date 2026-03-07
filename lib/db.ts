import mysql from "mysql2/promise";

let pool: mysql.Pool | null = null;

function getPool(): mysql.Pool {
  if (!pool) {
    const host = process.env.MYSQL_HOST || "localhost";
    const user = process.env.MYSQL_USER || "root";
    const password = process.env.MYSQL_PASSWORD || "";
    const database = process.env.MYSQL_DATABASE || "portuhub";
    const port = parseInt(process.env.MYSQL_PORT || "3306", 10);
    pool = mysql.createPool({
      host,
      user,
      password,
      database,
      port,
      waitForConnections: true,
      connectionLimit: 10,
      queueLimit: 0,
    });
  }
  return pool;
}

export async function query<T = mysql.RowDataPacket[]>(
  sql: string,
  params?: (string | number | null)[]
): Promise<T> {
  const p = getPool();
  const [rows] = await p.execute(sql, params);
  return rows as T;
}

export async function queryOne<T = mysql.RowDataPacket>(
  sql: string,
  params?: (string | number | null)[]
): Promise<T | null> {
  const rows = await query<mysql.RowDataPacket[]>(sql, params);
  return rows.length > 0 ? (rows[0] as T) : null;
}

export { getPool };
