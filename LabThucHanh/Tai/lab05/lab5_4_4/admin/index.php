<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Back-End</title>
    <link rel="stylesheet" type="text/css" href="css/style.css" />

</head>

<body>
    <h2>Nguyễn Trung Hậu - DH52200651 - D22_TH07</h2>
    <hr>
    <div id="contain">
        <div id="header">Header</div>
        <div id="body">
            <div id="left">
                <a href="index.php">Home</a><br />
                <a href="index.php?mod=sach">Danh Mục Sách</a><br />
                <a href="index.php?mod=loai">Loại sách</a><br />
                <a href="index.php?mod=nhaxb">Nhà xuất bản</a><br />
                <a href="index.php?mod=order">Quản lý đơn hàng</a><br />
                <a href="index.php?mod=user">Thông tin user</a><br />
                <hr />
                <a href="../index.php">Trang front-end</a>
            </div>
            <div id="right">

                <div id=thongtinadmin>
                    <div class=info>
                        Thông tin admin
                    </div>
                    <div class=logout>
                        <a href="#">Thoát</a>
                    </div>
                </div>

                <div>
                    <?php
                    // Lấy tham số module từ URL
                    $mod = isset($_GET['mod']) ? $_GET['mod'] : '';
                    $ac = isset($_GET['ac']) ? $_GET['ac'] : '';

                    // Kiểm tra và include trang tương ứng
                    switch ($mod) {
                        case 'sach':
                            include 'module_sach.php';
                            break;
                        case 'loai':
                            include 'module_loai.php';
                            break;
                        case 'nhaxb':
                            include 'module_nhaxb.php';
                            break;
                        case 'order':
                            include 'module_order.php';
                            break;
                        case 'user':
                            include 'module_user.php';
                            break;
                        default:
                            include 'module_home.php';
                            break;
                    }
                    ?>
                </div>


            </div>
        </div>
        <div id="footer">footer</div>
    </div>
</body>

</html>