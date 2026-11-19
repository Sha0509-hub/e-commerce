# ⚡ Nexus Store — E-Commerce Website

A full-featured, premium dark-themed e-commerce platform built with **HTML, CSS, PHP 8+, and MySQL**.

---

## ✨ Features

### Storefront
| Feature | Details |
|---|---|
| Home Page | Hero banner, product grid, category filter, live search |
| Product Detail | Image, description, qty selector, related products |
| Shopping Cart | Session-based cart, quantity update, remove, clear |
| Checkout | Address form, mock payment, order creation with DB transaction |
| Order Confirmation | Full order summary after successful checkout |
| Authentication | Register & Login with session management |
| Profile | Customer order history with status badges |

### Admin Panel (`/ecommerce/admin/`)
| Feature | Details |
|---|---|
| Dashboard | Revenue, orders, products, customers stats + low-stock alert |
| Products | Full CRUD via modal (Add/Edit/Delete), image URL, featured flag |
| Orders | List with status filter tabs, search, pagination |
| Order Detail | Line items, customer info, visual progress tracker, status update |

---

## 🚀 Setup Instructions

### Prerequisites
- **XAMPP** or **WAMP** (PHP 8.0+, MySQL 5.7+)
- Web browser

### Step 1 — Place project files
Copy the `ecommerce` folder into your server root:
- **XAMPP**: `C:\xampp\htdocs\ecommerce\`
- **WAMP**: `C:\wamp64\www\ecommerce\`

### Step 2 — Import the database
1. Start Apache and MySQL in XAMPP/WAMP
2. Open **phpMyAdmin** at `http://localhost/phpmyadmin`
3. Click **Import** tab
4. Choose `db/ecommerce.sql` and click **Go**

### Step 3 — Configure database (if needed)
Edit `includes/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');   // Your MySQL username
define('DB_PASS', '');       // Your MySQL password (blank for XAMPP default)
define('DB_NAME', 'ecommerce_db');
```

### Step 4 — Create uploads folder
Create the directory for product image uploads:
```
ecommerce/
└── uploads/
    └── products/   ← Create this folder (make sure it's writable)
```

### Step 5 — Visit the store!
| URL | Description |
|---|---|
| `http://localhost/ecommerce/` | Storefront homepage |
| `http://localhost/ecommerce/login.php` | Customer login |
| `http://localhost/ecommerce/register.php` | Create account |
| `http://localhost/ecommerce/admin/login.php` | Admin panel |

---

## 🔑 Default Credentials

### Admin Panel
| Field | Value |
|---|---|
| Username | `admin` |
| Password | `password` |

### Demo Customer
| Field | Value |
|---|---|
| Email | `john@example.com` |
| Password | `password` |

---

## 📁 Project Structure

```
ecommerce/
├── index.php               ← Home / product listing
├── product.php             ← Product detail page
├── cart.php                ← Shopping cart
├── cart_action.php         ← Add/remove/update cart handler
├── checkout.php            ← Checkout form + order creation
├── order_success.php       ← Order confirmation
├── login.php               ← Customer login
├── register.php            ← Customer registration
├── logout.php              ← Logout handler
├── profile.php             ← Customer order history
│
├── admin/
│   ├── index.php           ← Dashboard (stats + recent orders)
│   ├── login.php           ← Admin login
│   ├── logout.php          ← Admin logout
│   ├── products.php        ← Product management (CRUD)
│   ├── product_action.php  ← Add/Edit/Delete handler
│   ├── orders.php          ← Order list with filters
│   ├── order_detail.php    ← Order detail + status update
│   └── includes/
│       ├── header.php
│       └── footer.php
│
├── includes/
│   ├── db.php              ← PDO database connection
│   ├── auth.php            ← Session auth helpers
│   ├── functions.php       ← Cart, flash, formatting utils
│   ├── header.php          ← Storefront header + nav
│   └── footer.php          ← Storefront footer
│
├── css/
│   ├── style.css           ← Storefront design system (dark/glassmorphism)
│   └── admin.css           ← Admin panel styles
│
├── js/
│   └── main.js             ← Interactivity, filters, modals, toasts
│
├── uploads/
│   └── products/           ← Product image uploads (create this!)
│
└── db/
    └── ecommerce.sql       ← Database schema + 12 sample products
```

---

## 🎨 Design System

- **Theme**: Dark mode with violet/purple accents (`#7c3aed`)
- **Glassmorphism** cards with `backdrop-filter: blur()`
- **Font**: Inter (Google Fonts)
- **Animations**: Hover lifts, fade-ins, toast notifications
- **Responsive**: Mobile-first with CSS Grid & Flexbox

---

## 🛡️ Security Features

- Prepared PDO statements (SQL injection prevention)
- `password_hash()` / `password_verify()` for passwords
- `htmlspecialchars()` / `strip_tags()` for XSS prevention
- Session regeneration on login
- Redirect validation (prevents open redirects)
- Admin route protection via `requireAdminLogin()`
- CSRF-safe: POST-only state-changing operations

---

## 📋 Database Schema

```
users          — Customer accounts
admins         — Admin accounts  
categories     — Product categories (4 default)
products       — Product catalog (12 default)
orders         — Customer orders
order_items    — Line items per order
```

---

## 🔧 Customization Tips

- **Add categories**: Insert rows into the `categories` table
- **Change colors**: Edit CSS variables in `css/style.css` (`:root` block)
- **Add products**: Use the Admin Panel → Products → Add Product
- **Real payments**: Replace the mock payment section in `checkout.php` with Stripe/PayPal SDK

---

*Built with ❤️ — HTML · CSS · PHP 8 · MySQL*
