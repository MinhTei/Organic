
<?php
/**
 * admin/statistics.php - Trang Thống kê & Báo cáo doanh thu, đơn hàng, sản phẩm bán chạy
 *
 * Chức năng:
 * - Thống kê doanh thu, số đơn hàng, giá trị TB đơn hàng
 * - Hiển thị biểu đồ doanh thu, trạng thái đơn hàng
 * - Liệt kê top sản phẩm bán chạy
 * - Lọc theo khoảng ngày
 * - Giao diện đồng bộ với các trang quản trị khác
 *
 * Hướng dẫn:
 * - Sử dụng sidebar chung (_sidebar.php)
 * - Header hiển thị avatar, tên admin, link về trang chủ
 * - Nếu không có dữ liệu, sẽ hiển thị dữ liệu mẫu để xem giao diện
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    redirect(SITE_URL . '/auth.php');
}

$conn = getConnection();

// Lấy khoảng ngày lọc thống kê (mặc định: từ đầu tháng đến hôm nay)
$startDate = $_GET['start'] ?? date('Y-m-01'); // Ngày đầu tháng
$endDate = $_GET['end'] ?? date('Y-m-d'); // Hôm nay

// Thống kê doanh thu theo ngày
$revenueStmt = $conn->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as orders,
        SUM(total_amount) as revenue
    FROM orders
    WHERE created_at BETWEEN ? AND ?
    AND status != 'cancelled'
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$revenueStmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
$revenueData = $revenueStmt->fetchAll();

// Tổng hợp số liệu: tổng đơn, tổng doanh thu, giá trị TB đơn hàng
$totalStmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(total_amount) as total_revenue,
        AVG(total_amount) as avg_order_value
    FROM orders
    WHERE created_at BETWEEN ? AND ?
    AND status != 'cancelled'
");
$totalStmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
$totals = $totalStmt->fetch();

// Lấy top 10 sản phẩm bán chạy nhất
// Sử dụng unit_price thay cho price
$topProductsStmt = $conn->prepare("
    SELECT 
        p.id,
        p.name,
        p.image,
        SUM(oi.quantity) as total_sold,
        SUM(oi.quantity * oi.unit_price) as total_revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.created_at BETWEEN ? AND ?
    AND o.status != 'cancelled'
    GROUP BY p.id, p.name, p.image
    ORDER BY total_revenue DESC
    LIMIT 10
");
$topProductsStmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
$topProducts = $topProductsStmt->fetchAll();

// Thống kê số lượng đơn theo trạng thái
$statusStmt = $conn->prepare("
    SELECT status, COUNT(*) as count
    FROM orders
    WHERE created_at BETWEEN ? AND ?
    GROUP BY status
");
$statusStmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
$orderStatus = $statusStmt->fetchAll(PDO::FETCH_KEY_PAIR);

$pageTitle = 'Thống kê & Báo cáo';

// Nếu không có dữ liệu thực tế (DB trống), sinh dữ liệu mẫu để xem giao diện
$isSample = false;
if (empty($revenueData) || empty($totals) || (int)($totals['total_orders'] ?? 0) === 0) {
    $isSample = true;
    // 7 ngày gần nhất
    $dates = [];
    for ($i = 6; $i >= 0; $i--) {
        $dates[] = date('Y-m-d', strtotime("-{$i} days"));
    }
    $sampleRevenues = [1200000, 2400000, 1800000, 3000000, 1500000, 900000, 2100000];
    $revenueData = [];
    foreach ($dates as $idx => $d) {
        $revenueData[] = [
            'date' => $d,
            'orders' => rand(2, 12),
            'revenue' => $sampleRevenues[$idx] ?? 0
        ];
    }

    $totals = [
        'total_orders' => array_sum(array_map(fn($r) => $r['orders'], $revenueData)),
        'total_revenue' => array_sum($sampleRevenues),
        'avg_order_value' => array_sum($sampleRevenues) / max(1, array_sum(array_map(fn($r) => $r['orders'], $revenueData)))
    ];

    // Lấy danh sách sản phẩm từ database để tạo dữ liệu mẫu đồng bộ
    $sampleProductsStmt = $conn->prepare("SELECT id, name, image FROM products ORDER BY id LIMIT 3");
    $sampleProductsStmt->execute();
    $sampleProductsData = $sampleProductsStmt->fetchAll();
    
    if (!empty($sampleProductsData)) {
        $topProducts = [
            ['id' => $sampleProductsData[0]['id'] ?? 1, 'name' => $sampleProductsData[0]['name'] ?? 'Green Apple', 'image' => $sampleProductsData[0]['image'] ?? 'images/product/pro01.jpg', 'total_sold' => 120, 'total_revenue' => 120 * 45000],
            ['id' => $sampleProductsData[1]['id'] ?? 2, 'name' => $sampleProductsData[1]['name'] ?? 'Organic Carrot', 'image' => $sampleProductsData[1]['image'] ?? 'images/product/pro02.jpg', 'total_sold' => 85, 'total_revenue' => 85 * 35000],
            ['id' => $sampleProductsData[2]['id'] ?? 3, 'name' => $sampleProductsData[2]['name'] ?? 'Romaine Lettuce', 'image' => $sampleProductsData[2]['image'] ?? 'images/product/pro03.jpg', 'total_sold' => 60, 'total_revenue' => 60 * 22000]
        ];
    } else {
        $topProducts = [
            ['id' => 1, 'name' => 'Green Apple', 'image' => 'images/product/pro01.jpg', 'total_sold' => 120, 'total_revenue' => 120 * 45000],
            ['id' => 2, 'name' => 'Organic Carrot', 'image' => 'images/product/pro02.jpg', 'total_sold' => 85, 'total_revenue' => 85 * 35000],
            ['id' => 3, 'name' => 'Romaine Lettuce', 'image' => 'images/product/pro03.jpg', 'total_sold' => 60, 'total_revenue' => 60 * 22000]
        ];
    }

    $orderStatus = [
        'pending' => 5,
        'confirmed' => 20,
        'shipping' => 8,
        'delivered' => 10,
        'cancelled' => 2
    ];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <!-- Page Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Thống kê & Báo cáo</h2>
                    <p class="text-gray-600 mt-1">Phân tích dữ liệu kinh doanh</p>
                </div>
                
                <!-- Date Range Filter -->
                <form method="GET" class="flex gap-3">
                    <input type="date" name="start" value="<?= $startDate ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <input type="date" name="end" value="<?= $endDate ?>"
                           class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Xem báo cáo
                    </button>
                    <div class="relative">
                        <button type="button" id="exportBtn" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 ml-2 flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg">download</span>
                            Xuất báo cáo
                        </button>
                        <div id="exportDropdown" class="hidden absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
                            <button type="button" class="w-full px-4 py-2 text-left hover:bg-gray-100" onclick="exportReport('pdf')">Xuất PDF</button>
                            <button type="button" class="w-full px-4 py-2 text-left hover:bg-gray-100" onclick="exportReport('excel')">Xuất Excel</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Tổng đơn hàng</p>
                            <h3 class="text-3xl font-bold mt-1"><?= number_format($totals['total_orders']) ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-2xl">shopping_bag</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Tổng doanh thu</p>
                            <h3 class="text-3xl font-bold mt-1"><?= formatPrice($totals['total_revenue']) ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-2xl">payments</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Giá trị TB đơn hàng</p>
                            <h3 class="text-3xl font-bold mt-1"><?= formatPrice($totals['avg_order_value']) ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-2xl">trending_up</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Revenue Chart -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="text-lg font-bold mb-4">Doanh thu theo ngày</h3>
                    <div style="height:350px"> <!-- fix: give the chart a fixed height container -->
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

                <!-- Order Status Chart -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="text-lg font-bold mb-4">Trạng thái đơn hàng</h3>
                    <div style="height:350px"> <!-- fix: give the chart a fixed height container -->
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Products -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-bold mb-4">Top 10 sản phẩm bán chạy</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="text-left py-3 px-4 font-semibold text-sm">STT</th>
                                <th class="text-left py-3 px-4 font-semibold text-sm">Sản phẩm</th>
                                <th class="text-right py-3 px-4 font-semibold text-sm">Đã bán</th>
                                <th class="text-right py-3 px-4 font-semibold text-sm">Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topProducts as $index => $product): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-4 px-4 font-medium"><?= $index + 1 ?></td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="<?= imageUrl($product['image']) ?>" alt="" class="w-12 h-12 rounded-lg object-cover">
                                        <span class="font-medium"><?= sanitize($product['name']) ?></span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-right font-medium"><?= number_format($product['total_sold']) ?></td>
                                <td class="py-4 px-4 text-right font-bold text-green-600"><?= formatPrice($product['total_revenue']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
    // Export report dropdown logic
    document.addEventListener('DOMContentLoaded', function() {
        const exportBtn = document.getElementById('exportBtn');
        const exportDropdown = document.getElementById('exportDropdown');
        if (exportBtn && exportDropdown) {
            exportBtn.addEventListener('click', function(e) {
                exportDropdown.classList.toggle('hidden');
            });
            document.addEventListener('click', function(e) {
                if (!exportBtn.contains(e.target) && !exportDropdown.contains(e.target)) {
                    exportDropdown.classList.add('hidden');
                }
            });
        }
    });

    // Dummy export functions (to be implemented)
    function exportReport(format) {
        const startDate = document.querySelector('input[name="start"]').value;
        const endDate = document.querySelector('input[name="end"]').value;
        window.location.href = `export_report.php?format=${format}&start=${startDate}&end=${endDate}`;
    }
    // Revenue Chart
    // Ensure revenue values are numeric and compute a sensible suggested max so the line doesn't appear as a near-vertical line
    const revenueLabels = <?= json_encode(array_column($revenueData, 'date')) ?>;
    const revenueValues = <?= json_encode(array_map('floatval', array_column($revenueData, 'revenue'))) ?>;
    const maxRevenue = <?= json_encode((float) (count($revenueData) ? max(array_map(function($r){ return isset($r['revenue']) ? (float)$r['revenue'] : 0; }, $revenueData)) : 0)) ?>;

    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: revenueLabels,
            datasets: [{
                label: 'Revenue (₫)',
                data: revenueValues,
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.08)',
                tension: 0.3,
                fill: true,
                pointRadius: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: maxRevenue ? Math.ceil(maxRevenue * 1.2) : undefined,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN').format(value) + '₫';
                        }
                    }
                }
            }
        }
    });

    // Order Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusData = <?= json_encode($orderStatus) ?>;
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Confirmed', 'Shipping', 'Delivered', 'Cancelled'], // use English labels for sample preview
            datasets: [{
                data: [
                    parseInt(statusData.pending || 0),
                    parseInt(statusData.confirmed || 0),
                    parseInt(statusData.shipping || 0),
                    parseInt(statusData.delivered || 0),
                    parseInt(statusData.cancelled || 0)
                ],
                backgroundColor: [
                    'rgb(234, 179, 8)',
                    'rgb(59, 130, 246)',
                    'rgb(168, 85, 247)',
                    'rgb(34, 197, 94)',
                    'rgb(239, 68, 68)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
    </script>

</body>
</html>