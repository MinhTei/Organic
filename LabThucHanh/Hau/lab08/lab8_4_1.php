<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Bài vận dụng 4.1</title>
    <style>
        /* Định dạng khung chứa nội dung */
        #container {
            width: 800px;
            margin: 0 auto;
            /* căn giữa trang */
        }

        form {
            padding-bottom: 20px;
        }

        /* Định dạng bảng */
        table {
            width: auto;
            border-collapse: collapse;
            /* gộp viền */
        }

        /* Định dạng cho ô tiêu đề và ô dữ liệu */
        th,
        td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
            /* viền mỏng màu xám */
        }

        td img {
            display: block;
            margin: 0 auto;
            width: 40px;
        }

        #a_underline {
            text-decoration: none;
        }

        p,
        ol {
            color: blue;
        }
    </style>
</head>

<body>
    <div id="container">
        <h1>Nguyễn Trung Hậu - DH52200651</h1>
        <hr>
        <h3>TÌM KIẾM THÔNG TIN SÁCH</h3>
        <!-- Chuyển form sang GET để gởi string query -->
        <form action="/lab8_4_1.php" method="GET">

            <input type="text" name="ten" placeholder="từ, nhật, điển, thị...">
            <input type="submit" name="sm" value="Tìm">
        </form>
        <?php
        // ------------------- KẾT NỐI CSDL -------------------
        try {
            // Tạo đối tượng PDO kết nối đến database 'bookstore' với user 'root'
            $pdh = new PDO("mysql:host=localhost; dbname=bookstore", "root", "");
            // Thiết lập bộ mã UTF-8 để hiển thị tiếng Việt đúng
            $pdh->query("set names 'utf8'");
        } catch (Exception $e) {
            // Nếu kết nối thất bại thì báo lỗi và dừng chương trình
            echo $e->getMessage();
            exit;
        }

        // ------------------- TRUY VẤN SELECT VÀ PHÂN TRANG -------------------
        if (isset($_GET["sm"])) {
            if (isset($_GET["ten"])) {
                $search = trim($_GET["ten"]);

                // Phân trang: lấy trang hiện tại và số phần tử mỗi trang
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                if ($page < 1) $page = 1;
                $limit = 5; // số sách mỗi trang

                // Đếm tổng số kết quả phù hợp
                $countSql = "SELECT COUNT(*) as total FROM book WHERE book_name LIKE :ten";
                $cstm = $pdh->prepare($countSql);
                $cstm->bindValue(":ten", "%$search%");
                $cstm->execute();
                $totalRow = (int)$cstm->fetch(PDO::FETCH_ASSOC)['total'];

                if ($totalRow == 0) {
                    echo "<p>Không tìm thấy sách nào với từ khóa '<b>" . htmlspecialchars($search) . "</b>'</p>";
                } else {
                    $totalPages = (int)ceil($totalRow / $limit);
                    if ($page > $totalPages) $page = $totalPages;

                    $offset = ($page - 1) * $limit;

                    // Lấy dữ liệu trang hiện tại, bind offset/limit là kiểu INT
                    $sql = "SELECT * FROM book WHERE book_name LIKE :ten LIMIT :offset, :limit";
                    $stm = $pdh->prepare($sql);
                    $stm->bindValue(":ten", "%$search%");
                    $stm->bindValue(":offset", (int)$offset, PDO::PARAM_INT);
                    $stm->bindValue(":limit", (int)$limit, PDO::PARAM_INT);
                    $stm->execute();
                    $rows = $stm->fetchAll(PDO::FETCH_ASSOC);
        ?>
                    Các dữ liệu đã tìm được: <br>
                    <table border="1">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Tên sách</th>
                                <th>Mô tả</th>
                                <th>Giá</th>
                                <th style="text-align:center">Hình ảnh</th>
                                <th>Mã nhà xuất bản</th>
                                <th>Mã loại</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $key => $value) { ?>
                                <tr>
                                    <td><?php echo $value["book_id"] ?></td>
                                    <td><?php echo $value["book_name"] ?></td>
                                    <td><?php echo substr($value["description"], 0, 100) . " ... " ?></td>
                                    <td><?php echo $value["price"] ?></td>
                                    <td><img src="./lab8_4/image/book/<?php echo $value["img"] ?>" width="40px"></td>
                                    <td><?php echo $value["pub_id"] ?></td>
                                    <td><?php echo $value["cat_id"] ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                    <!-- Phân trang -->
                    <div style="margin-top:12px">
                        <?php
                        // Hiển thị liên kết trang, giữ tham số ten và sm
                        $baseQuery = 'ten=' . urlencode($search) . '&sm=1';
                        if ($page > 1) {
                            echo '<a href="?' . $baseQuery . '&page=1">Đầu</a> ';
                            echo '<a href="?' . $baseQuery . '&page=' . ($page - 1) . '">Trước</a> ';
                        }
                        // windowed pages
                        $window = 2;
                        $start = max(1, $page - $window);
                        $end = min($totalPages, $page + $window);
                        if ($start > 1) echo '... ';
                        for ($i = $start; $i <= $end; $i++) {
                            if ($i == $page) echo '<b>[' . $i . ']</b> ';
                            else echo '<a href="?' . $baseQuery . '&page=' . $i . '">' . $i . '</a> ';
                        }
                        if ($end < $totalPages) echo '... ';
                        if ($page < $totalPages) {
                            echo '<a href="?' . $baseQuery . '&page=' . ($page + 1) . '">Sau</a> ';
                            echo '<a href="?' . $baseQuery . '&page=' . $totalPages . '">Cuối</a> ';
                        }
                        ?>
                    </div>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    </div>
</body>

</html>