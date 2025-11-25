# Quick Start - Import Sản phẩm từ Excel

## 🚀 Cách nhanh nhất để bắt đầu

### 1. Vào trang Import

- Đăng nhập Admin
- Chọn "Quản lý sản phẩm" từ menu
- Nhấn nút "Import Excel" (nút xanh dương)

### 2. Tải template hoặc file mẫu

```
Tùy chọn A: Download template tự động
- Nhấn nút "Tải Template" trên trang import

Tùy chọn B: Sử dụng file mẫu
- File: /admin/sample_products.csv
- Tải về và chỉnh sửa tên, giá, danh mục
```

### 3. Chuẩn bị dữ liệu (chỉ cần 2 cột bắt buộc)

```
Tên sản phẩm  |  Giá
Cà rốt        |  35000
Táo           |  99000
Bông cải      |  33000
```

**Tất cả cột khác là tùy chọn:**

- Giá giảm (để trống nếu không có)
- Danh mục (để trống = không gán)
- Đơn vị (mặc định: kg)
- Tồn kho (mặc định: 0)
- v.v...

### 4. Upload và Import

1. Chọn file Excel/CSV
2. (Tùy chọn) Chọn danh mục để áp dụng cho tất cả
3. Nhấn "Import"
4. Kiểm tra kết quả

## ✅ Trường hợp sử dụng phổ biến

### Import rau củ từ nhà cung cấp

**File input (vegetables.csv):**

```
Tên sản phẩm,Giá,Danh mục,Tồn kho
Cà rốt,35000,Rau củ,150
Bông cải,33000,Rau củ,80
Cà chua,25000,Rau củ,100
```

**Kết quả:** 3 sản phẩm thêm vào danh mục "Rau củ"

---

### Import trái cây có khuyến mãi

**File input (fruits_sale.csv):**

```
Tên sản phẩm,Giá,Giá giảm,Danh mục,Mô tả
Táo,99000,,Trái cây,Táo nhập khẩu
Cam,45000,40000,Trái cây,Cam khuyến mãi
Chuối,15000,,Trái cây,Chuối tươi
```

**Kết quả:** 3 sản phẩm, trong đó 1 sản phẩm có giá khuyến mãi

---

### Import sản phẩm mới (New)

**File input (new_products.csv):**

```
Tên sản phẩm,Giá,Danh mục,Mới
Sản phẩm A,100000,Rau củ,yes
Sản phẩm B,200000,Trái cây,yes
Sản phẩm C,50000,Thịt & Hải sản,yes
```

**Kết quả:** 3 sản phẩm mới (được đánh dấu là "Mới" trên website)

## 📋 Cấu trúc file CSV đơn giản nhất

```csv
Tên sản phẩm,Giá
Sản phẩm 1,10000
Sản phẩm 2,20000
Sản phẩm 3,30000
```

Chỉ cần 2 cột! Mọi thứ khác tự động lấy giá trị mặc định.

## ⚠️ Những lỗi thường gặp

| Lỗi                                | Nguyên nhân                  | Cách sửa                                                                        |
| ---------------------------------- | ---------------------------- | ------------------------------------------------------------------------------- |
| "Tên sản phẩm không được để trống" | Ô trống ở cột tên            | Nhập tên sản phẩm vào ô đó                                                      |
| "Giá không hợp lệ"                 | Giá có chứa ký tự (₫, $)     | Chỉ nhập số: `35000`                                                            |
| "Danh mục XXX không tìm thấy"      | Tên danh mục sai chính tả    | Kiểm tra: Rau củ, Trái cây, Trứng & Bơ sữa, Bánh mì & Bánh ngọt, Thịt & Hải sản |
| "Sản phẩm đã tồn tại"              | Tên hoặc slug sản phẩm trùng | Đổi tên sản phẩm hoặc xóa sản phẩm cũ                                           |

## 🎯 Mẹo nhanh

### Tạo file CSV trong Excel

1. Mở Excel
2. Nhập dữ liệu
3. File > Save As
4. Chọn "CSV (Comma delimited) (.csv)"

### Mở template download

1. Nhấn "Tải Template" trên trang import
2. File tự động download
3. Mở với Excel, thêm sản phẩm của bạn
4. Lưu file

### Kiểm tra trước khi import

1. Đảm bảo có 2 cột bắt buộc: Tên sản phẩm, Giá
2. Danh mục phải trùng tên trong hệ thống
3. Giá phải là số
4. Không có sản phẩm trùng tên

### Xem chi tiết lỗi

- Sau khi import, cuộn xuống xem "Kết quả Import"
- Chi tiết lỗi cho biết hàng nào có vấn đề
- Sửa file, import lại

## 🔗 Liên kết nhanh

- Trang import: `/admin/product_import.php`
- File template: `/admin/download_template.php`
- File mẫu: `/admin/sample_products.csv`
- Hướng dẫn chi tiết: `/IMPORT_GUIDE.md`

## 📞 Cần giúp đỡ?

Xem `/IMPORT_GUIDE.md` để có hướng dẫn chi tiết về:

- Format từng trường dữ liệu
- Ví dụ thực tế
- Xử lý sự cố
- FAQ

---

**Tip:** CSV là định dạng đơn giản nhất. Bạn có thể tạo trong Excel, Google Sheets, hoặc bất kỳ phần mềm nào khác.
