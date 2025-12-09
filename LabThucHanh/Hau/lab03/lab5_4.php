<!DOCTYPE html>
<html>

<head>
    <title>Hình Chữ Nhật Rỗng</title>
    <style>
        pre {
            font-family: monospace;
            font-size: 1.2em;
            line-height: 1.2;
        }

        .error {
            color: red;
        }
    </style>
</head>

<body>

    <h2>Nhập Kích Thước Hình Chữ Nhật</h2>
    <form action="" method="post">
        <label for="chieu_dai">Chiều Dài (d):</label>
        <input type="number" name="chieu_dai" id="chieu_dai" min="1" required value="<?php echo isset($_POST['chieu_dai']) ? htmlspecialchars($_POST['chieu_dai']) : ''; ?>">
        <br><br>
        <label for="chieu_rong">Chiều Rộng (r):</label>
        <input type="number" name="chieu_rong" id="chieu_rong" min="1" required value="<?php echo isset($_POST['chieu_rong']) ? htmlspecialchars($_POST['chieu_rong']) : ''; ?>">
        <br><br>
        <input type="submit" value="Vẽ Hình">
    </form>

    <?php

    function drawHollowRectangle($d, $r)
    {
        // Kiểm tra đầu vào hợp lệ
        if ($d < 1 || $r < 1) {
            echo "<p class='error'>Chiều dài và chiều rộng phải là số nguyên dương.</p>";
            return;
        }

        // Bắt đầu khối <pre> để đảm bảo khoảng trắng hiển thị chính xác
        echo "<pre>\n";

        // Vòng lặp ngoài: Duyệt qua từng hàng (chiều rộng r)
        for ($i = 1; $i <= $r; $i++) {

            // Vòng lặp trong: Duyệt qua từng cột (chiều dài d)
            for ($j = 1; $j <= $d; $j++) {

                // Điều kiện để in ký tự '*':
                // 1. Hàng đầu tiên ($i == 1)
                // 2. Hàng cuối cùng ($i == $r)
                // 3. Cột đầu tiên ($j == 1)
                // 4. Cột cuối cùng ($j == $d)

                if ($i == 1 || $i == $r || $j == 1 || $j == $d) {
                    echo "* "; // In ký tự '*' và một khoảng trắng
                } else {
                    echo "  "; // In hai khoảng trắng để căn chỉnh với "* "
                }
            }
            echo "\n"; // Xuống dòng khi kết thúc một hàng
        }

        echo "</pre>";
    }

    // --- Xử lý Input từ Form ---

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $chieu_dai = (int)$_POST['chieu_dai'];
        $chieu_rong = (int)$_POST['chieu_rong'];

        echo "<hr>";
        echo "<h3>Kết quả hình chữ nhật {$chieu_dai}x{$chieu_rong}:</h3>";

        drawHollowRectangle($chieu_dai, $chieu_rong);
    }
    ?>

</body>

</html>