-- =====================================
-- RESET DATABASE
-- =====================================
DROP DATABASE IF EXISTS organic_db;
CREATE DATABASE organic_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE organic_db;

-- Tắt kiểm tra khóa ngoại khi reset bảng
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================
-- 1. BẢNG CATEGORIES (DANH MỤC)
-- =====================================
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT 'grass',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================
-- 2. BẢNG PRODUCTS (SẢN PHẨM)
-- =====================================
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,0) NOT NULL,
    sale_price DECIMAL(10,0),
    unit VARCHAR(50) DEFAULT 'kg',
    image VARCHAR(255),
    stock INT DEFAULT 0,
    is_organic BOOLEAN DEFAULT TRUE,
    is_new BOOLEAN DEFAULT FALSE,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- =====================================
-- 3. BẢNG USERS (NGƯỜI DÙNG)
-- =====================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255),
    membership ENUM('bronze','silver','gold') DEFAULT 'bronze',
    role ENUM('customer','admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================
-- 4. BẢNG ADDRESSES (ĐỊA CHỈ)
-- =====================================
CREATE TABLE addresses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    is_default BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================
-- 5. BẢNG ORDERS (ĐƠN HÀNG)
-- =====================================
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    total_amount DECIMAL(12,0) NOT NULL,
    shipping_fee DECIMAL(10,0) DEFAULT 25000,
    status ENUM('pending','confirmed','shipping','delivered','cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT 'cod',
    shipping_address TEXT,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- =====================================
-- 6. BẢNG ORDER_ITEMS (CHI TIẾT ĐƠN)
-- =====================================
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,0) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- =====================================
-- 7. BẢNG DASHBOARD STATS (THỐNG KÊ)
-- =====================================
CREATE TABLE dashboard_stats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    stat_date DATE NOT NULL,
    total_orders INT DEFAULT 0,
    total_revenue DECIMAL(12,0) DEFAULT 0,
    total_customers INT DEFAULT 0,
    new_customers INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (stat_date)
);

-- =====================================
-- 8. BẢNG ADMIN LOGS (LỊCH SỬ HOẠT ĐỘNG)
-- =====================================
CREATE TABLE admin_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT,
    action VARCHAR(100),
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
);

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================
-- DỮ LIỆU MẪU
-- =====================================

-- DANH MỤC
INSERT INTO categories (name, slug, icon) VALUES
('Rau củ', 'rau-cu', 'grass'),
('Trái cây', 'trai-cay', 'nutrition'),
('Trứng & Bơ sữa', 'trung-bo-sua', 'egg'),
('Bánh mì & Bánh ngọt', 'banh-mi', 'bakery_dining'),
('Thịt & Hải sản', 'thit-hai-san', 'lunch_dining');

-- SẢN PHẨM (FULL LIST NHƯ BẠN GỬI)
INSERT INTO products (category_id, name, slug, description, price, sale_price, unit, image, stock, is_organic, is_new, is_featured) VALUES
(1, 'Cà rốt hữu cơ', 'ca-rot-huu-co', 'Cà rốt tươi ngon từ Đà Lạt', 35000, NULL, '500g', '', 100, TRUE, FALSE, TRUE),
(1, 'Bông cải xanh', 'bong-cai-xanh', 'Bông cải xanh giàu vitamin', 33000, 28000, 'cái', '', 50, TRUE, FALSE, TRUE),
(1, 'Cà chua bi', 'ca-chua-bi', 'Cà chua bi ngọt tự nhiên', 25000, NULL, 'hộp 250g', '', 80, TRUE, TRUE, FALSE),
(1, 'Xà lách Romaine', 'xa-lach-romaine', 'Xà lách tươi giòn', 22000, NULL, 'bó', '', 60, TRUE, FALSE, FALSE),
(1, 'Ớt chuông xanh', 'ot-chuong-xanh', 'Ớt chuông giòn ngọt', 60000, NULL, 'kg', '', 40, TRUE, FALSE, FALSE),
(1, 'Cà tím', 'ca-tim', 'Cà tím tươi ngon', 30000, NULL, 'kg', '', 70, TRUE, FALSE, FALSE),
(1, 'Cà chua hữu cơ', 'ca-chua-huu-co', 'Cà chua Đà Lạt chín đỏ', 55000, NULL, '500g', '', 90, TRUE, TRUE, TRUE),
(1, 'Dưa leo hữu cơ', 'dua-leo-huu-co', 'Dưa leo giòn mát', 30000, NULL, 'kg', '', 55, TRUE, FALSE, FALSE),
(2, 'Táo Envy', 'tao-envy', 'Táo nhập khẩu New Zealand', 99000, NULL, '0.5kg', '', 30, TRUE, TRUE, TRUE),
(3, 'Trứng gà thả vườn', 'trung-ga-tha-vuon', 'Trứng gà sạch tự nhiên', 129000, NULL, 'vỉ 10 trứng', '', 45, TRUE, FALSE, TRUE),
(4, 'Bánh mì nguyên cám', 'banh-mi-nguyen-cam', 'Bánh mì tốt cho sức khỏe', 159000, NULL, 'ổ', '', 25, TRUE, TRUE, TRUE);

-- USERS (PASSWORD MẶC ĐỊNH: 123456)
INSERT INTO users (name, email, phone, password, membership, role) VALUES
('Lê An', 'lean@email.com', '0901234567',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'silver', 'customer'),

('Admin Xanh Organic', 'admin@xanhorganic.vn', '0900000000',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'gold', 'admin');
