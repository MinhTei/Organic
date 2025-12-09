<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Bài 5.5.1</title>
</head>

<body>
    <h2>Nguyễn Trung Hậu - DH52200651 - D22_TH07</h2>
    <hr>
    <?php
    // Hàm bảo mật: chuyển đổi ký tự đặc biệt sang thực thể HTML
    function h($s)
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }

    // Kiểm tra xem yêu cầu là POST hay không
    $isPost = $_SERVER['REQUEST_METHOD'] === 'POST';

    // Giá trị mặc định cho Form 1
    $form1_x = isset($_POST['x']) ? $_POST['x'] : '1';
    $form1_y = isset($_POST['y']) ? $_POST['y'] : '2';
    $form1_z = isset($_POST['z']) ? $_POST['z'] : '3';

    // Giá trị mặc định cho Form 2 (x là một mảng)
    if (isset($_POST['x']) && is_array($_POST['x'])) {
        $form2_x = $_POST['x'];
        // đảm bảo có ít nhất hai phần tử
        if (!isset($form2_x[0])) $form2_x[0] = '1';
        if (!isset($form2_x[1])) $form2_x[1] = '2';
    } else {
        $form2_x = array('1', '2');
    }
    $form2_y = isset($_POST['y']) ? $_POST['y'] : '3';

    // Giá trị mặc định cho Form 3
    $ten = isset($_POST['ten']) ? $_POST['ten'] : '';
    $gt = isset($_POST['gt']) ? (string)$_POST['gt'] : null; // '1' (Nam) hoặc '0' (Nữ)
    $st = (isset($_POST['st']) && is_array($_POST['st'])) ? $_POST['st'] : array(); // Mảng các sở thích

    // Xuất thông tin debug (hiển thị REQUEST/POST) và kết quả khi có POST
    echo "REQUEST:";
    print_r($_REQUEST);
    echo "<hr>POST<br>";
    print_r($_POST);

    ?>
    <hr>
    <a href="lab5_2.php?x=1&y=2&z=3">Link 1</a><br>
    <a href="lab5_2.php?x[]=1&x[]=2&y=3">Link 2</a><br>
    <a href="lab5_2.php?mod=product&ac=detail&id=1">Link 3</a><br>
    <a href="lab5_2.php?mod=product&ac=list&name=a&page=2">Link 4</a><br>
    <hr>
    <fieldset>
        <legend>Form 1</legend>
        <form action="<?php echo h($_SERVER['PHP_SELF']); ?>" method="post">
            Nhập x:<input type="text" name="x" value="<?php echo h($form1_x); ?>"><br>
            Nhập y:<input type="text" name="y" value="<?php echo h($form1_y); ?>"><br>
            Nhập z:<input type="text" name="z" value="<?php echo h($form1_z); ?>"><br>
            <input type="submit">
        </form>
    </fieldset>

    <fieldset>
        <legend>Form 2</legend>
        <form action="<?php echo h($_SERVER['PHP_SELF']); ?>" method="post">
            Nhập x1:<input type="text" name="x[]" value="<?php echo h($form2_x[0]); ?>"><br>
            Nhập x2:<input type="text" name="x[]" value="<?php echo h($form2_x[1]); ?>"><br>
            Nhập y:<input type="text" name="y" value="<?php echo h($form2_y); ?>"><br>
            <input type="submit">
        </form>
    </fieldset>

    <fieldset>
        <legend>Form 3</legend>
        <form action="<?php echo h($_SERVER['PHP_SELF']); ?>" method="post">
            Nhập tên:<input type="text" name="ten" value="<?php echo h($ten); ?>"><br>
            giới tính:<input type="radio" name="gt" value="1" <?php if ($gt === '1') echo 'checked'; ?>>Nam
            <input type="radio" name="gt" value="0" <?php if ($gt === '0') echo 'checked'; ?>>Nữ<br>
            Sở Thích:<input type="checkbox" name="st[]" value="tt" <?php if (in_array('tt', $st)) echo 'checked'; ?>>Thể Thao
            <input type="checkbox" name="st[]" value="dl" <?php if (in_array('dl', $st)) echo 'checked'; ?>>Du Lịch
            <input type="checkbox" name="st[]" value="game" <?php if (in_array('game', $st)) echo 'checked'; ?>>Game<br>
            <input type="submit">
        </form>
    </fieldset>
    <?php
    // Xác thực và hiển thị kết quả Form 3 (nếu có dữ liệu gửi lên liên quan đến Form 3)
    if ($isPost && (isset($_POST['ten']) || isset($_POST['gt']) || isset($_POST['st']))) {
        $errors = array();

        // Xác thực Tên
        $name_trim = trim((string)$ten);
        if ($name_trim === '') {
            $errors[] = 'Vui lòng nhập tên.';
        }

        // Xác thực Giới tính
        if ($gt !== '1' && $gt !== '0') {
            $errors[] = 'Vui lòng chọn giới tính.';
        }

        // Xác thực Sở thích
        if (empty($st) || !is_array($st)) {
            $errors[] = 'Vui lòng chọn ít nhất một sở thích.';
        }

        if (!empty($errors)) {
            // Hiển thị lỗi
            echo '<hr>';
            echo " <h2 style='color:red;'>Lỗi !</h2>";
            echo '<ul style="color:red;">';
            foreach ($errors as $err) {
                echo '<li>' . h($err) . '</li>';
            }
            echo '</ul>';
        } else {
            // Hiển thị kết quả thành công
            echo '<hr>';
            echo " <h2 style='color:green;'>Kết quả</h2>";
            echo '<strong>Tên:</strong> ' . h($name_trim) . '<br>';
            echo '<strong>Giới tính:</strong> ' . ($gt === '1' ? 'Nam' : 'Nữ') . '<br>';
            echo '<strong>Sở thích:</strong> ';
            $labels = array();
            // Chuyển giá trị sở thích thành nhãn tiếng Việt
            foreach ($st as $s) {
                if ($s === 'tt') $labels[] = 'Thể Thao';
                elseif ($s === 'dl') $labels[] = 'Du Lịch';
                elseif ($s === 'game') $labels[] = 'Game';
                else $labels[] = h($s); // Trường hợp sở thích không xác định
            }
            echo h(implode(', ', $labels));
        }
    }
    ?>
</body>

</html>