<?php
// Module quản lý thông tin người dùng
echo "<h2>Quản Lý Thông Tin Người Dùng</h2>";
echo "<p>Trang quản lý thông tin người dùng</p>";
echo "<table border='1' cellpadding='8' cellspacing='0' width='100%'>";
echo "<tr><th>STT</th><th>Tên Đăng Nhập</th><th>Email</th><th>Ngày Tạo</th><th>Hành Động</th></tr>";
echo "<tr><td>1</td><td>user01</td><td>user01@example.com</td><td>2024-12-01</td><td><a href='#'>Xem</a> | <a href='#'>Sửa</a> | <a href='#'>Xóa</a></td></tr>";
echo "<tr><td>2</td><td>user02</td><td>user02@example.com</td><td>2024-12-15</td><td><a href='#'>Xem</a> | <a href='#'>Sửa</a> | <a href='#'>Xóa</a></td></tr>";
echo "</table>";
echo "<br /><a href='index.php?mod=user&ac=add'>Thêm người dùng mới</a>";
