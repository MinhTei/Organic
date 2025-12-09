<><?php
$a = array(1, -3, 5); //mảng có 3 phần tử
$b = array("a"=>2, "b"=>4, "c"=>-6);//mảng có 3 phần tử.Các index của mảng là chuỗi
?>
Nội dung giá trị mảng a :
<?php
foreach($a as $value)
{
	echo $value ." ";	
}
?>
<br> Nôi dung mảng a (key-value) 
<?php
foreach($a as $key=>$value)
{
	echo "($key - $value )";	
}
?>
<br /> Nội dung mảng b: (key - value):
<?php
foreach($b as $k=>$v)
{
	echo "($k - $v) ";	
}
?>
<br />Hiển thị nội dung mảng b ra dạng bảng:
<table border=1>
	<tr><td>STT</td><td>Key</td><td>Value</td></tr>
    <?php
	$i=0;
	foreach($b as $k=>$v)
	{	$i++;
		echo "<tr><td>$i</td>";
		echo "<td>$k</td>";
		echo "<td>$v</td></tr>";
	}
	?>
</table>
<pre>
<h2>Đếm số lượng phần tử dương</h2>
<?php
print_r($a);
$dem=0;
foreach($a as $ptu){
	if($ptu> 0)
		$dem++;
}
echo "Số lượng phần tử dương trong mảng là: $dem";
?>

<h2>Lưu các phần tử dương sang mảng mới</h2>
<?php
echo"Mảng ban đầu: <br/>";
print_r($b);
$newArr=array();
foreach($b as $key=>$value){
	if($value > 0)
		$newArr[$key]=$value;
}
echo "Mảng các số dương: <br/>";
print_r($newArr);
?>
</pre>