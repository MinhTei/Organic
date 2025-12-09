<?php
/*
bảng cửu chương $n, màu nền $color
- Input: $n là một số nguyên dương (1->10)
		 $color: Tên màu nền.Mặc định là green
- Output: Bảng cửu chương, được xuât trong hàm

*/
function BCC($n, $colorHead, $color1, $color2)
{
?>
    <table bgcolor="red">
        <tr bgcolor="<?php echo $colorHead; ?>">
            <td colspan="3">Bảng cửu chương <?php echo $n; ?></td>
        </tr>
        <?php
        for ($i = 1; $i <= 10; $i++) {
        ?>
            <tr bgcolor="<?php if ($i % 2 == 0) echo $color2;
                            else echo $color1 ?>">
                <td><?php echo $n; ?></td>
                <td><?php echo $i; ?></td>
                <td><?php echo $n * $i; ?></td>
            </tr>
        <?php
        }
        ?>
    </table>
<?php
}
/*
Hàm in ra bàn cờ vua với màu các ô thay đổi và được định nghĩa trong css: cellBlack, cellWhite
- Input: $size: kích thước bàn cờ: là 1 số nguyên dương (mặc định là 8)
- Output: bàn cờ HTML 

*/
function BanCo($n)
{
?>
    <div id="banco">
        <?php
        for ($i = 1; $i <= $n; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                $classCss = (($i + $j) % 2) == 0 ? "cellWhite" : "cellBlack";
                echo "<div class='$classCss'> $i - $j</div>";
            }
            echo "<div class='clear' />";
        }
        ?>
    </div>
<?php
}
?>