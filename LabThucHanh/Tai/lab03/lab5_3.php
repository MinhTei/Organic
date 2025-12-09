<!DOCTYPE html>
<html>

<head>
    <title>Lab5_3</title>
</head>

<body>

    <h2>Nhập Chuỗi để Tính Tổng Các Chữ Số</h2>
    <form action="" method="post">
        <input type="text" name="input_string"
            placeholder="Ví dụ: ngay15thang7nam2015"
            required size="50">
        <input type="submit" value="Tính Tổng">
    </form>

    <?php

    /**
     * Hàm tính tổng các chữ số (0-9) có trong một chuỗi.
     * @param string $str Chuỗi đầu vào (có thể chứa cả chữ và số).
     * @return int Tổng của tất cả các chữ số tìm thấy trong chuỗi.
     */
    function sumDigitsInString($str)
    {
        $total_sum = 0;

        // Sử dụng biểu thức chính quy (Regular Expression) để tìm tất cả các chữ số.
        // '\d' là ký hiệu viết tắt cho [0-9].
        // $matches[0] sẽ lưu trữ mảng các chữ số được tìm thấy
        if (preg_match_all('/\d/', $str, $matches)) {
            $digits_array = $matches[0];

            // Tính tổng
            foreach ($digits_array as $digit) {
                // Ép kiểu chuỗi số thành số nguyên (int) trước khi cộng
                $total_sum += (int)$digit;
            }
        }

        return $total_sum;
    }

    // --- Phần Xử lý Input từ Form ---

    // Kiểm tra nếu form đã được gửi
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['input_string'])) {

        // Lấy chuỗi đã nhập
        $input_str = htmlspecialchars($_POST['input_string']);

        // Gọi hàm tính tổng
        $result_sum = sumDigitsInString($input_str);

        echo "<hr>";
        echo "<h3>Kết quả:</h3>";
        echo "<p>Chuỗi đã nhập: '{$input_str}'</p>";
        echo "<p >Tổng các chữ số tìm được là: {$result_sum}</p>";
    }
    ?>

</body>

</html>