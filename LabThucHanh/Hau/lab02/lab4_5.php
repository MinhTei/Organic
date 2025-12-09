<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Lab4_5</title>
</head>

<body>
<?php
	include("lab2_5a.php");
	include("lab2_5b.php");

	if(isset($x))
		echo "Giá trị của x là: $x";
	else
		echo "Biến x không tồn tại";
echo"<br/><i/> Kết quả sẽ là Giá trị của x là 20. Vì lúc đầu file lab2_5a.php khai báo x=10 nhưng sau khi ta nhúng thêm file lab2_5b.php bên dưới thì lúc này x sẽ cập nhật x+=10 nên sẽ có giá trị là 20 "
?>
</body>
</html>