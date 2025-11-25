# 🔧 Hỏi & Đáp - Import Sản phẩm

## ❓ Các câu hỏi thường gặp

### Q: Tôi gặp lỗi "PhpSpreadsheet chưa được cài đặt", phải làm sao?

**A:** Bạn có 2 cách:

1. **Cách 1: Sử dụng CSV (dễ nhất, KHUYẾN NGHỊ)**

   - Mở file Excel của bạn
   - File > Save As
   - Chọn "CSV (Comma delimited) (.csv)"
   - Upload file CSV lên hệ thống
   - ✓ Xong! Không cần cài đặt gì thêm

2. **Cách 2: Cài đặt PhpSpreadsheet**
   - Mở Command Prompt / PowerShell
   - Chạy: `cd c:\wamp64\www\Organic`
   - Chạy: `composer require phpoffice/phpspreadsheet`
   - Chờ cài đặt (vài phút)
   - Sau đó upload file Excel bình thường

**Khuyến nghị:** Dùng CSV vì đơn giản và không cần cài thêm gì.

---

### Q: Cách nào để tạo file CSV?

**A:** Rất dễ! Làm theo bước này:

1. **Tùy chọn 1: Trong Excel**

   - Mở Excel
   - Nhập dữ liệu của bạn
   - File > Save As
   - Tìm thư mục lưu
   - Tên file: `products.csv`
   - Kiểu file: **CSV (Comma delimited) (\*.csv)**
   - Save
   - ✓ Xong!

2. **Tùy chọn 2: Trong Google Sheets**

   - Mở Google Sheets
   - Nhập dữ liệu
   - File > Download > Comma-separated values (.csv)
   - ✓ Xong!

3. **Tùy chọn 3: Text editor**
   - Mở Notepad hoặc VS Code
   - Gõ (cách nhau bằng dấu phẩy):
     ```
     Tên sản phẩm,Giá
     Cà rốt,35000
     Táo,99000
     ```
   - Save As > Kiểu: All Files > Tên: `products.csv`
   - ✓ Xong!

---

### Q: File CSV của tôi không hoạt động, tại sao?

**A:** Kiểm tra các điều sau:

❌ **Vấn đề**: Ô "Tên sản phẩm" để trống  
✓ **Sửa**: Điền tên sản phẩm vào tất cả ô

❌ **Vấn đề**: Cột "Giá" có chứa ký tự (₫, $, hoặc chữ)  
✓ **Sửa**: Chỉ nhập số, ví dụ: `35000` (không phải `35.000₫`)

❌ **Vấn đề**: File không phải CSV (là Excel vẫn)  
✓ **Sửa**: File > Save As > Chọn "CSV (Comma delimited)"

❌ **Vấn đề**: Encoding sai (có ký tự lạ)  
✓ **Sửa**: Save As > Encoding: **UTF-8**

❌ **Vấn đề**: Danh mục sai chính tả  
✓ **Sửa**: Kiểm tra lại: "Rau củ", "Trái cây", v.v...

---

### Q: Phải có bao nhiêu cột trong file?

**A:** **Tối thiểu 2 cột (bắt buộc)**:

- Tên sản phẩm
- Giá

**Tùy chọn thêm**:

- Giá giảm
- Danh mục
- Đơn vị (mặc định: kg)
- Tồn kho (mặc định: 0)
- Mô tả
- Hữu cơ (mặc định: yes)
- Mới (mặc định: no)

**Ví dụ tối thiểu**:

```
Tên sản phẩm,Giá
Cà rốt,35000
```

**Ví dụ đầy đủ**:

```
Tên sản phẩm,Giá,Giá giảm,Danh mục,Đơn vị,Tồn kho,Mô tả,Hữu cơ,Mới
Cà rốt,35000,,Rau củ,kg,100,Cà rốt tươi,yes,no
```

---

### Q: Tôi muốn import 1000 sản phẩm cùng lúc, được không?

**A:** Có thể nhưng:

- **Nên nhất**: Import < 500 sản phẩm (nhanh, an toàn)
- **Có thể**: 500-1000 sản phẩm (mất vài phút)
- **Không nên**: > 1000 sản phẩm (có thể timeout)

**Mẹo**: Chia nhỏ thành nhiều file, import từng phần.

---

### Q: Sản phẩm import sẽ hiển thị trên website ngay không?

**A:** Có, **ngay lập tức** nếu:

- ✓ Sản phẩm được set `is_active = 1` (mặc định)
- ✓ Sản phẩm thuộc danh mục hợp lệ

Sản phẩm sẽ hiển thị ở:

- Trang danh mục (nếu chọn danh mục)
- Trang chủ (nếu set là nổi bật)

---

### Q: Có thể import ảnh sản phẩm không?

**A:** Hiện tại **không hỗ trợ**. Bạn phải:

1. Import sản phẩm (chỉ tên, giá, v.v...)
2. Sau đó upload ảnh bằng chức năng "Sửa" sản phẩm

**Mẹo**: Xếp danh sách sản phẩm theo tên để dễ tìm khi thêm ảnh.

---

### Q: Có thể cập nhật sản phẩm hiện tại qua import không?

**A:** Hiện tại **không hỗ trợ**. Import chỉ thêm sản phẩm mới.

Nếu sản phẩm đã tồn tại (tên/slug trùng), hệ thống sẽ **bỏ qua** nó.

**Cách cập nhật sản phẩm hiện tại**:

- Vào Admin > Quản lý sản phẩm
- Nhấn "Sửa" sản phẩm
- Thay đổi thông tin
- Lưu

---

### Q: Khi import, nếu có lỗi thì sao?

**A:** Hệ thống sử dụng **Transaction**:

- Nếu **toàn bộ import thành công** → Tất cả sản phẩm được thêm
- Nếu **có lỗi** → Hệ thống báo chi tiết:
  - Hàng nào lỗi
  - Lỗi gì
  - Cách sửa

**Sản phẩm lỗi sẽ bị bỏ qua**, sản phẩm khác vẫn được thêm.

---

### Q: Làm sao để xem lỗi chi tiết?

**A:** Sau khi import, cuộn xuống mục "Kết quả Import":

- **Số lượng thêm thành công** ✓
- **Số lỗi** ❌
- **Số cảnh báo** ⚠️
- **Chi tiết từng lỗi** (hàng nào, lỗi gì)

---

### Q: File template là gì?

**A:** Template là **file mẫu** giúp bạn:

- Biết cấu trúc đúng (header, cột)
- Biết format dữ liệu
- Copy-paste dễ dàng

**Cách tải**:

1. Vào trang import
2. Nhấn "Tải Template"
3. File tự động download

---

### Q: Tôi có thể dùng file từ nhà cung cấp khác được không?

**A:** **Có, nhưng cần chỉnh sửa**:

1. Nhà cung cấp gửi cho bạn file (Excel hoặc CSV)
2. Mở file
3. Kiểm tra các cột:
   - Có "Tên sản phẩm"? ✓
   - Có "Giá"? ✓
   - Format đúng không? ✓
4. Chuyển sang CSV nếu cần
5. Upload

**Ví dụ**: File từ nhà cung cấp:

```
Mã SP,Tên SP,Giá bán,Kho
P001,Cà rốt,35000,100
```

Chuyển thành:

```
Tên sản phẩm,Giá,Tồn kho
Cà rốt,35000,100
```

---

### Q: Tôi quên cột "Giá", sẽ sao?

**A:** Hệ thống sẽ báo lỗi:

```
❌ Lỗi: File không có cột yêu cầu: Giá
```

**Cách sửa**:

1. Mở file lại
2. Thêm cột "Giá" ở đầu tiên hoặc sau "Tên sản phẩm"
3. Điền giá cho tất cả sản phẩm
4. Upload lại

---

### Q: Tên danh mục phải chính xác 100% không?

**A:** Có, **phải chính xác 100%**:

✓ **Đúng**: `Rau củ`  
❌ **Sai**: `Rau cũ` (thiếu dấu)

✓ **Đúng**: `Trứng & Bơ sữa`  
❌ **Sai**: `Trung bo sua` (không dấu, sai chữ)

**Danh mục chính xác**:

- Rau củ
- Trái cây
- Trứng & Bơ sữa
- Bánh mì & Bánh ngọt
- Thịt & Hải sản

**Tip**: Copy-paste tên danh mục từ hệ thống để chắc chắn.

---

### Q: Các cột tùy chọn nên điền gì?

**A:**

| Cột      | Ví dụ              | Ghi chú               |
| -------- | ------------------ | --------------------- |
| Giá giảm | `28000`            | Để trống nếu không có |
| Danh mục | `Rau củ`           | Để trống để không gán |
| Đơn vị   | `kg`, `cái`, `hộp` | Mặc định: kg          |
| Tồn kho  | `100`, `0`, `250`  | Mặc định: 0           |
| Mô tả    | Bất kỳ             | Để trống được         |
| Hữu cơ   | `yes` hoặc `no`    | Mặc định: yes         |
| Mới      | `yes` hoặc `no`    | Mặc định: no          |

---

## 🎓 Ví dụ từ A đến Z

### Tình huống: Bạn có 5 sản phẩm rau từ nhà cung cấp

**Bước 1: Tạo file CSV**

Mở Excel, nhập:

```
Tên sản phẩm,Giá,Danh mục,Tồn kho,Mô tả
Cà rốt,35000,Rau củ,100,Cà rốt tươi
Bông cải,33000,Rau củ,80,Bông cải xanh
Cà chua,25000,Rau củ,120,Cà chua ngọt
Dưa chuột,18000,Rau củ,150,Dưa chuột giòn
Cà tím,22000,Rau củ,60,Cà tím mềm
```

Save As → CSV → Lưu

**Bước 2: Truy cập trang import**

- Đăng nhập Admin
- Quản lý sản phẩm
- Nhấn "Import Excel"

**Bước 3: Upload file**

- Nhấn "Chọn file"
- Chọn file CSV vừa tạo
- Nhấn "Import"

**Bước 4: Kiểm tra kết quả**

- Xem "Kết quả Import"
- Nếu thành công: ✓ 5 sản phẩm được thêm
- Nếu có lỗi: Xem chi tiết, sửa file, import lại

**Xong!** 🎉

---

## 📞 Liên hệ hỗ trợ

Nếu vấn đề không giải quyết được:

1. Xem file `IMPORT_GUIDE.md` (hướng dẫn chi tiết)
2. Xem file `IMPORT_QUICKSTART.md` (hướng dẫn nhanh)
3. Xem file `IMPORT_SETUP.md` (thiết lập và cài đặt)
4. Liên hệ quản trị viên website

---

**Chúc bạn import sản phẩm thành công! ✨**
