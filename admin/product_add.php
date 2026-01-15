<?php

/**
 * admin/product_add.php - Add new product (admin)
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    redirect(SITE_URL . '/auth.php');
}

$conn = getConnection();
$error = '';

// Load categories
$categories = getCategories();
//Xử lý form khi thêm sản phẩm
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

    // Xử lý upload ảnh
    $imagePath = '';
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../images/product/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $orig = basename($_FILES['image']['name']);
        $safe = time() . '_' . preg_replace('/[^a-z0-9_\-\.]/i', '_', $orig);
        $target = $uploadDir . $safe;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $imagePath = 'images/product/' . $safe; // lưu đường dẫn tương đối
        }
    }

    // Thêm sản phẩm vào database
    if (empty($name) || $price === null) {
        $error = 'Vui lòng nhập tên và giá sản phẩm.';
    } elseif (!$category_id) {
        $error = 'Vui lòng chọn danh mục sản phẩm.';
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
            $_SESSION['success'] = 'Thêm sản phẩm thành công!';
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
    <link href="<?= SITE_URL ?>/css/tailwind.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <style>
        body {
            font-family: 'Be Vietnam Pro', sans-serif
        }
    </style>
</head>

<body class="bg-gray-50">
    <?php
    // thêm header
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
                    <input name="slug" id="slugInput" class="w-full border px-3 py-2 rounded" value="<?= isset($_POST['slug']) ? sanitize($_POST['slug']) : '' ?>">
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
                    <div style="position:relative;">
                        <input type="file" name="image" accept="image/*" id="productImageInput" class="w-full">
                        <button type="button" onclick="document.getElementById('productImageInput').value=''; productImagePreview.src=''; productImagePreview.style.display='none';"
                            class="absolute top-2 right-2 px-2 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600" style="z-index:2;">Xóa hình</button>
                    </div>
                    <img id="productImagePreview" src="" style="display:none;max-width:96px;margin-top:8px;" />
                    <script>
                        document.getElementById('productImageInput').addEventListener('change', function(e) {
                            const [file] = e.target.files;
                            if (file) {
                                productImagePreview.src = URL.createObjectURL(file);
                                productImagePreview.style.display = 'block';
                            } else {
                                productImagePreview.src = '';
                                productImagePreview.style.display = 'none';
                            }
                        });
                    </script>
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

    <script>
        // Tạo slug tự động khi nhập tên
        function slugify(text) {
            return text.toString().toLowerCase() // Chuyển thành chữ thường
                .normalize('NFKD') // Chuẩn hóa Unicode
                .replace(/đ/g, 'd') // Thay đ bằng d
                .replace(/[\u0300-\u036f]/g, '') //Bỏ dấu
                .replace(/[^a-z0-9]+/g, '-') // Thay ký tự đặc biệt bằng dấu gạch ngang
                .replace(/^-+|-+$/g, ''); // Bỏ dấu gạch ngang ở đầu và cuối
        }

        const nameInput = document.querySelector('input[name="name"]'); // Lấy input tên danh mục
        const slugInput = document.getElementById('slugInput'); // Lấy input slug
        if (nameInput && slugInput) {
            nameInput.addEventListener('input', function() {
                //Lấy giá trị hiện tại của ô tên, đi qua bộ lọc slugify, rồi gán kết quả vào ô slug.
                slugInput.value = slugify(this.value);
            });
        }
    </script>
    <style>
        /* ===== RESPONSIVE FOR ADMIN FORMS ===== */
        /* Mobile: < 768px */
        @media (max-width: 767px) {

            input,
            textarea,
            select {
                font-size: 16px !important;
            }

            label {
                font-size: 0.85rem !important;
            }

            button,
            a {
                padding: 0.5rem 1rem !important;
                font-size: 0.9rem !important;
            }
        }

        /* Tablet: 768px - 1024px */
        @media (min-width: 768px) and (max-width: 1024px) {

            input,
            textarea,
            select {
                padding: 0.6rem !important;
                font-size: 0.9rem !important;
            }
        }
    </style>

</body>

</html>