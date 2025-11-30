# Chức Năng Xuất Báo Cáo

## Mô Tả
Chức năng xuất báo cáo cho phép quản lý viên xuất dữ liệu thống kê doanh thu, đơn hàng và sản phẩm bán chạy dưới dạng **Excel** hoặc **PDF**.

## Tính Năng

### 1. **Xuất Excel (.xlsx)**
- Báo cáo doanh thu chi tiết theo ngày
- Top 15 sản phẩm bán chạy nhất
- Thống kê trạng thái đơn hàng
- Tổng hợp các chỉ tiêu chính (tổng đơn, doanh thu, trung bình)
- Định dạng chuyên nghiệp với tiêu đề, màu sắc, căn chỉnh

### 2. **Xuất PDF (dạng HTML)**
- Báo cáo tương tự Excel nhưng dạng HTML
- Có thể in trực tiếp từ trình duyệt
- Hỗ trợ lưu thành PDF qua chức năng in của trình duyệt

## Cách Sử Dụng

### Từ Giao Diện Web
1. Đăng nhập với tài khoản Admin
2. Vào menu **Thống kê & Báo cáo** (`/admin/statistics.php`)
3. Chọn khoảng ngày cần xuất báo cáo:
   - Nhập ngày bắt đầu (Từ)
   - Nhập ngày kết thúc (Đến)
4. Nhấn nút **Xuất báo cáo** → Chọn định dạng:
   - **Xuất PDF**: Xuất dạng HTML có thể in
   - **Xuất Excel**: Tải file Excel (.xlsx)

### Từ URL Trực Tiếp
```
# Xuất Excel
/admin/export_report.php?format=excel&start=2025-01-01&end=2025-01-31

# Xuất PDF
/admin/export_report.php?format=pdf&start=2025-01-01&end=2025-01-31
```

## Cấu Trúc Báo Cáo

### Phần 1: Tổng Hợp Chỉ Tiêu
- **Tổng số đơn hàng**: Số lượng đơn hàng trong kỳ
- **Tổng doanh thu**: Tổng tiền từ tất cả đơn hàng
- **Doanh thu TB/đơn**: Trung bình doanh thu mỗi đơn
- **Đơn hàng đã giao**: Số đơn đã được giao thành công
- **Đơn hàng đã hủy**: Số đơn bị hủy

### Phần 2: Doanh Thu Theo Ngày
Bảng chi tiết doanh thu từng ngày:
- Ngày
- Số đơn hàng
- Doanh thu

### Phần 3: Top 15 Sản Phẩm Bán Chạy
Danh sách những sản phẩm bán chạy nhất:
- STT
- Tên sản phẩm
- Số lượng bán
- Doanh thu
- Giá bình quân

### Phần 4: Trạng Thái Đơn Hàng
Thống kê số lượng đơn hàng theo từng trạng thái:
- Chờ xác nhận
- Đã xác nhận
- Đang xử lý
- Đang giao
- Đã giao
- Đã hủy
- Đã hoàn tiền

## Tệp Liên Quan

- `admin/statistics.php` - Trang hiển thị thống kê
- `admin/export_report.php` - Script xử lý xuất báo cáo
- `vendor/phpoffice/phpspreadsheet/` - Thư viện Excel

## Lưu Ý

1. **Yêu cầu quyền**: Chỉ admin mới có thể xuất báo cáo
2. **Dữ liệu**: Báo cáo chỉ bao gồm đơn hàng có trạng thái không phải "cancelled"
3. **Tệp Excel**: Được tạo bằng PHPSpreadsheet v5.3 (tương thích Office 2007+)
4. **Tệp PDF**: Được xuất dạng HTML để tối giản dependencies

## Cải Tiến Tương Lai

- [ ] Thêm hỗ trợ TCPDF/mPDF để xuất PDF thực sự
- [ ] Thêm biểu đồ trong báo cáo
- [ ] Cho phép tùy chỉnh các cột báo cáo
- [ ] Xuất dạng CSV
- [ ] Lên lịch xuất báo cáo tự động
