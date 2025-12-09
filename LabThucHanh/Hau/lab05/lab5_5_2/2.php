<?php
function postIndex($index, $value = "")
{
	if (!isset($_POST[$index]))	return $value;
	return $_POST[$index];
}

$sm 	= postIndex("submit");
$ten 	= postIndex("ten");
$gt 	= postIndex("gt");
$arrImg = array("image/png", "image/jpeg", "image/bmp");

if ($sm == "") {
	header("location:1.php");
	exit; //quay ve 1.php
}

$err = "";
if ($ten == "") $err .= "Phải nhập tên <br>";
if ($gt == "") $err .= "Phải chọn giới tính <br>";

$uploadedFiles = array();

// Xử lý nhiều file upload
if (isset($_FILES["hinh"]["error"]) && is_array($_FILES["hinh"]["error"])) {
	$fileCount = count($_FILES["hinh"]["error"]);
	for ($i = 0; $i < $fileCount; $i++) {
		$errFile = $_FILES["hinh"]["error"][$i];

		// Bỏ qua các file không được chọn
		if ($errFile == UPLOAD_ERR_NO_FILE) {
			continue;
		}

		if ($errFile > 0) {
			$err .= "Lỗi file hình (file #" . ($i + 1) . ") <br>";
			continue;
		}

		$type = $_FILES["hinh"]["type"][$i];
		if (!in_array($type, $arrImg)) {
			$err .= "File #" . ($i + 1) . " không phải file hình <br>";
			continue;
		}

		$temp = $_FILES["hinh"]["tmp_name"][$i];
		$name = $_FILES["hinh"]["name"][$i];

		if (!move_uploaded_file($temp, "image/" . $name)) {
			$err .= "Không thể lưu file #" . ($i + 1) . "<br>";
		} else {
			$uploadedFiles[] = $name;
		}
	}

	if (empty($uploadedFiles) && $err == "") {
		$err .= "Vui lòng chọn ít nhất một file hình <br>";
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Lab5_3/2</title>
</head>

<body>
	<?php
	if ($err != "")
		echo $err;
	else {
		if ($gt == "1") echo "Chào Anh: $ten ";
		else echo "Chào Chị $ten ";
	?>
		<hr>
		<?php
		foreach ($uploadedFiles as $img) {
			echo '<img src="image/' . $img . '" style="max-width:300px; margin:10px;"><br>';
		}
		?>
	<?php
	}
	?>
	<p>
		<a href="1.php">Tiếp tục</a>
	</p>
</body>

</html>