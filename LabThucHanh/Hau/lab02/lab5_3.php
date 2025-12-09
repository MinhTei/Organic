<?php
echo"<bold/> Giải Phương Trình Bậc 2";
echo"<br/>";
$a = 1;
$b = -3;
$c = 2;
if ($a == 0) {
    // Trường hợp phương trình bậc nhất
    if ($b == 0) {
        echo ($c == 0) ? "Phương trình vô số nghiệm." : "Phương trình vô nghiệm.";
    } else {
        $x = -$c / $b;
        echo "Phương trình bậc nhất có nghiệm x = $x";
    }
} else {
    // Phương trình bậc hai
    $delta = $b * $b - 4 * $a * $c;

    if ($delta < 0) {
        echo "Phương trình vô nghiệm.";
    } elseif ($delta == 0) {
        $x = -$b / (2 * $a);
        echo "Phương trình có nghiệm kép x = $x";
    } else {
        $x1 = (-$b + sqrt($delta)) / (2 * $a);
        $x2 = (-$b - sqrt($delta)) / (2 * $a);
        echo "Phương trình có hai nghiệm phân biệt:<br>";
        echo "x1 = $x1 <br>";
        echo "x2 = $x2";
    }
}
?>
