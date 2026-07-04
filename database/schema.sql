-- =====================================================================
--  FreshSip Beverages - Juice Bar Management System (JMS)
--  Database schema + sample seed data
--
--  How to load (from the project root):
--     mysql -u root -p < database/schema.sql
--  or import this file through phpMyAdmin.
-- =====================================================================

DROP DATABASE IF EXISTS jms_db;
CREATE DATABASE jms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jms_db;

-- ---------------------------------------------------------------------
--  USERS  (Admin / Staff / Customer)
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(100)  NOT NULL,
    email          VARCHAR(150)  NOT NULL UNIQUE,
    password       VARCHAR(255)  NOT NULL,
    role           ENUM('Admin','Staff','Customer') NOT NULL DEFAULT 'Customer',
    loyalty_points INT           NOT NULL DEFAULT 0,
    created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  CATEGORIES  (juice categories, managed by admin)
-- ---------------------------------------------------------------------
CREATE TABLE categories (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  PRODUCTS  (menu items)
-- ---------------------------------------------------------------------
CREATE TABLE products (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(120)   NOT NULL,
    description  VARCHAR(255)   DEFAULT NULL,
    price        DECIMAL(8,2)   NOT NULL DEFAULT 0.00,
    category_id  INT            DEFAULT NULL,
    icon         VARCHAR(30)    DEFAULT 'cup-straw',
    is_available TINYINT(1)     NOT NULL DEFAULT 1,
    created_at   TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  INVENTORY  (ingredients / raw material)
-- ---------------------------------------------------------------------
CREATE TABLE inventory (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    ingredient_name VARCHAR(120)  NOT NULL UNIQUE,
    unit            VARCHAR(20)   NOT NULL DEFAULT 'units',
    stock_level     DECIMAL(10,2) NOT NULL DEFAULT 0,
    alert_threshold DECIMAL(10,2) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  PRODUCT_INGREDIENTS  (recipe / bill of materials)
--  Links a product to the ingredients (and quantities) it consumes.
--  Used to automatically deduct stock when an order is placed.
-- ---------------------------------------------------------------------
CREATE TABLE product_ingredients (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    product_id    INT           NOT NULL,
    ingredient_id INT           NOT NULL,
    quantity_used DECIMAL(10,2) NOT NULL DEFAULT 1,
    CONSTRAINT fk_pi_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_pi_ingredient
        FOREIGN KEY (ingredient_id) REFERENCES inventory(id) ON DELETE CASCADE,
    UNIQUE KEY uq_product_ingredient (product_id, ingredient_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  ORDERS
-- ---------------------------------------------------------------------
CREATE TABLE orders (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT           NOT NULL,
    order_date      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    subtotal        DECIMAL(10,2) NOT NULL DEFAULT 0,
    discount        DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_amount    DECIMAL(10,2) NOT NULL DEFAULT 0,
    points_earned   INT           NOT NULL DEFAULT 0,
    points_redeemed INT           NOT NULL DEFAULT 0,
    payment_method  ENUM('Cash','Card','UPI') NOT NULL DEFAULT 'Cash',
    status          ENUM('Pending','Preparing','Served','Cancelled') NOT NULL DEFAULT 'Pending',
    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  ORDER_ITEMS
-- ---------------------------------------------------------------------
CREATE TABLE order_items (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    order_id       INT           NOT NULL,
    product_id     INT           DEFAULT NULL,
    product_name   VARCHAR(120)  NOT NULL,   -- snapshot in case product changes/deleted
    unit_price     DECIMAL(8,2)  NOT NULL,
    quantity       INT           NOT NULL DEFAULT 1,
    customizations VARCHAR(255)  DEFAULT NULL,
    CONSTRAINT fk_oi_order
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_oi_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =====================================================================
--  SEED DATA
-- =====================================================================

-- NOTE: Demo users (Admin / Staff / Customer) are created by opening
--       setup.php in the browser once, so their passwords are hashed
--       correctly with PHP's password_hash(). See setup.php / README.

-- Categories
INSERT INTO categories (name) VALUES
('Fruit Juices'), ('Smoothies'), ('Detox & Greens'), ('Milkshakes');

-- Inventory (ingredients)
INSERT INTO inventory (ingredient_name, unit, stock_level, alert_threshold) VALUES
('Orange',        'pcs', 100, 20),
('Apple',         'pcs',  80, 20),
('Mango',         'pcs',  50, 15),
('Banana',        'pcs',  60, 15),
('Spinach',       'g',  5000, 1000),
('Milk',          'ml', 20000, 4000),
('Yogurt',        'ml', 10000, 2000),
('Ice',           'g',  30000, 5000),
('Sugar',         'g',  8000, 1500),
('Protein Powder','g',  3000, 500),
('Honey',         'ml',  4000, 800),
('Ginger',        'g',  1000, 200);

-- Products
INSERT INTO products (name, description, price, category_id, icon) VALUES
('Classic Orange Juice', 'Freshly squeezed oranges, no additives.',        4.50, 1, 'cup-straw'),
('Apple Cooler',         'Crisp apple juice served over ice.',             4.00, 1, 'cup'),
('Mango Tango Smoothie', 'Mango + yogurt + a hint of honey.',              6.00, 2, 'cup-hot'),
('Banana Blast',         'Banana, milk and honey smoothie.',               5.50, 2, 'cup-hot'),
('Green Detox',          'Spinach, apple and ginger cold-pressed juice.',  6.50, 3, 'flower1'),
('Protein Power Shake',  'Banana milkshake boosted with protein powder.',  7.00, 4, 'lightning-charge');

-- Recipes (product_ingredients) - quantities consumed per single order
INSERT INTO product_ingredients (product_id, ingredient_id, quantity_used) VALUES
-- Classic Orange Juice
(1, 1, 4), (1, 8, 100),
-- Apple Cooler
(2, 2, 3), (2, 8, 120),
-- Mango Tango Smoothie
(3, 3, 1), (3, 7, 150), (3, 11, 20), (3, 8, 80),
-- Banana Blast
(4, 4, 2), (4, 6, 200), (4, 11, 15),
-- Green Detox
(5, 5, 150), (5, 2, 1), (5, 12, 20),
-- Protein Power Shake
(6, 4, 1), (6, 6, 250), (6, 10, 30), (6, 8, 100);
