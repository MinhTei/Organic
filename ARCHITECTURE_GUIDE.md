# 🎯 Organic Platform - Detailed Architecture & API Reference

## 📖 Mục Lục
1. [Request/Response Flow](#requestresponse-flow)
2. [Frontend Architecture](#frontend-architecture)
3. [Backend Architecture](#backend-architecture)
4. [Database Schema Details](#database-schema-details)
5. [Security Model](#security-model)
6. [Performance Optimization](#performance-optimization)
7. [Common Use Cases](#common-use-cases)

---

## 🔄 Request/Response Flow

### 1. **Product Listing Flow**
```
GET /products.php?category=1&sort=price_asc&page=2
    ↓
products.php:
    - Parse query params
    - Call getProducts(['category_id' => 1, 'sort' => 'price_asc', 'page' => 2])
    ↓
includes/functions.php:getProducts():
    - Build WHERE clause with conditions
    - Count total rows
    - LIMIT offset, limit
    - JOIN with categories table
    - Return ['products' => [...], 'total' => 150, 'pages' => 25]
    ↓
Render HTML:
    - Display products grid
    - Pagination links
    - Sidebar filters
    ↓
Response: HTML page
```

### 2. **Add to Cart Flow**
```
JavaScript: addToCart(5)
    ↓
fetch('cart.php', {
    method: 'POST',
    body: 'action=add&product_id=5&quantity=1'
})
    ↓
cart.php:
    - Check $_SESSION['cart'] exists
    - If action='add':
        $_SESSION['cart'][5] = ($_SESSION['cart'][5] ?? 0) + 1
    - Return JSON: {success: true, cart_count: 3}
    ↓
JavaScript:
    - Update cart count in header
    - Show notification
    - Redirect to cart.php (optional)
```

### 3. **Checkout & Order Creation**
```
POST /thanhtoan.php
    ↓
thanhtoan.php:
    - Validate user logged in
    - Check shipping address
    - Validate cart items stock
    - Create order in orders table
    - Insert order_items
    - Clear $_SESSION['cart']
    - Send confirmation email
    ↓
redirect('/order_success.php?id=123')
    ↓
Response: Success page with order number
```

### 4. **Admin Product Import**
```
POST /admin/product_import.php (multipart/form-data)
    ↓
product_import.php:
    - Move uploaded file to temp
    - Call importProductsFromExcel($tmpFile)
    ↓
import_helper.php:
    - Detect file type (csv/xlsx/xls)
    - Parse rows
    - Map headers
    - Validate each row
    - INSERT into products table
    - Return result with errors/warnings
    ↓
Response: JSON {success: 5, errors: [...], warnings: [...]}
```

---

## 🎨 Frontend Architecture

### 1. **Layout Structure**
```
includes/header.php
├── Meta tags
├── CSS includes
├── Navigation bar
│   ├── Logo
│   ├── Search box
│   ├── Category menu
│   ├── Cart icon (with count)
│   ├── Wishlist icon
│   └── User menu
└── Mobile menu button

[PAGE CONTENT]

includes/footer.php
├── About section
├── Links
├── Contact info
├── Newsletter signup
└── Copyright
```

### 2. **Responsive Breakpoints**

**Mobile (< 768px):**
- Single column layout
- Full-width buttons
- Hamburger menu
- Bottom navigation

**Tablet (768px - 1024px):**
- 2-3 column layout
- Sidebar filter
- Grid products

**Desktop (>= 1024px):**
- Full sidebar
- 4+ column grid
- Sticky header

### 3. **CSS Architecture**

**Tailwind CSS** - Utility-first approach
```html
<div class="flex items-center gap-4 p-6 rounded-lg bg-white border border-gray-200">
    <img class="w-16 h-16 rounded-full object-cover" src="...">
    <div>
        <h3 class="text-lg font-bold text-gray-900">Product Name</h3>
        <p class="text-sm text-gray-600">Product description</p>
    </div>
</div>
```

**Custom CSS** (`css/styles.css`)
- Product card styles
- Button variants
- Color variables
- Animations

### 4. **JavaScript Event Handling**

#### Cart Operations
```javascript
// Click handler in header
document.querySelector('.cart-icon').addEventListener('click', () => {
    window.location.href = '/cart.php';
});

// Add to cart (from product page)
document.querySelector('.btn-add-cart').addEventListener('click', async (e) => {
    const qty = document.querySelector('#quantity').value;
    const productId = new URLSearchParams(location.search).get('id');
    
    const response = await fetch('/cart.php', {
        method: 'POST',
        body: new FormData(form)
    });
    
    const result = await response.json();
    if (result.success) {
        showNotification('Added to cart!', 'success');
        updateCartCount(result.cart_count);
    }
});
```

#### Form Submission
```javascript
// Search form
document.querySelector('.search-form').addEventListener('submit', (e) => {
    const keyword = e.target.querySelector('input[name="search"]').value;
    window.location.href = `/products.php?search=${encodeURIComponent(keyword)}`;
});
```

---

## 🏗️ Backend Architecture

### 1. **Session Management**

**Session Lifecycle:**
```php
// On login (auth.php)
session_start();
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role'] = $user['role'];    // 'customer' or 'admin'
$_SESSION['user_membership'] = $user['membership'];

// Anywhere in the app
if (!isset($_SESSION['user_id'])) {
    redirect('/auth.php');  // Require login
}

if ($_SESSION['user_role'] !== 'admin') {
    redirect('/auth.php');  // Require admin
}

// On logout
session_destroy();
redirect('/index.php');
```

### 2. **Database Connection**

**Connection Singleton:**
```php
function getConnection() {
    static $conn = null;
    if ($conn === null) {
        try {
            $conn = new PDO(
                "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Connection error: " . $e->getMessage());
        }
    }
    return $conn;
}
```

**Benefits:**
- Single connection for entire request
- Automatic statement preparation
- Named parameters support
- Exception error mode

### 3. **Error Handling**

**Pattern: Try-Catch**
```php
try {
    $stmt = $conn->prepare("INSERT INTO orders ...");
    $stmt->execute($params);
    $success = "Order created successfully!";
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    error_log($e);  // Log for debugging
}
```

### 4. **Helper Functions Pattern**

**Consistent Pattern:**
```php
/**
 * Get products with pagination and filters
 * @param array $options  [page, category_id, search, sort, limit]
 * @return array ['products' => [...], 'total' => int, 'pages' => int]
 */
function getProducts($options = []) {
    // 1. Extract options with defaults
    $page = $options['page'] ?? 1;
    $limit = $options['limit'] ?? ITEMS_PER_PAGE;
    $offset = ($page - 1) * $limit;
    
    // 2. Build WHERE conditions
    $where = ["1=1"];
    $params = [];
    
    if (!empty($options['category_id'])) {
        $where[] = "p.category_id = ?";
        $params[] = $options['category_id'];
    }
    
    // 3. Execute count query
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE " . implode(" AND ", $where));
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();
    
    // 4. Execute data query
    $stmt = $conn->prepare("SELECT * FROM products WHERE " . implode(" AND ", $where) . " LIMIT ? OFFSET ?");
    $params[] = $limit;
    $params[] = $offset;
    $stmt->execute($params);
    
    // 5. Return structured result
    return [
        'products' => $stmt->fetchAll(),
        'total' => $total,
        'pages' => ceil($total / $limit),
        'current_page' => $page
    ];
}
```

---

## 🗄️ Database Schema Details

### 1. **Query Examples**

**Get product with category:**
```sql
SELECT p.*, c.name as category_name
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
WHERE p.id = 1;
```

**Get recent orders with customer info:**
```sql
SELECT o.id, o.order_code, o.total_amount, 
       u.name as customer_name, u.email
FROM orders o
LEFT JOIN users u ON o.user_id = u.id
ORDER BY o.created_at DESC
LIMIT 10;
```

**Get top-selling products:**
```sql
SELECT p.id, p.name, COUNT(oi.id) as sold_count
FROM products p
LEFT JOIN order_items oi ON p.id = oi.product_id
GROUP BY p.id
ORDER BY sold_count DESC
LIMIT 5;
```

### 2. **Indexing Strategy**

**Primary Keys:**
- All tables have `id` PRIMARY KEY AUTO_INCREMENT

**Unique Indexes:**
- `users(email)` - Fast login lookup
- `products(slug)` - URL-friendly lookup
- `categories(slug)` - URL-friendly lookup
- `coupons(code)` - Coupon validation
- `blog_posts(slug)` - Post lookup

**Foreign Key Indexes:**
- `products(category_id)` - Filter by category
- `orders(user_id)` - Get user orders
- `order_items(order_id)` - Get order items
- `wishlists(user_id, product_id)` - UNIQUE constraint

### 3. **Data Types**

```sql
-- Text Data
name VARCHAR(255)               -- Short text
description TEXT                -- Long text
email VARCHAR(100) UNIQUE       -- Indexed email

-- Numeric Data
price DECIMAL(10,2)             -- Money (8 digits + 2 decimals)
stock INT                       -- Integer count
discount_percentage DECIMAL(5,2) -- 0-100 with decimals

-- Status Enums
status ENUM('draft','published','archived')
role ENUM('customer','admin','staff')

-- Boolean Flags
is_organic TINYINT(1)           -- 0 or 1
is_featured TINYINT(1)

-- Timestamps
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

---

## 🔐 Security Model

### 1. **Input Validation & Sanitization**

```php
// Helper function
function sanitize($data) {
    if ($data === null) return '';
    if (!is_string($data)) $data = (string)$data;
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Usage in forms
$email = sanitize($_POST['email']);           // Remove HTML/JS
$price = (float)$_POST['price'];              // Type cast
$quantity = max(1, (int)$_POST['quantity']); // Validate range

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Invalid email format';
}

// Validate URL
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    $error = 'Invalid URL';
}
```

### 2. **SQL Injection Prevention**

**❌ UNSAFE:**
```php
$result = $conn->query("SELECT * FROM users WHERE email = '$email'");
```

**✅ SAFE - Prepared Statements:**
```php
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$result = $stmt->fetch();

// OR with named parameters
$stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute([':email' => $email]);
$result = $stmt->fetch();
```

### 3. **XSS Prevention**

**Output Escaping:**
```php
// In HTML
<h1><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h1>

// Or use helper
<h1><?= sanitize($product['name']) ?></h1>

// URL context
<a href="<?= urlencode($slug) ?>">Link</a>

// JSON context
<script>
var product = <?= json_encode($product) ?>;
</script>
```

### 4. **Password Security**

```php
// Hashing (on registration/password change)
$hashed = password_hash($password, PASSWORD_DEFAULT);
// OUTPUT: $2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86AGR0dCnm

// Verification (on login)
if (password_verify($password, $hashed)) {
    // Password is correct
}
```

### 5. **CSRF Protection** (Optional)

```php
// Generate token
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// In form
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
</form>

// Verify on submit
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    die('CSRF token mismatch');
}
```

### 6. **File Upload Security**

```php
if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $error = 'Upload failed';
} else {
    $info = getimagesize($_FILES['image']['tmp_name']);
    if ($info === false) {
        $error = 'Invalid image';
    }
    
    if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
        $error = 'File too large (max 5MB)';
    }
    
    if (!in_array($info['mime'], ['image/jpeg', 'image/png', 'image/webp'])) {
        $error = 'Invalid file type';
    }
    
    // Generate unique name to prevent overwrites
    $filename = time() . '_' . basename($_FILES['image']['name']);
    move_uploaded_file($_FILES['image']['tmp_name'], "uploads/$filename");
}
```

---

## ⚡ Performance Optimization

### 1. **Database Optimization**

```php
// ❌ N+1 Problem
foreach ($orders as $order) {
    $stmt = $conn->query("SELECT * FROM users WHERE id = {$order['user_id']}");  // Loop query!
    $user = $stmt->fetch();
}

// ✅ JOIN Query
$stmt = $conn->query("
    SELECT o.*, u.name, u.email
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
");
$orders = $stmt->fetchAll();
```

### 2. **Image Optimization**

```php
// Use imageUrl() helper
function imageUrl($path) {
    if (empty($path)) {
        return SITE_URL . '/images/placeholder.png';
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;  // Already full URL
    }
    return SITE_URL . '/' . ltrim($path, '/');
}

// In HTML - use responsive srcset
<img src="<?= imageUrl($product['image']) ?>"
     srcset="<?= imageUrl($product['image_small']) ?> 400w,
             <?= imageUrl($product['image_large']) ?> 800w"
     sizes="(max-width: 600px) 100vw, 50vw"
     alt="Product">
```

### 3. **CSS & JS Loading**

```html
<!-- Critical CSS (inline) -->
<style>
    /* Hero image, critical layout */
</style>

<!-- Defer non-critical CSS -->
<link rel="preload" href="./css/styles.css" as="style">
<link rel="stylesheet" href="./css/styles.css">

<!-- Defer JavaScript -->
<script defer src="./js/scripts.js"></script>

<!-- Async for analytics only -->
<script async src="analytics.js"></script>
```

### 4. **Caching Strategy**

```php
// Browser caching
header('Cache-Control: max-age=3600, public');  // 1 hour
header('Expires: ' . gmdate('r', time() + 3600));

// Disable caching for dynamic content
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// ETag for conditional requests
$etag = md5_file($filename);
header('ETag: "' . $etag . '"');
if ($_SERVER['HTTP_IF_NONE_MATCH'] === $etag) {
    header('HTTP/1.1 304 Not Modified');
    exit;
}
```

---

## 🎯 Common Use Cases

### 1. **Add New Feature: Review Products**

**Files to modify:**
```
1. organic_db.sql
   - CREATE TABLE product_reviews (...)

2. admin/reviews.php
   - List pending reviews
   - Approve/reject reviews

3. product_detail.php
   - Display approved reviews
   - Show review form for logged-in users

4. includes/functions.php
   - Add getProductReviews() function

5. js/scripts.js
   - Add submitReview() function
```

### 2. **Add New Filter: Price Range**

**In products.php:**
```php
$minPrice = $_GET['min_price'] ?? null;
$maxPrice = $_GET['max_price'] ?? null;

if ($minPrice) {
    $where[] = "COALESCE(p.sale_price, p.price) >= ?";
    $params[] = $minPrice;
}
if ($maxPrice) {
    $where[] = "COALESCE(p.sale_price, p.price) <= ?";
    $params[] = $maxPrice;
}
```

### 3. **Integrate Payment Gateway**

**In thanhtoan.php:**
```php
if ($_POST['payment_method'] === 'paypal') {
    // Redirect to PayPal
    $paypalUrl = 'https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=...';
    redirect($paypalUrl);
} elseif ($_POST['payment_method'] === 'stripe') {
    // Use Stripe API
    require_once 'vendor/autoload.php';
    \Stripe\Stripe::setApiKey('sk_...');
    $session = \Stripe\Checkout\Session::create([...]);
    redirect($session->url);
}
```

### 4. **Add Email Notifications**

**In includes/email_functions.php:**
```php
function sendOrderEmail($email, $order) {
    $subject = "Order Confirmation #{$order['id']}";
    $message = "...HTML email...";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: info@xanhorganic.vn\r\n";
    
    mail($email, $subject, $message, $headers);
}
```

---

## 📋 Checklist for Production Deployment

- [ ] Update database credentials in `includes/config.php`
- [ ] Set `SITE_URL` to production domain
- [ ] Update `.htaccess` for URL rewriting (if using Apache)
- [ ] Enable HTTPS/SSL certificate
- [ ] Set up automated database backups
- [ ] Configure error logging (not displaying errors)
- [ ] Set `display_errors = Off` in php.ini
- [ ] Implement rate limiting for API endpoints
- [ ] Add CAPTCHA to contact form
- [ ] Configure email server for notifications
- [ ] Test all payment methods
- [ ] Optimize database indexes
- [ ] Run security audit
- [ ] Set up monitoring/alerting

---

**Document Created:** 2025-12-07
**Last Updated:** 2025-12-07
