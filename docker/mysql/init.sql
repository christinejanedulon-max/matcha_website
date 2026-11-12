-- =====================================================
-- MATCHA MOMENTS CAFE - DATABASE SCHEMA & INITIAL DATA
-- =====================================================

CREATE DATABASE IF NOT EXISTS matcha_moments CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE matcha_moments;

-- 1. USERS TABLE (Supports Customers, Staff & Admins)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'customer',
    roles JSON DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. PRODUCTS TABLE (Menu items, categories, pricing, stocks, and creator tracking)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_by_user_id INT NULL,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(50) DEFAULT 'Drinks',
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock_quantity INT NOT NULL DEFAULT 0,
    image_url VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. ORDERS TABLE (Tracks customer orders, fulfillment, payment, tracking & GCash reference)
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NULL,
    user_id INT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(180) NULL,
    phone VARCHAR(50) NULL,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    fulfillment_method VARCHAR(50) DEFAULT 'Pickup',
    delivery_address TEXT NULL,
    payment_method VARCHAR(50) DEFAULT 'Cash',
    reference_number VARCHAR(100) DEFAULT NULL,
    notes TEXT NULL,
    product_name VARCHAR(255) DEFAULT NULL,
    item_name VARCHAR(255) DEFAULT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    status_updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. ORDER ITEMS TABLE (Relates specific cart quantities to orders)
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NULL,
    product_name VARCHAR(255) DEFAULT NULL,
    options VARCHAR(255) DEFAULT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. REVIEWS TABLE (Customer ratings and feedback comments)
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    customer_name VARCHAR(100) NOT NULL,
    rating INT NOT NULL,
    comment TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. NEWSLETTER SUBSCRIPTIONS TABLE
CREATE TABLE IF NOT EXISTS newsletter_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    status VARCHAR(50) DEFAULT 'subscribed',
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- INITIAL DEFAULT DATA SEEDING
-- =====================================================

-- 1. Insert Default Admin Account
INSERT INTO users (full_name, email, password, role, roles, status) VALUES 
('Cafe Administrator', 'admin@matchamoments.com', '$2y$12$fPAL955t/YTIUJKv8jn4SOR2Z7StZLTgXr3RZgAHO0D82NCq/1z5S', 'admin', '["ROLE_ADMIN"]', 'active')
ON DUPLICATE KEY UPDATE password='$2y$12$fPAL955t/YTIUJKv8jn4SOR2Z7StZLTgXr3RZgAHO0D82NCq/1z5S', roles='["ROLE_ADMIN"]', status='active';

-- 2. Insert Default Staff Account
INSERT INTO users (full_name, email, password, role, roles, status) VALUES 
('Matcha Lead Barista', 'staff@matchamoments.com', '$2y$12$ohfwI1ZQr83AmZHgYthxf.Ue/eB9/qE3Rzd/55N8Yp0SxwwMtx7o.', 'staff', '["ROLE_STAFF"]', 'active')
ON DUPLICATE KEY UPDATE password='$2y$12$ohfwI1ZQr83AmZHgYthxf.Ue/eB9/qE3Rzd/55N8Yp0SxwwMtx7o.', roles='["ROLE_STAFF"]', status='active';

-- 3. Insert Default Customer / User Account
INSERT INTO users (full_name, email, password, role, roles, status) VALUES 
('Matcha Customer', 'matcha@matchamoments.com', '$2y$12$MiEBaZ6/GQFh6Fm4JKIMg.qB/4EforlM7eHG29qKahZZoWOGBqq8S', 'customer', '["ROLE_USER"]', 'active')
ON DUPLICATE KEY UPDATE password='$2y$12$MiEBaZ6/GQFh6Fm4JKIMg.qB/4EforlM7eHG29qKahZZoWOGBqq8S', status='active';

-- Insert Default Menu Items
INSERT INTO products (name, category, price, stock_quantity, image_url) VALUES 
('Tropical Matcha', 'Signature Lattes & Bestsellers', 185.00, 50, 'images/tropical.png'),
('Matcha Latte', 'Signature Lattes & Bestsellers', 170.00, 50, 'images/latte.png'),
('Matcha Cloud', 'Signature Lattes & Bestsellers', 195.00, 40, 'images/cloud.png'),
('Choco Matcha Fusion', 'Signature Lattes & Bestsellers', 180.00, 45, 'images/choco.png'),
('Vanilla Oat Matcha', 'Signature Lattes & Bestsellers', 185.00, 30, 'images/vanilla.png'),
('Strawberry Matcha', 'Fruit & Specialty Fusions', 190.00, 35, 'images/strawberry.png'),
('Mango Cloud Matcha', 'Fruit & Specialty Fusions', 195.00, 30, 'images/mango.png'),
('Coconut Matcha', 'Fruit & Specialty Fusions', 180.00, 25, 'images/coconut.png'),
('Peach Blossom Matcha', 'Fruit & Specialty Fusions', 185.00, 25, 'images/peach.png'),
('Sea Salt Matcha', 'Fruit & Specialty Fusions', 190.00, 30, 'images/sea.png'),
('Ube Cake Matcha', 'Premium Cake Matcha Creations', 210.00, 20, 'images/ube.png'),
('Pistachio Matcha', 'Premium Cake Matcha Creations', 175.00, 25, 'images/pis.png'),
('Tiramisu Cake Matcha', 'Premium Cake Matcha Creations', 175.00, 20, 'images/tira.png'),
('Cheesecake Matcha', 'Premium Cake Matcha Creations', 160.00, 25, 'images/cake.png')
ON DUPLICATE KEY UPDATE name=name;

