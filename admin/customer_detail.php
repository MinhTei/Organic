<?php
/**
 * admin/customer_detail.php - Chi tiết khách hàng (Admin)
 */

require_once '../config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    redirect(SITE_URL . '/auth.php');
}

$customerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$customerId) {
    redirect('customers.php');
}

$conn = getConnection();

// Lấy thông tin khách hàng
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id AND role = 'customer'");
$stmt->execute([':id' => $customerId]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    redirect('customers.php');
}

// Lấy các đơn hàng của khách hàng
$stmt = $conn->prepare("
    SELECT o.*, 
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
    FROM orders o 
    WHERE o.user_id = :user_id 
    ORDER BY o.created_at DESC
");
$stmt->execute([':user_id' => $customerId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Thống kê của khách hàng
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_orders,
        COALESCE(SUM(final_amount), 0) as total_spent,
        COALESCE(AVG(final_amount), 0) as avg_order_value
    FROM orders 
    WHERE user_id = :user_id AND status != 'cancelled'
");
$stmt->execute([':user_id' => $customerId]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Định nghĩa nhãn trạng thái
$statusLabels = [
    'pending' => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'processing' => 'Đang xử lý',
    'shipping' => 'Đang giao',
    'delivered' => 'Đã giao',
    'cancelled' => 'Đã hủy',
    'refunded' => 'Đã hoàn tiền'
];

$statusColors = [
    'pending' => '#f59e0b',
    'confirmed' => '#3b82f6',
    'processing' => '#06b6d4',
    'shipping' => '#06b6d4',
    'delivered' => '#22c55e',
    'cancelled' => '#ef4444',
    'refunded' => '#8b5cf6'
];

$membershipLabels = [
    'bronze' => 'Đồng',
    'silver' => 'Bạc',
    'gold' => 'Vàng',
    'platinum' => 'Bạch kim'
];

$membershipColors = [
    'bronze' => '#8b6f47',
    'silver' => '#a8a9ad',
    'gold' => '#ffa500',
    'platinum' => '#e5e7eb'
];

$pageTitle = 'Chi tiết khách hàng - ' . $customer['name'];
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
            <!-- Breadcrumb -->
            <div class="mb-6 flex items-center gap-2 text-sm text-gray-600">
                <a href="<?= SITE_URL ?>/admin/customers.php" class="text-blue-600 hover:underline">Quản lý Khách hàng</a>
                <span>/</span>
                <span><?= sanitize($customer['name']) ?></span>
            </div>

            <!-- Customer Header -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-start gap-4">
                        <div class="w-20 h-20 rounded-lg bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-white text-3xl font-bold">
                            <?= strtoupper(substr($customer['name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900"><?= sanitize($customer['name']) ?></h2>
                            <p class="text-gray-500 text-sm mt-1">ID: <?= $customer['id'] ?></p>
                            <div class="flex items-center gap-2 mt-3">
                                <span style="
                                    display: inline-block;
                                    background: <?= $membershipColors[$customer['membership']] ?>20;
                                    color: <?= $membershipColors[$customer['membership']] ?>;
                                    padding: 0.25rem 0.75rem;
                                    border-radius: 9999px;
                                    font-weight: 600;
                                    font-size: 0.75rem;
                                    border: 1px solid <?= $membershipColors[$customer['membership']] ?>40;
                                ">
                                    Hạng <?= $membershipLabels[$customer['membership']] ?>
                                </span>
                                <span style="
                                    display: inline-block;
                                    background: <?= $customer['status'] === 'active' ? '#d1fae5' : '#fee2e2' ?>;
                                    color: <?= $customer['status'] === 'active' ? '#059669' : '#dc2626' ?>;
                                    padding: 0.25rem 0.75rem;
                                    border-radius: 9999px;
                                    font-weight: 600;
                                    font-size: 0.75rem;
                                ">
                                    <?= $customer['status'] === 'active' ? '✓ Hoạt động' : '✗ Bị khóa' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Info Grid -->
            <div class="grid grid-cols-3 gap-6 mb-6">
                <!-- Contact Info -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Thông tin liên hệ</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-gray-500 mb-1">Email</p>
                            <p class="font-medium text-gray-900"><?= sanitize($customer['email']) ?></p>
                            <?php if ($customer['email_verified']): ?>
                                <p class="text-xs text-green-600 mt-1">✓ Đã xác minh</p>
                            <?php else: ?>
                                <p class="text-xs text-gray-400 mt-1">Chưa xác minh</p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="text-gray-500 mb-1">Điện thoại</p>
                            <p class="font-medium text-gray-900"><?= sanitize($customer['phone'] ?? 'Chưa cập nhật') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Thống kê</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-gray-500 text-sm mb-1">Tổng đơn hàng</p>
                            <p class="text-3xl font-bold text-green-600"><?= $stats['total_orders'] ?></p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm mb-1">Tổng chi tiêu</p>
                            <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_spent'], 0) ?>đ</p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm mb-1">Trung bình/đơn</p>
                            <p class="text-lg font-bold text-gray-700"><?= number_format($stats['avg_order_value'], 0) ?>đ</p>
                        </div>
                    </div>
                </div>

                <!-- Account Info -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Tài khoản</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-gray-500 mb-1">Điểm thưởng</p>
                            <p class="text-2xl font-bold text-green-600"><?= $customer['points'] ?></p>
                        </div>
                        <div>
                            <p class="text-gray-500 mb-1">Ngày tham gia</p>
                            <p class="font-medium text-gray-900"><?= date('d/m/Y', strtotime($customer['created_at'])) ?></p>
                        </div>
                        <div>
                            <p class="text-gray-500 mb-1">Đăng nhập cuối</p>
                            <p class="font-medium text-gray-900"><?= $customer['last_login_at'] ? date('d/m/Y H:i', strtotime($customer['last_login_at'])) : 'Chưa bao giờ' ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders Section -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Lịch sử đơn hàng</h3>
                
                <?php if (empty($orders)): ?>
                    <div class="text-center py-12">
                        <span class="material-symbols-outlined text-gray-300 text-5xl">shopping_cart</span>
                        <p class="text-gray-500 mt-2">Khách hàng chưa có đơn hàng</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-gray-700">Mã đơn</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-gray-700">Ngày đặt</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-gray-700">Sản phẩm</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-gray-700">Tổng tiền</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-gray-700">Trạng thái</th>
                                    <th class="text-left py-3 px-4 font-semibold text-sm text-gray-700">Hành động</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <?php foreach ($orders as $order): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="py-4 px-4">
                                        <a href="<?= SITE_URL ?>/admin/order_detail.php?id=<?= $order['id'] ?>" class="text-blue-600 hover:underline font-medium">
                                            #<?= $order['id'] ?>
                                        </a>
                                    </td>
                                    <td class="py-4 px-4 text-sm text-gray-700">
                                        <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                    </td>
                                    <td class="py-4 px-4 text-sm text-gray-700">
                                        <?= $order['item_count'] ?> sản phẩm
                                    </td>
                                    <td class="py-4 px-4 font-medium text-gray-900">
                                        <?= number_format($order['final_amount'], 0) ?>đ
                                    </td>
                                    <td class="py-4 px-4">
                                        <span style="
                                            display: inline-block;
                                            background: <?= $statusColors[$order['status']] ?>20;
                                            color: <?= $statusColors[$order['status']] ?>;
                                            padding: 0.25rem 0.75rem;
                                            border-radius: 9999px;
                                            font-weight: 600;
                                            font-size: 0.75rem;
                                        ">
                                            <?= $statusLabels[$order['status']] ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <a href="<?= SITE_URL ?>/admin/order_detail.php?id=<?= $order['id'] ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            Xem chi tiết →
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
</body>
</html>