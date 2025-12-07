<?php

/**
 * admin/product_import.php - Import danh sách sản phẩm từ Excel
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/import_helper.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    redirect(SITE_URL . '/auth.php');
}

$conn = getConnection();
$success = '';
$error = '';
$importResult = null;

// Load categories
$categories = getCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra file upload
    if (empty($_FILES['import_file']['name'])) {
        $error = 'Vui lòng chọn file để import.';
    } else if ($_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Lỗi upload file: ' . $_FILES['import_file']['error'];
    } else {
        $categoryId = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;

        // Lưu file tạm thời
        $uploadDir = sys_get_temp_dir();
        $tmpFile = $uploadDir . DIRECTORY_SEPARATOR . 'import_' . time() . '_' . basename($_FILES['import_file']['name']);

        if (move_uploaded_file($_FILES['import_file']['tmp_name'], $tmpFile)) {
            // Thực hiện import
            $importResult = importProductsFromExcel($tmpFile, $categoryId);

            // Xóa file tạm
            @unlink($tmpFile);

            if ($importResult['success'] > 0) {
                $success = "Import thành công {$importResult['success']} sản phẩm!";
            }

            if (!empty($importResult['errors'])) {
                $error = "Có " . count($importResult['errors']) . " lỗi trong quá trình import.";
            }
        } else {
            $error = 'Không thể lưu file upload.';
        }
    }
}

$pageTitle = 'Import sản phẩm';
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
    <style>
        body {
            font-family: 'Be Vietnam Pro', sans-serif
        }
    </style>
</head>

<body class="bg-gray-50">
    <?php
    $headerPath = __DIR__ . '/../includes/header.php';
    if (file_exists($headerPath)) {
        include $headerPath;
    }
    ?>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-6">Import sản phẩm từ Excel</h1>

        <?php if ($error): ?>
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded">
                <strong>Lỗi:</strong> <?= is_array($error) ? implode(', ', array_map('sanitize', $error)) : sanitize($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
                <strong>Thành công:</strong> <?= is_array($success) ? implode(', ', array_map('sanitize', $success)) : sanitize($success) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Import -->
            <div class="lg:col-span-2">
                <div class="bg-white p-6 rounded-lg border">
                    <h2 class="text-lg font-semibold mb-4">1. Chuẩn bị dữ liệu</h2>

                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded">
                        <p class="text-sm mb-3"><strong>Hướng dẫn:</strong></p>
                        <ul class="text-sm list-disc list-inside space-y-1 text-gray-700">
                            <li>File phải là Excel (.xlsx, .xls) hoặc CSV</li>
                            <li>Hàng đầu tiên phải là header (tên cột)</li>
                            <li>Các cột bắt buộc: <strong>Tên sản phẩm</strong>, <strong>Giá</strong></li>
                            <li>Các cột tùy chọn: Danh mục, Mô tả, Đơn vị, Tồn kho, Giá giảm, Hữu cơ, Mới</li>
                            <li>Sản phẩm trùng slug sẽ bị bỏ qua</li>
                        </ul>
                    </div>

                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Chọn file Excel</label>
                            <input type="file" name="import_file" accept=".xlsx,.xls,.csv" required
                                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Danh mục (không bắt buộc)</label>
                            <select name="category_id" class="w-full border px-3 py-2 rounded">
                                <option value="">-- Lấy từ file hoặc để trống --</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= sanitize($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Nếu chọn danh mục, tất cả sản phẩm import sẽ thuộc danh mục này</p>
                        </div>

                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium">
                            <span class="material-symbols-outlined inline mr-1">upload_file</span> Import
                        </button>
                    </form>
                </div>
            </div>

            <!-- Template & Guide -->
            <div>
                <div class="bg-white p-6 rounded-lg border mb-6">
                    <h2 class="text-lg font-semibold mb-4">2. Tải template</h2>
                    <p class="text-sm text-gray-600 mb-4">Tải file mẫu để biết cách định dạng dữ liệu:</p>
                    <a href="download_template.php" class="block w-full px-4 py-2 bg-green-600 text-white text-center rounded hover:bg-green-700 font-medium">
                        <span class="material-symbols-outlined inline mr-1">download</span> Tải Template
                    </a>
                </div>

                <div class="bg-white p-6 rounded-lg border">
                    <h2 class="text-lg font-semibold mb-4">3. Ví dụ dữ liệu</h2>
                    <div class="text-xs overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border px-2 py-1 text-left">Tên</th>
                                    <th class="border px-2 py-1 text-left">Giá</th>
                                    <th class="border px-2 py-1 text-left">Danh mục</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border px-2 py-1">Cà rốt</td>
                                    <td class="border px-2 py-1">35000</td>
                                    <td class="border px-2 py-1">Rau củ</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="border px-2 py-1">Táo</td>
                                    <td class="border px-2 py-1">99000</td>
                                    <td class="border px-2 py-1">Trái cây</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kết quả Import -->
        <?php if ($importResult): ?>
            <div class="mt-8 bg-white p-6 rounded-lg border">
                <h2 class="text-lg font-semibold mb-4">Kết quả Import</h2>

                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="p-4 bg-green-50 border border-green-200 rounded text-center">
                        <div class="text-3xl font-bold text-green-600"><?= $importResult['success'] ?></div>
                        <div class="text-sm text-gray-600">Thêm thành công</div>
                    </div>
                    <div class="p-4 bg-red-50 border border-red-200 rounded text-center">
                        <div class="text-3xl font-bold text-red-600"><?= count($importResult['errors']) ?></div>
                        <div class="text-sm text-gray-600">Lỗi</div>
                    </div>
                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded text-center">
                        <div class="text-3xl font-bold text-yellow-600"><?= count($importResult['warnings']) ?></div>
                        <div class="text-sm text-gray-600">Cảnh báo</div>
                    </div>
                </div>

                <?php if (!empty($importResult['errors'])): ?>
                    <div class="mb-4">
                        <h3 class="font-semibold text-red-600 mb-2">Lỗi:</h3>
                        <div class="bg-red-50 border border-red-200 rounded p-4 max-h-60 overflow-y-auto">
                            <ul class="text-sm space-y-1">
                                <?php foreach ($importResult['errors'] as $err): ?>
                                    <li class="text-red-700">• <?= sanitize($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($importResult['warnings'])): ?>
                    <div>
                        <h3 class="font-semibold text-yellow-600 mb-2">Cảnh báo:</h3>
                        <div class="bg-yellow-50 border border-yellow-200 rounded p-4 max-h-60 overflow-y-auto">
                            <ul class="text-sm space-y-1">
                                <?php foreach ($importResult['warnings'] as $warn): ?>
                                    <li class="text-yellow-700">• <?= sanitize($warn) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="mt-6">
            <a href="products.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                <span class="material-symbols-outlined inline mr-1">arrow_back</span> Quay lại danh sách sản phẩm
            </a>
        </div>
    </div>

    <style>
        /* ===== RESPONSIVE FOR ADMIN FORMS ===== */
        /* Mobile: < 768px */
        @media (max-width: 767px) {
            input, textarea, select {
                font-size: 16px !important;
            }
            
            label {
                font-size: 0.85rem !important;
            }
            
            button, a {
                padding: 0.5rem 1rem !important;
                font-size: 0.9rem !important;
            }
            
            table {
                font-size: 0.75rem !important;
            }
            
            th, td {
                padding: 0.5rem 0.25rem !important;
            }
        }

        /* Tablet: 768px - 1024px */
        @media (min-width: 768px) and (max-width: 1024px) {
            input, textarea, select {
                padding: 0.6rem !important;
                font-size: 0.9rem !important;
            }
            
            table {
                font-size: 0.85rem !important;
            }
        }
    </style>

</body>

</html>