<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài 5.5.3</title>
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
        <div id="jsErrors" style="display:none; color:red; border:1px solid red; padding:10px; margin-bottom:10px; background-color:#ffe6e6;"></div>
        <fieldset>
            <form id="dangKyThanhCong" action="" method="post" enctype="multipart/form-data" onsubmit="validateForm(event)">
                <label for="username"><strong>Tên đăng nhập (*):</strong></label>
                <input type="text" name="ten" id="username">
                <span id="tenError" style="color:red;"></span>
                <br>
                <br>
                <label for="pass"><strong>Mật khẩu (*):</strong></label>
                <input type="password" name="matkhau" id="pass">
                <span id="passError" style="color:red;"></span>
                <br>
                <br>
                <label for="repass"><strong>Nhập lại mật khẩu (*):</strong></label>
                <input type="password" name="nhaplaimatkhau" id="repass">
                <span id="repassError" style="color:red;"></span>
                <br>
                <br>
                <strong>Giới tính (*):</strong>
                <input type="radio" name="gioitinh" id="nam" value="Nam">
                <label for="nam">Nam</label>
                <input type="radio" name="gioitinh" id="nu" value="Nữ">
                <label for="nu">Nữ</label>
                <span id="gioitinhError" style="color:red;"></span>
                <br>
                <br>
                <label for="st"><strong>Sở thích:</strong></label>
                <br>
                <textarea name="sothich" id="st" cols="30" rows="5"></textarea>
                <br>
                <br>
                <label for="image">Hình ảnh (tùy chọn):</label>
                <input type="file" id="image" name="hinhanh" accept="image/*">
                <span id="imageError" style="color:red;"></span>
                <br>
                <br>
                <label for="tinh"><strong>Tỉnh (*):</strong></label>
                <select name="tinh" id="tinh">
                    <option value="">Chọn tỉnh</option>
                    <option value="Hà Nội">Hà Nội</option>
                    <option value="Hồ Chí Minh">Hồ Chí Minh</option>
                    <option value="Nha Trang">Nha Trang</option>
                    <option value="Cần Thơ">Cần Thơ</option>
                    <option value="Đồng Nai">Đồng Nai</option>
                </select>
                <span id="tinhError" style="color:red;"></span>
                <br>
                <br>
                <input type="submit" value="Gửi">
                <input type="reset" value="Nhập lại">
            </form>
        </fieldset>
    </div>

    <script>
        function validateForm(event) {
            // Ngăn chặn gửi form mặc định
            event.preventDefault();

            // Xóa các lỗi trước đó
            clearErrors();
            let isValid = true;
            const errors = [];

            // Lấy giá trị từ form
            const ten = document.getElementById('username').value.trim();
            const matkhau = document.getElementById('pass').value;
            const nhaplaimatkhau = document.getElementById('repass').value;
            const gioitinh = document.querySelector('input[name="gioitinh"]:checked');
            const tinh = document.getElementById('tinh').value.trim();
            const imageInput = document.getElementById('image');

            // Kiểm tra tên đăng nhập
            if (ten === '') {
                showError('tenError', 'Tên đăng nhập không được để trống.');
                isValid = false;
            } else if (ten.length < 3) {
                showError('tenError', 'Tên đăng nhập phải ít nhất 3 ký tự.');
                isValid = false;
            }

            // Kiểm tra mật khẩu
            if (matkhau === '') {
                showError('passError', 'Mật khẩu không được để trống.');
                isValid = false;
            } else if (matkhau.length < 6) {
                showError('passError', 'Mật khẩu phải ít nhất 6 ký tự.');
                isValid = false;
            }

            // Kiểm tra nhập lại mật khẩu
            if (nhaplaimatkhau === '') {
                showError('repassError', 'Nhập lại mật khẩu không được bỏ trống.');
                isValid = false;
            } else if (matkhau !== nhaplaimatkhau) {
                showError('repassError', 'Mật khẩu và nhập lại mật khẩu không trùng nhau.');
                isValid = false;
            }

            // Kiểm tra giới tính
            if (!gioitinh) {
                showError('gioitinhError', 'Bạn chưa chọn giới tính.');
                isValid = false;
            }

            // Kiểm tra tỉnh thành
            if (tinh === '') {
                showError('tinhError', 'Bạn chưa chọn tỉnh thành.');
                isValid = false;
            }

            // Kiểm tra hình ảnh nếu được chọn
            if (imageInput.files.length > 0) {
                const file = imageInput.files[0];
                const allowedExtensions = ['jpg', 'jpeg', 'png', 'bmp', 'gif'];
                const fileExtension = file.name.split('.').pop().toLowerCase();

                if (!allowedExtensions.includes(fileExtension)) {
                    showError('imageError', 'Hình ảnh phải có đuôi jpg, png, bmp hoặc gif.');
                    isValid = false;
                }

                // Kiểm tra kích thước file (tối đa 5MB)
                const maxSize = 5 * 1024 * 1024;
                if (file.size > maxSize) {
                    showError('imageError', 'Kích thước file phải nhỏ hơn 5MB.');
                    isValid = false;
                }
            }

            // Nếu hợp lệ, gửi form
            if (isValid) {
                document.getElementById('dangKyThanhCong').submit();
            } else {
                // Hiển thị container lỗi
                document.getElementById('jsErrors').style.display = 'block';
                document.getElementById('jsErrors').innerHTML = '<strong>Vui lòng kiểm tra các lỗi dưới đây:</strong>';
            }
        }

        function showError(elementId, message) {
            const errorElement = document.getElementById(elementId);
            if (errorElement) {
                errorElement.textContent = message;
                errorElement.style.display = 'inline';
            }
        }

        function clearErrors() {
            const errorElements = document.querySelectorAll('[id$="Error"]');
            errorElements.forEach(element => {
                element.textContent = '';
                element.style.display = 'none';
            });
            document.getElementById('jsErrors').style.display = 'none';
        }

        // Xóa lỗi khi người dùng nhập liệu
        document.getElementById('username').addEventListener('blur', function() {
            if (this.value.trim() !== '') {
                document.getElementById('tenError').textContent = '';
            }
        });

        document.getElementById('pass').addEventListener('blur', function() {
            if (this.value !== '') {
                document.getElementById('passError').textContent = '';
            }
        });

        document.getElementById('repass').addEventListener('blur', function() {
            if (this.value !== '') {
                document.getElementById('repassError').textContent = '';
            }
        });

        document.getElementById('tinh').addEventListener('change', function() {
            if (this.value !== '') {
                document.getElementById('tinhError').textContent = '';
            }
        });
    </script>
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

    // Xử lý hình ảnh chỉ xử lý khi có thêm hình
    $imageName = ""; // Dùng để lưu đường dẫn file ảnh khi được upload
    $luuhinh = "";
    if (!empty($_FILES['hinhanh']['name'])) {
        $image = $_FILES['hinhanh'];
        $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
        // pathinfo($image['name'], PATHINFO_EXTENSION) → lấy phần mở rộng file (ví dụ "jpg", "png").
        // strtolower(...) → chuyển về chữ thường để tiện kiểm tra (JPG → jpg).
        $allowed = ['jpg', 'png', 'bmp', 'gif'];
        if (!in_array($ext, $allowed)) {
            $errors[] = "Hình ảnh phải có đuôi jpg, png, bmp hoặc gif.";
        } else {
            $uploadDir = "image/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $imageName =  basename($image['name']); // basename($image['name']) → lấy tên file gốc (không có đường dẫn).
            $luuhinh = $uploadDir . $imageName; // Có đường dẫn để lưu vào thư mục image
            move_uploaded_file($image['tmp_name'], $luuhinh); // Chuyển file từ tạm thời (tmp_name) sang thư mục đích (image/).
        }
    }
    // Hiển thị kết quả
    if (!empty($errors)) { // Nếu có dữ liệu trong mảng lỗi
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