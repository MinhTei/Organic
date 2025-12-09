<!DOCTYPE html>
<html>

<head>
    <title>Lab5_1</title>
</head>

<body>

    <h2>Nhập số lượng số nguyên tố bạn muốn hiển thị:</h2>
    <form action="" method="post">
        <input type="number" name="n_prime" min="1" required>
        <input type="submit" value="Hiển thị">
    </form>

    <?php
    // Kiểm tra nếu form đã được gửi
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['n_prime'])) {

        $n = (int)$_POST['n_prime'];

        if ($n < 1) {
            echo "<p style='color: red;'>Vui lòng nhập một số nguyên dương lớn hơn 0.</p>";
        } else {
            echo "<h3> $n số nguyên tố đầu tiên là:</h3>";
            echo "<p>";

            // Gọi hàm chính để hiển thị
            printNPrimes($n);

            echo "</p>";
        }
    }

    // --- Định nghĩa các hàm ---

    /**
     * Hàm kiểm tra một số có phải là số nguyên tố hay không
     * @param int $number Số cần kiểm tra
     * @return bool True nếu là số nguyên tố, False nếu không
     */
    function isPrime($number)
    {
        if ($number <= 1) {
            return false;
        }
        // Chỉ cần kiểm tra đến căn bậc hai của số đó
        for ($i = 2; $i * $i <= $number; $i++) {
            if ($number % $i == 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * Hàm in ra n số nguyên tố đầu tiên
     * @param int $n Số lượng số nguyên tố cần in
     */
    function printNPrimes($n)
    {
        $count = 0; // Đếm số lượng số nguyên tố đã tìm thấy
        $currentNumber = 2; // Bắt đầu kiểm tra từ số 2 (số nguyên tố nhỏ nhất)
        $primeList = []; // Mảng chứa các số nguyên tố tìm được

        while ($count < $n) {
            if (isPrime($currentNumber)) {
                $primeList[] = $currentNumber;
                $count++;
            }
            $currentNumber++;
        }

        // Xuất mảng các số nguyên tố
        echo implode(", ", $primeList);
    }
    ?>

</body>

</html>