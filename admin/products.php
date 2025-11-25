<?php

/**
 * admin/products.php - Trang Quản lý Sản phẩm
 *
 * Chức năng:
 * - Hiển thị danh sách sản phẩm, tìm kiếm, lọc theo danh mục, trạng thái
 * - Thêm, sửa, xóa sản phẩm, cập nhật trạng thái nổi bật/mới
 * - Giao diện đồng bộ với các trang quản trị khác
 *
 * Hướng dẫn:
 * - Sử dụng sidebar chung (_sidebar.php)
 * - Header hiển thị avatar, tên admin, link về trang chủ
 */

require_once '../config.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    redirect(SITE_URL . '/auth.php');
}

$conn = getConnection();
$success = '';
$error = '';

// Xử lý các hành động với sản phẩm (xóa, chuyển trạng thái nổi bật/mới)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Xóa sản phẩm
    if (isset($_POST['delete_product'])) {
        $productId = (int)$_POST['product_id'];
        $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
        if ($stmt->execute([':id' => $productId])) {
            $success = 'Đã xóa sản phẩm thành công!';
        } else {
            $error = 'Không thể xóa sản phẩm!';
        }
    }

    // Chuyển trạng thái nổi bật
    if (isset($_POST['toggle_featured'])) {
        $productId = (int)$_POST['product_id'];
        $stmt = $conn->prepare("UPDATE products SET is_featured = NOT is_featured WHERE id = :id");
        if ($stmt->execute([':id' => $productId])) {
            $success = 'Đã cập nhật trạng thái nổi bật!';
        }
    }

    // Chuyển trạng thái mới
    if (isset($_POST['toggle_new'])) {
        $productId = (int)$_POST['product_id'];
        $stmt = $conn->prepare("UPDATE products SET is_new = NOT is_new WHERE id = :id");
        if ($stmt->execute([':id' => $productId])) {
            $success = 'Đã cập nhật trạng thái mới!';
        }
    }
}

// Lấy tham số lọc, tìm kiếm
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Xây dựng điều kiện truy vấn theo bộ lọc
$where = ['1=1'];
$params = [];

if ($categoryId) {
    $where[] = "p.category_id = :category_id";
    $params[':category_id'] = $categoryId;
}

if ($search) {
    $where[] = "(p.name LIKE :search OR p.description LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($status === 'featured') {
    $where[] = "p.is_featured = 1";
} elseif ($status === 'new') {
    $where[] = "p.is_new = 1";
} elseif ($status === 'sale') {
    $where[] = "p.sale_price IS NOT NULL";
} elseif ($status === 'out_of_stock') {
    $where[] = "p.stock <= 0";
}

$whereClause = implode(' AND ', $where);

// Lấy danh sách sản phẩm kèm tên danh mục
$sql = "SELECT p.*, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE $whereClause
    ORDER BY p.id ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Lấy danh sách danh mục để lọc
$categories = getCategories();

// Thống kê số lượng sản phẩm theo trạng thái
$stats = [
    'total' => $conn->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'featured' => $conn->query("SELECT COUNT(*) FROM products WHERE is_featured = 1")->fetchColumn(),
    'new' => $conn->query("SELECT COUNT(*) FROM products WHERE is_new = 1")->fetchColumn(),
    'out_of_stock' => $conn->query("SELECT COUNT(*) FROM products WHERE stock <= 0")->fetchColumn(),
];

$pageTitle = 'Quản lý sản phẩm';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="../css/styles.css" rel="stylesheet" />
    <link href="../css/styles.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;700;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <style>
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>

<body class="bg-gray-50">

    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-green-600 text-3xl">admin_panel_settings</span>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">Admin Dashboard</h1>
                        <p class="text-xs text-gray-500">Xanh Organic</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="<?= SITE_URL ?>" class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-1">
                        <span class="material-symbols-outlined text-lg">storefront</span>
                        <span>Về trang chủ</span>
                    </a>
                    <div class="flex items-center gap-2 pl-3 border-l border-gray-200">
                        <div class="w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center font-bold">
                            <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
                        </div>
                        <span class="text-sm font-medium text-gray-700"><?= sanitize($_SESSION['user_name']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="flex">
        <!-- Sidebar -->
        <?php include __DIR__ . '/_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <?php if ($success): ?>
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center gap-2">
                    <span class="material-symbols-outlined">check_circle</span>
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg flex items-center gap-2">
                    <span class="material-symbols-outlined">error</span>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Quản lý sản phẩm</h2>
                    <p class="text-gray-600 mt-1">Quản lý danh sách sản phẩm và thông tin</p>
                </div>
                <div class="flex gap-3">
                    <a href="product_import.php" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                        <span class="material-symbols-outlined">upload_file</span>
                        Import Excel
                    </a>
                    <a href="product_add.php" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                        <span class="material-symbols-outlined">add</span>
                        Thêm sản phẩm mới
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Tổng sản phẩm</p>
                            <h3 class="text-2xl font-bold mt-1"><?= $stats['total'] ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-blue-600">inventory_2</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Sản phẩm nổi bật</p>
                            <h3 class="text-2xl font-bold mt-1"><?= $stats['featured'] ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-yellow-600" style="font-variation-settings: 'FILL' 1;">star</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Hàng mới</p>
                            <h3 class="text-2xl font-bold mt-1"><?= $stats['new'] ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-green-600">new_releases</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Hết hàng</p>
                            <h3 class="text-2xl font-bold mt-1 text-red-600"><?= $stats['out_of_stock'] ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-red-600">production_quantity_limits</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <form method="GET" class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[250px]">
                        <input type="text" name="search" value="<?= sanitize($search) ?>"
                            placeholder="Tìm kiếm sản phẩm..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>

                    <select name="category" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">Tất cả danh mục</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                                <?= sanitize($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Tất cả trạng thái</option>
                        <option value="featured" <?= $status === 'featured' ? 'selected' : '' ?>>Nổi bật</option>
                        <option value="new" <?= $status === 'new' ? 'selected' : '' ?>>Hàng mới</option>
                        <option value="sale" <?= $status === 'sale' ? 'selected' : '' ?>>Đang giảm giá</option>
                        <option value="out_of_stock" <?= $status === 'out_of_stock' ? 'selected' : '' ?>>Hết hàng</option>
                    </select>

                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">search</span>
                        Lọc
                    </button>

                    <?php if ($search || $categoryId || $status !== 'all'): ?>
                        <a href="products.php" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg">restart_alt</span>
                            Đặt lại
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Products Table -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">ID</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Sản phẩm</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Danh mục</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Giá</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Tồn kho</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Trạng thái</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-gray-500">
                                        <span class="material-symbols-outlined text-5xl mb-2">inventory_2</span>
                                        <p>Không có sản phẩm nào</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="py-4 px-4 font-medium text-gray-900">#<?= $product['id'] ?></td>
                                        <td class="py-4 px-4">
                                            <div class="flex items-center gap-3">
                                                <img src="<?= imageUrl($product['image']) ?>" alt="<?= sanitize($product['name']) ?>"
                                                    class="w-16 h-16 rounded-lg object-cover border border-gray-200">
                                                <div>
                                                    <p class="font-medium text-gray-900"><?= sanitize($product['name']) ?></p>
                                                    <p class="text-sm text-gray-500"><?= sanitize($product['unit']) ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">
                                                <?= sanitize($product['category_name']) ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <?php if ($product['sale_price']): ?>
                                                <div>
                                                    <p class="font-semibold text-green-600"><?= formatPrice($product['sale_price']) ?></p>
                                                    <p class="text-sm text-gray-400 line-through"><?= formatPrice($product['price']) ?></p>
                                                </div>
                                            <?php else: ?>
                                                <p class="font-semibold text-gray-900"><?= formatPrice($product['price']) ?></p>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-4">
                                            <?php if ($product['stock'] > 0): ?>
                                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                                    <?= $product['stock'] ?> <?= $product['unit'] ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">
                                                    Hết hàng
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="flex flex-wrap gap-1">
                                                <?php if ($product['is_featured']): ?>
                                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-medium">
                                                        Nổi bật
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($product['is_new']): ?>
                                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-medium">
                                                        Mới
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($product['is_organic']): ?>
                                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-medium">
                                                        Hữu cơ
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="flex items-center gap-2">
                                                <a href="product_edit.php?id=<?= $product['id'] ?>"
                                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg"
                                                    title="Chỉnh sửa">
                                                    <span class="material-symbols-outlined text-lg">edit</span>
                                                </a>

                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                                    <button type="submit" name="toggle_featured"
                                                        class="p-2 <?= $product['is_featured'] ? 'text-yellow-600 hover:bg-yellow-50' : 'text-gray-400 hover:bg-gray-100' ?> rounded-lg"
                                                        title="<?= $product['is_featured'] ? 'Bỏ nổi bật' : 'Đánh dấu nổi bật' ?>">
                                                        <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' <?= $product['is_featured'] ? '1' : '0' ?>;">star</span>
                                                    </button>
                                                </form>

                                                <form method="POST" class="inline"
                                                    onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                                    <button type="submit" name="delete_product"
                                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg"
                                                        title="Xóa">
                                                        <span class="material-symbols-outlined text-lg">delete</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

</body>

</html>