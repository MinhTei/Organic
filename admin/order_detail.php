<?php
/**
 * admin/order_detail.php - Chi tiết đơn hàng (Admin)
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    redirect(SITE_URL . '/auth.php');
}

$orderId = (int)($_GET['id'] ?? 0);
if (!$orderId) {
    redirect(SITE_URL . '/admin/orders.php');
}

$conn = getConnection();

// Lấy thông tin đơn hàng
$stmt = $conn->prepare("
    SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    redirect(SITE_URL . '/admin/orders.php');
}

// Lấy các sản phẩm trong đơn hàng
$stmt = $conn->prepare("
    SELECT oi.*, p.name as product_name, p.image as product_image
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Nhãn và màu cho trạng thái
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

$paymentMethods = [
    'cod' => 'Thanh toán khi nhận',
    'bank_transfer' => 'Chuyển khoản'
];

$pageTitle = 'Chi tiết Đơn hàng';
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
                <a href="<?= SITE_URL ?>/admin/orders.php" class="text-blue-600 hover:underline">Quản lý Đơn hàng</a>
                <span>/</span>
                <span>Chi tiết Đơn hàng #<?= $order['id'] ?></span>
            </div>

            <!-- Order Header -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Đơn hàng #<?= $order['id'] ?></h2>
                        <p class="text-gray-500 text-sm mt-1"><?= $order['order_code'] ?></p>
                    </div>
                    <span style="
                        display: inline-block;
                        background: <?= $statusColors[$order['status']] ?>20;
                        color: <?= $statusColors[$order['status']] ?>;
                        padding: 0.5rem 1rem;
                        border-radius: 9999px;
                        font-weight: 600;
                        font-size: 0.875rem;
                    " <?= $order['status'] === 'cancelled' ? 'style="opacity: 0.5;"' : '' ?>>
                        <?= $statusLabels[$order['status']] ?>
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Ngày đặt</p>
                        <p class="font-medium text-gray-900"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500">Phương thức thanh toán</p>
                        <p class="font-medium text-gray-900"><?= $paymentMethods[$order['payment_method']] ?? $order['payment_method'] ?></p>
                    </div>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Thông tin khách hàng</h3>
                    <div class="space-y-2 text-sm">
                        <p><span class="text-gray-500">Tên:</span> <span class="font-medium"><?= sanitize($order['customer_name']) ?></span></p>
                        <p><span class="text-gray-500">Email:</span> <span class="font-medium"><?= sanitize($order['customer_email']) ?></span></p>
                        <p><span class="text-gray-500">Điện thoại:</span> <span class="font-medium"><?= sanitize($order['customer_phone']) ?></span></p>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Địa chỉ giao hàng</h3>
                    <div class="space-y-2 text-sm">
                        <p><span class="text-gray-500">Người nhận:</span> <span class="font-medium"><?= sanitize($order['shipping_name']) ?></span></p>
                        <p><span class="text-gray-500">Điện thoại:</span> <span class="font-medium"><?= sanitize($order['shipping_phone']) ?></span></p>
                        <p><span class="text-gray-500">Email:</span> <span class="font-medium"><?= sanitize($order['shipping_email'] ?? '') ?></span></p>
                        <p><span class="text-gray-500">Địa chỉ:</span> <span class="font-medium"><?= sanitize($order['shipping_address']) ?></span></p>
                        <p><span class="text-gray-500">Phường/Xã:</span> <span class="font-medium"><?= sanitize($order['shipping_ward']) ?></span></p>
                        <p><span class="text-gray-500">Quận/Huyện:</span> <span class="font-medium"><?= sanitize($order['shipping_district']) ?></span></p>
                        <p><span class="text-gray-500">Thành phố:</span> <span class="font-medium"><?= sanitize($order['shipping_city']) ?></span></p>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Chi tiết sản phẩm</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b">
                            <tr>
                                <th class="text-left py-3 px-4 font-semibold">Sản phẩm</th>
                                <th class="text-left py-3 px-4 font-semibold">Giá</th>
                                <th class="text-left py-3 px-4 font-semibold">Số lượng</th>
                                <th class="text-left py-3 px-4 font-semibold">Tổng</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr class="border-b">
                                <td class="py-4 px-4">
                                    <div class="flex gap-3">
                                        <?php if (!empty($item['product_image'])): ?>
                                            <img src="<?= SITE_URL ?>/<?= $item['product_image'] ?>" alt="<?= sanitize($item['product_name']) ?>" class="w-20 h-20 rounded object-cover">
                                        <?php else: ?>
                                            <div class="w-20 h-20 rounded bg-gray-200 flex items-center justify-center">
                                                <span class="material-symbols-outlined text-gray-400">image</span>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <p class="font-medium"><?= sanitize($item['product_name']) ?></p>
                                            <p class="text-xs text-gray-500">#<?= $item['product_id'] ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4"><?= formatPrice($item['unit_price']) ?></td>
                                <td class="py-4 px-4"><?= $item['quantity'] ?></td>
                                <td class="py-4 px-4 font-semibold"><?= formatPrice($item['unit_price'] * $item['quantity']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6 max-w-md ml-auto">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Tóm tắt đơn hàng</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tổng phụ:</span>
                        <span class="font-medium"><?= formatPrice($order['total_amount']) ?></span>
                    </div>
                    <?php if ($order['discount_amount'] > 0): ?>
                    <div class="flex justify-between text-red-600">
                        <span>Giảm giá:</span>
                        <span class="font-medium">-<?= formatPrice($order['discount_amount']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($order['shipping_fee'] > 0): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Phí vận chuyển:</span>
                        <span class="font-medium"><?= formatPrice($order['shipping_fee']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($order['tax_amount'] > 0): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Thuế:</span>
                        <span class="font-medium"><?= formatPrice($order['tax_amount']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="pt-3 border-t flex justify-between">
                        <span class="font-bold">Tổng tiền:</span>
                        <span class="font-bold text-green-600 text-lg"><?= formatPrice($order['final_amount']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <div class="mb-6">
                <a href="<?= SITE_URL ?>/admin/orders.php" class="text-blue-600 hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-lg">arrow_back</span>
                    Quay lại danh sách đơn hàng
                </a>
            </div>
        </main>
    </div>

    <style>
        /* ===== RESPONSIVE FOR ADMIN DETAIL PAGES ===== */
        /* Mobile: < 768px */
        @media (max-width: 767px) {
            table {
                font-size: 0.75rem !important;
            }
            
            th, td {
                padding: 0.5rem 0.25rem !important;
            }
            
            h1 {
                font-size: 1.25rem !important;
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
