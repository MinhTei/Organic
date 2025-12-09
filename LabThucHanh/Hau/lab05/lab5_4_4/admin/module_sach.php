<?php
// Module quản lý sách
echo "<h2>Quản Lý Danh Mục Sách</h2>";
echo "<p>Trang quản lý danh mục sách</p>";
echo "<table border='1' cellpadding='8' cellspacing='0' width='100%'>";
echo "<tr><th>STT</th><th>Tên Sách</th><th>Tác Giả</th><th>Giá</th><th>Hành Động</th></tr>";
echo "<tr><td>1</td><td>Lập trình PHP</td><td>Nguyễn A</td><td>150,000đ</td><td><a href='#'>Sửa</a> | <a href='#'>Xóa</a></td></tr>";
echo "<tr><td>2</td><td>Web Design</td><td>Trần B</td><td>200,000đ</td><td><a href='#'>Sửa</a> | <a href='#'>Xóa</a></td></tr>";
echo "</table>";
echo "<br /><a href='index.php?mod=sach&ac=add'>Thêm sách mới</a>";
