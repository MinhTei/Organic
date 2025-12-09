<?php
    //Kết hợp hàm và vòng lặp
    function kiemtranguyento($x) //Kiểm tra 1 số có nguyên tố hay không
    {
        if ($x < 2)
            return false;
        if ($x == 2)
            return true;
        if ($x % 2 == 0)  // Kiểm tra số chẵn
            return false;

        $sqrtX = sqrt($x);
        $i = 3;
        while ($i <= $sqrtX) { 
            if ($x % $i == 0)
                return false;
            $i += 2; // Kiểm tra các số lẻ
        }
        return true;
    }

    // Kiểm tra số 3
    if (kiemtranguyento(9))
        echo " là số nguyên tố";
    else
        echo " không phải số nguyên tố";
?>
