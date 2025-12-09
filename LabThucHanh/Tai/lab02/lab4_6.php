<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Lab4_6</title>
</head>

<body>
<?php
	include("lab2_5a.php");
	include("lab2_5b.php");
	include("lab2_5b.php");
	if(isset($x))
		echo "Giá trị của x là: $x";
	else
		echo "Biến x không tồn tại";
echo"<br/><i/> Kết quả sẽ là Giá trị của x là 30. Vì lúc đầu file lab2_5a.php khai báo x=10 
nhưng sau khi ta nhúng thêm 2 file lab2_5b.php bên dưới thì lúc này x sẽ cập nhật x+=10 2 lần nên sẽ có giá trị là 30. ";

echo"<br/><i/> Sau khi xóa file lab2_5b.php thì kết quả sẽ xuất ra màn hình là những Cảnh báo Warning do không tìm được file nhúng vào nhưng chương trình vẫn chạy và kết quả là 10 được nhúng từ file lab2_5.php."
?>
</body>
</html>