# FreshSip &middot; Juice Bar Management System (JMS)

An automated Point-of-Sale (POS) and inventory management system for **FreshSip Beverages Pvt. Ltd.**, built with **PHP 8 + MySQL** and a Bootstrap 5 front end.

The project streamlines ordering, manages customer loyalty and tracks ingredient stock in real time.

---

## 1. Tech stack

| Layer     | Technology                                   |
|-----------|----------------------------------------------|
| Language  | PHP 8.x (PDO, prepared statements)           |
| Database  | MySQL 8 / MariaDB                            |
| Server    | Apache (XAMPP / WAMP)                         |
| Frontend  | HTML5, CSS3, Bootstrap 5, Bootstrap Icons, vanilla JS |

---

## 2. Feature-based folder structure

Every feature lives in its **own folder** under `features/`, with all of its
files kept together:

```
ISD_Project/
├── assets/                     # css + js
│   ├── css/style.css
│   └── js/app.js
├── config/
│   └── db.php                  # PDO connection
├── includes/                   # shared building blocks
│   ├── bootstrap.php           # session, BASE_URL, loads db + helpers
│   ├── functions.php           # auth, cart, flash, csrf helpers
│   ├── header.php  / footer.php
│   └── auth_check.php
├── database/
│   └── schema.sql              # full schema + sample data
│
├── features/
│   ├── auth/                   # login, register, logout
│   ├── menu-management/        # Epic 001 - products & categories (Admin)
│   ├── order-management/       # Epic 001 - cart & customizations
│   ├── kitchen-display/        # Epic 001 - live order board (Staff)
│   ├── billing/                # Epic 002 - checkout, payment, receipt
│   ├── loyalty/                # Epic 002 - points & rewards
│   ├── inventory/              # Epic 003 - stock, recipes, low-stock alerts
│   └── reporting/              # Epic 003 - sales reports + CSV export
│
├── admin/dashboard.php         # Admin overview
├── staff/dashboard.php         # Staff overview
├── index.php                   # Customer menu / landing page
├── setup.php                   # one-time installer (creates demo users)
└── README.md
```

---

## 3. Setup (XAMPP / WAMP)

1. **Copy the project** into your web root so it is served by Apache:
   - XAMPP: `C:\xampp\htdocs\ISD_Project`
   - WAMP:  `C:\wamp64\www\ISD_Project`

2. **Start Apache and MySQL** from the XAMPP/WAMP control panel.

3. **Create the database.** Open phpMyAdmin (<http://localhost/phpmyadmin>) →
   *Import* → choose `database/schema.sql` → **Go**.
   (Or from a terminal: `mysql -u root -p < database/schema.sql`.)

4. **Check credentials** in `config/db.php`. Defaults match a stock XAMPP
   install (`root` / no password). Change if needed.

5. **Run the installer once** to create the demo accounts with correctly
   hashed passwords:

   <http://localhost/ISD_Project/setup.php>

6. **Open the app:** <http://localhost/ISD_Project/>

> You may delete `setup.php` after step 5.

---

## 4. Demo accounts

All demo passwords are **`password123`**.

| Role     | Email                  | Lands on          |
|----------|------------------------|-------------------|
| Admin    | admin@freshsip.test    | Admin dashboard   |
| Staff    | staff@freshsip.test    | Kitchen display   |
| Customer | alice@freshsip.test    | Customer menu     |

New customers can also self-register from the sign-up page.

---

## 5. Feature walk-through (mapped to the Agile backlog)

### Epic 001 — Operational Core
- **Menu Management** (`features/menu-management/`): admins add/edit/delete
  products and manage categories.
- **Customization** (`features/order-management/`, US001-US003): customers add
  *No Sugar*, *Extra Ice*, *Add Protein*, etc. per item.
- **Kitchen Display** (`features/kitchen-display/`, US004-US005): staff see a
  live board (auto-refreshing every 5s) and advance orders
  `Pending → Preparing → Served`.

### Epic 002 — Billing & Loyalty
- **Billing Engine** (`features/billing/`): itemised, printable receipts.
- **Payments** (US006): Cash / Card / UPI (Card & UPI are placeholders).
- **Loyalty** (`features/loyalty/`, US007-US008): earn **1 point per $1**;
  redeem **10 points = $1** off at checkout.

### Epic 003 — Inventory & Reporting
- **Stock Tracking** (`features/inventory/`, US009): each product has a
  **recipe** (`product_ingredients`); placing an order deducts the ingredients
  inside a single SQL **transaction** (rolls back if stock is insufficient).
- **Low Stock Alerts** (US010): dashboard + inventory warnings when stock
  drops to/below the alert threshold.
- **Sales Reports** (`features/reporting/`, US011): daily/monthly revenue and
  top-selling items using `SUM()` / `GROUP BY`, exportable to CSV.

---

## 6. Database design

Tables: `users`, `categories`, `products`, `inventory`,
`product_ingredients` (recipe/BOM), `orders`, `order_items`.

Key additions beyond the base ERD (kept the design clean and made inventory
deduction meaningful):
- `categories` table + `products.category_id` (instead of a free-text category).
- `product_ingredients` recipe table linking products to the ingredients they
  consume, enabling automatic, quantity-aware stock deduction.
- `orders` stores `subtotal`, `discount`, `points_earned`, `points_redeemed`
  and `payment_method` for a complete billing record.

---

## 7. Security notes

- All DB access uses **PDO prepared statements** (SQL-injection safe).
- Passwords hashed with `password_hash()` / verified with `password_verify()`.
- Output escaped via a global `e()` helper (XSS safe).
- State-changing forms protected with **CSRF tokens**.
- Role-based access control via `require_role()`.

---

*Student project for FreshSip Beverages Pvt. Ltd. — Juice Bar Management System.*
