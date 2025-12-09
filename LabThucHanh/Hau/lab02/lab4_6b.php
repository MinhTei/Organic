<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Lab4_6B</title>
</head>

<body>
<?php
	require("lab2_5a.php");
	require("lab2_5b.php");
	require("lab2_5b.php");
	//Sau khi xóa file lab2_5b.php thì kết quả sẽ xuất ra màn hình là những Lỗi nghiệm trọng (Fatal error) do không tìm được file nhúng vào. Và chương trình dừng lại
	if(isset($x))
		echo "Giá trị của x là: $x";
	else
		echo "Biến x không tồn tại";
echo"<br/><i/> Kết quả vẫn giống như lab4_6 vẫn bằng 30";

?>
</body>
</html>