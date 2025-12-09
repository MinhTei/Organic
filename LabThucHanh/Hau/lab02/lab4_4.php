<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Lab4_4</title>
</head>

<body>
<?php
	// include("lab2_5a.php");
	//Nếu comment dòng 10 thì kết quả sẽ xuất ra là Giá trị biến x không tồn tại. Do chúng ta lấy giá trị biến X từ file lab2_5a.php được nhúng vào.
	if(isset($x))
		echo "Giá trị của x là: $x";
	else
		echo "Biến x không tồn tại";
echo"<br/><i/>Nếu comment dòng 10 thì kết quả sẽ xuất ra là Giá trị biến x không tồn tại. Do chúng ta lấy giá trị biến X từ file lab2_5a.php được nhúng vào."
?>
</body>
</html>