# 🔧 FIX: Địa Chỉ Khác - Chỉ Dùng Tạm Thời

## 📋 Vấn Đề Gốc

### Trước Fix:
- ❌ Khi khách chọn "Giao đến địa chỉ khác"
- ❌ Nhập địa chỉ mới → **Tự động LƯU vào "Địa chỉ đã lưu"**
- ❌ Kết quả: Danh sách địa chỉ đã lưu bị lộn xộn với các địa chỉ tạm thời

### Sau Fix:
- ✅ Khi khách chọn "Giao đến địa chỉ khác"
- ✅ Nhập địa chỉ mới → **Chỉ dùng TẠM THỜI cho đơn hàng này**
- ✅ **KHÔNG tự động lưu** vào danh sách địa chỉ đã lưu
- ✅ Nếu muốn lưu → Khách tự thêm ở trang "Thông tin cá nhân"

---

## 🔄 Luồng Xử Lý

### Scenario 1: Khách chọn "Giao đến địa chỉ khác"

**Trước Fix:**
```
1. Khách nhập địa chỉ mới
2. Ấn "Đặt hàng"
3. Tạo order ✅
4. Lưu vào customer_addresses ❌ (không nên)
5. Redirect → success
6. Khách mở "Thông tin cá nhân" → Thấy địa chỉ vừa nhập ❌
```

**Sau Fix:**
```
1. Khách nhập địa chỉ mới
2. Ấn "Đặt hàng"
3. Tạo order ✅
4. KHÔNG lưu vào customer_addresses ✅ (chỉ dùng tạm)
5. Redirect → success
6. Khách mở "Thông tin cá nhân" → Không thấy địa chỉ vừa nhập ✅
```

### Scenario 2: Khách muốn lưu địa chỉ

**Flow:**
```
1. Khách đặt hàng với "Giao đến địa chỉ khác"
2. Đơn hàng thành công
3. Khách vào "Thông tin cá nhân" → Tab "Địa chỉ"
4. Ấn "Thêm địa chỉ mới"
5. Nhập lại địa chỉ
6. Lưu ✅

Hoặc khách có thể lưu địa chỉ mặc định ở tab "Cài đặt"
```

---

## 📝 Code Thay Đổi

### File: `thanhtoan.php`

**Dòng ~268 - Phần xử lý lưu địa chỉ**

```php
// TRƯỚC (SAI):
if ($addressType === 'new' && !empty($name) && !empty($phone)) {
    $stmtAddr = $conn->prepare("INSERT INTO customer_addresses (user_id, name, phone, address, note, is_default, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
    $stmtAddr->execute([$userId, $name, $phone, $address, 'địa chỉ người nhận gần đây']);
}

// SAU (ĐÚNG):
// Note: Địa chỉ mới chỉ được dùng tạm thời, không lưu vào customer_addresses
// Nếu khách muốn lưu, họ phải tự thêm ở trang user_info.php
```

**Giải thích:**
- ✅ Loại bỏ code tự động lưu địa chỉ
- ✅ Chỉ dùng để tạo order
- ✅ Khách chủ động quyết định lưu hay không

---

## 📊 So Sánh Behavior

| Tình Huống | Trước Fix | Sau Fix |
|-----------|---------|--------|
| Đặt hàng địa chỉ khác | Tự động lưu ❌ | Chỉ dùng tạm ✅ |
| Danh sách "Địa chỉ đã lưu" | Bị lộn xộn ❌ | Sạch sẽ ✅ |
| Khách muốn lưu | Đã lưu rồi ❌ | Tự thêm ✅ |
| Khách không muốn lưu | Vẫn lưu ❌ | Không lưu ✅ |

---

## 💡 Lợi Ích

### Cho Khách Hàng:
- ✅ Danh sách địa chỉ gọn gàng
- ✅ Chỉ lưu những địa chỉ thực sự dùng thường xuyên
- ✅ Toàn quyền kiểm soát danh sách của mình

### Cho Website:
- ✅ Database sạch, không lộn xộn
- ✅ Giảm dữ liệu rác
- ✅ Logic rõ ràng và đúng

---

## 🧪 Cách Test

### Test 1: Đặt hàng địa chỉ khác
```
1. Đăng nhập khách hàng
2. Vào sản phẩm → Thêm vào giỏ
3. Vào giỏ hàng
4. Ấn "Thanh toán"
5. Chọn "Giao đến địa chỉ khác"
6. Nhập địa chỉ: 123 Tôn Đức Thắng, Phường 3, Quận 4
7. Ấn "Đặt hàng"
8. ✅ Đơn thành công
```

### Test 2: Kiểm tra danh sách địa chỉ
```
1. Ấn vào "Thông tin cá nhân" (trang khách hàng)
2. Tab "Địa chỉ"
3. ❌ Không thấy địa chỉ "123 Tôn Đức Thắng" ✅
4. Chỉ thấy những địa chỉ khách tự thêm
```

### Test 3: Khách tự lưu địa chỉ
```
1. Vào "Thông tin cá nhân"
2. Tab "Địa chỉ"
3. Ấn "Thêm địa chỉ"
4. Nhập: 123 Tôn Đức Thắng, Phường 3, Quận 4
5. Ấn "Lưu"
6. ✅ Thấy địa chỉ trong danh sách
```

---

## 📱 User Experience Flow

### Scenario A: Khách Thường Xuyên
```
Lần 1: Đặt hàng → Địa chỉ tạm → Lưu lại ở "Thông tin cá nhân"
Lần 2+: Chọn từ "Địa chỉ đã lưu" (nhanh hơn)
```

### Scenario B: Khách Một Lần
```
Lần 1: Đặt hàng → Địa chỉ tạm → Không lưu
Lần 2+: Nhập lại địa chỉ mới (nếu cần)
```

---

## 🎯 Tóm Tắt Thay Đổi

| Phần | Thay Đổi |
|-----|---------|
| **File** | `thanhtoan.php` |
| **Dòng** | ~268 |
| **Hành Động** | Xóa code tự động lưu địa chỉ |
| **Tác Dụng** | Địa chỉ chỉ dùng tạm, không lưu tự động |

---

## 📌 Ghi Chú Quan Trọng

### Về Flow Nhập Địa Chỉ:
- 📝 Form "Giao đến địa chỉ khác" vẫn hoạt động bình thường
- 📝 Địa chỉ vẫn được gửi trong order
- 📝 Chỉ khác: Không lưu vào `customer_addresses`

### Về Hữu Dụng:
- Khách có thể đặt hàng giao tới các địa chỉ khác nhau mà không bị lộn
- Danh sách "Địa chỉ đã lưu" thực sự chỉ chứa những địa chỉ khách muốn lưu
- Giúp quản lý danh sách sạch sẽ

---

## ✅ Kết Quả

🎉 **Sau Fix:**
- ✅ Địa chỉ khác chỉ dùng tạm, không lưu tự động
- ✅ Danh sách "Địa chỉ đã lưu" sạch sẽ
- ✅ Khách toàn quyền kiểm soát
- ✅ Logic rõ ràng và dễ hiểu

