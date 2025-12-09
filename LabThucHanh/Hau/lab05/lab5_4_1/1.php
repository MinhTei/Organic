<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài 4.1</title>
</head>
<body>
    <h2>Nguyễn Trung Hậu - DH52200651 - D22_TH07</h2>
    <hr/>
    <?php
        $arr = array();
        $r = array("id"=>1, "name"=>"Product1");
        $arr[] = $r;
        $r = array("id"=>2, "name"=>"Product2");
        $arr[] = $r;
        $r = array("id"=>3, "name"=>"Product3");
        $arr[] = $r;
        $r = array("id"=>4, "name"=>"Product4");
        $arr[] = $r;

        foreach ($arr as $key=>$value){
            $id=$arr[$key]["id"];
            echo'<a href="2.php?id='.$id.'">Product '.$id.' </a><br/>';
        }
    ?>
</body>
</html>