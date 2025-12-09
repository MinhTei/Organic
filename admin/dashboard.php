
<?php
/**
 * admin/dashboard.php - Trang Tổng quan quản trị
 *
 * Chức năng:
 * - Hiển thị tổng quan số liệu: sản phẩm, đơn hàng, doanh thu, khách hàng
 * - Thống kê đơn hàng theo trạng thái, sản phẩm bán chạy, đơn gần đây
 * - Giao diện đồng bộ với các trang quản trị khác
 *
 * Hướng dẫn:
 * - Sử dụng sidebar chung (_sidebar.php)
 * - Header hiển thị avatar, tên admin, link về trang chủ
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    redirect(SITE_URL . '/auth.php');
}

$conn = getConnection();

// Lấy các số liệu tổng quan
$stats = [];

// Tổng số sản phẩm
$stmt = $conn->query("SELECT COUNT(*) as total FROM products");
$stats['total_products'] = $stmt->fetch()['total'];

// Tổng số đơn hàng và doanh thu
$stmt = $conn->query("SELECT COUNT(*) as total, SUM(total_amount) as revenue FROM orders");
$orderStats = $stmt->fetch();
$stats['total_orders'] = $orderStats['total'];
$stats['total_revenue'] = $orderStats['revenue'] ?? 0;

// Tổng số khách hàng
$stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
$stats['total_customers'] = $stmt->fetch()['total'];

// Thống kê đơn hàng theo trạng thái
$stmt = $conn->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
$ordersByStatus = [];
while ($row = $stmt->fetch()) {
    $ordersByStatus[$row['status']] = $row['count'];
}

// Lấy 10 đơn hàng gần nhất
$stmt = $conn->query("SELECT o.*, u.name as customer_name FROM orders o 
                      LEFT JOIN users u ON o.user_id = u.id 
                      ORDER BY o.created_at DESC LIMIT 10");
$recentOrders = $stmt->fetchAll();

// Lấy top 5 sản phẩm bán chạy
$stmt = $conn->query("SELECT p.*, COUNT(oi.id) as order_count 
                      FROM products p 
                      LEFT JOIN order_items oi ON p.id = oi.product_id 
                      GROUP BY p.id 
                      ORDER BY order_count DESC 
                      LIMIT 5");
$topProducts = $stmt->fetchAll();

// Tổng số khách hàng đang hoạt động (nếu có cột status)
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'customer' AND status = 'active'");
    $stats['active_customers'] = (int)$stmt->fetchColumn();
} catch (PDOException $e) {
    // Nếu không có cột status thì đếm tất cả khách hàng
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = ?");
    $stmt->execute(['customer']);
    $stats['active_customers'] = (int)$stmt->fetchColumn();
}

// Thống kê đánh giá sản phẩm
try {
    $stmt = $conn->query("SELECT status, COUNT(*) as count FROM product_reviews GROUP BY status");
    $reviewsByStatus = [];
    while ($row = $stmt->fetch()) {
        $reviewsByStatus[$row['status']] = $row['count'];
    }
    $stats['pending_reviews'] = $reviewsByStatus['pending'] ?? 0;
    $stats['approved_reviews'] = $reviewsByStatus['approved'] ?? 0;
    $stats['total_reviews'] = array_sum($reviewsByStatus);
} catch (PDOException $e) {
    $stats['pending_reviews'] = 0;
    $stats['approved_reviews'] = 0;
    $stats['total_reviews'] = 0;
}

$pageTitle = 'Dashboard Admin';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    <style>
        body { font-family: 'Be Vietnam Pro', sans-serif; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="px-3 sm:px-4 md:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14 sm:h-16">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                    <span class="material-symbols-outlined text-green-600 text-2xl sm:text-3xl flex-shrink-0">admin_panel_settings</span>
                    <div class="min-w-0">
                        <h1 class="text-sm sm:text-lg font-bold text-gray-900 truncate">Admin Dashboard</h1>
                        <p class="text-xs text-gray-500">Xanh Organic</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                    <a href="<?= SITE_URL ?>" class="flex items-center gap-1 text-xs sm:text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 p-2 rounded-lg transition">
                        <span class="material-symbols-outlined text-lg">storefront</span>
                        <span class="hidden sm:inline">Về trang chủ</span>
                    </a>
                    <div class="flex items-center gap-2 sm:pl-3 sm:border-l sm:border-gray-200">
                        <div class="w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center font-bold text-xs sm:text-sm flex-shrink-0">
                            <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
                        </div>
                        <span class="text-xs sm:text-sm font-medium text-gray-700 hidden sm:inline truncate"><?= sanitize($_SESSION['user_name']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="flex">
        <!-- Sidebar -->
        <?php $adminCurrent = basename($_SERVER['PHP_SELF']); ?>
        <aside class="hidden md:block md:w-64 bg-white border-r border-gray-200 min-h-screen">
            <nav class="p-4 space-y-1">
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $adminCurrent === 'dashboard.php' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Tổng quan</span>
                </a>

                <a href="categories.php" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $adminCurrent === 'categories.php' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
                    <span class="material-symbols-outlined">category</span>
                    <span>Danh mục</span>
                </a>

                <a href="products.php" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $adminCurrent === 'products.php' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
                    <span class="material-symbols-outlined">inventory_2</span>
                    <span>Sản phẩm</span>
                </a>

                <a href="orders.php" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $adminCurrent === 'orders.php' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
                    <span class="material-symbols-outlined">shopping_cart</span>
                    <span>Đơn hàng</span>
                    <?php if (!empty($ordersByStatus['pending'])): ?>
                        <span class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                            <?= $ordersByStatus['pending'] ?>
                        </span>
                    <?php endif; ?>
                </a>

                <a href="customers.php" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $adminCurrent === 'customers.php' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
                    <span class="material-symbols-outlined">people</span>
                    <span>Khách hàng</span>
                </a>

                <a href="reviews.php" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $adminCurrent === 'reviews.php' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
                    <span class="material-symbols-outlined">star</span>
                    <span>Đánh giá</span>
                </a>

                <a href="posts.php" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $adminCurrent === 'posts.php' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
                    <span class="material-symbols-outlined">article</span>
                    <span>Quản lý bài viết</span>
                </a>

                <a href="statistics.php" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $adminCurrent === 'statistics.php' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
                    <span class="material-symbols-outlined">analytics</span>
                    <span>Thống kê</span>
                </a>

                <hr class="my-4">

                <a href="settings.php" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $adminCurrent === 'settings.php' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
                    <span class="material-symbols-outlined">settings</span>
                    <span>Cài đặt</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-3 sm:p-4 md:p-6">
            <!-- Page Header -->
            <div class="mb-6 sm:mb-8">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Tổng quan</h2>
                <p class="text-sm sm:text-base text-gray-600 mt-1">Xin chào, <?= sanitize($_SESSION['user_name']) ?>! 👋</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 sm:gap-6 mb-6 sm:mb-8">
                <!-- Revenue Card -->
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg sm:rounded-xl p-4 sm:p-6 text-white">
                    <div class="flex items-center justify-between mb-3 sm:mb-4">
                        <div class="flex-1">
                            <p class="text-green-100 text-xs sm:text-sm font-medium">Doanh thu</p>
                            <h3 class="text-xl sm:text-3xl font-bold mt-1"><?= formatPrice($stats['total_revenue']) ?></h3>
                        </div>
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0 ml-2">
                            <span class="material-symbols-outlined text-lg sm:text-2xl">payments</span>
                        </div>
                    </div>
                    <p class="text-green-100 text-xs sm:text-sm">
                        <span class="font-semibold">+12.5%</span> so với tháng trước
                    </p>
                </div>

                <!-- Orders Card -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg sm:rounded-xl p-4 sm:p-6 text-white">
                    <div class="flex items-center justify-between mb-3 sm:mb-4">
                        <div class="flex-1">
                            <p class="text-blue-100 text-xs sm:text-sm font-medium">Đơn hàng</p>
                            <h3 class="text-xl sm:text-3xl font-bold mt-1"><?= $stats['total_orders'] ?></h3>
                        </div>
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0 ml-2">
                            <span class="material-symbols-outlined text-lg sm:text-2xl">shopping_cart</span>
                        </div>
                    </div>
                    <p class="text-blue-100 text-xs sm:text-sm">
                        <span class="font-semibold"><?= $ordersByStatus['pending'] ?? 0 ?></span> đơn chờ xử lý
                    </p>
                </div>

                <!-- Products Card -->
                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg sm:rounded-xl p-4 sm:p-6 text-white">
                    <div class="flex items-center justify-between mb-3 sm:mb-4">
                        <div class="flex-1">
                            <p class="text-orange-100 text-xs sm:text-sm font-medium">Sản phẩm</p>
                            <h3 class="text-xl sm:text-3xl font-bold mt-1"><?= $stats['total_products'] ?></h3>
                        </div>
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0 ml-2">
                            <span class="material-symbols-outlined text-lg sm:text-2xl">inventory_2</span>
                        </div>
                    </div>
                    <p class="text-orange-100 text-xs sm:text-sm">
                        <a href="products.php" class="font-semibold hover:underline text-xs sm:text-sm">Quản lý sản phẩm →</a>
                    </p>
                </div>

                <!-- Customers Card -->
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg sm:rounded-xl p-4 sm:p-6 text-white">
                    <div class="flex items-center justify-between mb-3 sm:mb-4">
                        <div class="flex-1">
                            <p class="text-purple-100 text-xs sm:text-sm font-medium">Khách hàng</p>
                            <h3 class="text-xl sm:text-3xl font-bold mt-1"><?= $stats['total_customers'] ?></h3>
                        </div>
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0 ml-2">
                            <span class="material-symbols-outlined text-lg sm:text-2xl">people</span>
                        </div>
                    </div>
                    <p class="text-purple-100 text-xs sm:text-sm">
                        <a href="customers.php" class="font-semibold hover:underline text-xs sm:text-sm">Xem danh sách →</a>
                    </p>
                </div>

                <!-- Pending Reviews Card -->
                <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg sm:rounded-xl p-4 sm:p-6 text-white">
                    <div class="flex items-center justify-between mb-3 sm:mb-4">
                        <div class="flex-1">
                            <p class="text-red-100 text-xs sm:text-sm font-medium">Đánh giá chờ</p>
                            <h3 class="text-xl sm:text-3xl font-bold mt-1"><?= $stats['pending_reviews'] ?></h3>
                        </div>
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0 ml-2">
                            <span class="material-symbols-outlined text-lg sm:text-2xl">rate_review</span>
                        </div>
                    </div>
                    <p class="text-red-100 text-xs sm:text-sm">
                        <a href="reviews.php" class="font-semibold hover:underline">Duyệt ngay →</a>
                    </p>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
                <!-- Recent Orders -->
                <div class="lg:col-span-2 bg-white rounded-lg sm:rounded-xl border border-gray-200 p-3 sm:p-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 sm:gap-0 mb-4 sm:mb-6">
                        <h3 class="text-base sm:text-lg font-bold text-gray-900">Đơn hàng gần đây</h3>
                        <a href="orders.php" class="text-xs sm:text-sm text-green-600 font-medium hover:text-green-700">
                            Xem tất cả →
                        </a>
                    </div>

                    <div class="overflow-x-auto -mx-3 sm:mx-0">
                        <table class="w-full text-xs sm:text-sm">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-2 sm:py-3 px-3 sm:px-4 text-xs sm:text-sm font-semibold text-gray-600">Mã ĐH</th>
                                    <th class="text-left py-2 sm:py-3 px-3 sm:px-4 text-xs sm:text-sm font-semibold text-gray-600">Khách hàng</th>
                                    <th class="text-left py-2 sm:py-3 px-3 sm:px-4 text-xs sm:text-sm font-semibold text-gray-600">Số tiền</th>
                                    <th class="text-left py-2 sm:py-3 px-3 sm:px-4 text-xs sm:text-sm font-semibold text-gray-600">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): 
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'confirmed' => 'bg-blue-100 text-blue-800',
                                        'shipping' => 'bg-purple-100 text-purple-800',
                                        'delivered' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800'
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Chờ xác nhận',
                                        'confirmed' => 'Đã xác nhận',
                                        'shipping' => 'Đang giao',
                                        'delivered' => 'Đã giao',
                                        'cancelled' => 'Đã hủy'
                                    ];
                                ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-2 sm:py-4 px-3 sm:px-4 font-medium text-gray-900">#<?= $order['id'] ?></td>
                                    <td class="py-2 sm:py-4 px-3 sm:px-4 text-gray-700 truncate"><?= sanitize($order['customer_name'] ?? 'Khách') ?></td>
                                    <td class="py-2 sm:py-4 px-3 sm:px-4 font-semibold text-green-600"><?= formatPrice($order['total_amount']) ?></td>
                                    <td class="py-2 sm:py-4 px-3 sm:px-4">
                                        <span class="px-2 sm:px-3 py-1 rounded-full text-xs font-semibold <?= $statusColors[$order['status']] ?>">
                                            <?= $statusLabels[$order['status']] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="bg-white rounded-lg sm:rounded-xl border border-gray-200 p-3 sm:p-6">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-4 sm:mb-6">Sản phẩm bán chạy</h3>
                    <div class="space-y-3 sm:space-y-4">
                        <?php foreach ($topProducts as $product): ?>
                        <div class="flex items-center gap-2 sm:gap-3">
                            <img src="<?= imageUrl($product['image']) ?>" alt="<?= sanitize($product['name']) ?>"
                                 class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg object-cover flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 text-xs sm:text-sm truncate"><?= sanitize($product['name']) ?></p>
                                <p class="text-xs text-gray-500"><?= $product['order_count'] ?> đơn hàng</p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        /* ===== RESPONSIVE FOR ADMIN DASHBOARD ===== */
        /* Prevent horizontal scroll on mobile */
        @media (max-width: 640px) {
            body {
                overflow-x: hidden;
            }
            
            table {
                font-size: 0.75rem;
            }
            
            /* Hide sidebar text on very small screens */
            @media (max-width: 380px) {
                .flex-col > * {
                    font-size: 0.8rem;
                }
            }
        }

        /* Tablet: 641px - 1024px */
        @media (min-width: 641px) and (max-width: 1024px) {
            table {
                font-size: 0.85rem;
            }
        }
        
        /* Extra large screens */
        @media (min-width: 1280px) {
            main {
                max-width: 100%;
            }
        }
    </style>

</body>
</html>