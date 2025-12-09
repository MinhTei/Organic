<?php
// Module quản lý loại sách
echo "<h2>Quản Lý Loại Sách</h2>";
echo "<p>Trang quản lý loại sách</p>";
echo "<table border='1' cellpadding='8' cellspacing='0' width='100%'>";
echo "<tr><th>STT</th><th>Tên Loại</th><th>Mô Tả</th><th>Hành Động</th></tr>";
echo "<tr><td>1</td><td>Lập trình</td><td>Các cuốn sách về lập trình</td><td><a href='#'>Sửa</a> | <a href='#'>Xóa</a></td></tr>";
echo "<tr><td>2</td><td>Thiết kế</td><td>Các cuốn sách về thiết kế</td><td><a href='#'>Sửa</a> | <a href='#'>Xóa</a></td></tr>";
echo "</table>";
echo "<br /><a href='index.php?mod=loai&ac=add'>Thêm loại sách mới</a>";
