# PortuHub – Simple Ecommerce

A simple ecommerce site with product listing, admin product management, and a landing page carousel of new items.

## Run locally

```bash
npm install
npm run dev
```

Open [http://localhost:3000](http://localhost:3000).

## Features

- **Landing page** – Hero and a **carousel of newest products** (scroll horizontally).
- **Products** – `/products` lists all products with image, description, price, and stock.
- **Add products** – In **Admin** you can add products with:
  - **Image** (file upload)
  - **Description**
  - **Price**
  - **Stock**
- **Admin** – `/admin`:
  - **Login** with admin password (default: `@PORTUHUB2026`).
  - **Stock management** – Edit and save stock for each product.
  - **Add new product** – Form at the bottom for image, name, description, price, stock.

## Admin password

Default password: **@PORTUHUB2026**

To change it, create a `.env` file (see `.env.example`):

```
ADMIN_PASSWORD=your-secret-password
```

## Data

- Products are stored in `data/products.json` (created on first add).
- Uploaded images go to `public/uploads/`.
