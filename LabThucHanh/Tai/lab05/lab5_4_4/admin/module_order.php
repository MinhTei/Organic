<?php
// Module quản lý đơn hàng
echo "<h2>Quản Lý Đơn Hàng</h2>";
echo "<p>Trang quản lý đơn hàng</p>";
echo "<table border='1' cellpadding='8' cellspacing='0' width='100%'>";
echo "<tr><th>STT</th><th>Mã Đơn</th><th>Tên Khách</th><th>Ngày Đặt</th><th>Trạng Thái</th><th>Hành Động</th></tr>";
echo "<tr><td>1</td><td>DH001</td><td>Nguyễn Văn A</td><td>2025-01-10</td><td>Đã giao</td><td><a href='#'>Xem</a> | <a href='#'>Xóa</a></td></tr>";
echo "<tr><td>2</td><td>DH002</td><td>Trần Thị B</td><td>2025-01-11</td><td>Chờ xử lý</td><td><a href='#'>Xem</a> | <a href='#'>Xóa</a></td></tr>";
echo "</table>";
echo "<br /><a href='index.php?mod=order&ac=view'>Xem chi tiết</a>";
