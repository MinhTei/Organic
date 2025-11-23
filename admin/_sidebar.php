<?php
// admin/_sidebar.php - Sidebar dùng chung cho các trang quản trị
//
// Cách dùng: include __DIR__ . '/_sidebar.php';
//
// Chức năng:
// - Hiển thị menu trái các mục quản lý: Tổng quan, Danh mục, Sản phẩm, Đơn hàng, Khách hàng, Đánh giá, Thống kê, Cài đặt
// - Tô sáng mục đang chọn
// - Hiển thị badge số lượng đơn chờ xử lý, đánh giá chờ duyệt (nếu có)

// Xác định trang hiện tại để highlight menu
$adminCurrent = basename($_SERVER['PHP_SELF']);

// Giá trị mặc định an toàn cho badge
$ordersByStatus = $ordersByStatus ?? [];
$pendingReviews = 0;
if (isset($stats) && is_array($stats)) {
    $pendingReviews = isset($stats['pending']) ? (int)$stats['pending'] : $pendingReviews;
}
?>
<aside class="w-64 bg-white border-r border-gray-200 min-h-screen">
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
                <span class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full"><?= $ordersByStatus['pending'] ?></span>
            <?php endif; ?>
        </a>

        <a href="customers.php" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $adminCurrent === 'customers.php' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
            <span class="material-symbols-outlined">people</span>
            <span>Khách hàng</span>
        </a>

        <a href="reviews.php" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $adminCurrent === 'reviews.php' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
            <span class="material-symbols-outlined">star</span>
            <span>Đánh giá</span>
            <?php if ($pendingReviews > 0): ?>
                <span class="ml-auto bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded-full"><?= $pendingReviews ?></span>
            <?php endif; ?>
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
