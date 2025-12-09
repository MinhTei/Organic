<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Lab 4_4</title>
    <style>
        #banco {
            border: solid;
            padding: 15px;
            background: #E8E8E8
        }

        #banco .cellBlack {
            width: 50px;
            height: 50px;
            background: black;
            float: left;
        }

        #banco .cellWhite {
            width: 50px;
            height: 50px;
            background: white;
            float: left
        }

        .clear {
            clear: both
        }
    </style>
</head>
<form action="" method="post">
    <h4>Nhập số bảng cửu chương</h4>
    <input type="number" name="soBCC">
    <input type="submit" value="Nhập">
</form>

<body>
    <?php
    include("function.php");
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['soBCC'])) {
            $n = (int)$_POST['soBCC'];
            $colorHead = "yellow";
            $color1 = "pink";
            $color2 = "#fffccc";
            $functions = [
                'BCC',
                'BanCo'
            ];
            $result = "";
            if ($n >= 1 && $n <= 10) {
                foreach ($functions as $fs) {
                    if (function_exists($fs)) {
                        $result .= $fs($n, $colorHead, $color1, $color2);
                    }
                }
            } else
                echo "<p style='color: red;'> Vui lòng nhập số từ 1 đến 10";
        }
    }
    ?>
</body>

</html>