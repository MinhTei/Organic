<?php
require_once __DIR__ . '/classes/Db.php';
require_once __DIR__ . '/classes/Book.php';

// DB config (adjust if needed)
$DB_HOST = 'localhost';
$DB_NAME = 'bookstore';
$DB_USER = 'root';
$DB_PASS = '';

$bookObj = new Book($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);

// Lấy publisher và category
$publishers = $bookObj->getPublishers();
$categories = $bookObj->getCategories();

// Build maps for display
$pubMap = array();
foreach ($publishers as $p) $pubMap[$p['pub_id']] = $p['pub_name'];
$catMap = array();
foreach ($categories as $c) $catMap[$c['cat_id']] = $c['cat_name'];

// Xử lý xóa (sử dụng param book_id giống lab8_4_2.php)
if (isset($_GET['book_id'])) {
    $id = $_GET['book_id'];
    $bookObj->deleteBook($id);
    header('Location: lab8_4_3.php');
    exit;
}

// Xử lý chỉnh sửa: nếu có ?edit=ID thì load thông tin sách để điền vào form
$isEditing = false;
$editingBook = null;
if (isset($_GET['edit'])) {
    $eid = $_GET['edit'];
    $editingBook = $bookObj->getBook($eid);
    if ($editingBook) $isEditing = true;
}

$messages = array();
// Xử lý thêm hoặc cập nhật sách
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sm'])) {
    $book_id = isset($_POST['book_id']) ? trim($_POST['book_id']) : '';
    $book_name = isset($_POST['book_name']) ? trim($_POST['book_name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $pub_id = isset($_POST['pub_id']) ? $_POST['pub_id'] : '';
    $cat_id = isset($_POST['cat_id']) ? $_POST['cat_id'] : '';

    // Xử lý ảnh: lưu vào image/book/ (thư mục của file hiện tại)
    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'image' . DIRECTORY_SEPARATOR . 'book' . DIRECTORY_SEPARATOR;
    if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
    $imgName = '';
    if (!empty($_FILES['img']['name']) && $_FILES['img']['error'] == 0) {
        $orig = basename($_FILES['img']['name']);
        $imgName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '', $orig);
        $target = $uploadDir . $imgName;
        if (!move_uploaded_file($_FILES['img']['tmp_name'], $target)) {
            $messages[] = 'Không thể lưu file ảnh.';
            $imgName = '';
        }
    }

    // Nếu là cập nhật (orig_book_id tồn tại)
    if (!empty($_POST['orig_book_id'])) {
        $origId = $_POST['orig_book_id'];
        // nếu không upload ảnh mới thì giữ ảnh cũ
        if (empty($imgName) && isset($_POST['existing_img'])) {
            $imgName = $_POST['existing_img'];
        }
        $data = array(
            ':book_name' => $book_name,
            ':description' => $description,
            ':price' => $price,
            ':img' => $imgName,
            ':pub_id' => $pub_id,
            ':cat_id' => $cat_id
        );
        $res = $bookObj->updateBook($origId, $data);
        if ($res) {
            header('Location: lab8_4_3.php');
            exit;
        } else {
            $messages[] = 'Lỗi cập nhật sách.';
        }
    } else {
        // Thêm mới
        $res = $bookObj->addBook(array(
            ':book_id' => $book_id,
            ':book_name' => $book_name,
            ':description' => $description,
            ':price' => $price,
            ':img' => $imgName,
            ':pub_id' => $pub_id,
            ':cat_id' => $cat_id
        ));
        if ($res) {
            header('Location: lab8_4_3.php');
            exit;
        } else {
            $messages[] = 'Lỗi thêm sách.';
        }
    }
}

// Phân trang: lấy trang hiện tại và số phần tử mỗi trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 5; // số sách mỗi trang

// Lấy dữ liệu
$books = $bookObj->getBooksPaging($page, $limit);
$totalBooks = (int)$bookObj->countAll();
$totalPages = $totalBooks > 0 ? ceil($totalBooks / $limit) : 1;
if ($page > $totalPages) $page = $totalPages;
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Bài vận dụng 4.3</title>
    <style>
        #container {
            width: 800px;
            margin: 0 auto
        }
    </style>
</head>

<body>
    <div id="container">
        <h1>Nguyễn Trung Hậu - DH52200651</h1>
        <hr>
        <h3>Thêm sách mới</h3>
        <?php if (!empty($messages)) {
            foreach ($messages as $m) echo '<div>' . htmlspecialchars($m) . '</div>';
        } ?>
        <form action="lab8_4_3.php" method="post" enctype="multipart/form-data">
            <table>
                <tr>
                    <td>Mã sách:</td>
                    <td><input type="text" name="book_id" required value="<?php echo $isEditing ? htmlspecialchars($editingBook['book_id']) : ''; ?>" <?php echo $isEditing ? 'readonly' : ''; ?> /></td>
                </tr>
                <tr>
                    <td>Tên sách:</td>
                    <td><input type="text" name="book_name" required value="<?php echo $isEditing ? htmlspecialchars($editingBook['book_name']) : ''; ?>" /></td>
                </tr>
                <tr>
                    <td>Mô tả:</td>
                    <td><textarea name="description"><?php echo $isEditing ? htmlspecialchars($editingBook['description']) : ''; ?></textarea></td>
                </tr>
                <tr>
                    <td>Giá:</td>
                    <td><input type="text" name="price" value="<?php echo $isEditing ? htmlspecialchars($editingBook['price']) : ''; ?>" /></td>
                </tr>
                <tr>
                    <td>Hình ảnh:</td>
                    <td>
                        <input type="file" name="img" accept="image/*" />
                        <?php if ($isEditing && !empty($editingBook['img'])) {
                            echo '<div>Ảnh hiện tại: <img src="./lab8_4/image/book/' . htmlspecialchars($editingBook['img']) . '" width="80" alt=""></div>';
                        } ?>
                    </td>
                </tr>
                <tr>
                    <td>Nhà xuất bản:</td>
                    <td>
                        <select name="pub_id">
                            <?php foreach ($publishers as $publisher) {
                                $sel = ($isEditing && $editingBook['pub_id'] == $publisher['pub_id']) ? ' selected' : '';
                                echo '<option value="' . htmlspecialchars($publisher['pub_id']) . '"' . $sel . '>' . htmlspecialchars($publisher['pub_name']) . '</option>';
                            } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Danh mục sách:</td>
                    <td>
                        <select name="cat_id">
                            <?php foreach ($categories as $category) {
                                $sel = ($isEditing && $editingBook['cat_id'] == $category['cat_id']) ? ' selected' : '';
                                echo '<option value="' . htmlspecialchars($category['cat_id']) . '"' . $sel . '>' . htmlspecialchars($category['cat_name']) . '</option>';
                            } ?>
                        </select>
                    </td>
                </tr>
                <?php if ($isEditing) { ?>
                    <tr>
                        <td colspan="2">
                            <input type="hidden" name="orig_book_id" value="<?php echo htmlspecialchars($editingBook['book_id']); ?>" />
                            <input type="hidden" name="existing_img" value="<?php echo htmlspecialchars($editingBook['img']); ?>" />
                            <input type="submit" name="sm" value="Cập nhật" />
                            <a href="lab8_4_3.php">Hủy</a>
                        </td>
                    </tr>
                <?php } else { ?>
                    <tr>
                        <td colspan="2"><input type="submit" name="sm" value="Thêm sách" /></td>
                    </tr>
                <?php } ?>
            </table>
        </form>

        <h3>Danh sách sách (Trang <?php echo $page; ?>/<?php echo $totalPages; ?>)</h3>
        <table border="1" cellpadding="6">
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
            <?php foreach ($books as $book) {
                $img = !empty($book['img']) ? './lab8_4/image/book/' . htmlspecialchars($book['img']) : 'image/book/default.jpg';
                $pubName = isset($pubMap[$book['pub_id']]) ? $pubMap[$book['pub_id']] : $book['pub_id'];
                $catName = isset($catMap[$book['cat_id']]) ? $catMap[$book['cat_id']] : $book['cat_id'];
                echo '<tr>';
                echo '<td>' . htmlspecialchars($book['book_id']) . '</td>';
                echo '<td>' . htmlspecialchars($book['book_name']) . '</td>';
                echo '<td>' . htmlspecialchars(substr($book['description'], 0, 100)) . '</td>';
                echo '<td>' . htmlspecialchars($book['price']) . '</td>';
                echo '<td><img src="' . $img . '" width="80" alt=""></td>';
                echo '<td>' . htmlspecialchars($pubName) . '</td>';
                echo '<td>' . htmlspecialchars($catName) . '</td>';
                echo '<td><a href="lab8_4_3.php?edit=' . urlencode($book['book_id']) . '&page=' . $page . '">Sửa</a> | <a href="lab8_4_3.php?book_id=' . urlencode($book['book_id']) . '" onclick="return confirm(\'Xác nhận xóa?\')">Xóa</a></td>';
                echo '</tr>';
            } ?>
        </table>

        <!-- Phân trang -->
        <div style="margin-top:12px">
            <?php
            // Hiển thị liên kết trang
            if ($page > 1) {
                echo '<a href="?page=1">Đầu</a> ';
                echo '<a href="?page=' . ($page - 1) . '">Trước</a> ';
            }
            // windowed pages
            $window = 2;
            $start = max(1, $page - $window);
            $end = min($totalPages, $page + $window);
            if ($start > 1) echo '... ';
            for ($i = $start; $i <= $end; $i++) {
                if ($i == $page) echo '<b>[' . $i . ']</b> ';
                else echo '<a href="?page=' . $i . '">' . $i . '</a> ';
            }
            if ($end < $totalPages) echo '... ';
            if ($page < $totalPages) {
                echo '<a href="?page=' . ($page + 1) . '">Sau</a> ';
                echo '<a href="?page=' . $totalPages . '">Cuối</a> ';
            }
            ?>
        </div>
    </div>
</body>

</html>