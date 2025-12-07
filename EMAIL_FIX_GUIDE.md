# 🔧 FIX: Lưu Email Giao Hàng Từ Form Đặt Hàng

## 📋 Vấn Đề Gốc

### Trước Fix:
- ❌ Form "Giao đến địa chỉ khác" có input email
- ❌ Khách nhập email khác → **Email KHÔNG được lưu** vào database
- ❌ Hệ thống dùng email của tài khoản thay vì email khách nhập
- ❌ Email giao hàng sai: Dùng email đăng ký, không phải email người nhận

### Sau Fix:
- ✅ Thêm cột `shipping_email` vào bảng `orders`
- ✅ Lưu email khách nhập từ form
- ✅ Admin có thể xem email giao hàng thực tế
- ✅ Thông tin giao hàng đầy đủ và chính xác

---

## 🔄 Thay Đổi Chi Tiết

### 1️⃣ **Bảng Database: orders**

**Thêm cột mới:**
```sql
ALTER TABLE `orders` ADD COLUMN `shipping_email` varchar(100) DEFAULT NULL AFTER `shipping_phone`;
```

**Vị trí:** Sau cột `shipping_phone`

**Kiểu dữ liệu:** `varchar(100)` - Email giao hàng (có thể khác email tài khoản)

---

### 2️⃣ **File: thanhtoan.php**

**Thay đổi:**
- Thêm `:shipping_email` vào INSERT query
- Thêm tham số `':shipping_email' => $email` vào execute()

**Code trước:**
```php
$sql = "INSERT INTO orders (
    ...
    shipping_name, shipping_phone, shipping_address, shipping_ward, 
    ...
) VALUES (
    ...
    :shipping_name, :shipping_phone, :shipping_address, :shipping_ward,
    ...
)";

$stmt->execute([
    ...
    ':shipping_name' => $name,
    ':shipping_phone' => $phone,
    ':shipping_address' => $address,
    ...
]);
```

**Code sau:**
```php
$sql = "INSERT INTO orders (
    ...
    shipping_name, shipping_phone, shipping_email, shipping_address, shipping_ward, 
    ...
) VALUES (
    ...
    :shipping_name, :shipping_phone, :shipping_email, :shipping_address, :shipping_ward,
    ...
)";

$stmt->execute([
    ...
    ':shipping_name' => $name,
    ':shipping_phone' => $phone,
    ':shipping_email' => $email,  // ✅ THÊM
    ':shipping_address' => $address,
    ...
]);
```

---

### 3️⃣ **File: order_detail.php** (Khách hàng)

**Thêm hiển thị email:**
```php
<div style="display: grid; grid-template-columns: clamp(80px, 20vw, 150px) 1fr; gap: clamp(0.75rem, 1.5vw, 1rem); font-size: clamp(0.85rem, 1.8vw, 0.95rem);">
    <span style="color: var(--muted-light);">Email:</span>
    <span><?= htmlspecialchars($order['shipping_email'] ?? '') ?></span>
</div>
```

**Vị trí:** Sau "Số điện thoại" trong phần "Thông tin giao hàng"

---

### 4️⃣ **File: admin/order_detail.php** (Admin)

**Thêm hiển thị email:**
```php
<p><span class="text-gray-500">Email:</span> <span class="font-medium"><?= sanitize($order['shipping_email'] ?? '') ?></span></p>
```

**Vị trí:** Sau "Điện thoại" trong phần "Địa chỉ giao hàng"

---

## 📊 Luồng Dữ Liệu

### Trước Fix:
```
Form Input: Email (khác email tài khoản)
    ↓
Xử lý PHP: $email = sanitize($_POST['email'] ?? '');
    ↓
Database: KHÔNG LƯU ❌
    ↓
Admin view: Không thấy email giao hàng
```

### Sau Fix:
```
Form Input: Email (khác email tài khoản)
    ↓
Xử lý PHP: $email = sanitize($_POST['email'] ?? '');
    ↓
Database: LƯU vào shipping_email ✅
    ↓
Admin view: Thấy email giao hàng chính xác ✅
```

---

## 🧪 Cách Test

### Test 1: Cập nhật Database
```sql
-- 1. Chạy migration
ALTER TABLE `orders` ADD COLUMN `shipping_email` varchar(100) DEFAULT NULL AFTER `shipping_phone`;

-- 2. Verify
SHOW COLUMNS FROM orders WHERE Field = 'shipping_email';
-- Kết quả: Phải thấy column 'shipping_email'
```

### Test 2: Đặt Hàng Với Email Khác
```
1. Đăng nhập khách hàng
   - Email tài khoản: user@gmail.com

2. Vào sản phẩm → Thêm vào giỏ

3. Ấn "Thanh toán"

4. Chọn "Giao đến địa chỉ khác"

5. Nhập form:
   - Họ tên: Hiếu Toàn
   - Số điện thoại: +84966330649
   - Email: buiminhtai97@gmail.com (khác với user@gmail.com)
   - Địa chỉ: 65/13A, Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn
   - Thành phố: TP. Hồ Chí Minh

6. Ấn "Đặt hàng"

7. ✅ Đơn hàng thành công
```

### Test 3: Kiểm Tra Database
```sql
SELECT id, order_code, shipping_name, shipping_phone, shipping_email FROM orders WHERE id = (Số ID vừa đặt);

Kết quả mong muốn:
- shipping_email: buiminhtai97@gmail.com ✅ (Email khách nhập, không phải email tài khoản)
```

### Test 4: Kiểm Tra Chi Tiết Đơn (Khách)
```
1. Khách vào "Lịch sử đơn hàng"
2. Click vào đơn vừa đặt
3. Phần "Thông tin giao hàng"
4. ✅ Thấy Email: buiminhtai97@gmail.com
```

### Test 5: Kiểm Tra Admin
```
1. Admin login
2. Quản lý Đơn hàng
3. Click "Chi tiết" vào đơn vừa đặt
4. Phần "Địa chỉ giao hàng"
5. ✅ Thấy Email: buiminhtai97@gmail.com
```

---

## 📁 File Thay Đổi

| File | Thay Đổi |
|------|---------|
| **organic_db.sql** | Thêm cột `shipping_email` |
| **thanhtoan.php** | Lưu email vào database |
| **order_detail.php** | Hiển thị email giao hàng |
| **admin/order_detail.php** | Hiển thị email giao hàng (admin) |
| **migrations/2025_12_07_add_shipping_email.sql** | Script migration |

---

## ⚡ Cách Áp Dụng

### Cách 1: Nếu Database Còn Trống (Mới Cài)
```sql
-- Không cần gì, database schema đã cập nhật trong organic_db.sql
-- Vừa chạy lại: mysql -u root -p xan80975_organic < organic_db.sql
```

### Cách 2: Nếu Database Đã Có Dữ Liệu (Production)
```sql
-- 1. Backup database trước
mysqldump -u root -p xan80975_organic > backup_2025_12_07.sql

-- 2. Chạy migration
ALTER TABLE `orders` ADD COLUMN `shipping_email` varchar(100) DEFAULT NULL AFTER `shipping_phone`;

-- 3. Verify
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'orders' AND COLUMN_NAME = 'shipping_email';

-- Phải thấy kết quả: Field | Type | Null
```

### Cách 3: Dùng Script Migration
```bash
# Terminal/Command Prompt
mysql -u root -p xan80975_organic < migrations/2025_12_07_add_shipping_email.sql
```

---

## 🎯 Tóm Tắt Thay Đổi

### Before (❌ Sai):
```
Khách nhập: Email = buiminhtai97@gmail.com
Database lưu: ??? (KHÔNG LƯU)
Admin thấy: Không có email giao hàng
Kết quả: Email giao hàng sai/mất ❌
```

### After (✅ Đúng):
```
Khách nhập: Email = buiminhtai97@gmail.com
Database lưu: shipping_email = buiminhtai97@gmail.com ✅
Admin thấy: Email giao hàng chính xác ✅
Kết quả: Thông tin đầy đủ và chính xác ✅
```

---

## 📌 Ghi Chú

### Về Email:
- ✅ Khi chọn "Giao đến địa chỉ khác" → Dùng email từ form
- ✅ Khi chọn "Giao đến địa chỉ đã lưu" → Vẫn dùng email tài khoản (nếu không nhập mới)
- ✅ Email giao hàng khác email tài khoản → Là bình thường

### Về Backward Compatibility:
- ✅ Cột mới có `DEFAULT NULL` → Không ảnh hưởng dữ liệu cũ
- ✅ Code kiểm tra `$order['shipping_email'] ?? ''` → Không báo lỗi nếu NULL

### Về Email Notification:
- ✅ Email thông báo được gửi tới `$email` (email giao hàng)
- ✅ Nếu khách muốn sửa → Vào "Lịch sử đơn hàng" để xem

---

## ✅ Kết Quả

🎉 **Sau Fix:**
- ✅ Email giao hàng được lưu chính xác
- ✅ Admin có toàn bộ thông tin giao hàng
- ✅ Khách xem được email giao hàng của mình
- ✅ Không gây xung đột với email tài khoản
- ✅ Data consistency: Đầy đủ, chính xác, rõ ràng

