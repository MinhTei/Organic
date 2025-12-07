# 🔧 Fix: Lưu Đầy Đủ Thông Tin Từ Địa Chỉ Đã Lưu

## ❌ Vấn Đề Cũ
Khi khách hàng chọn **"Sử dụng địa chỉ đã lưu"** và đặt hàng, chỉ:
- `saved_address_id` được lưu
- Thông tin chi tiết (name, phone, email, address, ward, district, city) chỉ hiển thị nhưng **KHÔNG được gửi lên server**

Kết quả: Database chỉ có ID, không có thông tin cụ thể.

---

## ✅ Giải Pháp

### 1️⃣ **Thêm Hidden Inputs Để Lưu Dữ Liệu**
```html
<!-- Hidden inputs to store data for submission -->
<input type="hidden" name="name_saved" id="name_saved">
<input type="hidden" name="phone_saved" id="phone_saved">
<input type="hidden" name="email_saved" id="email_saved">
<input type="hidden" name="address_saved" id="address_saved">
<input type="hidden" name="ward_saved" id="ward_saved">
<input type="hidden" name="district_saved" id="district_saved">
<input type="hidden" name="city_saved" id="city_saved">
```

### 2️⃣ **Thêm Data Attributes Vào Options**
```html
<option value="<?= $addr['id'] ?>" 
        data-ward="<?= sanitize($addr['ward'] ?? '') ?>" 
        data-district="<?= sanitize($addr['district'] ?? '') ?>" 
        data-city="<?= sanitize($addr['city'] ?? 'TP. Hồ Chí Minh') ?>">
    <?= sanitize($addr['name']) ?> - <?= sanitize($addr['phone']) ?>
</option>
```

### 3️⃣ **JavaScript Populate Hidden Fields**
```javascript
// Extract từ dropdown text
const name = namePhone[0].trim();
const phone = namePhone[1].trim() || '';

// Set vào display + hidden inputs
document.getElementById('name_saved').value = name;
document.getElementById('phone_saved').value = phone;

// Extract từ data attributes
const ward = selectedOption.getAttribute('data-ward') || '';
const district = selectedOption.getAttribute('data-district') || '';
const city = selectedOption.getAttribute('data-city') || '';

document.getElementById('ward_saved').value = ward;
document.getElementById('district_saved').value = district;
document.getElementById('city_saved').value = city;
```

### 4️⃣ **PHP Xử Lý POST Data**
```php
if ($addressType === 'saved') {
    // Get data from hidden inputs (sent by JavaScript)
    // Fallback to database nếu không có
    $name = sanitize($_POST['name_saved'] ?? '') ?: $selectedAddr['name'];
    $phone = sanitize($_POST['phone_saved'] ?? '') ?: $selectedAddr['phone'];
    $address = sanitize($_POST['address_saved'] ?? '') ?: $selectedAddr['address'];
    $ward = sanitize($_POST['ward_saved'] ?? '') ?: ($selectedAddr['ward'] ?? '');
    $district = sanitize($_POST['district_saved'] ?? '') ?: ($selectedAddr['district'] ?? '');
    $city = sanitize($_POST['city_saved'] ?? '') ?: ($selectedAddr['city'] ?? '...');
    $email = sanitize($_POST['email_saved'] ?? '') ?: ($user['email'] ?? '');
}
```

### 5️⃣ **Thêm Display Fields Cho Ward/District/City**
```html
<input type="text" name="ward_display" readonly ...>
<input type="text" name="district_display" readonly ...>
<input type="text" name="city_display" readonly ...>
```

---

## 📊 So Sánh Trước/Sau

### Trước:
```
POST Data:
{
  address_type: "saved",
  saved_address_id: 5,
  // ❌ Không có chi tiết!
}

Database orders:
{
  shipping_name: NULL,
  shipping_phone: NULL,
  shipping_address: NULL,
  shipping_ward: NULL,
  shipping_district: NULL,
  shipping_city: NULL
}
```

### Sau:
```
POST Data:
{
  address_type: "saved",
  saved_address_id: 5,
  name_saved: "Minh Hợp",
  phone_saved: "0966330643",
  email_saved: "minh@example.com",
  address_saved: "123 Nguyễn Huệ",
  ward_saved: "Bến Nghé",
  district_saved: "Quận 1",
  city_saved: "TP. Hồ Chí Minh"
}

Database orders:
{
  shipping_name: "Minh Hợp",
  shipping_phone: "0966330643",
  shipping_email: "minh@example.com",
  shipping_address: "123 Nguyễn Huệ",
  shipping_ward: "Bến Nghé",
  shipping_district: "Quận 1",
  shipping_city: "TP. Hồ Chí Minh"  ✅ Đầy đủ!
}
```

---

## 🔄 Quy Trình Dữ Liệu

```
1. User chọn saved address dropdown
   ↓
2. JavaScript event: onchange="updateAddressDisplay()"
   ↓
3. Extract dữ liệu từ:
   - Dropdown text (name, phone, address)
   - Data attributes (ward, district, city)
   ↓
4. Populate vào:
   - Display fields (readonly hiển thị)
   - Hidden fields (gửi server)
   ↓
5. User submit form
   ↓
6. PHP nhận hidden input + cơ sở dữ liệu
   ↓
7. INSERT INTO orders (with all shipping info)
```

---

## 🛡️ Safety Features

✅ **Data Validation**: Tất cả dữ liệu qua `sanitize()`
✅ **Fallback Logic**: Nếu hidden input rỗng, dùng DB
✅ **User Email**: Luôn có giá trị (user input hoặc account email)
✅ **Display Only**: Readonly fields không thể sửa (UX tốt)

---

## 📝 Files Modified

- ✅ `thanhtoan.php` - 4 thay đổi:
  1. Thêm 7 hidden inputs
  2. Thêm data attributes vào options
  3. Cập nhật PHP POST logic
  4. Cập nhật JavaScript populate function

---

## ✨ Kết Quả

✅ Đầy đủ thông tin được lưu từ saved address
✅ Tất cả 7 trường (name, phone, email, address, ward, district, city) được gửi
✅ Backward compatible - fallback vẫn hoạt động
✅ Phù hợp với user experience - display + hidden layer

**Status: COMPLETE** 🎉

