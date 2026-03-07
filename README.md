# PortuHub – Simple Ecommerce

A simple ecommerce site with product listing, admin product management, and a landing page carousel of new items. Uses **MySQL** for admin users and products.

## Prerequisites

- Node.js 18+
- MySQL 8 (or 5.7+)

## Setup

### 1. Create database and tables

In MySQL, run the schema script:

```bash
mysql -u root -p < scripts/schema.sql
```

Or open `scripts/schema.sql` in your MySQL client and run it.

### 2. Environment variables

Copy `.env.example` to `.env` and set your MySQL credentials:

```
MYSQL_HOST=localhost
MYSQL_PORT=3306
MYSQL_USER=root
MYSQL_PASSWORD=your_mysql_password
MYSQL_DATABASE=portuhub

ADMIN_USERNAME=admin
ADMIN_PASSWORD=@PORTUHUB2026
```

### 3. Create admin user

Seed the default admin user (username from `ADMIN_USERNAME`, password from `ADMIN_PASSWORD`):

```bash
npm run db:seed
```

### 4. Install and run

```bash
npm install
npm run dev
```

Open [http://localhost:3000](http://localhost:3000).

## Admin login

- **URL:** `/admin`
- **Default after seed:** username `admin`, password `@PORTUHUB2026` (or whatever you set in `.env`).

## Features

- **Landing page** – Hero and a **carousel of newest products** (scroll horizontally).
- **Products** – `/products` lists all products with image, description, price, and stock.
- **Add / edit products** – In Admin (after login): add or edit products (images, name, description, price, stock).
- **Admin** – Login with username + password (stored in MySQL). Sessions stored in database.

## Data

- **Admin users** and **sessions** – MySQL tables `admin_users`, `sessions`.
- **Products** – MySQL table `products`.
- **Uploaded images** – Still saved to `public/uploads/`.
