<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File htaccess</title>
</head>
<body>
    <pre>
        # Bắt đầu file .htaccess

## 1. Rewrite URL
# Bật engine rewrite
RewriteEngine On

# Định nghĩa quy tắc rewrite
# Chuyển đổi path/index.php?mod=news&id=1 thành path/news/1.html
# Quy tắc này giả định rằng 'news' là giá trị cố định cho tham số 'mod' và '1' là giá trị cho tham số 'id'.
# Nếu bạn muốn tổng quát hơn cho nhiều module, bạn cần cung cấp thêm thông tin về cấu trúc URL mong muốn.
# Dưới đây là quy tắc tổng quát:
# Chuyển đổi: path/MOD_NAME/ID.html -> path/index.php?mod=MOD_NAME&id=ID
RewriteRule ^([a-zA-Z0-9_-]+)/([0-9]+)\.html$ index.php?mod=$1&id=$2 [L,QSA]

---

## 2. Chặn các máy có địa chỉ IP bắt đầu: 234.45
# Sử dụng module mod_authz_core (cho Apache 2.4+) hoặc mod_authz_host (cho Apache 2.2)

<IfModule mod_authz_core>
    Require all granted
    # Chặn tất cả IP bắt đầu 234.45
    Require not ip 234.45
</IfModule>

# Nếu bạn dùng Apache cũ hơn (ví dụ 2.2), dùng đoạn này:
# <IfModule mod_authz_host>
#     Order allow,deny
#     Allow from all
#     Deny from 234.45
# </IfModule>

---

## 3. Chặn Hotlink (Ngăn các website khác nhúng hình ảnh)
# Thay your-domain.com bằng tên miền thực tế của bạn
RewriteCond %{HTTP_REFERER} !^$
RewriteCond %{HTTP_REFERER} !^https?://(www\.)?your-domain\.com [NC]
RewriteRule \.(jpe?g|png|gif|bmp)$ - [F]

---

## 4. Thay đổi trang chủ mặc định (DirectoryIndex)
# Đặt myindex.php là file mặc định khi truy cập thư mục gốc
DirectoryIndex myindex.php index.php

---

## 5. Cấm duyệt cây thư mục (Disable Directory Listing)
# Ngăn người dùng xem danh sách file khi truy cập một thư mục không có file index
Options -Indexes

---

## 6. Đặt mật khẩu bảo vệ thư mục
# Đây là phần cấu hình cho việc bảo vệ thư mục. 
# Bạn cần tạo thêm một file .htpasswd chứa tên người dùng và mật khẩu đã mã hóa.
# Đặt đoạn code này vào file .htaccess, hoặc tốt nhất là vào một file .htaccess riêng trong thư mục cần bảo vệ.

# AuthType Basic
# AuthName "Restricted Access"
# AuthUserFile /path/to/.htpasswd  # Thay bằng đường dẫn tuyệt đối đến file .htpasswd của bạn
# Require valid-user

# Kết thúc file .htaccess
    </pre>
</body>
</html>