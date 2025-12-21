<?php
// Xử lý trước khi xuất HTML: kết nối DB, xử lý thêm/xóa, lấy dữ liệu
try {
    $pdh = new PDO("mysql:host=localhost; dbname=bookstore", "root", "");
    $pdh->query("SET NAMES 'utf8'");
} catch (Exception $e) {
    echo "Kết nối DB lỗi: " . $e->getMessage();
    exit;
}

// Lấy danh sách publisher và category để dùng cho select và hiển thị
$pubStmt = $pdh->prepare("SELECT * FROM publisher");
$pubStmt->execute();
$publishers = $pubStmt->fetchAll(PDO::FETCH_ASSOC);
$pubMap = array();
foreach ($publishers as $p) $pubMap[$p['pub_id']] = $p['pub_name'];

$catStmt = $pdh->prepare("SELECT * FROM category");
$catStmt->execute();
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
$catMap = array();
foreach ($categories as $c) $catMap[$c['cat_id']] = $c['cat_name'];

// Danh sách mã sách không thể xóa (chỉnh sửa theo yêu cầu)
$protectedBookIds = array(
    'td01',
    'th01',
    'td02',
    'td03',
    'td04',
    'td05'
);

// Thông báo để hiển thị cho người dùng
$messages = array();

// Xử lý xóa (GET param 'del') - sẽ kiểm tra danh sách bảo vệ
if (isset($_GET['del'])) {
    $book_id = $_GET['del'];
    if (in_array($book_id, $protectedBookIds)) {
        $messages[] = "Sách mã <strong>" . htmlspecialchars($book_id) . "</strong> không được phép xóa.";
        // không xóa, tiếp tục hiển thị trang với thông báo
    } else {
        $del = $pdh->prepare("DELETE FROM book WHERE book_id = :book_id");
        $del->execute(array(':book_id' => $book_id));
        // redirect để tránh resubmit và để làm mới danh sách
        header('Location: lab8_4_2.php');
        exit;
    }
}

// Xử lý thêm sách (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sm'])) {
    $book_id = isset($_POST['book_id']) ? trim($_POST['book_id']) : '';
    $book_name = isset($_POST['book_name']) ? trim($_POST['book_name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $pub_id = isset($_POST['pub_id']) ? $_POST['pub_id'] : '';
    $cat_id = isset($_POST['cat_id']) ? $_POST['cat_id'] : '';

    // Xử lý file ảnh: lưu vào thư mục lab8_4/image/book/
    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'lab8_4' . DIRECTORY_SEPARATOR . 'image' . DIRECTORY_SEPARATOR . 'book' . DIRECTORY_SEPARATOR;
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }
    $imgName = '';
    if (!empty($_FILES['img']['name']) && $_FILES['img']['error'] == 0) {
        // đặt tên file an toàn bằng timestamp + original name
        $orig = basename($_FILES['img']['name']);
        $imgName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '', $orig);
        $target = $uploadDir . $imgName;
        if (!move_uploaded_file($_FILES['img']['tmp_name'], $target)) {
            $messages[] = 'Không thể lưu file ảnh.';
            $imgName = '';
        }
    }

    // Chèn vào DB
    $sql = "INSERT INTO book (book_id, book_name, description, price, img, pub_id, cat_id)
            VALUES (:book_id, :book_name, :description, :price, :img, :pub_id, :cat_id)";
    $stm = $pdh->prepare($sql);
    $res = $stm->execute(array(
        ':book_id' => $book_id,
        ':book_name' => $book_name,
        ':description' => $description,
        ':price' => $price,
        ':img' => $imgName,
        ':pub_id' => $pub_id,
        ':cat_id' => $cat_id
    ));
    if ($res) {
        $messages[] = 'Đã thêm sách thành công.';
        // redirect để tránh double submit
        header('Location: lab8_4_2.php');
        exit;
    } else {
        $messages[] = 'Lỗi khi thêm sách.';
    }
}

// Lấy danh sách sách để hiển thị
$stm = $pdh->prepare("SELECT * FROM book ORDER BY book_id");
$stm->execute();
$books = $stm->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Bài vận dụng 4.2</title>
    <style>
        #container {
            width: 900px;
            margin: 0 auto;
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
    </style>
</head>

<body>
    <div id="container">
        <h1>Nguyễn Trung Hậu - DH52200651</h1>
        <hr>
        <h3>Thêm sách mới</h3>
        <?php if (!empty($messages)) {
            foreach ($messages as $m) echo '<div>' . $m . '</div>';
        } ?>
        <form action="lab8_4_2.php" method="post" enctype="multipart/form-data">
            <table>
                <tr>
                    <td>Mã sách:</td>
                    <td><input type="text" name="book_id" required /></td>
                </tr>
                <tr>
                    <td>Tên sách:</td>
                    <td><input type="text" name="book_name" required /></td>
                </tr>
                <tr>
                    <td>Mô tả:</td>
                    <td><textarea name="description"></textarea></td>
                </tr>
                <tr>
                    <td>Giá:</td>
                    <td><input type="text" name="price" /></td>
                </tr>
                <tr>
                    <td>Hình ảnh:</td>
                    <td><input type="file" name="img" accept="image/*" /></td>
                </tr>
                <tr>
                    <td>Nhà xuất bản:</td>
                    <td>
                        <select name="pub_id">
                            <?php foreach ($publishers as $publisher) {
                                echo '<option value="' . htmlspecialchars($publisher['pub_id']) . '">' . htmlspecialchars($publisher['pub_name']) . '</option>';
                            } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Danh mục sách:</td>
                    <td>
                        <select name="cat_id">
                            <?php foreach ($categories as $category) {
                                echo '<option value="' . htmlspecialchars($category['cat_id']) . '">' . htmlspecialchars($category['cat_name']) . '</option>';
                            } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"> <input type="submit" name="sm" value="Thêm sách" /></td>
                </tr>
            </table>
        </form>

        <h3>Danh sách sách</h3>
        <table border="1" style="border-collapse: collapse;" cellpadding="6">
            <tr>
                <th>Mã sách</th>
                <th>Tên sách</th>
                <th>Mô tả</th>
                <th>Giá</th>
                <th>Hình ảnh</th>
                <th>Nhà xuất bản</th>
                <th>Danh mục</th>
                <th>Thao tác</th>
            </tr>
            <?php
            foreach ($books as $book) {
                $imagePath = !empty($book['img']) ? ('lab8_4/image/book/' . $book['img']) : 'lab8_4/image/book/default.jpg';
                $desc = isset($book['description']) ? substr($book['description'], 0, 100)  . "..." : '';
                $pubName = isset($pubMap[$book['pub_id']]) ? $pubMap[$book['pub_id']] : $book['pub_id'];
                $catName = isset($catMap[$book['cat_id']]) ? $catMap[$book['cat_id']] : $book['cat_id'];
                echo "<tr>\n";
                echo "<td>" . htmlspecialchars($book['book_id']) . "</td>\n";
                echo "<td>" . htmlspecialchars($book['book_name']) . "</td>\n";
                echo "<td>" . htmlspecialchars($desc) . "</td>\n";
                echo "<td>" . htmlspecialchars($book['price']) . "</td>\n";
                echo "<td><img src='" . $imagePath . "' alt='" . htmlspecialchars($book['book_name']) . "' width='40' /></td>\n";
                echo "<td>" . htmlspecialchars($pubName) . "</td>\n";
                echo "<td>" . htmlspecialchars($catName) . "</td>\n";
                if (!empty($protectedBookIds) && in_array($book['book_id'], $protectedBookIds)) {
                    $msg = "Sách mã " . addslashes($book['book_id']) . " không được phép xóa.";
                    echo '<td><a href="#" onclick="alert(\'' . htmlspecialchars($msg, ENT_QUOTES) . '\'); return false;">Xóa</a></td>';
                } else {
                    echo '<td><a href="lab8_4_2.php?del=' . urlencode($book['book_id']) . '" onclick="return confirm(\'Xác nhận xóa?\')">Xóa</a></td>';
                }
                echo '</tr>';
            }
            ?>
        </table>

    </div>
</body>

</html>