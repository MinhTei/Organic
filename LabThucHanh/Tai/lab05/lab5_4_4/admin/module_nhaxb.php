<?php
// Module quản lý nhà xuất bản
echo "<h2>Quản Lý Nhà Xuất Bản</h2>";
echo "<p>Trang quản lý nhà xuất bản</p>";
echo "<table border='1' cellpadding='8' cellspacing='0' width='100%'>";
echo "<tr><th>STT</th><th>Tên Nhà Xuất Bản</th><th>Địa Chỉ</th><th>Hành Động</th></tr>";
echo "<tr><td>1</td><td>NXB Kim Đồng</td><td>Hà Nội</td><td><a href='#'>Sửa</a> | <a href='#'>Xóa</a></td></tr>";
echo "<tr><td>2</td><td>NXB Trẻ</td><td>TP.HCM</td><td><a href='#'>Sửa</a> | <a href='#'>Xóa</a></td></tr>";
echo "</table>";
echo "<br /><a href='index.php?mod=nhaxb&ac=add'>Thêm nhà xuất bản mới</a>";
