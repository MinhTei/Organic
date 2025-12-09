<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài 4.2</title>
</head>

<body>
    <h2>Nguyễn Trung Hậu - DH52200651 - D22_TH07</h2>
    <hr>
    <fieldset>
        <legend>Form Bài 4.2</legend>
        <form action="" method="get">
            <label for="name"><strong>Nhập tên sản phẩm cần tìm:</strong></label>
            <input type="text" name="ten_sp" id="name">
            <br>
            <strong>Cách tìm: </strong><input type="radio" name="ct" value="Gần đúng"> Gần đúng
            <input type="radio" name="ct" value="Chính xác"> Chính xác
            <br>
            <strong>Loại sản phẩm</strong>
            <br>
            <label for="L1">Loại 1</label>
            <input type="checkbox" name="lsp[]" id="L1" value="Loại 1">
            <br><label for="L2">Loại 2</label>
            <input type="checkbox" name="lsp[]" id="L2" value="Loại 2">
            <br>
            <label for="L3">Loại 3</label>
            <input type="checkbox" name="lsp[]" id="L3" value="Loại 3">
            <br>
            <label for="all">Tất cả</label>
            <input type="checkbox" name="lsp[]" id="all" value="Loại 1, Loại 2, Loại 3">
            <br>
            <input  type="submit" value="Tìm">
        </form>
    </fieldset>
    <hr>
    <h3>Thông tin sản phẩm</h3>
    <br>
    <?php
    if(isset($_GET['ten_sp'])){
        echo "Tên sản phẩm: " . htmlspecialchars($_GET['ten_sp']);
        echo "<br/>";
    }
    if(isset($_GET['ct'])){
        echo "Cách tìm: " . htmlspecialchars($_GET['ct']);
        echo "<br/>";
    }
    if (isset($_GET['lsp'])){
        echo "Loại sản phẩm: ";
        if (is_array($_GET['lsp'])){
            echo implode(", ",$_GET['lsp']);
        }
    }else
        echo "Bạn chưa chọn loại sản phẩm.";
        echo"<hr/>";
        print_r($_GET);
    ?>
</body>

</html>