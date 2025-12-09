<!DOCTYPE html>
<html lang="en">

<head>
    <title>Lab5_ 2</title>
</head>

<body>
    <?php
    function showArray($arr)
    {
        echo "<table border=1 cellspacing=0 cellpadding=3>
                <tr>
                    <th>STT</th>
                    <th>MÃ SẢN PHẨM</th>
                    <th>TÊN SẢN PHẨM</th>
                </tr>";
        for ($i = 0; $i < count($arr); $i++) {
            echo "<tr align=center>
                    <td>" . ($i + 1) . "</td>
                    <td>" . $arr[$i]['id'] . "</td>
                    <td>" . $arr[$i]['name'] . "</td>
                    </tr>";
        }
        echo "</table>";
    }

    $arr = array();
    $r = array("id" => "sp1", "name" => "Sản phẩm 1 ");
    $arr[] = $r;
    $r = array("id" => "sp2", "name" => "Sản phẩm 2 ");
    $arr[] = $r;
    $r = array("id" => "sp3", "name" => "Sản phẩm 3 ");
    $arr[] = $r;

    echo "<pre>";
    print_r($arr);
    echo "</pre>";

    showArray($arr);
    ?>
</body>

</html>