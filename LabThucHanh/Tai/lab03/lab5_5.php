<!DOCTYPE html>
<html>

<head>
    <title>lab5_5</title>
</head>

<body>

    <h2> Nhập Chuỗi để Tính Tổng Các Số Nguyên</h2>
    <form action="" method="post">
        <input type="text" name="input_string"
            placeholder="Ví dụ: ngay15thang7nam2015"
            required size="50">
        <input type="submit" value="Tính Tổng">
    </form>

    <?php

    /**
     * Hàm tìm và tính tổng các số nguyên (có thể nhiều chữ số) có trong một chuỗi.
     * @param string $str Chuỗi đầu vào.
     * @return int Tổng của tất cả các số nguyên tìm thấy trong chuỗi.
     */
    function sumIntegersInString($str)
    {
        $total_sum = 0;

        // Biểu thức chính quy (Regex) để tìm các nhóm chữ số liên tiếp:
        // '\d+' : Tìm 1 hoặc nhiều (\+) chữ số (\d) liên tiếp nhau.
        // $matches[0] sẽ lưu trữ mảng các số nguyên dưới dạng chuỗi ('15', '7', '2015',...)
        if (preg_match_all('/\d+/', $str, $matches)) {
            $integers_array = $matches[0];

            // Tính tổng
            foreach ($integers_array as $number_str) {
                // Ép kiểu chuỗi số thành số nguyên (int) và cộng vào tổng
                $total_sum += (int)$number_str;
            }

            // Lưu mảng các số tìm được để in ra minh họa (Tùy chọn)
            global $found_numbers_for_display;
            $found_numbers_for_display = $integers_array;
        }

        return $total_sum;
    }

    // --- Phần Xử lý Input từ Form ---

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['input_string'])) {

        $input_str = htmlspecialchars($_POST['input_string']);

        // Biến toàn cục để lưu các số tìm được
        $found_numbers_for_display = [];

        // Gọi hàm tính tổng
        $result_sum = sumIntegersInString($input_str);

        echo "<hr>";
        echo "<h3>Kết quả:</h3>";
        echo "<p>Chuỗi đã nhập: '{$input_str}'</p>";
        echo "<p>Tổng các số là: {$result_sum}</p>";
    }
    ?>

</body>

</html>