-- =====================================
-- ORGANIC DB - COMPLETE DATABASE SCHEMA
-- =====================================
DROP DATABASE IF EXISTS organic_db;
CREATE DATABASE organic_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE organic_db;

SET FOREIGN_KEY_CHECKS = 0;

-- =====================================
-- 1. BẢNG CATEGORIES (DANH MỤC)
-- =====================================
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(255) DEFAULT 'grass',
    description TEXT,
    parent_id INT DEFAULT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- 2. BẢNG PRODUCTS (SẢN PHẨM)
-- =====================================
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    short_description VARCHAR(500),
    price DECIMAL(10,0) NOT NULL,
    sale_price DECIMAL(10,0),
    cost_price DECIMAL(10,0),
    unit VARCHAR(50) DEFAULT 'kg',
    weight DECIMAL(8,2),
    image VARCHAR(255),
    gallery TEXT, -- JSON array of image URLs
    stock INT DEFAULT 0,
    sku VARCHAR(50) UNIQUE,
    barcode VARCHAR(50),
    is_organic BOOLEAN DEFAULT TRUE,
    is_new BOOLEAN DEFAULT FALSE,
    is_featured BOOLEAN DEFAULT FALSE,
    is_bestseller BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    view_count INT DEFAULT 0,
    sold_count INT DEFAULT 0,
    rating_avg DECIMAL(3,2) DEFAULT 0,
    rating_count INT DEFAULT 0,
    meta_title VARCHAR(200),
    meta_description VARCHAR(500),
    meta_keywords VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_category (category_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    gender ENUM('male','female','other') DEFAULT NULL,
    birthdate DATE,
    membership ENUM('bronze','silver','gold','platinum') DEFAULT 'bronze',
    points INT DEFAULT 0,
    role ENUM('customer','admin','staff') DEFAULT 'customer',
    is_active BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    email_verified_at TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    last_login_ip VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- 4. BẢNG PASSWORD_RESETS (ĐẶT LẠI MẬT KHẨU)
-- =====================================
CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- 5. BẢNG ADDRESSES (ĐỊA CHỈ)
-- =====================================
CREATE TABLE addresses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    ward VARCHAR(100),
    district VARCHAR(100),
    city VARCHAR(100),
    postal_code VARCHAR(20),
    address_type ENUM('home','office','other') DEFAULT 'home',
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- 6. BẢNG WISHLISTS (YÊU THÍCH)
-- =====================================
CREATE TABLE wishlists (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, product_id),
    INDEX idx_user (user_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- 7. BẢNG CARTS (GIỎ HÀNG)
-- =====================================
CREATE TABLE carts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- 8. BẢNG COUPONS (MÃ GIẢM GIÁ)
-- =====================================
CREATE TABLE coupons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    discount_type ENUM('percentage','fixed') DEFAULT 'percentage',
    discount_value DECIMAL(10,0) NOT NULL,
    min_order_value DECIMAL(10,0) DEFAULT 0,
    max_discount DECIMAL(10,0),
    usage_limit INT,
    used_count INT DEFAULT 0,
    start_date TIMESTAMP,
    end_date TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- 9. BẢNG ORDERS (ĐƠN HÀNG)
-- =====================================
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    order_code VARCHAR(50) UNIQUE,
    total_amount DECIMAL(12,0) NOT NULL,
    discount_amount DECIMAL(10,0) DEFAULT 0,
    shipping_fee DECIMAL(10,0) DEFAULT 25000,
    tax_amount DECIMAL(10,0) DEFAULT 0,
    final_amount DECIMAL(12,0) NOT NULL,
    status ENUM('pending','confirmed','processing','shipping','delivered','cancelled','refunded') DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT 'cod',
    payment_status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    shipping_name VARCHAR(100),
    shipping_phone VARCHAR(20),
    shipping_address TEXT,
    shipping_ward VARCHAR(100),
    shipping_district VARCHAR(100),
    shipping_city VARCHAR(100),
    note TEXT,
    coupon_code VARCHAR(50),
    tracking_number VARCHAR(100),
    cancelled_reason TEXT,
    cancelled_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_order_code (order_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- 10. BẢNG ORDER_ITEMS (CHI TIẾT ĐƠN)
-- =====================================
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(200),
    product_image VARCHAR(255),
    quantity INT NOT NULL,
    unit_price DECIMAL(10,0) NOT NULL,
    total_price DECIMAL(12,0) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- 11. BẢNG PRODUCT_REVIEWS (ĐÁNH GIÁ)
-- =====================================
CREATE TABLE product_reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    title VARCHAR(200),
    comment TEXT,
    images TEXT, -- JSON array of image URLs
    helpful_count INT DEFAULT 0,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    admin_reply TEXT,
    replied_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_product (product_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- 12. BẢNG NOTIFICATIONS (THÔNG BÁO)
-- =====================================
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type VARCHAR(50),
    title VARCHAR(200),
    message TEXT,
    link VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- 13. BẢNG ACTIVITY_LOGS (LỊCH SỬ HOẠT ĐỘNG)
-- =====================================
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100),
    description TEXT,
    entity_type VARCHAR(50),
    entity_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- 14. BẢNG BLOG_POSTS (BÀI VIẾT)
-- =====================================
CREATE TABLE blog_posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    author_id INT,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    excerpt VARCHAR(500),
    content TEXT,
    featured_image VARCHAR(255),
    status ENUM('draft','published','archived') DEFAULT 'draft',
    view_count INT DEFAULT 0,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- 15. BẢNG NEWSLETTER_SUBSCRIBERS (ĐĂNG KÝ NHẬN TIN)
-- =====================================
CREATE TABLE newsletter_subscribers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(100),
    status ENUM('active','unsubscribed') DEFAULT 'active',
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at TIMESTAMP NULL,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================
-- 16. BẢNG SETTINGS (CÀI ĐẶT HỆ THỐNG)
-- =====================================
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type VARCHAR(50) DEFAULT 'text',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================
-- DỮ LIỆU MẪU
-- =====================================

-- DANH MỤC
INSERT INTO categories (name, slug, icon, description, display_order) VALUES
('Rau củ', 'rau-cu', 'grass', 'Rau củ tươi ngon hữu cơ', 1),
('Trái cây', 'trai-cay', 'nutrition', 'Trái cây nhập khẩu và trong nước', 2),
('Trứng & Bơ sữa', 'trung-bo-sua', 'egg', 'Sản phẩm từ trứng và sữa', 3),
('Bánh mì & Bánh ngọt', 'banh-mi', 'bakery_dining', 'Bánh mì và bánh ngọt tươi mỗi ngày', 4),
('Thịt & Hải sản', 'thit-hai-san', 'lunch_dining', 'Thịt và hải sản tươi sống', 5);

-- SẢN PHẨM
INSERT INTO products (category_id, name, slug, description, price, sale_price, unit, image, stock, sku, is_organic, is_new, is_featured) VALUES
(1, 'Cà rốt hữu cơ', 'ca-rot-huu-co', 'Cà rốt tươi ngon từ Đà Lạt, giàu vitamin A tốt cho mắt', 35000, NULL, '500g', 'https://lh3.googleusercontent.com/aida-public/AB6AXuD8RQdsr0IximrJ-Jrgorj_zPyyroI6Dfs6HIvNjkaBWujWCp1k403nRwFWbA-978540FUkW06k7__NSW5KuH6-A9MTyRw70Hu__Rk8XU6KqzblZfeNan9A213sE7dpE2T5qxiFCsFMLtglz5MaeGIXm6qVO2dINWCKpDp7iuJkAYXmdYaIipZ14OGkBroPgSNBkwK29zgtnRvMSz99jJ_hnUbrmXStVHe1tqaggBMdSstRCxKgwzx1aZ-eDfdBt9FRLN0tbUVJIKxb', 100, 'VEG001', TRUE, FALSE, TRUE),

(1, 'Bông cải xanh', 'bong-cai-xanh', 'Bông cải xanh giàu vitamin C và chất xơ', 33000, 28000, 'cái', 'https://lh3.googleusercontent.com/aida-public/AB6AXuCi-qYnuvm_o9cLZcWAgaHjDNi-FfvsUtsFHmy7roZruCCjnvI3SYGQ31RjCJYsfXMObJ69_ikZtVNmFxE9E2rquQouggfdgVQMePbFD2ZP_B-M3nUzGxMatNmdNNTn0ka-HF2FGTfq8red1tEHAVRaej_6rwaJY67ypfBCDdHcmh8f1LUhVGATdrJ0u0-kiUzQ58WIYJMyWsBPMC7uJkcEy5yP0CkKNTpJcZSSXFz3lU__lwfhovuOg3QHvYew7l8B9MG3Kgjprb0W', 50, 'VEG002', TRUE, FALSE, TRUE),

(1, 'Cà chua bi', 'ca-chua-bi', 'Cà chua bi ngọt tự nhiên, hoàn hảo cho salad', 25000, NULL, 'hộp 250g', 'https://lh3.googleusercontent.com/aida-public/AB6AXuAcW2HNJaXm2Hngpw8wlfQOnYzGj6Vk2MZZwybZWW9mnoGm4PVs6n9ky8EfPy6Q_uLgh_NfMPOBlhqoUMNcNgXK0OFojINJ9VgYGh1zirkereSiTYzFhT6qwnkWyNu5jwrtFCrDNXS0D3IPN2HpB7xyHXCMBIG-zNJMOYxtPXe4fPvvAa3n_tn1Zcdq919h7Uv6CQB9xA2WbjZG7iOM5xFisT03o28EuEb-6IungXj-OnJTrwpM-71D56Ydi9xZ_fHA90qPfgjUDHZP', 80, 'VEG003', TRUE, TRUE, FALSE),

(2, 'Táo Envy', 'tao-envy', 'Táo nhập khẩu New Zealand, giòn ngọt', 99000, NULL, '0.5kg', 'https://lh3.googleusercontent.com/aida-public/AB6AXuDL1oD5Rm1pJzhuBoIP8cVv0Rw5LKGxaBh7fZzF7-Zf2iPG-mowIxwmZ0BjGE3aLcYyv_p6JHEID2ac0HlP2i27PJdLp-ATBRcqrMK1BT-HHTgOxzgOvjRhvWuI1NjeHWAjeMZjDhdFsJp0TpPrE8wjjXE_DRO6lb0QQI2A98xQdIrLuwgToS6MBgCKhVz6PnbN_ESFCG6ugRFsKn6Imd0jDSiXHR5lv9T0U-1i7aHt3gyaToK1SnIAVfth4Fq6QpAWvokI_HKqbA21', 30, 'FRU001', TRUE, TRUE, TRUE),

(3, 'Trứng gà thả vườn', 'trung-ga-tha-vuon', 'Trứng gà sạch tự nhiên, giàu dinh dưỡng', 129000, NULL, 'vỉ 10 trứng', 'https://lh3.googleusercontent.com/aida-public/AB6AXuBuA9DZITldWcLa8MFwbJDp5M-Synk_oaI5vkJxQ2RuPzCaxXle_I09wP2wdJui1vcu06ceBV2QT_8xkZpBzsM6wQq-MYtUm8O-s30Mf357aOaVR7yVl0nKbBW_WTjdro2ARhtM1OFYhLlBrnJLvzG4AsmowaqTICUFwfQkAjltESBR5iFenqyMWSgbvIklCcdjMTRQX7GOI5AxpCPykaZ5k_GtmT9e9SkXbfz-0jRKGnYzbgHMehlx_T4UQ4y10N4khmF2B-qTQSqi', 45, 'DAI001', TRUE, FALSE, TRUE);

-- USERS (PASSWORD: 123456)
INSERT INTO users (name, email, phone, password, membership, role) VALUES
('Lê An', 'lean@email.com', '0901234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'silver', 'customer'),
('Admin', 'admin@xanhorganic.vn', '0900000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'gold', 'admin');

-- COUPONS
INSERT INTO coupons (code, description, discount_type, discount_value, min_order_value, max_discount, usage_limit, start_date, end_date) VALUES
('WELCOME10', 'Giảm 10% cho đơn hàng đầu tiên', 'percentage', 10, 200000, 50000, 100, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
('FREESHIP', 'Miễn phí vận chuyển cho đơn từ 500k', 'fixed', 25000, 500000, NULL, NULL, NOW(), DATE_ADD(NOW(), INTERVAL 60 DAY));

-- SETTINGS
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'Xanh Organic', 'text', 'Tên website'),
('site_email', 'info@xanhorganic.vn', 'email', 'Email liên hệ'),
('site_phone', '1900123456', 'text', 'Số điện thoại'),
('smtp_host', 'smtp.gmail.com', 'text', 'SMTP Host'),
('smtp_port', '587', 'text', 'SMTP Port'),
('smtp_username', '', 'text', 'SMTP Username'),
('smtp_password', '', 'password', 'SMTP Password'),
('free_shipping_threshold', '500000', 'number', 'Miễn phí ship từ'),
('default_shipping_fee', '25000', 'number', 'Phí ship mặc định');