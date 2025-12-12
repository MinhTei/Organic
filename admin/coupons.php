<?php

/**
 * admin/coupons.php - Quản lý mã giảm giá
 */
session_start();

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit("Bạn không có quyền truy cập.");
}

require_once '../includes/config.php';
$conn = getConnection();

// Xử lý thêm/sửa mã giảm giá
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_edit') {
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $discount_type = $_POST['discount_type'] ?? 'percentage';
        $discount_value = (float)($_POST['discount_value'] ?? 0);
        $min_order_value = (float)($_POST['min_order_value'] ?? 0);
        $max_discount = !empty($_POST['max_discount']) ? (float)$_POST['max_discount'] : null;
        $usage_limit = !empty($_POST['usage_limit']) ? (int)$_POST['usage_limit'] : null;
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // Validation
        if (empty($code) || empty($discount_value)) {
            $message = 'Mã giảm giá và giá trị không được bỏ trống.';
            $messageType = 'error';
        } else {
            if ($id) {
                // Update
                try {
                    $stmt = $conn->prepare("
                        UPDATE coupons SET 
                        code = ?, 
                        description = ?, 
                        discount_type = ?, 
                        discount_value = ?, 
                        min_order_value = ?, 
                        max_discount = ?, 
                        usage_limit = ?, 
                        start_date = ?, 
                        end_date = ?, 
                        is_active = ?,
                        updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $code,
                        $description,
                        $discount_type,
                        $discount_value,
                        $min_order_value,
                        $max_discount,
                        $usage_limit,
                        $start_date,
                        $end_date,
                        $is_active,
                        $id
                    ]);
                    $message = 'Cập nhật mã giảm giá thành công!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Lỗi: ' . $e->getMessage();
                    $messageType = 'error';
                }
            } else {
                // Add
                try {
                    $stmt = $conn->prepare("
                        INSERT INTO coupons (
                        code, description, discount_type, discount_value,
                        min_order_value, max_discount, usage_limit,
                        start_date, end_date, is_active
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $code,
                        $description,
                        $discount_type,
                        $discount_value,
                        $min_order_value,
                        $max_discount,
                        $usage_limit,
                        $start_date,
                        $end_date,
                        $is_active
                    ]);
                    $message = 'Thêm mã giảm giá thành công!';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Lỗi: ' . $e->getMessage();
                    $messageType = 'error';
                }
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = (int)$_POST['id'];
        try {
            $conn->prepare("DELETE FROM coupons WHERE id = ?")->execute([$id]);
            $message = 'Xóa mã giảm giá thành công!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Lỗi: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// If this was an AJAX POST, return JSON and set a flash message in session
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if ($isAjax) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $messageType;
        header('Content-Type: application/json');
        echo json_encode([
            'success' => ($messageType === 'success'),
            'message' => $message
        ]);
        exit;
    }
}

// Lấy danh sách mã giảm giá
$coupons = $conn->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Lấy thông tin mã giảm giá để chỉnh sửa
$editingCoupon = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM coupons WHERE id = ?");
    $stmt->execute([$id]);
    $editingCoupon = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Lấy thống kê
$stats = [
    'total' => (int)$conn->query("SELECT COUNT(*) FROM coupons")->fetchColumn(),
    'active' => (int)$conn->query("SELECT COUNT(*) FROM coupons WHERE is_active = 1")->fetchColumn(),
    'inactive' => (int)$conn->query("SELECT COUNT(*) FROM coupons WHERE is_active = 0")->fetchColumn(),
];
// If there is a flash message from AJAX redirect, use it
if (empty($message) && isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $messageType = $_SESSION['flash_type'] ?? 'success';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý mã giảm giá - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
</head>

<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include '_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200 sticky top-0 z-40">
                <div class="flex items-center justify-between px-6 py-4">
                    <h1 class="text-2xl font-bold text-gray-900">Quản lý mã giảm giá</h1>
                    <button onclick="openForm()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <span class="material-symbols-outlined">add</span>
                        Thêm mã mới
                    </button>
                </div>
            </div>

            <!-- Message Alert -->
            <?php if ($message): ?>
                <div class="mx-6 mt-4 px-4 py-3 rounded-lg <?= $messageType === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <div class="p-6">
                <!-- Thống kê -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white p-4 rounded-lg border border-gray-200">
                        <p class="text-sm text-gray-600">Tổng mã</p>
                        <p class="text-3xl font-bold text-gray-900"><?= $stats['total'] ?></p>
                    </div>
                    <div class="bg-white p-4 rounded-lg border border-gray-200">
                        <p class="text-sm text-gray-600">Đang hoạt động</p>
                        <p class="text-3xl font-bold text-green-600"><?= $stats['active'] ?></p>
                    </div>
                    <div class="bg-white p-4 rounded-lg border border-gray-200">
                        <p class="text-sm text-gray-600">Vô hiệu hóa</p>
                        <p class="text-3xl font-bold text-red-600"><?= $stats['inactive'] ?></p>
                    </div>
                </div>

                <!-- Form thêm/sửa -->
                <div id="formContainer" class="hidden bg-white rounded-lg border border-gray-200 p-6 mb-6">
                    <h2 class="text-xl font-bold mb-4"><?= $editingCoupon ? 'Chỉnh sửa mã giảm giá' : 'Thêm mã giảm giá mới' ?></h2>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="add_edit">
                        <?php if ($editingCoupon): ?>
                            <input type="hidden" name="id" value="<?= $editingCoupon['id'] ?>">
                        <?php endif; ?>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Mã giảm giá -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mã giảm giá *</label>
                                <input type="text" name="code" required value="<?= $editingCoupon['code'] ?? '' ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="VD: WELCOME10">
                            </div>

                            <!-- Loại giảm giá -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Loại giảm giá *</label>
                                <select name="discount_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="percentage" <?= ($editingCoupon['discount_type'] ?? '') === 'percentage' ? 'selected' : '' ?>>Phần trăm (%)</option>
                                    <option value="fixed" <?= ($editingCoupon['discount_type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Số tiền cố định</option>
                                </select>
                            </div>

                            <!-- Giá trị giảm giá -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Giá trị giảm giá *</label>
                                <input type="number" name="discount_value" required step="0.01" value="<?= $editingCoupon['discount_value'] ?? '' ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="VD: 10">
                            </div>

                            <!-- Giá trị đơn hàng tối thiểu -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Giá trị đơn tối thiểu</label>
                                <input type="number" name="min_order_value" step="0.01" value="<?= $editingCoupon['min_order_value'] ?? '' ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="VD: 200000">
                            </div>

                            <!-- Giảm giá tối đa -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Giảm giá tối đa (cho %)</label>
                                <input type="number" name="max_discount" step="0.01" value="<?= $editingCoupon['max_discount'] ?? '' ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="VD: 50000">
                            </div>

                            <!-- Số lần sử dụng tối đa -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Số lần sử dụng tối đa</label>
                                <input type="number" name="usage_limit" value="<?= $editingCoupon['usage_limit'] ?? '' ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Để trống = không giới hạn">
                            </div>

                            <!-- Ngày bắt đầu -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ngày bắt đầu</label>
                                <?php
                                $startVal = '';
                                if (!empty($editingCoupon) && !empty($editingCoupon['start_date'])) {
                                    try {
                                        $startVal = (new DateTime($editingCoupon['start_date']))->format('Y-m-d\TH:i');
                                    } catch (Exception $e) {
                                        $startVal = '';
                                    }
                                }
                                ?>
                                <input type="datetime-local" name="start_date" value="<?= $startVal ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            </div>

                            <!-- Ngày kết thúc -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ngày kết thúc</label>
                                <?php
                                $endVal = '';
                                if (!empty($editingCoupon) && !empty($editingCoupon['end_date'])) {
                                    try {
                                        $endVal = (new DateTime($editingCoupon['end_date']))->format('Y-m-d\TH:i');
                                    } catch (Exception $e) {
                                        $endVal = '';
                                    }
                                }
                                ?>
                                <input type="datetime-local" name="end_date" value="<?= $endVal ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            </div>
                        </div>

                        <!-- Mô tả -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                            <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Mô tả về mã giảm giá..."><?= $editingCoupon['description'] ?? '' ?></textarea>
                        </div>

                        <!-- Trạng thái -->
                        <div class="flex items-center">
                            <input type="checkbox" id="is_active" name="is_active" <?= ($editingCoupon['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 text-green-600 rounded focus:ring-green-500">
                            <label for="is_active" class="ml-2 text-sm text-gray-700">Kích hoạt mã này</label>
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-2 justify-end">
                            <button type="button" onclick="closeForm()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Hủy</button>
                            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">Lưu</button>
                        </div>
                    </form>
                </div>

                <!-- Danh sách mã giảm giá -->
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Mã</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Mô tả</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Giảm giá</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Giá trị tối thiểu</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Sử dụng / Giới hạn</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Ngày hết hạn</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Trạng thái</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Hành động</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($coupons as $coupon): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 font-semibold text-gray-900"><?= htmlspecialchars($coupon['code']) ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-600"><?= substr($coupon['description'] ?? '', 0, 50) ?></td>
                                        <td class="px-6 py-4">
                                            <span class="font-semibold">
                                                <?= $coupon['discount_value'] ?>
                                                <?= $coupon['discount_type'] === 'percentage' ? '%' : 'đ' ?>
                                            </span>
                                            <?php if ($coupon['discount_type'] === 'percentage' && $coupon['max_discount']): ?>
                                                <span class="text-xs text-gray-500">(max: <?= number_format($coupon['max_discount']) ?>đ)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm"><?= number_format($coupon['min_order_value']) ?>đ</td>
                                        <td class="px-6 py-4 text-sm">
                                            <?= $coupon['used_count'] ?>
                                            <?php if ($coupon['usage_limit']): ?>
                                                / <?= $coupon['usage_limit'] ?>
                                            <?php else: ?>
                                                / ∞
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <?php if ($coupon['end_date']): ?>
                                                <?= date('d/m/Y', strtotime($coupon['end_date'])) ?>
                                            <?php else: ?>
                                                <span class="text-gray-400">Vô hạn</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 rounded text-xs font-medium <?= $coupon['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                                <?= $coupon['is_active'] ? 'Hoạt động' : 'Vô hiệu' ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex gap-2">
                                                <a href="?edit=<?= $coupon['id'] ?>" class="text-blue-600 hover:text-blue-800 text-sm">Sửa</a>
                                                <button onclick="deleteCoupon(<?= $coupon['id'] ?>)" class="text-red-600 hover:text-red-800 text-sm">Xóa</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (empty($coupons)): ?>
                        <div class="p-8 text-center">
                            <p class="text-gray-500">Chưa có mã giảm giá nào. <a href="#" onclick="openForm()" class="text-green-600 hover:text-green-700 font-medium">Thêm mã mới</a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal xóa -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-sm">
            <h3 class="text-lg font-bold mb-4">Xác nhận xóa?</h3>
            <p class="text-gray-600 mb-6">Bạn có chắc chắn muốn xóa mã giảm giá này không?</p>
            <form method="POST" class="flex gap-3 justify-end">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" id="deleteId" name="id">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Hủy</button>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">Xóa</button>
            </form>
        </div>
    </div>

    <script>
        function openForm() {
            document.getElementById('formContainer').classList.remove('hidden');
            document.querySelector('form')?.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        function closeForm() {
            document.getElementById('formContainer').classList.add('hidden');
        }

        function deleteCoupon(id) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        // Hiển thị form khi chỉnh sửa
        <?php if ($editingCoupon): ?>
            openForm();
        <?php endif; ?>
    </script>
    <script>
        // Intercept the add/edit form submission to use AJAX so we can close the form on success
        (function() {
            const form = document.querySelector('form[method="POST"]');
            if (!form) return;

            form.addEventListener('submit', function(e) {
                // Only intercept the add/edit form (it has hidden input action=add_edit)
                const actionInput = form.querySelector('input[name="action"]');
                if (!actionInput || actionInput.value !== 'add_edit') return; // allow normal submit for other forms

                e.preventDefault();

                const fd = new FormData(form);

                fetch(window.location.href, {
                        method: 'POST',
                        body: fd,
                        credentials: 'include',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).then(res => res.json())
                    .then(data => {
                        if (data && data.success) {
                            // close the form and refresh to show updated list and flash message
                            try {
                                closeForm();
                            } catch (e) {}
                            setTimeout(() => {
                                window.location.reload();
                            }, 300);
                        } else {
                            alert(data && data.message ? data.message : 'Lưu thất bại');
                        }
                    }).catch(err => {
                        console.error('Save coupon failed', err);
                        alert('Có lỗi khi lưu mã giảm giá.');
                    });
            });
        })();
    </script>
</body>

</html>