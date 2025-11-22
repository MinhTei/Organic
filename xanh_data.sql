-- Tạo database
CREATE DATABASE IF NOT EXISTS organic_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE organic_db;

-- Bảng danh mục sản phẩm
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT 'grass',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng sản phẩm
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,0) NOT NULL,
    sale_price DECIMAL(10,0) DEFAULT NULL,
    unit VARCHAR(50) DEFAULT 'kg',
    image VARCHAR(255),
    stock INT DEFAULT 0,
    is_organic BOOLEAN DEFAULT TRUE,
    is_new BOOLEAN DEFAULT FALSE,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Bảng người dùng
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255),
    membership ENUM('bronze','silver','gold') DEFAULT 'bronze',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng địa chỉ
CREATE TABLE addresses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    is_default BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng đơn hàng
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

-- Bảng chi tiết đơn hàng
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,0) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Dữ liệu mẫu: Danh mục
INSERT INTO categories (name, slug, icon) VALUES
('Rau củ', 'rau-cu', 'grass'),
('Trái cây', 'trai-cay', 'nutrition'),
('Trứng & Bơ sữa', 'trung-bo-sua', 'egg'),
('Bánh mì & Bánh ngọt', 'banh-mi', 'bakery_dining'),
('Thịt & Hải sản', 'thit-hai-san', 'lunch_dining');

-- Dữ liệu mẫu: Sản phẩm
INSERT INTO products (category_id, name, slug, description, price, sale_price, unit, image, stock, is_organic, is_new, is_featured) VALUES
(1, 'Cà rốt hữu cơ', 'ca-rot-huu-co', 'Cà rốt tươi ngon từ Đà Lạt', 35000, NULL, '500g', 'https://lh3.googleusercontent.com/aida-public/AB6AXuD8RQdsr0IximrJ-Jrgorj_zPyyroI6Dfs6HIvNjkaBWujWCp1k403nRwFWbA-978540FUkW06k7__NSW5KuH6-A9MTyRw70Hu__Rk8XU6KqzblZfeNan9A213sE7dpE2T5qxiFCsFMLtglz5MaeGIXm6qVO2dINWCKpDp7iuJkAYXmdYaIipZ14OGkBroPgSNBkwK29zgtnRvMSz99jJ_hnUbrmXStVHe1tqaggBMdSstRCxKgwzx1aZ-eDfdBt9FRLN0tbUVJIKxb', 100, TRUE, FALSE, TRUE),
(1, 'Bông cải xanh', 'bong-cai-xanh', 'Bông cải xanh giàu vitamin', 33000, 28000, 'cái', 'https://lh3.googleusercontent.com/aida-public/AB6AXuCi-qYnuvm_o9cLZcWAgaHjDNi-FfvsUtsFHmy7roZruCCjnvI3SYGQ31RjCJYsfXMObJ69_ikZtVNmFxE9E2rquQouggfdgVQMePbFD2ZP_B-M3nUzGxMatNmdNNTn0ka-HF2FGTfq8red1tEHAVRaej_6rwaJY67ypfBCDdHcmh8f1LUhVGATdrJ0u0-kiUzQ58WIYJMyWsBPMC7uJkcEy5yP0CkKNTpJcZSSXFz3lU__lwfhovuOg3QHvYew7l8B9MG3Kgjprb0W', 50, TRUE, FALSE, TRUE),
(1, 'Cà chua bi', 'ca-chua-bi', 'Cà chua bi ngọt tự nhiên', 25000, NULL, 'hộp 250g', 'https://lh3.googleusercontent.com/aida-public/AB6AXuAcW2HNJaXm2Hngpw8wlfQOnYzGj6Vk2MZZwybZWW9mnoGm4PVs6n9ky8EfPy6Q_uLgh_NfMPOBlhqoUMNcNgXK0OFojINJ9VgYGh1zirkereSiTYzFhT6qwnkWyNu5jwrtFCrDNXS0D3IPN2HpB7xyHXCMBIG-zNJMOYxtPXe4fPvvAa3n_tn1Zcdq919h7Uv6CQB9xA2WbjZG7iOM5xFisT03o28EuEb-6IungXj-OnJTrwpM-71D56Ydi9xZ_fHA90qPfgjUDHZP', 80, TRUE, TRUE, FALSE),
(1, 'Xà lách Romaine', 'xa-lach-romaine', 'Xà lách tươi giòn', 22000, NULL, 'bó', 'https://lh3.googleusercontent.com/aida-public/AB6AXuDipCSuVQ1rsWO4VKep0oIgAITPHsX37S6UNDHuijZ_rlve8-3tciA812xLsgJJe6_U_QBbyGiy6t16NjY1WbKsZmP-Un64UyAXKQLgqs66jS24XuOSyvgByemasHZGH_BwEoQg4WSafG8Bew8S6bkwywIP9nFZSP6a_RGEJgZSID-1WY1VyQQ5wxg-Kzg70JVxPdoSxDLAVCnHmPkKeIFFI8c9mwDtAGo00jtV_Gns0kOzi95QJhhwvaXIhk-x2N__gXTiTLnsRG1q', 60, TRUE, FALSE, FALSE),
(1, 'Ớt chuông xanh', 'ot-chuong-xanh', 'Ớt chuông giòn ngọt', 60000, NULL, 'kg', 'https://lh3.googleusercontent.com/aida-public/AB6AXuAh-B37016MLaDfe16JPune1RvSgrgW4SE6DlPsZCkm7qej0wRTRfHAZPnslU_Bh_99wzaeD2_i-vtFaGx5RY6AGQIlT30pO_Scsknysn-qtPQl7YV81s-d2e-fD88F9GXsB7D0VJmIxbvPKjH1nO3FvA4TFNqCviVNtID4AHue15cvqoyBXdtKit4VSBR5is-ibxqAcUFaXZjI9CBGAwSgdXsyXf6Wa5AkbILB6x2GuwpEzRzeKzsxgLtISLonvzPJEdMOpTJFRngS', 40, TRUE, FALSE, FALSE),
(1, 'Cà tím', 'ca-tim', 'Cà tím tươi ngon', 30000, NULL, 'kg', 'https://lh3.googleusercontent.com/aida-public/AB6AXuAbQ7Q9YfT8GidplHLKrUTSHfAJGMbHjdcWFD13xMUtq360grjaQz7GmOsnI2je5NijG9bBF5BH7YVk816HdwHtJKlqAX7OGBxJvQS6IDId5OgxtYvGTUpUX1L3FMWpINx7YOZwYIfmQ5P_kf0-qsQj9B3bZf0NaxqT41pLK4SnarlVdKWelNUkXEyQJzqd8Ee-Eb7zZE1XSI8JHxpcJ0uJM95yg6DMOhOIMRxnvVb4QHyx8QhyybkKprdSqmanUuBi-IKTK4nnBeUE', 70, TRUE, FALSE, FALSE),
(1, 'Cà chua hữu cơ', 'ca-chua-huu-co', 'Cà chua Đà Lạt chín đỏ', 55000, NULL, '500g', 'https://lh3.googleusercontent.com/aida-public/AB6AXuBtfqum7cUdnpkiBziy4_WyFot6zjfgOKciAGklNBII27EmhDWhh980xb-0MUDK55SktK15qzEIFgJyLA0Tc1CBhaorhJR1OJ6slqdVq9de1tfrjvdSRbhalQp51saWmcZpY2wY-7TbSiUtjj0SpJ3Ybpgk_vfGmBVXTtZRDIaTdvWPg0KNSECYtTL7OfOrGEJCJh5NSDUV8-gGLDgfY-q7fP8xKk3kanOgcOZwfcnE-TN9V7ww8uy0dWBCziuDwgV8tL8E9ZlJgYSW', 90, TRUE, TRUE, TRUE),
(1, 'Dưa leo hữu cơ', 'dua-leo-huu-co', 'Dưa leo giòn mát', 30000, NULL, 'kg', 'https://lh3.googleusercontent.com/aida-public/AB6AXuCt_Efz-7BAipjyE3oiYLYvwil-NQ_hB3MwlpgoeJpU6zTE3KcKi7Lrp4UblbbAd3TvnZj0pOP0C4S6Suh7j418zrTZLOvAgXA8GSKbcTQkQlnvHhHONKdR7RE0LWMlL0Tuy9Yku-_BiccyRuLWgMac6WqpcJkbf6-HiAebgzWCSseOfEo_EAmUr4PaMXg5Vk6GLe_k8xwHeVNpoAiVjge0mXyy3d2uSMNaEieYbVdlCjx5PCdCGyXAynBcYb2JPEDTvXKeIYKCnPn_', 55, TRUE, FALSE, FALSE),
(2, 'Táo Envy', 'tao-envy', 'Táo nhập khẩu New Zealand', 99000, NULL, '0.5kg', 'https://lh3.googleusercontent.com/aida-public/AB6AXuDL1oD5Rm1pJzhuBoIP8cVv0Rw5LKGxaBh7fZzF7-Zf2iPG-mowIxwmZ0BjGE3aLcYyv_p6JHEID2ac0HlP2i27PJdLp-ATBRcqrMK1BT-HHTgOxzgOvjRhvWuI1NjeHWAjeMZjDhdFsJp0TpPrE8wjjXE_DRO6lb0QQI2A98xQdIrLuwgToS6MBgCKhVz6PnbN_ESFCG6ugRFsKn6Imd0jDSiXHR5lv9T0U-1i7aHt3gyaToK1SnIAVfth4Fq6QpAWvokI_HKqbA21', 30, TRUE, TRUE, TRUE),
(3, 'Trứng gà thả vườn', 'trung-ga-tha-vuon', 'Trứng gà sạch tự nhiên', 129000, NULL, 'vỉ 10 trứng', 'https://lh3.googleusercontent.com/aida-public/AB6AXuBuA9DZITldWcLa8MFwbJDp5M-Synk_oaI5vkJxQ2RuPzCaxXle_I09wP2wdJui1vcu06ceBV2QT_8xkZpBzsM6wQq-MYtUm8O-s30Mf357aOaVR7yVl0nKbBW_WTjdro2ARhtM1OFYhLlBrnJLvzG4AsmowaqTICUFwfQkAjltESBR5iFenqyMWSgbvIklCcdjMTRQX7GOI5AxpCPykaZ5k_GtmT9e9SkXbfz-0jRKGnYzbgHMehlx_T4UQ4y10N4khmF2B-qTQSqi', 45, TRUE, FALSE, TRUE),
(4, 'Bánh mì nguyên cám', 'banh-mi-nguyen-cam', 'Bánh mì tốt cho sức khỏe', 159000, NULL, 'ổ', 'https://lh3.googleusercontent.com/aida-public/AB6AXuCDXonP1XXwFIVPMxeexkAYPIWND9PkSLuVJSuvJHT-eNqoMLeeEvdQAtpjfoVHKjwwVaTjsW0dVV76_UastoOJW6JTRdNEMalCUzPunFeidE5LU5urq54oC9tYzhwaMi9qppiR56bXAEFVtAESe0GKwmgSP2yjSAduOnWdKBfr8SiHF1R_zKPapaF35tluFVnLOC_9RcIN-4nnJPC1GVTw9ENvdVC4VrYqVRT-oNEJ9Nd_bN7SP9QvGFYd__tfwzq0RE5D4tTNfxaz', 25, TRUE, TRUE, TRUE);

-- User mẫu (password: 123456)
INSERT INTO users (name, email, phone, password, membership) VALUES
('Lê An', 'lean@email.com', '0901234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'silver');