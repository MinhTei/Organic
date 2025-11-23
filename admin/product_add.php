<?php
/**
 * admin/product_add.php - Add new product (admin)
 */

require_once '../config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    redirect(SITE_URL . '/auth.php');
}

$conn = getConnection();
$success = '';
$error = '';

// Load categories
$categories = getCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $name = sanitize($_POST['name'] ?? '');
    $slug = sanitize($_POST['slug'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price = $_POST['price'] !== '' ? (float)$_POST['price'] : null;
    $sale_price = $_POST['sale_price'] !== '' ? (float)$_POST['sale_price'] : null;
    $unit = sanitize($_POST['unit'] ?? '');
    $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
    $is_organic = isset($_POST['is_organic']) ? 1 : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    // server-side slug fallback
    if (empty($slug)) {
        $slug = preg_replace('/[^a-z0-9\-]+/i', '-', strtolower($name));
        $slug = trim($slug, '-');
    }

    // handle image upload
    $imagePath = '';
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../images/product/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $orig = basename($_FILES['image']['name']);
        $safe = time() . '_' . preg_replace('/[^a-z0-9_\-\.]/i', '_', $orig);
        $target = $uploadDir . $safe;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $imagePath = 'images/product/' . $safe; // store relative
        }
    }

    // Basic validation
    if (empty($name) || $price === null) {
        $error = 'Vui lòng nhập tên và giá sản phẩm.';
    } else {
        $sql = "INSERT INTO products (category_id, name, slug, description, price, sale_price, unit, image, stock, is_organic, is_new, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $res = $stmt->execute([
            $category_id,
            $name,
            $slug,
            $description,
            $price,
            $sale_price,
            $unit,
            $imagePath,
            $stock,
            $is_organic,
            $is_new,
            $is_featured
        ]);

        if ($res) {
            $success = 'Thêm sản phẩm thành công!';
            redirect('products.php');
        } else {
            $error = 'Có lỗi khi thêm sản phẩm.';
        }
    }
}

$pageTitle = 'Thêm sản phẩm';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= $pageTitle ?> - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <style>body{font-family:'Be Vietnam Pro',sans-serif}</style>
</head>
<body class="bg-gray-50">
<?php
// Include the public header if available (use __DIR__ to resolve reliably)
$headerPath = __DIR__ . '/../includes/header.php';
if (file_exists($headerPath)) {
    include $headerPath;
}
?>
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Thêm sản phẩm mới</h1>

    <?php if ($error): ?>
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg border">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Tên sản phẩm</label>
                <input name="name" required class="w-full border px-3 py-2 rounded" value="<?= isset($_POST['name']) ? sanitize($_POST['name']) : '' ?>">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Slug</label>
                <input name="slug" class="w-full border px-3 py-2 rounded" value="<?= isset($_POST['slug']) ? sanitize($_POST['slug']) : '' ?>">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Danh mục</label>
                <select name="category_id" class="w-full border px-3 py-2 rounded">
                    <option value="">-- Chọn danh mục --</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= sanitize($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Đơn vị</label>
                <input name="unit" class="w-full border px-3 py-2 rounded" value="<?= isset($_POST['unit']) ? sanitize($_POST['unit']) : 'kg' ?>">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Giá</label>
                <input type="number" name="price" step="0.01" class="w-full border px-3 py-2 rounded" value="<?= isset($_POST['price']) ? sanitize($_POST['price']) : '' ?>">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Giá giảm (nếu có)</label>
                <input type="number" name="sale_price" step="0.01" class="w-full border px-3 py-2 rounded" value="<?= isset($_POST['sale_price']) ? sanitize($_POST['sale_price']) : '' ?>">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Mô tả</label>
                <textarea name="description" class="w-full border px-3 py-2 rounded" rows="4"><?= isset($_POST['description']) ? sanitize($_POST['description']) : '' ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Ảnh sản phẩm</label>
                <input type="file" name="image" accept="image/*" class="w-full">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Tồn kho</label>
                <input type="number" name="stock" class="w-full border px-3 py-2 rounded" value="<?= isset($_POST['stock']) ? (int)$_POST['stock'] : 0 ?>">
            </div>
            <div class="flex items-center gap-4">
                <label><input type="checkbox" name="is_organic"> Hữu cơ</label>
                <label><input type="checkbox" name="is_new"> Mới</label>
                <label><input type="checkbox" name="is_featured"> Nổi bật</label>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded">Lưu</button>
            <a href="products.php" class="ml-3 px-4 py-2 bg-gray-200 rounded">Hủy</a>
        </div>
    </form>
</div>
</body>
</html>
