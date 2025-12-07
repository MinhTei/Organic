# 📚 Organic E-Commerce Platform - Toàn bộ Codebase

## 📋 Mục lục
1. [Cấu trúc Dự án](#cấu-trúc-dự-án)
2. [Công nghệ Sử dụng](#công-nghệ-sử-dụng)
3. [Các Trang Chính](#các-trang-chính)
4. [Cơ Sở Dữ Liệu](#cơ-sở-dữ-liệu)
5. [Hệ Thống Admin](#hệ-thống-admin)
6. [API & JavaScript](#api--javascript)
7. [Hàm Quan Trọng](#hàm-quan-trọng)
8. [Tính Năng Chính](#tính-năng-chính)

---

## 🏗️ Cấu trúc Dự án

```
Organic/
├── index.php                    # Trang chủ với slideshow banner
├── auth.php                     # Đăng nhập/Đăng ký
├── products.php                 # Danh sách sản phẩm với bộ lọc
├── product_detail.php           # Chi tiết sản phẩm + đánh giá
├── cart.php                     # Giỏ hàng
├── order_history.php            # Lịch sử đơn hàng
├── order_detail.php             # Chi tiết đơn hàng
├── order_success.php            # Trang xác nhận đặt hàng
├── wishlist.php                 # Danh sách sản phẩm yêu thích
├── user_info.php                # Thông tin cá nhân khách hàng
├── about.php                    # Trang về chúng tôi
├── contact.php                  # Form liên hệ
├── thanhtoan.php                # Xử lý thanh toán
├── forgot_password.php          # Quên mật khẩu
├── reset_password.php           # Đặt lại mật khẩu
├── test.php                     # Trang test
│
├── admin/                       # Thư mục Admin Dashboard
│   ├── index.php                # Trang chủ admin (redirect)
│   ├── dashboard.php            # Tổng quan (thống kê, doanh thu)
│   ├── products.php             # Quản lý sản phẩm
│   ├── product_add.php          # Thêm sản phẩm mới
│   ├── product_edit.php         # Chỉnh sửa sản phẩm
│   ├── product_import.php       # Import sản phẩm từ Excel/CSV
│   ├── download_template.php    # Tải template import
│   ├── sample_products.csv      # File mẫu 20 sản phẩm
│   ├── categories.php           # Quản lý danh mục
│   ├── orders.php               # Quản lý đơn hàng
│   ├── order_detail.php         # Chi tiết đơn hàng (admin)
│   ├── customers.php            # Quản lý khách hàng
│   ├── customer_detail.php      # Chi tiết khách hàng
│   ├── reviews.php              # Duyệt đánh giá sản phẩm
│   ├── posts.php                # Quản lý bài viết/tin tức
│   ├── role_manager.php         # Quản lý quyền người dùng
│   ├── settings.php             # Cài đặt hệ thống
│   ├── statistics.php           # Thống kê chi tiết
│   ├── export_report.php        # Xuất báo cáo
│   ├── _sidebar.php             # Sidebar chung cho admin
│   ├── image/                   # Upload ảnh admin
│   └── EXPORT_REPORT_README.md  # Hướng dẫn xuất báo cáo
│
├── includes/                    # Thư mục Include
│   ├── config.php               # Cấu hình database + hằng số
│   ├── functions.php            # Hàm chung (sản phẩm, danh mục)
│   ├── header.php               # Header layout (sticky top)
│   ├── footer.php               # Footer layout
│   ├── import_helper.php        # Hàm import Excel/CSV
│   ├── email_functions.php      # Gửi email
│   ├── wishlist_functions.php   # Hàm danh sách yêu thích
│   └── settings_helper.php      # Hàm lấy cài đặt từ database
│
├── api/                         # API Endpoints
│   ├── customer_addresses.php   # API quản lý địa chỉ
│   └── wishlist.php             # API danh sách yêu thích
│
├── css/                         # Thư mục CSS
│   ├── input.css                # CSS input (PostCSS)
│   ├── tailwind.css             # Tailwind compiled
│   ├── styles.css               # CSS custom chính
│   ├── breakpoints.css          # CSS responsive breakpoints
│   └── admin-mobile.css         # CSS mobile cho admin
│
├── js/                          # Thư mục JavaScript
│   └── scripts.js               # JavaScript chính (cart, wishlist, etc)
│
├── images/                      # Thư mục hình ảnh
│   ├── avatars/                 # Avatar người dùng
│   ├── categories/              # Icon danh mục
│   ├── logo/                    # Logo website
│   └── product/                 # Ảnh sản phẩm
│
├── vendor/                      # Composer dependencies
│   ├── phpoffice/phpspreadsheet # Thư viện đọc Excel
│   ├── maennchen/zipstream-php  # ZIP stream
│   ├── mpdf/mpdf                # PDF export
│   ├── markbaker/matrix         # Matrix operations
│   └── [... others ...]
│
├── organic_db.sql               # Database dump
├── composer.json                # PHP dependencies
├── package.json                 # Node.js dependencies
├── tailwind.config.js           # Tailwind config
├── postcss.config.js            # PostCSS config
├── README.md                    # Main README
└── IMPORT_*.md                  # Tài liệu import sản phẩm
```

---

## 🛠️ Công nghệ Sử dụng

### Backend
- **PHP 8.3+** - Ngôn ngữ chính
- **MySQL/PDO** - Database (prepared statements)
- **Composer** - PHP dependency manager
- **PHPOffice/PhpSpreadsheet** - Đọc Excel (.xlsx, .xls)
- **mPDF** - Tạo PDF

### Frontend
- **Tailwind CSS** - Styling utilities
- **JavaScript vanilla** - Không dùng framework
- **Material Symbols Outlined** - Icon library
- **Be Vietnam Pro font** - Font chữ

### Development Tools
- **PostCSS** - CSS processing
- **npm** - JavaScript package manager
- **Git** - Version control

---

## 📄 Các Trang Chính

### 1. **index.php** - Trang Chủ
- Slideshow 3 banner điều hướng
- Hiển thị sản phẩm nổi bật (featured)
- Hiển thị sản phẩm mới (is_new = 1)
- Danh sách danh mục theo grid
- Bài viết blog gần đây
- Search sản phẩm

**Tính năng:**
```php
- GET /index.php?search=keyword  → Tìm kiếm sản phẩm
- Responsive design (mobile/tablet/desktop)
- Adaptive slideshow timing
```

### 2. **auth.php** - Đăng Nhập/Đăng Ký
- 2 chế độ: login/register
- Hash password với PASSWORD_DEFAULT
- Session management
- Redirect admin → /admin/dashboard.php
- Redirect customer → /index.php

**SQL Injection Protection:**
```php
- Prepared statements cho tất cả queries
- parameterized WHERE clauses
```

### 3. **products.php** - Danh Sách Sản Phẩm
**Bộ lọc:**
- `category` - Danh mục
- `search` - Tìm kiếm
- `sort` - Sắp xếp (price_asc, price_desc, newest)
- `on_sale` - Đang giảm giá
- `is_new` - Hàng mới
- `is_organic` - Hữu cơ
- `min_price`, `max_price` - Khoảng giá
- `page` - Phân trang

**Sidebar Filter:**
- Danh mục + icon
- Khoảng giá (from-to)
- Checkboxes (sale, new, organic)

### 4. **product_detail.php** - Chi Tiết Sản Phẩm
**Hiển thị:**
- Ảnh sản phẩm lớn (1:1 aspect)
- Giá (sale vs original)
- Stock status
- Quantity selector
- Add to cart button
- Related products (cùng danh mục)
- Approved reviews

**Review System:**
```php
- POST /product_detail.php - Submit review
- INSERT INTO product_reviews (pending status)
- Display approved reviews only
```

### 5. **cart.php** - Giỏ Hàng
**AJAX Actions:**
- `action=add` - Thêm vào giỏ
- `action=update` - Cập nhật số lượng
- `action=remove` - Xóa sản phẩm
- `action=clear` - Xóa giỏ

**Tính năng:**
```php
- Stock check trước khi update
- Shipping fee tính toán ($25k default)
- Free shipping khi >= 500k
- Subtotal + shipping = Total
```

### 6. **thanhtoan.php** - Checkout
- Require login
- Xác nhận địa chỉ
- Chọn phương thức thanh toán (COD, bank transfer)
- Áp dụng mã coupon
- Tạo order
- Redirect /order_success.php

### 7. **order_history.php** - Lịch Sử Đơn Hàng
- Danh sách đơn hàng của khách (USER_ID)
- Status badge (pending, confirmed, shipping, delivered, cancelled)
- Mobile card view + Desktop table view
- Link → /order_detail.php?id=X

### 8. **wishlist.php** - Danh Sách Yêu Thích
- Require login
- Get wishlist từ database
- Display products grid
- Remove từ wishlist
- Add all to cart
- Pagination

**API:**
```php
POST /api/wishlist.php
- action=toggle (add/remove)
- product_id=X
```

### 9. **user_info.php** - Thông Tin Cá Nhân
**Tabs:**
- Profile - Tên, email, phone
- Addresses - Địa chỉ giao hàng (API)
- Change Password - Đổi mật khẩu
- Logout - Đăng xuất

---

## 🗄️ Cơ Sở Dữ Liệu

### Bảng Chính

#### 1. **users** - Người Dùng
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255),
    avatar VARCHAR(255),
    membership ENUM('bronze','silver','gold'),
    role ENUM('customer','admin','staff'),
    status ENUM('active','inactive','banned'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 2. **categories** - Danh Mục
```sql
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE,
    slug VARCHAR(100) UNIQUE,
    icon VARCHAR(255),
    description TEXT,
    parent_id INT,
    display_order INT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Danh mục có sẵn:**
- Rau củ
- Trái cây
- Trứng & Bơ Sữa
- Bánh mì & Bánh ngọt
- Thịt & Hải sản

#### 3. **products** - Sản Phẩm
```sql
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2),
    unit VARCHAR(50),
    image VARCHAR(255),
    stock INT DEFAULT 0,
    is_organic TINYINT(1) DEFAULT 0,
    is_new TINYINT(1) DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 4. **orders** - Đơn Hàng
```sql
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_code VARCHAR(50) UNIQUE,
    user_id INT,
    subtotal DECIMAL(10,2),
    shipping_fee DECIMAL(10,2),
    discount_amount DECIMAL(10,2),
    total_amount DECIMAL(10,2),
    final_amount DECIMAL(10,2),
    payment_method ENUM('cod','bank_transfer'),
    status ENUM('pending','confirmed','processing','shipping','delivered','cancelled','refunded'),
    customer_name VARCHAR(100),
    customer_email VARCHAR(100),
    customer_phone VARCHAR(20),
    shipping_address TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 5. **order_items** - Chi Tiết Đơn Hàng
```sql
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    product_name VARCHAR(255),
    unit_price DECIMAL(10,2),
    quantity INT,
    total_price DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 6. **product_reviews** - Đánh Giá Sản Phẩm
```sql
CREATE TABLE product_reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT,
    user_id INT,
    rating INT (1-5),
    comment TEXT,
    status ENUM('pending','approved','rejected'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 7. **wishlists** - Danh Sách Yêu Thích
```sql
CREATE TABLE wishlists (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (user_id, product_id)
);
```

#### 8. **blog_posts** - Bài Viết/Tin Tức
```sql
CREATE TABLE blog_posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    author_id INT,
    title VARCHAR(200),
    slug VARCHAR(200) UNIQUE,
    excerpt VARCHAR(500),
    content TEXT,
    featured_image VARCHAR(255),
    status ENUM('draft','published','archived'),
    view_count INT DEFAULT 0,
    published_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 9. **coupons** - Mã Giảm Giá
```sql
CREATE TABLE coupons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE,
    description TEXT,
    discount_type ENUM('percentage','fixed'),
    discount_value DECIMAL(10,0),
    min_order_value DECIMAL(10,0),
    max_discount DECIMAL(10,0),
    usage_limit INT,
    used_count INT DEFAULT 0,
    start_date TIMESTAMP,
    end_date TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Mã có sẵn:**
- `WELCOME10` - Giảm 10% (min: 200k)
- `FREESHIP` - Free ship (min: 500k)

#### 10. **contact_messages** - Tin Nhắn Liên Hệ
```sql
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(50),
    subject VARCHAR(255),
    message TEXT,
    status ENUM('pending','replied','archived'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 11. **activity_logs** - Nhật Ký Hoạt Động
```sql
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100),
    description TEXT,
    entity_type VARCHAR(50),
    entity_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 12. **system_settings** - Cài Đặt Hệ Thống
```sql
CREATE TABLE system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    key VARCHAR(100) UNIQUE,
    value LONGTEXT,
    type ENUM('string','number','boolean','json'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## 👨‍💼 Hệ Thống Admin

### Trang Dashboard (/admin/dashboard.php)
**Thống kê:**
- Tổng sản phẩm
- Tổng đơn hàng + doanh thu
- Tổng khách hàng
- Thống kê đơn hàng theo trạng thái
- Top 5 sản phẩm bán chạy
- 10 đơn hàng gần nhất
- Thống kê đánh giá

### Quản lý Sản phẩm (/admin/products.php)
**Tính năng:**
- Danh sách sản phẩm (tìm, lọc, phân trang)
- Lọc theo: danh mục, status (featured/new/sale/out_of_stock)
- Thêm sản phẩm mới
- Chỉnh sửa sản phẩm
- Xóa sản phẩm
- Toggle featured/new status

### Import Sản phẩm (/admin/product_import.php)
**Hỗ trợ:**
- CSV format (hoạt động ngay)
- Excel format (.xlsx, .xls) - cần PhpSpreadsheet

**Columns:**
```
Bắt buộc: Tên sản phẩm, Giá
Tùy chọn: Danh mục, Giá giảm, Đơn vị, Tồn kho, 
          Mô tả, Hữu cơ, Mới
```

**Hàm xử lý:** `includes/import_helper.php`
- `importProductsFromExcel()` - Import từ file
- `processProductRows()` - Xử lý từng hàng
- `mapHeaderColumns()` - Ánh xạ columns

### Quản lý Đơn hàng (/admin/orders.php)
**Tính năng:**
- Danh sách đơn hàng (tìm, lọc, phân trang)
- Lọc theo trạng thái
- Cập nhật trạng thái đơn
- Xem chi tiết đơn

**Trạng thái:**
- pending → confirmed → shipping → delivered
- cancelled, refunded

### Quản lý Khách hàng (/admin/customers.php)
- Danh sách khách hàng
- Xem chi tiết (profile, orders, reviews)
- Ban/unban khách
- Thống kê purchase behavior

### Quản lý Danh mục (/admin/categories.php)
- Thêm/sửa/xóa danh mục
- Upload icon
- Parent category (phân cấp)
- Display order

### Duyệt Đánh giá (/admin/reviews.php)
- Pending reviews
- Approve/reject/delete
- Hiển thị rating + comment

### Quản lý Bài Viết (/admin/posts.php)
- Create/edit/delete blog posts
- Publish/draft status
- Featured image
- SEO slug

### Cài Đặt Hệ Thống (/admin/settings.php)
**Tabs:**
- General - Site name, logo, email, phone
- Email - SMTP settings
- Shipping - Free shipping threshold, default fee
- Payment - Payment methods config

---

## 🔌 API & JavaScript

### API Endpoints

#### 1. `/api/wishlist.php` - Danh Sách Yêu Thích
```php
POST /api/wishlist.php
{
    "action": "toggle",        // add/remove
    "product_id": 1
}

Response:
{
    "success": true,
    "message": "Added to wishlist",
    "count": 5
}
```

#### 2. `/api/customer_addresses.php` - Địa Chỉ Giao Hàng
```php
POST /api/customer_addresses.php
{
    "action": "list|add|edit|delete",
    "id": 1,
    "address": "...",
    "city": "...",
    "district": "...",
    "ward": "..."
}
```

### JavaScript Functions (js/scripts.js)

#### Giỏ Hàng
```javascript
addToCart(productId, quantity=1)    // Add to cart
updateCart(productId, quantity)     // Update quantity
removeFromCart(productId)           // Remove item
clearCart()                         // Clear all items
```

#### Danh Sách Yêu Thích
```javascript
toggleFavorite(productId)           // Add/remove from wishlist
isInWishlist(productId)             // Check if in wishlist
```

#### Thông Báo
```javascript
showNotification(message, type)     // Show toast (success/error/warning)
```

#### Đơn Hàng
```javascript
changeSlide(n)                      // Hero slideshow
goToSlide(n)                        // Direct to slide
changeQty(delta)                    // Change quantity in detail page
```

---

## 🎯 Hàm Quan Trọng

### includes/config.php
```php
getConnection()                     // PDO connection (singleton)
formatPrice($price)                 // Format VND: 1000 → 1.000₫
sanitize($data)                     // XSS protection
redirect($url)                      // Header redirect + exit
```

### includes/functions.php
```php
getCategories()                     // All categories
getProducts($options)               // Products with filters/pagination
getProduct($idOrSlug)               // Single product
getFeaturedProducts($limit)         // Featured products
getLatestPosts($limit)              // Latest blog posts
getRelatedProducts($id, $catId)     // Related in same category
imageUrl($path)                     // Normalize image paths
renderProductCard($product)         // HTML product card
renderPagination($page, $total)     // HTML pagination
```

### includes/import_helper.php
```php
importProductsFromExcel($filePath, $categoryId)
processProductRows($rows, $categoryId)
mapHeaderColumns($headers)
validateProductRow($row, $rowNum)
```

### includes/wishlist_functions.php
```php
toggleWishlistItem($userId, $productId)  // Add/remove
getUserWishlist($userId, $page)          // Paginated wishlist
isInWishlist($userId, $productId)        // Check
```

### includes/settings_helper.php
```php
getSystemSetting($key, $default)   // Get setting from DB
setSystemSetting($key, $value)     // Save setting to DB
getSettingAmount($key, $default)   // Get numeric setting
```

---

## ✨ Tính Năng Chính

### 1. **E-Commerce**
- ✅ Danh sách sản phẩm với bộ lọc nhiều chiều
- ✅ Chi tiết sản phẩm + đánh giá
- ✅ Giỏ hàng (session-based)
- ✅ Checkout + tạo order
- ✅ Lịch sử đơn hàng
- ✅ Mã giảm giá (percentage/fixed)
- ✅ Free shipping threshold

### 2. **User Management**
- ✅ Đăng ký/Đăng nhập
- ✅ Xác minh email (optional)
- ✅ Reset password
- ✅ Thông tin cá nhân
- ✅ Địa chỉ giao hàng
- ✅ Danh sách yêu thích
- ✅ Lịch sử đơn hàng
- ✅ Membership levels (bronze/silver/gold)

### 3. **Admin Features**
- ✅ Dashboard analytics
- ✅ Quản lý sản phẩm (CRUD)
- ✅ Import từ Excel/CSV
- ✅ Quản lý danh mục
- ✅ Quản lý đơn hàng
- ✅ Quản lý khách hàng
- ✅ Duyệt đánh giá
- ✅ Quản lý blog/tin tức
- ✅ Cài đặt hệ thống
- ✅ Xuất báo cáo

### 4. **Security**
- ✅ SQL Injection protection (prepared statements)
- ✅ XSS protection (sanitize + htmlspecialchars)
- ✅ Password hashing (PASSWORD_DEFAULT)
- ✅ Session management
- ✅ Role-based access control
- ✅ File upload validation

### 5. **Performance**
- ✅ Database indexing
- ✅ Lazy loading images
- ✅ AJAX cart operations
- ✅ Pagination (6 items/page)
- ✅ Caching-ready structure

### 6. **Responsive Design**
- ✅ Mobile-first approach
- ✅ Tailwind CSS breakpoints
- ✅ Adaptive images
- ✅ Touch-friendly UI
- ✅ Desktop/tablet/mobile views

### 7. **SEO Optimization**
- ✅ Semantic HTML
- ✅ URL slugs
- ✅ Meta tags
- ✅ Open Graph ready
- ✅ Structured data ready

---

## 📝 File Cấu Hình

### tailwind.config.js
```javascript
theme: {
    colors: {
        primary: "#b6e633",           // Xanh lá
        primary-dark: "#9acc2a",
        background-light: "#f7f8f6",
        text-light: "#161811",
        card-light: "#ffffff",
        border-light: "#e3e5dc",
        muted-light: "#7e8863"
    }
}
```

### package.json
```json
{
    "build:css": "tailwindcss -i ./css/input.css -o ./css/tailwind.css",
    "watch:css": "tailwindcss -i ./css/input.css -o ./css/tailwind.css --watch"
}
```

### composer.json
```json
{
    "require": {
        "phpoffice/phpspreadsheet": "^1.29",
        "mpdf/mpdf": "^8.1"
    }
}
```

---

## 🚀 Hướng Dẫn Sử Dụng

### 1. Setup Ban Đầu
```bash
# 1. Copy file sang WAMP
# 2. Tạo database organic_db
# 3. Import organic_db.sql
# 4. Cập nhật config.php:
define('DB_HOST', 'localhost');
define('DB_NAME', 'organic_db');
define('SITE_URL', 'http://localhost/organic');

# 5. Install PHP dependencies
composer install

# 6. Install Node dependencies (optional)
npm install

# 7. Build CSS (if changed)
npm run build:css
```

### 2. Admin Login
- Email: admin@example.com (hoặc tạo user role=admin)
- Password: hashedPassword
- URL: /admin/dashboard.php

### 3. Import Sản Phẩm
- Vào /admin/product_import.php
- Tải template hoặc tạo file CSV
- Upload và import

### 4. Import từ Excel (Optional)
```bash
composer require phpoffice/phpspreadsheet
```

---

## 🔒 Bảo Mật

### SQL Injection Prevention
```php
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
```

### XSS Prevention
```php
sanitize($data)                     // Strip tags + htmlspecialchars
htmlspecialchars($text)             // Escape HTML
htmlspecialchars_decode($text)      // Decode entities
```

### Password Security
```php
password_hash($password, PASSWORD_DEFAULT)
password_verify($input, $hash)
```

### Session Security
```php
session_start()
$_SESSION['user_id']                // Check before actions
```

---

## 📊 Database Relationships

```
users
  ├── orders (user_id)
  │   └── order_items (order_id)
  ├── product_reviews (user_id)
  ├── wishlists (user_id)
  │   └── products
  └── customer_addresses (user_id)

categories
  └── products (category_id)
      ├── product_reviews (product_id)
      └── order_items (product_id)

blog_posts
  └── users (author_id)

coupons
  └── orders (coupon_id) [optional]
```

---

## 📞 Support & Documentation

- **IMPORT_README.md** - Tóm tắt import
- **IMPORT_SETUP.md** - Hướng dẫn chi tiết
- **IMPORT_QUICKSTART.md** - Hướng dẫn nhanh
- **IMPORT_GUIDE.md** - Hướng dẫn rất chi tiết
- **IMPORT_FAQ.md** - Câu hỏi thường gặp
- **IMPORT_CHECKLIST.md** - Danh sách kiểm tra

---

## 🎓 Kết Luận

**Organic E-Commerce Platform** là một nền tảng bán hàng trực tuyến hoàn chỉnh với:
- ✅ Frontend responsive cho khách hàng
- ✅ Backend admin đầy đủ tính năng
- ✅ Database chuẩn mực với indexing
- ✅ Bảo mật cao (SQL injection, XSS safe)
- ✅ Có thể mở rộng dễ dàng

Toàn bộ code được viết bằng PHP vanilla, không dùng framework, dễ maintain và deploy.

---

**Generated:** 2025-12-07
**Version:** 1.0.0
**Status:** ✅ Production Ready
