-- =========================================
-- E-Commerce Database Schema + Seed Data
-- =========================================

CREATE DATABASE IF NOT EXISTS ecommerce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecommerce_db;

-- -----------------------------------------
-- USERS
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------
-- ADMINS
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- -----------------------------------------
-- CATEGORIES
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- -----------------------------------------
-- PRODUCTS
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(500) DEFAULT NULL,
    featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -----------------------------------------
-- ORDERS
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    zip VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL DEFAULT 'United States',
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('Pending','Processing','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -----------------------------------------
-- ORDER ITEMS
-- -----------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    qty INT NOT NULL DEFAULT 1,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================
-- SEED DATA
-- =========================================

-- Admin account: admin / admin123
INSERT INTO admins (username, password_hash) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Demo user: john@example.com / password
INSERT INTO users (name, email, password_hash) VALUES
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Categories
INSERT INTO categories (name, slug) VALUES
('Electronics', 'electronics'),
('Clothing', 'clothing'),
('Accessories', 'accessories'),
('Home & Living', 'home-living');

-- Products (Electronics)
INSERT INTO products (category_id, name, slug, description, price, stock, image, featured) VALUES
(1, 'ProBook Laptop 15"', 'probook-laptop-15',
 'Powerful 15" laptop with Intel Core i7, 16GB RAM, 512GB SSD, and a stunning 4K display. Perfect for professionals and creators.',
 1299.99, 25, 'https://picsum.photos/seed/laptop15/600/600', 1),

(1, 'PixelPhone X12 Pro', 'pixelphone-x12-pro',
 'Flagship smartphone with 200MP camera system, 5000mAh battery, 6.8" AMOLED display, and 5G connectivity.',
 899.99, 40, 'https://picsum.photos/seed/phone12/600/600', 1),

(1, 'SoundMax Pro Headphones', 'soundmax-pro-headphones',
 'Premium over-ear headphones with active noise cancellation, 40hr battery life, and Hi-Res audio certification.',
 249.99, 60, 'https://picsum.photos/seed/headphones/600/600', 0),

(1, 'UltraTab 11 Tablet', 'ultratab-11',
 '11-inch tablet with a 120Hz display, M2 chip, 8GB RAM, and support for stylus input. Work and play redefined.',
 649.99, 30, 'https://picsum.photos/seed/tablet11/600/600', 1),

(1, 'SmartWatch Series 9', 'smartwatch-series-9',
 'Advanced health monitoring, GPS, ECG, blood oxygen sensor, and a beautiful Always-On Retina display.',
 399.99, 50, 'https://picsum.photos/seed/watch9/600/600', 0);

-- Products (Clothing)
INSERT INTO products (category_id, name, slug, description, price, stock, image, featured) VALUES
(2, 'Urban Flex Hoodie', 'urban-flex-hoodie',
 'Ultra-soft cotton blend hoodie with a modern streetwear silhouette. Available in multiple colorways.',
 79.99, 100, 'https://picsum.photos/seed/hoodie1/600/600', 0),

(2, 'SlimFit Chinos', 'slimfit-chinos',
 'Classic slim-fit chinos crafted from premium stretch fabric. Versatile for both casual and smart-casual looks.',
 59.99, 80, 'https://picsum.photos/seed/chinos/600/600', 0),

(2, 'Velocity Running Shoes', 'velocity-running-shoes',
 'Lightweight and breathable running shoes with energy-return foam midsole and adaptive traction outsole.',
 129.99, 70, 'https://picsum.photos/seed/shoes1/600/600', 1);

-- Products (Accessories)
INSERT INTO products (category_id, name, slug, description, price, stock, image, featured) VALUES
(3, 'Apex Leather Wallet', 'apex-leather-wallet',
 'Full-grain leather bifold wallet with RFID blocking. Slim profile with smart card organization.',
 49.99, 120, 'https://picsum.photos/seed/wallet1/600/600', 0),

(3, 'Aviator Sunglasses', 'aviator-sunglasses',
 'Classic aviator-style sunglasses with UV400 polarized lenses and a premium titanium frame.',
 89.99, 90, 'https://picsum.photos/seed/sunglass/600/600', 0),

(3, 'ProGrip Backpack 30L', 'progrip-backpack-30l',
 'Spacious 30L backpack with laptop compartment, anti-theft zipper, USB charging port, and water resistance.',
 149.99, 45, 'https://picsum.photos/seed/backpack/600/600', 1);

-- Products (Home & Living)
INSERT INTO products (category_id, name, slug, description, price, stock, image, featured) VALUES
(4, 'AromaPro Diffuser', 'aromapro-diffuser',
 'Ultrasonic aromatherapy diffuser with 7-color LED lighting, timer, and 500ml capacity. Create your sanctuary.',
 39.99, 150, 'https://picsum.photos/seed/diffuser/600/600', 0);

-- Sample Orders
INSERT INTO orders (user_id, full_name, email, address, city, zip, country, total, status, created_at) VALUES
(1, 'John Doe', 'john@example.com', '123 Main Street, Apt 4B', 'New York', '10001', 'United States', 1549.98, 'Delivered', '2026-10-15 10:23:00'),
(1, 'John Doe', 'john@example.com', '123 Main Street, Apt 4B', 'New York', '10001', 'United States', 249.99, 'Shipped', '2026-10-28 14:11:00'),
(NULL, 'Alice Smith', 'alice@example.com', '456 Oak Avenue', 'Los Angeles', '90001', 'United States', 899.99, 'Processing', '2026-11-01 09:05:00'),
(NULL, 'Bob Johnson', 'bob@example.com', '789 Pine Road', 'Chicago', '60601', 'United States', 209.98, 'Pending', '2026-11-02 18:44:00');

-- Order Items
INSERT INTO order_items (order_id, product_id, product_name, price, qty) VALUES
(1, 1, 'ProBook Laptop 15"', 1299.99, 1),
(1, 3, 'SoundMax Pro Headphones', 249.99, 1),
(2, 3, 'SoundMax Pro Headphones', 249.99, 1),
(3, 2, 'PixelPhone X12 Pro', 899.99, 1),
(4, 6, 'Urban Flex Hoodie', 79.99, 1),
(4, 9, 'Apex Leather Wallet', 49.99, 1),
(4, 7, 'SlimFit Chinos', 59.99, 1),
(4, 10, 'Aviator Sunglasses', 89.99, 0);
