<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Lab4_3</title>
</head>

<body>
<?php
$a=1;
$b=2;
$x="1";
$s1="Xin";
$s2= "chào";
$s=$s1." ".$s2;//ghép chuỗi
echo "a + b = ".($a+$b)."<br/>";
echo "a + x = ".($a+$x)."<br/>";
echo "x + a = ".($x+$a)."<br/>";
echo "Ghép chuỗi: $s"."<br/>";
echo "Phân biệt == và === ";
echo"<br>";
if($a==$x)
	echo "a và x giống nhau";
else 
	echo "a và x khác nhau";
echo "<br/><i/> Kết quả hiển thị a và x khác nhau là do ở điều kiện if ta đang dùng dấu === để so sánh vì sẽ so sánh luôn kiểu dữ liệu ";
echo "<br/><i/> Nếu muốn a và x giống nhau ta chỉ cần thay thế dấu === thành dấu ==";
?>
</body>
</html>