<!DOCTYPE html>
<html lang="en">

<head>
    <title>Lab5_1</title>
</head>

<body>
    <?php
    function showArray($arr)
    {
        echo "<table border=1 cellspacing=0 cellpadding=3>
                <tr>
                    <th>INDEX</th>
                    <th>VALUE</th>
                </tr>";
        foreach ($arr as $key => $value) {
            echo "<tr>
                    <td align=center>" . $key . "</td>
                    <td align=center>" . $value . "</td>
                    </tr>";
        }
        echo "</table>";
    }

    $mang = array();
    for ($i = 0; $i <= 10; $i++) {
        $mang[] = rand(1, 100);
    }

    echo "<pre>";
    print_r($mang);
    echo "</pre>";

    showArray($mang);
    ?>
</body>

</html>