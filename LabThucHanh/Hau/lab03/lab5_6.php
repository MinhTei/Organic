<!DOCTYPE html>
<html>

<head>
    <title>Lab5_6</title>
</head>

<body>

    <h2>Loại Bỏ Khoảng Trắng Dư Thừa Trong Chuỗi</h2>
    <form action="" method="post">
        <input type="text" name="input_string"
            required size="50"
            value="<?php echo isset($_POST['input_string']) ? htmlspecialchars($_POST['input_string']) : ''; ?>">
        <input type="submit" value="Xử Lý">
    </form>

    <?php

    /**
     * Hàm loại bỏ các khoảng trắng dư thừa trong một chuỗi.
     * * @param string $str Chuỗi đầu vào.
     * @return string Chuỗi đã được chuẩn hóa khoảng trắng.
     */
    function removeExtraSpaces($str)
    {
        // 1. Thay thế nhiều khoảng trắng liên tiếp (\s+) bằng một khoảng trắng (' ')
        // '/\s+/' là biểu thức chính quy (Regex) tìm kiếm một hoặc nhiều khoảng trắng.
        $str = preg_replace('/\s+/', ' ', $str);

        // 2. Loại bỏ khoảng trắng ở đầu và cuối chuỗi bằng hàm trim()
        $str = trim($str);

        return $str;
    }

    // --- Phần Xử lý Input từ Form ---

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['input_string'])) {

        $input_str = $_POST['input_string'];

        // Gọi hàm xử lý chuỗi
        $processed_str = removeExtraSpaces($input_str);

        echo "<hr>";
        echo "<h3>Kết quả:</h3>";
        echo "<p>Chuỗi gốc: '{$input_str}'</p>";
        echo "<p>Chuỗi đã chuẩn hóa: '{$processed_str}'</p>";
    }
    ?>

</body>

</html>