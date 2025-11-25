# Hướng dẫn Import Sản phẩm từ Excel

## Tính năng

- Import danh sách sản phẩm từ file Excel (.xlsx, .xls) hoặc CSV
- Hỗ trợ các trường bắt buộc và tùy chọn
- Kiểm tra dữ liệu tự động trước khi import
- Báo cáo chi tiết lỗi và cảnh báo
- Tải template mẫu

## Cài đặt

### 1. Sử dụng CSV (Không cần thêm thư viện)

CSV là định dạng đơn giản nhất, bạn có thể tạo trong Excel hoặc Google Sheets.

### 2. Sử dụng Excel (Tùy chọn - cần cài đặt PhpSpreadsheet)

Nếu muốn hỗ trợ đầy đủ file Excel, cài đặt thư viện:

```bash
cd c:\wamp64\www\Organic
composer require phpoffice/phpspreadsheet
```

Hoặc cài đặt thủ công:

- Tải composer từ https://getcomposer.org/download/
- Chạy lệnh trên trong thư mục dự án

## Cách sử dụng

### Bước 1: Chuẩn bị dữ liệu

**Các cột bắt buộc:**

- `Tên sản phẩm` - Tên sản phẩm (không được để trống)
- `Giá` - Giá sản phẩm (phải là số, không được để trống)

**Các cột tùy chọn:**

- `Danh mục` - Tên danh mục (tìm kiếm trong database)
- `Giá giảm` - Giá khuyến mãi (phải là số nếu có)
- `Đơn vị` - Đơn vị tính (mặc định: kg)
- `Tồn kho` - Số lượng tồn (mặc định: 0)
- `Mô tả` - Mô tả sản phẩm
- `Hữu cơ` - yes/no (mặc định: yes)
- `Mới` - yes/no (mặc định: no)

### Bước 2: Tạo file Excel/CSV

**Option A: Sử dụng template**

1. Vào Admin > Quản lý sản phẩm > Import Excel
2. Nhấn nút "Tải Template"
3. Mở file, thêm dữ liệu sản phẩm của bạn
4. Lưu file

**Option B: Tạo thủ công**

Tạo file Excel với các cột sau (trên dòng đầu tiên):

| Tên sản phẩm | Giá   | Giá giảm | Danh mục | Đơn vị | Tồn kho | Mô tả         | Hữu cơ | Mới |
| ------------ | ----- | -------- | -------- | ------ | ------- | ------------- | ------ | --- |
| Cà rốt       | 35000 |          | Rau củ   | kg     | 100     | Cà rốt tươi   | yes    | no  |
| Bông cải     | 33000 | 28000    | Rau củ   | cái    | 50      | Bông cải xanh | yes    | no  |
| Táo          | 99000 |          | Trái cây | 0.5kg  | 200     | Táo nhập khẩu | yes    | yes |

**Hoặc CSV:**

```
Tên sản phẩm,Giá,Giá giảm,Danh mục,Đơn vị,Tồn kho,Mô tả,Hữu cơ,Mới
Cà rốt,35000,,Rau củ,kg,100,Cà rốt tươi,yes,no
Bông cải,33000,28000,Rau củ,cái,50,Bông cải xanh,yes,no
```

### Bước 3: Import

1. Vào Admin > Quản lý sản phẩm > Nhấn nút "Import Excel"
2. Chọn file Excel/CSV từ máy tính
3. (Tùy chọn) Chọn danh mục để áp dụng cho tất cả sản phẩm
4. Nhấn "Import"
5. Kiểm tra kết quả báo cáo

## Format dữ liệu

### Tên sản phẩm

- Bắt buộc
- Không để trống
- Ví dụ: "Cà rốt hữu cơ", "Bông cải xanh"

### Giá

- Bắt buộc
- Phải là số (có thể có dấu . hoặc ,)
- Không có ký tự đơn vị (₫, $, etc)
- Ví dụ: `35000`, `99000.50`, `28,000`

### Giá giảm (nếu có)

- Tùy chọn
- Phải là số nếu có
- Để trống nếu không có giá khuyến mãi
- Ví dụ: `28000`, `99000`

### Danh mục

- Tùy chọn
- Phải trùng tên danh mục trong hệ thống
- Nếu không tìm thấy, sẽ bỏ qua và hiển thị cảnh báo
- Danh mục hiện có:
  - Rau củ
  - Trái cây
  - Trứng & Bơ sữa
  - Bánh mì & Bánh ngọt
  - Thịt & Hải sản

### Đơn vị

- Tùy chọn, mặc định: "kg"
- Ví dụ: `kg`, `cái`, `hộp`, `vỉ 10 trứng`, `0.5kg`

### Tồn kho

- Tùy chọn, mặc định: 0
- Phải là số nguyên
- Ví dụ: `100`, `50`, `0`

### Mô tả

- Tùy chọn
- Có thể chứa xuống dòng và ký tự đặc biệt
- Ví dụ: "Cà rốt tươi ngon từ Đà Lạt, giàu vitamin A"

### Hữu cơ (Organic)

- Tùy chọn, mặc định: "yes"
- Chỉ chấp nhận: `yes`, `no`, `Yes`, `No`, `YES`, `NO`
- Ví dụ: `yes`, `no`

### Mới (New)

- Tùy chọn, mặc định: "no"
- Chỉ chấp nhận: `yes`, `no`, `Yes`, `No`, `YES`, `NO`
- Ví dụ: `yes`, `no`

## Báo cáo lỗi

Import sẽ báo cáo chi tiết các lỗi gặp phải:

- **Lỗi**: Sản phẩm không được import (yêu cầu sửa trước khi thử lại)

  - Tên sản phẩm trống
  - Giá không hợp lệ
  - Slug sản phẩm trùng nhau
  - Lỗi database

- **Cảnh báo**: Sản phẩm bị bỏ qua nhưng không ảnh hưởng tới sản phẩm khác
  - Danh mục không tìm thấy
  - Sản phẩm đã tồn tại (trùng slug)

Ví dụ báo cáo:

```
Thêm thành công: 5 sản phẩm
Lỗi: 2
Cảnh báo: 1

Lỗi:
• Hàng 3: Giá không hợp lệ (phải là số)
• Hàng 5: Sản phẩm 'Cà chua' đã tồn tại (slug trùng)

Cảnh báo:
• Hàng 7: Danh mục 'Rau ngoại' không tìm thấy
```

## Những điều cần lưu ý

1. **Slug tự động**: Hệ thống tự động tạo slug từ tên sản phẩm. Nếu slug trùng, sản phẩm sẽ bị bỏ qua.

2. **Danh mục**:

   - Nếu chọn danh mục khi import, tất cả sản phẩm sẽ thuộc danh mục đó
   - Nếu để trống, sẽ lấy danh mục từ cột "Danh mục" trong file
   - Nếu cả hai đều không có, sản phẩm sẽ có danh mục trống

3. **Transaction**: Toàn bộ quá trình import là một transaction. Nếu có lỗi, không có sản phẩm nào bị xóa hoặc thay đổi.

4. **Ảnh sản phẩm**: Không hỗ trợ import ảnh trực tiếp từ file. Bạn cần thêm ảnh sau khi import bằng cách edit sản phẩm.

5. **Hiệu năng**:
   - Import tối đa vài trăm sản phẩm cùng lúc là tốt
   - Nếu import nhiều sản phẩm (>1000), có thể mất vài phút

## Ví dụ thực tế

### Ví dụ 1: Import rau củ từ CSV

File `import_vegetables.csv`:

```csv
Tên sản phẩm,Giá,Giá giảm,Danh mục,Đơn vị,Tồn kho,Mô tả,Hữu cơ,Mới
Cà rốt hữu cơ,35000,,Rau củ,kg,150,Cà rốt tươi từ Đà Lạt,yes,no
Bông cải xanh,33000,28000,Rau củ,cái,80,Bông cải giàu vitamin C,yes,no
Cà chua bi,25000,,Rau củ,hộp 250g,100,Cà chua bi ngọt tự nhiên,yes,yes
Cà tím,22000,,Rau củ,kg,60,Cà tím mềm,yes,no
```

Kết quả: Import 4 sản phẩm thành công, không có lỗi

### Ví dụ 2: Import trái cây từ Excel

File `import_fruits.xlsx`:
| Tên sản phẩm | Giá | Giá giảm | Danh mục | Đơn vị | Tồn kho | Mô tả | Hữu cơ | Mới |
|---|---|---|---|---|---|---|---|---|
| Táo Envy | 99000 | | Trái cây | 0.5kg | 200 | Táo nhập khẩu New Zealand | yes | yes |
| Cam Valencia | 45000 | 40000 | Trái cây | kg | 300 | Cam ngọt không hạt | yes | no |
| Chuối vàng | 15000 | | Trái cây | kg | 500 | Chuối tươi | yes | no |

Kết quả: Import 3 sản phẩm thành công

## Xử lý sự cố

### Lỗi: "Thư viện PhpSpreadsheet chưa được cài đặt"

**Giải pháp**:

1. Cài đặt Composer (nếu chưa có)
2. Mở Command Prompt/PowerShell
3. Chạy lệnh:

```bash
cd c:\wamp64\www\Organic
composer require phpoffice/phpspreadsheet
```

Hoặc chỉ sử dụng file CSV (không cần thêm thư viện)

### Lỗi: "File phải là Excel (.xlsx, .xls) hoặc CSV"

**Giải pháp**: Kiểm tra định dạng file

- Excel: `.xlsx`, `.xls`
- CSV: `.csv`

Nếu file Excel, hãy "Save As" > Chọn định dạng `Excel Workbook (.xlsx)` hoặc `Excel 97-2003 Workbook (.xls)`

### Lỗi: "Tên sản phẩm không được để trống" (Hàng X)

**Giải pháp**: Kiểm tra cột "Tên sản phẩm" (hoặc biến thể như "Tên", "Product name")

- Không để ô trống
- Đúng tên cột trong header

### Lỗi: "Giá không hợp lệ" (Hàng X)

**Giải pháp**: Kiểm tra cột "Giá"

- Phải là số (35000 hoặc 35.000)
- Không có ký tự đơn vị (₫, $)
- Không để ô trống

### Lỗi: "Danh mục 'XXX' không tìm thấy"

**Giải pháp**: Kiểm tra tên danh mục

- Phải trùng chính xác với tên danh mục trong hệ thống (phân biệt hoa/thường)
- Danh mục hiện có: Rau củ, Trái cây, Trứng & Bơ sữa, Bánh mì & Bánh ngọt, Thịt & Hải sản

### Lỗi: "Sản phẩm 'XXX' đã tồn tại (slug trùng)"

**Giải pháp**:

- Sản phẩm có tên hoặc slug trùng đã tồn tại
- Kiểm tra danh sách sản phẩm để xem tên sản phẩm
- Đặt tên khác hoặc cập nhật sản phẩm hiện tại

## Câu hỏi thường gặp

**Q: Có thể import cùng lúc bao nhiêu sản phẩm?**
A: Lý thuyết là vô hạn, nhưng khuyến nghị < 1000 để tránh timeout. Nếu cần import nhiều, chia thành nhiều file.

**Q: Các sản phẩm bị lỗi có ảnh hưởng tới sản phẩm khác?**
A: Không. Toàn bộ import là một transaction. Nếu toàn bộ không thành công, không sản phẩm nào được thêm.

**Q: Có thể cập nhật sản phẩm hiện tại qua import?**
A: Hiện tại chỉ hỗ trợ thêm sản phẩm mới. Nếu slug trùng, sản phẩm sẽ bị bỏ qua. Để cập nhật, hãy sử dụng chức năng "Sửa" trực tiếp.

**Q: Sản phẩm import sẽ hiển thị trên trang web ngay không?**
A: Có, nếu `is_active = 1` (mặc định). Sản phẩm sẽ hiển thị trên trang chủ nếu được set là nổi bật hoặc trên trang danh mục.

**Q: Có thể import ảnh sản phẩm?**
A: Không, nhưng bạn có thể thêm đường dẫn ảnh trong cột tùy chọn (tính năng này chưa được thêm vào template). Hiện tại, hãy upload ảnh sau khi import.

## Hỗ trợ

Nếu gặp vấn đề:

1. Kiểm tra báo cáo lỗi chi tiết
2. Xem phần "Xử lý sự cố" ở trên
3. Liên hệ quản trị viên website
