<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab7 Tạo CSDL bookstore</title>
</head>

<body>
    <pre>
4.1. Tạo CSDL
CREATE DATABASE bookstore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Chọn CSDL (Trong phpMyAdmin, bạn sẽ chọn CSDL 'bookstore' ở cột bên trái)
USE bookstore;

4.2. Tạo bảng loai
CREATE TABLE loai (
    maloai VARCHAR(5) NOT NULL PRIMARY KEY,
    tenloai VARCHAR(50) NOT NULL
);
-- Bảng khachhang
CREATE TABLE khachhang (
    email VARCHAR(50) NOT NULL PRIMARY KEY,
    matkhau VARCHAR(32) NOT NULL,
    tenkh VARCHAR(50) NOT NULL,
    diachi VARCHAR(100),
    dienthoai VARCHAR(11)
);

-- Bảng nhaxb
CREATE TABLE nhaxb (
    manxb VARCHAR(5) NOT NULL PRIMARY KEY,
    tennxb TEXT NOT NULL
);

-- Bảng sach
CREATE TABLE sach (
    masach VARCHAR(15) NOT NULL PRIMARY KEY,
    tensach VARCHAR(250) NOT NULL,
    mota TEXT,
    gia FLOAT NOT NULL,
    hinh VARCHAR(50),
    manxb VARCHAR(5) NOT NULL, -- Khóa ngoại
    maloai VARCHAR(5) NOT NULL  -- Khóa ngoại
);

-- Bảng hoadon
CREATE TABLE hoadon (
    mahd INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(50) NOT NULL, -- Khóa ngoại
    ngayhd DATETIME NOT NULL,
    tennguoinhan VARCHAR(50),
    diachinguoinhan VARCHAR(80),
    ngaynhan DATE,
    dienthoainguoinhan VARCHAR(11),
    trangthai TINYINT(4)
);

-- Bảng chitiet_hoadon (chitietthd trong sơ đồ)
CREATE TABLE chitiet_hoadon (
    mahd INT(11) NOT NULL,       -- Khóa ngoại, thành phần của Khóa chính
    masach VARCHAR(15) NOT NULL, -- Khóa ngoại, thành phần của Khóa chính
    soluong TINYINT(4) NOT NULL,
    gia FLOAT NOT NULL,
    PRIMARY KEY (mahd, masach)
);
4.3 .Thêm khóa ngoại cho bảng sach
ALTER TABLE sach
ADD CONSTRAINT fk_sach_nhaxb FOREIGN KEY (manxb) REFERENCES nhaxb(manxb) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT fk_sach_loai FOREIGN KEY (maloai) REFERENCES loai(maloai) ON DELETE CASCADE ON UPDATE CASCADE;

-- Thêm khóa ngoại cho bảng hoadon
ALTER TABLE hoadon
ADD CONSTRAINT fk_hoadon_khachhang FOREIGN KEY (email) REFERENCES khachhang(email) ON DELETE CASCADE ON UPDATE CASCADE;

-- Thêm khóa ngoại cho bảng chitiet_hoadon
ALTER TABLE chitiet_hoadon
ADD CONSTRAINT fk_cthd_hoadon FOREIGN KEY (mahd) REFERENCES hoadon(mahd) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT fk_cthd_sach FOREIGN KEY (masach) REFERENCES sach(masach) ON DELETE CASCADE ON UPDATE CASCADE;


4.4 Sửa cột trangthai của bảng hoadon để có giá trị mặc định là 0.
-- Thay đổi cấu trúc bảng hoadon
-- Cột trangthai kiểu tinyint(4), không cho phép NULL, mặc định = 0
ALTER TABLE hoadon 
MODIFY trangthai TINYINT(4) NOT NULL DEFAULT 0;


4.5 Tạo view tên top_10 để lưu 10 sách có giá cao nhất
CREATE VIEW top_10_sach_gia_cao AS
SELECT masach, tensach, gia
FROM sach
ORDER BY gia DESC   -- Sắp xếp giảm dần theo giá
LIMIT 10;           -- Lấy 10 sách đầu tiên

-- Xem dữ liệu trong view vừa tạo
SELECT * FROM top_10;


4.6 Tạo procedure để liệt masach, tensach của 1 loại khi biết mã loại
-- Đặt dấu phân cách lệnh thành $$ để viết procedure
DELIMITER $$

-- Tạo procedure tên lietke, tham số đầu vào là category_code (mã loại)
CREATE PROCEDURE lietke(IN category_code VARCHAR(5))
BEGIN
    SELECT masach, tensach
    FROM sach
    WHERE maloai = category_code;  -- Lọc theo mã loại
END $$

-- Trả lại dấu phân cách mặc định ;
DELIMITER ;

-- Gọi procedure để liệt kê sách thuộc loại M01
CALL lietke('M01');


5.1 Viết câu truy vấn tất cả các quyển sách được nhóm theo từng loại.
-- Lấy thông tin sách kèm tên loại
SELECT s.masach, s.tensach, s.maloai, l.tenloai
FROM sach s
JOIN loai l ON s.maloai = l.maloai   -- Kết nối bảng sach và loai
ORDER BY l.tenloai, s.masach;        -- Sắp xếp theo tên loại, sau đó theo mã sách


5.2 Tạo procedure dùng để cập nhật giá 1 quyển sách
-- Đặt dấu phân cách thành $$
DELIMITER $$

-- Tạo procedure tên capnhat, có 2 tham số: mã sách và giá mới
CREATE PROCEDURE capnhat(IN book_id VARCHAR(15), IN new_price FLOAT)
BEGIN
    UPDATE sach
    SET gia = new_price
    WHERE masach = book_id;   -- Cập nhật giá cho sách có mã book_id
END $$

-- Trả lại dấu phân cách mặc định ;
DELIMITER ;

-- Gọi procedure để cập nhật giá sách S12345 thành 350.0
CALL capnhat('S12345', 350.0);


5.3 Viết câu truy vấn cho biết sách nào bán chạy nhất
-- Tính tổng số lượng bán cho từng sách
SELECT s.masach, s.tensach, SUM(ct.soluong) AS tongsoluong
FROM chitiethd ct
JOIN sach s ON ct.masach = s.masach
GROUP BY s.masach, s.tensach
ORDER BY tongsoluong DESC   -- Sắp xếp giảm dần theo tổng số lượng
LIMIT 1;                    -- Lấy sách bán chạy nhất


5.4 Tạo view top_10_ban_chay để lưu 10 sách bán chạy nhất
CREATE VIEW top_10_ban_chay AS
SELECT s.masach, s.tensach, SUM(ct.soluong) AS tongsoluong
FROM chitiethd ct
JOIN sach s ON ct.masach = s.masach
GROUP BY s.masach, s.tensach
ORDER BY tongsoluong DESC
LIMIT 10;


5.5 Sao lưu CSDL bookstore
-- Chạy trong CMD/Terminal, không phải MySQL CLI
-- Xuất toàn bộ dữ liệu và cấu trúc của database bookstore ra file .sql
mysqldump -u username -p bookstore > bookstore_backup.sql


5.6 Xóa toàn bộ database bookstore (cẩn thận vì không thể khôi phục nếu chưa backup)
DROP DATABASE bookstore;


5.7 Phục hồi CSDL bookstore
-- Chạy trong CMD/Terminal, không phải MySQL CLI
-- Nạp lại dữ liệu từ file backup vào database bookstore
mysql -u username -p bookstore < bookstore_backup.sql
</pre>
</body>

</html>