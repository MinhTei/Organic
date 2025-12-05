
<?php
/**
 * admin/orders.php - Trang Quản lý Đơn hàng
 *
 * Chức năng:
 * - Hiển thị danh sách đơn hàng, lọc theo trạng thái, tìm kiếm
 * - Cập nhật trạng thái đơn hàng (chờ, xác nhận, giao, hoàn thành, hủy)
 * - Xem chi tiết đơn hàng
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

// Xử lý cập nhật trạng thái đơn hàng
if (isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $status = sanitize($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if ($stmt->execute([$status, $orderId])) {
        $success = 'Cập nhật trạng thái đơn hàng thành công!';
    }
}

// Lấy các tham số lọc, tìm kiếm, phân trang
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Xây dựng điều kiện truy vấn theo bộ lọc
$where = [];
$params = [];

if ($status) {
    $where[] = "o.status = ?";
    $params[] = $status;
}

if ($search) {
    $where[] = "(o.id = ? OR u.name LIKE ? OR u.email LIKE ?)";
    $params[] = $search;
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Lấy tổng số đơn hàng (phục vụ phân trang)
$countStmt = $conn->prepare("SELECT COUNT(*) FROM orders o LEFT JOIN users u ON o.user_id = u.id $whereClause");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Lấy danh sách đơn hàng theo trang, bộ lọc
$stmt = $conn->prepare("
    SELECT o.*, u.name as customer_name, u.email as customer_email
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    $whereClause 
    ORDER BY o.created_at DESC 
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Nhãn và màu cho các trạng thái đơn hàng
$statusLabels = [
    'pending' => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'shipping' => 'Đang giao',
    'delivered' => 'Đã giao',
    'cancelled' => 'Đã hủy'
];

$statusColors = [
    'pending' => 'bg-yellow-100 text-yellow-800',
    'confirmed' => 'bg-blue-100 text-blue-800',
    'shipping' => 'bg-purple-100 text-purple-800',
    'delivered' => 'bg-green-100 text-green-800',
    'cancelled' => 'bg-red-100 text-red-800'
];

$pageTitle = 'Quản lý Đơn hàng';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
</head>
<body class="bg-gray-50 font-['Be_Vietnam_Pro']">
    
    <!-- Header (match admin style) -->
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
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg"><?= $success ?></div>
            <?php endif; ?>

            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Quản lý Đơn hàng</h2>
                <p class="text-gray-600 mt-1">Tổng cộng <?= $total ?> đơn hàng</p>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
                <form method="GET" class="flex gap-4">
                    <input type="text" name="search" value="<?= sanitize($search) ?>" 
                           placeholder="Tìm theo mã đơn, tên, email..."
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    
                    <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">Tất cả trạng thái</option>
                        <?php foreach ($statusLabels as $key => $label): ?>
                            <option value="<?= $key ?>" <?= $status == $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit" class="px-6 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800">
                        Lọc
                    </button>
                </form>
            </div>

            <!-- Orders Table -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left py-3 px-4 font-semibold text-sm">Mã ĐH</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm">Khách hàng</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm">Tổng tiền</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm">Trạng thái</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm">Ngày đặt</th>
                            <th class="text-center py-3 px-4 font-semibold text-sm">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-4 px-4 font-medium">#<?= $order['id'] ?></td>
                            <td class="py-4 px-4">
                                <div>
                                    <p class="font-medium"><?= sanitize($order['customer_name'] ?? 'Khách') ?></p>
                                    <p class="text-sm text-gray-500"><?= sanitize($order['customer_email'] ?? '') ?></p>
                                </div>
                            </td>
                            <td class="py-4 px-4 font-semibold text-green-600">
                                <?= formatPrice($order['total_amount']) ?>
                            </td>
                            <td class="py-4 px-4">
                                <?php if ($order['status'] === 'cancelled'): ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $statusColors[$order['status']] ?>" style="opacity: 0.5; cursor: not-allowed;">
                                        <?= $statusLabels[$order['status']] ?>
                                    </span>
                                <?php else: ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <select name="status" onchange="this.form.submit()"
                                                class="px-3 py-1 rounded-full text-xs font-semibold <?= $statusColors[$order['status']] ?> border-none cursor-pointer">
                                            <?php foreach ($statusLabels as $key => $label): ?>
                                                <option value="<?= $key ?>" <?= $order['status'] == $key ? 'selected' : '' ?>>
                                                    <?= $label ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-4 text-sm text-gray-600">
                                <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <a href="order_detail.php?id=<?= $order['id'] ?>" 
                                   class="inline-flex items-center gap-1 px-3 py-1 text-blue-600 hover:bg-blue-50 rounded-lg">
                                    <span class="material-symbols-outlined text-lg">visibility</span>
                                    <span class="text-sm">Chi tiết</span>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="flex justify-center mt-6">
                <nav class="flex gap-2">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?><?= $status ? '&status=' . $status : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
                           class="px-4 py-2 rounded-lg <?= $i == $page ? 'bg-green-600 text-white' : 'bg-white hover:bg-gray-100' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            </div>
            <?php endif; ?>
        </main>
    </div>

</body>
</html>