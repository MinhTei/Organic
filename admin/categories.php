<?php

/**
 * admin/categories.php - Trang Quản lý Danh mục
 *
 * Chức năng:
 * - Hiển thị danh sách danh mục, thêm, sửa, xóa danh mục
 * - Upload icon (ảnh) cho danh mục
 * - Sinh slug tự động từ tên danh mục
 * - Giao diện đồng bộ với các trang quản trị khác
 *
 * Hướng dẫn:
 * - Sử dụng sidebar chung (_sidebar.php)
 * - Header hiển thị avatar, tên admin, link về trang chủ
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    redirect(SITE_URL . '/auth.php');
}

$conn = getConnection();
$success = '';
$error = '';

// Xử lý thêm/sửa danh mục
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category'])) {
    $name = sanitize($_POST['name']);
    $slug = sanitize($_POST['slug']);
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    // Sinh slug phía server nếu chưa có
    if (empty($slug)) {
        $slug = preg_replace('/[^a-z0-9\-]+/i', '-', strtolower($name));
        $slug = trim($slug, '-');
    }

    // Xử lý upload icon (ảnh) nếu có
    $iconPath = '';
    if (!empty($_FILES['icon']['name']) && $_FILES['icon']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../images/categories/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $origName = basename($_FILES['icon']['name']);
        $safeName = time() . '_' . preg_replace('/[^a-z0-9_\-\.]/i', '_', $origName);
        $targetPath = $uploadDir . $safeName;

        if (move_uploaded_file($_FILES['icon']['tmp_name'], $targetPath)) {
            // Lưu đường dẫn tương đối vào DB cho dễ di chuyển
            $iconPath = 'images/categories/' . $safeName;
        }
    }

    if (empty($name) || empty($slug)) {
        $error = 'Vui lòng điền đầy đủ thông tin.';
    } else {
        if ($id > 0) {
            // Giữ icon cũ nếu không upload mới
            $stmt = $conn->prepare("SELECT icon FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $existingIcon = $stmt->fetchColumn();
            if (empty($iconPath)) {
                $iconPath = $existingIcon;
            }

            // Cập nhật
            $stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ?, icon = ? WHERE id = ?");
            if ($stmt->execute([$name, $slug, $iconPath, $id])) {
                $success = 'Cập nhật danh mục thành công!';
            } else {
                $error = 'Có lỗi xảy ra.';
            }
        } else {
            // Thêm mới (icon có thể rỗng)
            $stmt = $conn->prepare("INSERT INTO categories (name, slug, icon) VALUES (?, ?, ?)");
            if ($stmt->execute([$name, $slug, $iconPath])) {
                $success = 'Thêm danh mục thành công!';
            } else {
                $error = 'Có lỗi xảy ra.';
            }
        }
    }
}

// Xử lý xóa danh mục
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = 'Xóa danh mục thành công!';
    } else {
        $error = 'Không thể xóa danh mục này.';
    }
}

// Lấy tất cả danh mục
$categories = getCategories();

// Lấy thông tin danh mục để sửa (nếu có)
$editCategory = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    foreach ($categories as $cat) {
        if ($cat['id'] == $id) {
            $editCategory = $cat;
            break;
        }
    }
}

$pageTitle = 'Quản lý Danh mục';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;700;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>

<body class="bg-gray-50 font-['Be_Vietnam_Pro']">

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
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Quản lý Danh mục</h2>
                <p class="text-gray-600 mt-1">Tổng cộng <?= count($categories) ?> danh mục</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Form Add/Edit -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h3 class="text-lg font-bold mb-4">
                            <?= $editCategory ? 'Sửa danh mục' : 'Thêm danh mục mới' ?>
                        </h3>

                        <form method="POST" enctype="multipart/form-data">
                            <?php if ($editCategory): ?>
                                <input type="hidden" name="id" value="<?= $editCategory['id'] ?>">
                            <?php endif; ?>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Tên danh mục <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" required
                                        value="<?= $editCategory ? htmlspecialchars(strip_tags(html_entity_decode($editCategory['name'], ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8') : '' ?>"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Slug <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="slug" id="slugInput" required
                                        value="<?= $editCategory ? sanitize($editCategory['slug']) : '' ?>"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <p class="text-xs text-gray-500 mt-1">VD: rau-cu, trai-cay — will be auto-filled from name</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Icon (Image)
                                    </label>
                                    <?php if ($editCategory && !empty($editCategory['icon'])): ?>
                                        <div class="mb-3">
                                            <?php $ic = $editCategory['icon']; ?>
                                            <?php if (preg_match('/\.(jpg|jpeg|png|gif|svg)$/i', $ic) || strpos($ic, '/images/') !== false || strpos($ic, 'http') === 0): ?>
                                                <img src="<?= imageUrl($ic) ?>" alt="icon" class="w-16 h-16 object-cover rounded-md border">
                                            <?php else: ?>
                                                <div class="w-16 h-16 rounded-md bg-green-50 flex items-center justify-center text-green-600">
                                                    <span class="material-symbols-outlined"><?= sanitize($editCategory['icon']) ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div style="position:relative;">
                                        <input type="file" name="icon" accept="image/*" id="iconInput"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        <button type="button" onclick="document.getElementById('iconInput').value=''; iconPreview.src=''; iconPreview.style.display='none';"
                                            class="mt-3 px-2 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600" style="z-index:2;">Xóa Tệp</button>
                                    </div>

                                    <img id="iconPreview" src="" style="display:none;max-width:64px;margin-top:8px;" />
                                    <p class="text-xs text-gray-500 mt-1">Upload an image file (jpg, png, svg). Leave empty để giữ icon hiện tại.</p>
                                    <script>
                                        document.getElementById('iconInput').addEventListener('change', function(e) {
                                            const [file] = e.target.files;
                                            if (file) {
                                                iconPreview.src = URL.createObjectURL(file);
                                                iconPreview.style.display = 'block';
                                            } else {
                                                iconPreview.src = '';
                                                iconPreview.style.display = 'none';
                                            }
                                        });
                                    </script>
                                </div>
                            </div>

                            <div class="flex gap-3 mt-6">
                                <?php if ($editCategory): ?>
                                    <a href="categories.php" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-center hover:bg-gray-50">
                                        Hủy
                                    </a>
                                <?php endif; ?>
                                <button type="submit" name="save_category"
                                    class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                    <?= $editCategory ? 'Cập nhật' : 'Thêm mới' ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Categories List -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-gray-600">ID</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-gray-600">Icon</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-gray-600">Tên danh mục</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-gray-600">Slug</th>
                                    <th class="text-center py-3 px-4 font-semibold text-sm text-gray-600">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="py-4 px-4 font-medium text-gray-900"><?= $cat['id'] ?></td>
                                        <td class="py-4 px-4">
                                            <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center overflow-hidden">
                                                <?php $iconVal = $cat['icon'] ?? ''; ?>
                                                <?php if ($iconVal && (preg_match('/\.(jpg|jpeg|png|gif|svg)$/i', $iconVal) || strpos($iconVal, '/images/') !== false || strpos($iconVal, 'http') === 0)): ?>
                                                    <img src="<?= imageUrl($iconVal) ?>" alt="icon" class="w-full h-full object-cover">
                                                <?php else: ?>
                                                    <span class="material-symbols-outlined text-green-600"><?= sanitize($iconVal ?: 'category') ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 font-medium text-gray-900"><?= htmlspecialchars(strip_tags(html_entity_decode($cat['name'], ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="py-4 px-4 text-gray-600 text-sm font-mono"><?= sanitize($cat['slug']) ?></td>
                                        <td class="py-4 px-4">
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="?edit=<?= $cat['id'] ?>"
                                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                                                    <span class="material-symbols-outlined text-lg">edit</span>
                                                </a>
                                                <button onclick="deleteCategory(<?= $cat['id'] ?>)"
                                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                                    <span class="material-symbols-outlined text-lg">delete</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function deleteCategory(id) {
            if (confirm('Bạn có chắc chắn muốn xóa danh mục này?\nLưu ý: Các sản phẩm thuộc danh mục này sẽ không có danh mục.')) {
                window.location.href = '?delete=' + id;
            }
        }
    </script>

    <script>
        // Auto-generate slug from name for convenience
        function slugify(text) {
            return text.toString().toLowerCase()
                .normalize('NFKD')
                .replace(/[\u0300-\u036f]/g, '') // remove diacritics
                .replace(/[^az0--9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        const nameInput = document.querySelector('input[name="name"]');
        const slugInput = document.getElementById('slugInput');
        if (nameInput && slugInput) {
            nameInput.addEventListener('input', function() {
                // Only auto-fill when slug is empty or matches previous auto slug
                slugInput.value = slugify(this.value);
            });
        }
    </script>

    <style>
        /* ===== RESPONSIVE FOR ADMIN TABLES ===== */
        /* Mobile: < 768px */
        @media (max-width: 767px) {
            table {
                font-size: 0.75rem !important;
            }
            
            th, td {
                padding: 0.5rem 0.25rem !important;
            }
            
            .btn {
                padding: 0.25rem 0.5rem !important;
                font-size: 0.7rem !important;
            }
        }

        /* Tablet: 768px - 1024px */
        @media (min-width: 768px) and (max-width: 1024px) {
            table {
                font-size: 0.85rem !important;
            }
            
            th, td {
                padding: 0.6rem 0.4rem !important;
            }
        }
    </style>

</body>

</html>