<!DOCTYPE html>
<html>

<head>
    <title>Lab5_2</title>
</head>

<body>

    <h2>Nhập Chuỗi để kiểm tra Đối Xứng</h2>
    <form action="" method="post">
        <input type="text" name="input_string" placeholder="Ví dụ: madam" required size="40">
        <input type="submit" value="Kiểm tra">
    </form>

    <?php

    /**
     * Hàm kiểm tra một chuỗi có đối xứng (Palindrome) hay không.
     * Hàm này loại bỏ khoảng trắng và không phân biệt chữ hoa/thường.
     * @param string $str Chuỗi cần kiểm tra.
     * @return bool True nếu chuỗi đối xứng, False nếu không.
     */
    function isPalindrome($str)
    {
        // 1. Xử lý chuỗi: Loại bỏ khoảng trắng và chuyển về chữ thường
        $processed_str = strtolower(str_replace(' ', '', $str));

        // 2. Đảo ngược chuỗi
        $reversed_str = strrev($processed_str);

        // 3. So sánh
        return $processed_str === $reversed_str;
    }

    // --- Phần Xử lý Input ---

    // Kiểm tra nếu form đã được gửi và có dữ liệu
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['input_string'])) {

        // Lấy chuỗi từ form
        $input_str = htmlspecialchars($_POST['input_string']);

        echo "<hr>";
        echo "<h3>Kết quả kiểm tra:</h3>";
        echo "<p>Chuỗi đã nhập:'{$input_str}' </p>";

        // Gọi hàm kiểm tra
        if (isPalindrome($input_str)) {
            echo "<p style='color: green; font-size: 1.2em;'>Chuỗi đối xứng .</p>";
        } else {
            echo "<p style='color: red; font-size: 1.2em;'>Chuỗi không đối xứng.</p>";
        }
    }
    ?>

</body>

</html>