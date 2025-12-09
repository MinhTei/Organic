<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài 4.3</title>
</head>
<style>
    .div {
        width: fit-content;
        margin: 0 auto;
    }

    fieldset {
        background: whitesmoke;
        width: fit-content;
        padding: 20px;
    }
</style>

<body>
    <h2>Nguyễn Trung Hậu - DH52200651 - D22_TH07</h2>
    <hr>
    <div class="div">
        <h2 align="center">Đăng ký thành viên</h2>
        <fieldset>
            <form action="" method="post" enctype="multipart/form-data">
                <label for="username"><strong>Tên đăng nhập (*):</strong></label>
                <input type="text" name="ten" id="username" required>
                <br>
                <br>
                <label for="pass"><strong>Mật khẩu (*):</strong></label>
                <input type="password" name="matkhau" id="pass" required>
                <br>
                <br>
                <label for="repass"><strong>Nhập lại mật khẩu (*):</strong></label>
                <input type="password" name="nhaplaimatkhau" id="repass" required>
                <br>
                <br>
                <strong>Giới tính (*):</strong>
                <input type="radio" name="gioitinh" id="nam" value="Nam" required>
                <label for="nam">Nam</label>
                <input type="radio" name="gioitinh" id="nu" value="Nữ" required>
                <label for="nu">Nữ</label>
                <br>
                <br>
                <label for="st"><strong>Sở thích:</strong></label>
                <br>
                <textarea name="sothich" id="st" cols="30" rows="5"></textarea>
                <br>
                <br>
                <label for="image">Hình ảnh (tùy chọn):</label>
                <input type="file" id="image" name="hinhanh" accept="image/*">
                <br>
                <br>
                <label for="tinh"><strong>Tỉnh (*):</strong></label>
                <select name="tinh" id="tỉnh" required>
                    <option value="">Chọn tỉnh</option>
                    <option value="Hà Nội">Hà Nội</option>
                    <option value="Hồ Chí Minh">Hồ Chí Minh</option>
                    <option value="Nha Trang">Nha Trang</option>
                    <option value="Cần Thơ">Cần Thơ</option>
                    <option value="Đồng Nai">Đồng Nai</option>
                </select>
                <br>
                <br>
                <input type="submit" value="Gửi">
                <input type="reset" value="Nhập lại">
            </form>
        </fieldset>
    </div>
</body>
<?php
//Kiểm tra thông tin có được gửi chưa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = $_POST['ten'] ?? '';
    $matkhau = $_POST['matkhau'] ?? '';
    $nhaplaimatkhau = $_POST['nhaplaimatkhau'] ?? '';
    $gioitinh = $_POST['gioitinh'] ?? '';
    $sothich = $_POST['sothich'] ?? '';
    $tinh = $_POST['tinh'] ?? '';
    $errors = [];
    //Kiểm tra nhập dữ liệu
    if (empty($ten))
        $errors[] = "Tên đăng nhập không được để trống.";
    if (empty($matkhau))
        $errors[] = "Mật khẩu không được để trống.";
    if (empty($nhaplaimatkhau))
        $errors[] = "Nhập lại mật khẩu không được bỏ trống.";
    if (!empty($matkhau) && !empty($nhaplaimatkhau) && $matkhau !== $nhaplaimatkhau)
        $errors[] = "Mật khẩu và nhập lại mật khẩu không trùng nhau.";
    if (empty($gioitinh))
        $errors[] = "Bạn chưa chọn giới tính.";
    if (empty($tinh))
        $errors[] = "Bạn chưa chọn tỉnh thành.";

    //Xử lý hình ảnh chỉ xử lý khi có thêm hình
    $imageName = ""; // Dùng để lưu đường dẫn file ảnh khi được upload
    $luuhinh = "";
    if (!empty($_FILES['hinhanh']['name'])) {
        $image = $_FILES['hinhanh'];
        $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
        //pathinfo($image['name'], PATHINFO_EXTENSION) → lấy phần mở rộng file (ví dụ "jpg", "png").
        //strtolower(...) → chuyển về chữ thường để tiện kiểm tra (JPG → jpg).
        $allowed = ['jpg', 'png', 'bmp', 'gif'];
        if (!in_array($ext, $allowed)) {
            $errors[] = "Hình ảnh phải có đuôi jpg, png, bmp hoặc gif.";
        } else {
            $uploadDir = "image/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $imageName =  basename($image['name']); //basename($image['name']) → lấy tên file gốc (không có đường dẫn).
            $luuhinh = $uploadDir . $imageName; // Có đường dẫn để lưu vào thư mục image
            move_uploaded_file($image['tmp_name'], $luuhinh); //chuyển file từ tạm thời (tmp_name) sang thư mục đích (uploads/).
        }
    }
    //Hiển thị kết quả
    if (!empty($errors)) { //Nếu có dữ liệu trong mảng lỗi
        echo "<div style='text-algin: center';>";
        echo "<h3>Dữ liệu không hợp lệ </h3>";
        foreach ($errors as $err) {
            echo "<p>-$err</p>";
        }
        echo "</div>";
    } else {
        echo "<table cellpadding='8' cellspacing='0' style='border-collapse: collapse; margin: 0 auto;'>";
        echo "<tr ><th colspan='2'><h3>Thông tin người dùng</h3></th></tr>";
        echo "<tr><td><strong>Tên đăng nhập:</strong></td><td>$ten</td></tr>";
        echo "<tr><td><strong>Mật khẩu:</strong></td><td>$matkhau</td></tr>";
        echo "<tr><td><strong>Giới tính:</strong></td><td>$gioitinh</td></tr>";
        echo "<tr><td><strong>Sở thích:</strong></td><td>$sothich</td></tr>";
        echo "<tr><td><strong>Tỉnh thành:</strong></td><td>$tinh</td></tr>";
        if (!empty($luuhinh)) {
            echo "<tr><td><strong>Hình ảnh:</strong></td><td>$imageName</td></tr>";
            echo "<tr><td colspan='2' style='text-align: center'><img src='$luuhinh' width='200' alt='Hình ảnh người dùng'/></td></tr>";
        } else {
            echo "<tr><td><strong>Hình ảnh:</strong></td><td>Chưa cập nhật.</td></tr>";
        }
        echo "</table>";
    }
}
?>

</html>