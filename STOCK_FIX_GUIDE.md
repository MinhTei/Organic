# 🔧 FIX: Khôi Phục Số Lượng Hàng Khi Hủy Đơn

## 📋 Vấn Đề Gốc
- ✗ Khi khách hủy đơn hàng → trạng thái đơn thay đổi thành "cancelled"
- ✗ **NHƯNG** số lượng sản phẩm trong kho **KHÔNG được khôi phục**
- ✗ Dẫn tới mất hàng trong kho (stock sai)

## ✅ Giải Pháp

### 1️⃣ **order_detail.php** (Khách hủy đơn từ trang chi tiết)

**Cập nhật logic hủy đơn:**

```php
// TRƯỚC (SAII):
if ($cancelOrder && in_array($cancelOrder['status'], ['pending', 'confirmed', 'processing'])) {
    $updateStmt = $conn->prepare("UPDATE orders SET status = 'cancelled', cancelled_at = NOW() WHERE id = ?");
    if ($updateStmt->execute([$orderId])) {
        $cancelMessage = 'Đơn hàng đã được hủy thành công!';
    }
}

// SAU (ĐÚNG):
if ($cancelOrder && in_array($cancelOrder['status'], ['pending', 'confirmed', 'processing'])) {
    try {
        $conn->beginTransaction();
        
        // 1. Lấy danh sách sản phẩm trong đơn
        $orderItemsStmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
        $orderItemsStmt->execute([$orderId]);
        $orderItems = $orderItemsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 2. Khôi phục stock cho mỗi sản phẩm
        foreach ($orderItems as $item) {
            $restoreStmt = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            $restoreStmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        // 3. Cập nhật trạng thái đơn hàng
        $updateStmt = $conn->prepare("UPDATE orders SET status = 'cancelled', cancelled_at = NOW() WHERE id = ?");
        $updateStmt->execute([$orderId]);
        
        $conn->commit();
        $cancelMessage = 'Đơn hàng đã được hủy thành công! Số lượng sản phẩm đã được khôi phục.';
    } catch (Exception $e) {
        $conn->rollBack();
        $cancelMessage = 'Có lỗi xảy ra khi hủy đơn hàng. Vui lòng thử lại.';
    }
}
```

**Gì đã thay đổi:**
- ✅ Sử dụng `beginTransaction()` để đảm bảo thao tác nguyên tử
- ✅ Lấy tất cả sản phẩm trong `order_items`
- ✅ Cộng lại số lượng cho từng sản phẩm: `stock = stock + quantity`
- ✅ Nếu lỗi → `rollBack()` để không mất data

---

### 2️⃣ **admin/orders.php** (Admin hủy đơn từ danh sách)

**Cập nhật logic cập nhật trạng thái:**

```php
// TRƯỚC (SAI):
if (isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $status = sanitize($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if ($stmt->execute([$status, $orderId])) {
        $success = 'Cập nhật trạng thái đơn hàng thành công!';
    }
}

// SAU (ĐÚNG):
if (isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = sanitize($_POST['status']);
    
    // Lấy trạng thái cũ
    $checkStmt = $conn->prepare("SELECT status FROM orders WHERE id = ?");
    $checkStmt->execute([$orderId]);
    $oldStatusRow = $checkStmt->fetch();
    $oldStatus = $oldStatusRow['status'] ?? null;
    
    try {
        $conn->beginTransaction();
        
        // CASE 1: Thay đổi THÀNH "cancelled" - cộng lại stock
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            $itemsStmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $itemsStmt->execute([$orderId]);
            $items = $itemsStmt->fetchAll();
            
            foreach ($items as $item) {
                $restoreStmt = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
                $restoreStmt->execute([$item['quantity'], $item['product_id']]);
            }
        }
        // CASE 2: Thay đổi TỪ "cancelled" SANG trạng thái khác - trừ lại stock
        elseif ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
            $itemsStmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $itemsStmt->execute([$orderId]);
            $items = $itemsStmt->fetchAll();
            
            foreach ($items as $item) {
                $reduceStmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $reduceStmt->execute([$item['quantity'], $item['product_id']]);
            }
        }
        
        // Cập nhật trạng thái
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $orderId]);
        
        $conn->commit();
        $success = 'Cập nhật trạng thái đơn hàng thành công!';
    } catch (Exception $e) {
        $conn->rollBack();
        $error = 'Có lỗi xảy ra khi cập nhật trạng thái. Vui lòng thử lại.';
    }
}
```

**Gì đã thay đổi:**
- ✅ Kiểm tra trạng thái **CŨ** của đơn trước khi cập nhật
- ✅ Nếu thay đổi **THÀNH** "cancelled" → cộng lại stock
- ✅ Nếu hủy được "uncancelled" → trừ lại stock (admin sửa lại)
- ✅ Sử dụng transaction để an toàn

---

## 🔄 Luồng Xử Lý (Workflow)

### Khi Đặt Hàng (order creation - thanhtoan.php):
```
1. Khách chọn sản phẩm → SESSION['cart']
2. Ấn "Đặt hàng"
3. Tạo record trong orders
4. Tạo order_items
5. TRỪ stock: stock = stock - quantity ✅
6. Xóa giỏ hàng
7. Redirect → success page
```

### Khi Hủy Hàng (order cancellation):
```
order_detail.php (Khách hủy):
1. Khách ấn "Hủy đơn"
2. Kiểm tra status ∈ [pending, confirmed, processing]
3. Lấy order_items
4. CỘNG stock: stock = stock + quantity ✅ (MỚI THÊM)
5. Cập nhật status = 'cancelled'
6. Commit transaction
7. Hiển thị thông báo

admin/orders.php (Admin hủy):
1. Admin chọn status = 'cancelled'
2. Kiểm tra status CŨ
3. Nếu cũ ≠ 'cancelled' → CỘNG stock ✅ (MỚI THÊM)
4. Cập nhật status
5. Commit transaction
```

---

## 📊 Ví Dụ Cụ Thể

### Scenario 1: Đặt rồi hủy ngay
```
Sản phẩm "Cà rốt" - Stock ban đầu: 50

1. Khách đặt 3 cà rốt
   - Stock sau đặt: 50 - 3 = 47 ✅

2. Khách hủy đơn
   - Stock sau hủy: 47 + 3 = 50 ✅ (TRƯỚC FIX: 47 ❌)

Kết quả: Stock đúng!
```

### Scenario 2: Admin xóa rồi khôi phục
```
Sản phẩm "Bông cải" - Stock: 100

1. Khách đặt 2 bông cải
   - Stock: 100 - 2 = 98 ✅

2. Admin thay status từ "pending" → "cancelled"
   - Stock: 98 + 2 = 100 ✅ (TRƯỚC FIX: 98 ❌)

3. Admin thay status từ "cancelled" → "confirmed"
   - Stock: 100 - 2 = 98 ✅ (TRƯỚC FIX: 100 ❌)

Kết quả: Stock luôn chính xác!
```

---

## 🧪 Cách Test

### 1. Test Khách Hủy Đơn
```
1. Login khách hàng
2. Đặt hàng với 1-2 sản phẩm
3. Vào "Lịch sử đơn hàng"
4. Click vào đơn → "Hủy đơn"
5. Kiểm tra database: stock có cộng lại không?
   SELECT name, stock FROM products;
```

### 2. Test Admin Hủy Đơn
```
1. Login admin
2. Quản lý Đơn hàng
3. Tìm đơn "pending"
4. Thay status thành "cancelled"
5. Kiểm tra database: stock có cộng lại không?
```

### 3. Test Admin Khôi Phục Đơn
```
1. Chọn đơn "cancelled"
2. Thay status thành "pending"
3. Kiểm tra: stock có trừ lại không?
```

---

## 🔒 Tính Năng Bảo Mật

✅ **Transaction Safety**
- Nếu bất kỳ bước nào fail → rollBack tất cả
- Tránh tình trạng stock cộng nhưng status không update

✅ **Status Validation**
- Chỉ hủy được đơn với status: pending, confirmed, processing
- Không hủy lại nếu đã cancelled

✅ **Kiểm Tra Trạng Thái Cũ**
- Tránh cộng/trừ stock nhiều lần
- Admin có thể đảo ngược được

---

## 📝 Tóm Tắt Thay Đổi

| File | Thay Đổi | Kết Quả |
|------|---------|--------|
| `order_detail.php` | Thêm logic khôi phục stock | Khách hủy → stock cộng lại |
| `admin/orders.php` | Thêm logic check status cũ | Admin hủy → stock cộng lại |
| | | Khôi phục → stock trừ lại |

**Tất cả sử dụng transaction để đảm bảo data consistency!**

---

## ✨ Kết Quả

🎉 **Sau Fix:**
- ✅ Khách hủy đơn → stock tự động khôi phục
- ✅ Admin hủy/khôi phục đơn → stock tự động điều chỉnh
- ✅ Không bao giờ mất hàng trong kho
- ✅ Data luôn nhất quán (consistent)

