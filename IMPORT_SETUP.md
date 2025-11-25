# 📋 Chức năng Import Sản phẩm - Hướng dẫn chi tiết

## ✅ Tính năng đã thêm

Bạn vừa có thêm chức năng **Import danh sách sản phẩm từ Excel/CSV** với các đặc điểm:

✓ Import từ file **CSV** (hoạt động ngay - không cần cài đặt thư viện)  
✓ Import từ file **Excel** (.xlsx, .xls) - nếu có PhpSpreadsheet  
✓ Kiểm tra dữ liệu tự động trước import  
✓ Báo cáo chi tiết lỗi và cảnh báo  
✓ Tải template mẫu  
✓ Xem ví dụ dữ liệu trực tiếp trên trang

---

## 🚀 Cách sử dụng (Đơn giản nhất)

### Bước 1: Vào trang Import

1. Đăng nhập Admin
2. Chọn "Quản lý sản phẩm"
3. Nhấn nút **"Import Excel"** (nút xanh dương)

### Bước 2: Chuẩn bị file

**Cách A: Dùng file CSV (dễ nhất)**

- Tạo file trong Excel hoặc Google Sheets
- Hàng đầu tiên: `Tên sản phẩm` | `Giá`
- Dòng tiếp theo: dữ liệu sản phẩm
- Save As → **CSV (Comma delimited)**

**Cách B: Tải template**

- Nhấn nút **"Tải Template"** trên trang import
- Mở file, thêm sản phẩm của bạn
- Lưu file

### Bước 3: Upload và Import

1. Chọn file từ máy
2. Nhấn "Import"
3. Kiểm tra kết quả

---

## 📝 Format dữ liệu

### File CSV / Excel cơ bản nhất (2 cột bắt buộc):

```
Tên sản phẩm,Giá
Cà rốt hữu cơ,35000
Bông cải xanh,33000
Cà chua bi,25000
```

### Đầy đủ (9 cột):

```
Tên sản phẩm,Giá,Giá giảm,Danh mục,Đơn vị,Tồn kho,Mô tả,Hữu cơ,Mới
Cà rốt,35000,,Rau củ,kg,100,Cà rốt tươi từ Đà Lạt,yes,no
Bông cải,33000,28000,Rau củ,cái,80,Bông cải xanh giàu vitamin,yes,no
Táo,99000,,Trái cây,0.5kg,150,Táo nhập khẩu New Zealand,yes,yes
```

---

## ⚠️ Nếu gặp lỗi "Thư viện PhpSpreadsheet chưa được cài đặt"

### Giải pháp 1: Sử dụng file CSV (KHUYẾN NGHỊ - dễ nhất)

- Chuyển file Excel sang CSV:
  - Mở Excel
  - File > Save As
  - Chọn "CSV (Comma delimited) (.csv)"
  - Lưu file
- Sau đó upload file CSV vào hệ thống

### Giải pháp 2: Cài đặt PhpSpreadsheet (nếu cần)

1. Mở **Command Prompt** hoặc **PowerShell**
2. Chuyển đến thư mục website:
   ```bash
   cd c:\wamp64\www\Organic
   ```
3. Cài đặt PhpSpreadsheet:
   ```bash
   composer require phpoffice/phpspreadsheet
   ```
4. Chờ cài đặt xong (vài phút)

---

## 📂 Các file đã thêm

```
includes/import_helper.php          - Hàm xử lý import
admin/product_import.php            - Trang import
admin/download_template.php         - Download template
admin/sample_products.csv           - File mẫu
IMPORT_GUIDE.md                     - Hướng dẫn chi tiết
IMPORT_QUICKSTART.md                - Hướng dẫn nhanh
```

---

## 🎯 Danh sách danh mục hiện có

Import sản phẩm vào đúng danh mục, hãy sử dụng tên sau:

- **Rau củ**
- **Trái cây**
- **Trứng & Bơ sữa**
- **Bánh mì & Bánh ngọt**
- **Thịt & Hải sản**

---

## ✨ Ví dụ thực tế

### Import 1: Rau củ từ nhà cung cấp

**File: vegetables.csv**

```csv
Tên sản phẩm,Giá,Danh mục,Tồn kho
Cà rốt,35000,Rau củ,150
Bông cải,33000,Rau củ,80
Cà chua,25000,Rau củ,100
Dưa chuột,18000,Rau củ,120
```

**Kết quả:** 4 sản phẩm được thêm vào danh mục "Rau củ"

---

### Import 2: Sản phẩm khuyến mãi

**File: sale_products.csv**

```csv
Tên sản phẩm,Giá,Giá giảm,Danh mục,Mô tả
Cam,45000,40000,Trái cây,Cam khuyến mãi 10%
Nho,75000,68000,Trái cây,Nho xanh khuyến mãi
```

**Kết quả:** 2 sản phẩm với giá khuyến mãi

---

### Import 3: Sản phẩm mới

**File: new_items.csv**

```csv
Tên sản phẩm,Giá,Danh mục,Mới
Sản phẩm A,100000,Rau củ,yes
Sản phẩm B,200000,Trái cây,yes
Sản phẩm C,50000,Thịt & Hải sản,yes
```

**Kết quả:** 3 sản phẩm được đánh dấu "Mới"

---

## 🐛 Xử lý lỗi phổ biến

| Lỗi                                | Nguyên nhân                             | Cách sửa                         |
| ---------------------------------- | --------------------------------------- | -------------------------------- |
| "Tên sản phẩm không được để trống" | Ô trống ở cột tên                       | Nhập tên sản phẩm                |
| "Giá không hợp lệ"                 | Giá có ký tự (₫, $) hoặc không phải số  | Chỉ nhập số: `35000`             |
| "Danh mục XXX không tìm thấy"      | Tên danh mục sai chính tả               | Kiểm tra lại tên danh mục        |
| "Sản phẩm đã tồn tại"              | Slug/tên sản phẩm trùng                 | Đổi tên hoặc xóa cái cũ          |
| "PhpSpreadsheet chưa được cài đặt" | Import file Excel khi chưa cài thư viện | Dùng CSV hoặc cài PhpSpreadsheet |

---

## 💡 Mẹo hữu ích

### Tạo file CSV trong Excel

1. Mở Excel
2. Nhập dữ liệu (hàng đầu là header)
3. **File > Save As**
4. Chọn **"CSV (Comma delimited) (.csv)"**
5. Lưu file

### Kiểm tra trước import

- ✓ Có 2 cột bắt buộc: "Tên sản phẩm", "Giá"
- ✓ Danh mục trùng tên trong hệ thống
- ✓ Giá là số (không có ₫, $)
- ✓ Không có sản phẩm trùng tên

### Xem chi tiết lỗi

- Sau import, cuộn xuống xem "Kết quả Import"
- Xem lỗi ở từng hàng
- Sửa file, import lại

---

## 🔗 Truy cập nhanh

| Tính năng          | Link                           |
| ------------------ | ------------------------------ |
| Trang import       | `/admin/product_import.php`    |
| Download template  | `/admin/download_template.php` |
| File mẫu CSV       | `/admin/sample_products.csv`   |
| Hướng dẫn chi tiết | `/IMPORT_GUIDE.md`             |
| Hướng dẫn nhanh    | `/IMPORT_QUICKSTART.md`        |

---

## 📞 Cần hỗ trợ?

1. **Lỗi file**: Kiểm tra format CSV/Excel
2. **Lỗi dữ liệu**: Xem báo cáo lỗi chi tiết trên trang
3. **Cài đặt thư viện**: Xem phần "Giải pháp 2" ở trên
4. **Tài liệu đầy đủ**: Xem file `IMPORT_GUIDE.md`

---

**Chúc bạn sử dụng vui vẻ! 🎉**
