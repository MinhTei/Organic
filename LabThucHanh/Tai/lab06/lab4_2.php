<?php
// Hàm lấy dữ liệu từ form POST
function postIndex($index, $value = "")
{
  if (!isset($_POST[$index])) return $value;
  return trim($_POST[$index]);
}

// Hàm kiểm tra tính hợp lệ của username
function checkUserName($string)
{
  if (preg_match("/^[a-zA-Z0-9._-]*$/", $string))
    return true;
  return false;
}

// Hàm kiểm tra định dạng email
function checkEmail($string)
{
  if (preg_match("/^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+\.[a-zAZ.]{2,5}$/", $string))
    return true;
  return false;
}

// Hàm kiểm tra mật khẩu
function checkPassword($string)
{
  if (preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/", $string))
    return true;
  return false;
}

// Hàm kiểm tra số điện thoại
function checkPhone($string)
{
  if (preg_match("/^\d{10}$/", $string))
    return true;
  return false;
}

// Hàm kiểm tra định dạng ngày sinh
function checkDateOfBirth($string)
{
  if (preg_match("/^(0[1-9]|[12][0-9]|3[01])[-\/](0[1-9]|1[0-2])[-\/]\d{4}$/", $string))
    return true;
  return false;
}

// Lấy dữ liệu từ form
$username = postIndex("username");
$email = postIndex("email");
$password = postIndex("password");
$phone = postIndex("phone");
$dateOfBirth = postIndex("date");

$errors = []; // Mảng lưu các lỗi

// Kiểm tra hợp lệ của các thông tin
if (isset($_POST['submit'])) {
    
    // Kiểm tra username hợp lệ
    if (checkUserName($username) == false) {
        $errors[] = "Username: Các ký tự được phép: a-z, A-Z, số 0-9, ký tự ., _ và - <br>";
    }

    // Kiểm tra mật khẩu hợp lệ
    if (checkPassword($password) == false) {
        $errors[] = "Mật khẩu phải có ít nhất 8 ký tự, chứa ít nhất một ký tự số, một ký tự hoa và một ký tự thường.<br>";
    }

    // Kiểm tra email hợp lệ
    if (checkEmail($email) == false) {
        $errors[] = "Định dạng email sai!<br>";
    }

    // Kiểm tra ngày sinh hợp lệ
    if (checkDateOfBirth($dateOfBirth) == false) {
        $errors[] = "Ngày sinh phải có định dạng dd/mm/yyyy hoặc dd-mm-yyyy!<br>";
    }

    // Kiểm tra số điện thoại hợp lệ
    if (checkPhone($phone) == false) {
        $errors[] = "Số điện thoại phải chỉ chứa số và có 10 chữ số!<br>";
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" ...>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bài 6_4_2</title>
  <!-- Thêm Bootstrap CSS từ CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }

    .form-container {
      max-width: 600px;
      margin: 50px auto;
      padding: 30px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }

    .info {
      color: red;
    }

    .success {
      color: green;
      font-weight: bold;
    }
    .errors{
        color: red;
        font-weight: bold;
    }
  </style>
</head>

<body>
  <div class="form-container">
    <h1 class="text-center">Đăng ký thông tin</h1>
    <form action="lab4_2.php" method="post" enctype="multipart/form-data" id="frm1">
      <div class="mb-3">
        <label for="username" class="form-label">UserName</label>
        <input type="text" class="form-control" id="username" name="username" value="<?php echo $username; ?>" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Mật khẩu</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>
      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="showPassword" onclick="displayPass()">
        <label class="form-check-label" for="showPassword">Hiển thị mật khẩu</label>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>"required>
      </div>
      <div class="mb-3">
        <label for="date" class="form-label">Ngày sinh</label>
        <input type="text" class="form-control" id="date" name="date" value="<?php echo $dateOfBirth; ?>" required>
      </div>
      <div class="mb-3">
        <label for="phone" class="form-label">Điện thoại</label>
        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $phone; ?>" required>
      </div>
      <div class="text-center">
        <button type="submit" class="btn btn-primary" name="submit">Đăng ký</button>
      </div>
    </form>

    <?php
    if (isset($_POST['submit'])) {
      if (count($errors) > 0) {
    ?>  
        <div class="errors mt-3"> <h1>Lỗi !</h1></div>
        <div class="info mt-3">
          <?php
          foreach ($errors as $error) {
              echo "-". $error;
          }
          ?>
        </div>
      <?php
      } else {
      ?>
        <div class="success mt-3"> <h1>Đăng ký thành công!</h1></div>
      <?php
      }
    }
    ?>
  </div>

  <!-- Thêm Bootstrap JS và Popper.js từ CDN -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>

  <script>
    // Hàm hiện mật khẩu bằng JS
    function displayPass() {
      var passwordField = document.getElementById('password');
      var checkbox = document.querySelector('input[type="checkbox"]');
      if (checkbox.checked) {
        passwordField.type = 'text'; // Hiển thị mật khẩu
      } else {
        passwordField.type = 'password'; // Ẩn mật khẩu
      }
    }
  </script>
</body>

</html>
