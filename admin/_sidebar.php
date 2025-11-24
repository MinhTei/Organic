<?php
/**
 * admin/_sidebar.php - Sidebar cải tiến với menu Quản lý tài khoản
 * 
 * Đặc biệt:
 * - Admin: Hiển thị dropdown với 2 link (Role Management + Khách hàng)
 * - Staff/User_MA: Chỉ hiển thị link Khách hàng đơn giản
 */

// Xác định trang hiện tại
$adminCurrent = basename($_SERVER['PHP_SELF']);

// Giá trị mặc định an toàn
$ordersByStatus = $ordersByStatus ?? [];
$pendingReviews = 0;
if (isset($stats) && is_array($stats)) {
    $pendingReviews = isset($stats['pending']) ? (int)$stats['pending'] : $pendingReviews;
}
?>
<aside class="w-64 bg-white border-r border-gray-200 min-h-screen">
    <nav class="p-4 space-y-1">
        <!-- Tổng quan -->
        <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $adminCurrent === 'dashboard.php' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
            <span class="material-symbols-outlined">dashboard</span>
            <span>Tổng quan</span>
        </a>

        <!-- Danh mục -->
        <a href="categories.php" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $adminCurrent === 'categories.php' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
            <span class="material-symbols-outlined">category</span>
            <span>Danh mục</span>
        </a>

        <!-- Sản phẩm -->
        <a href="products.php" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $adminCurrent === 'products.php' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
            <span class="material-symbols-outlined">inventory_2</span>
            <span>Sản phẩm</span>
        </a>

        <!-- Đơn hàng -->
        <a href="orders.php" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $adminCurrent === 'orders.php' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
            <span class="material-symbols-outlined">shopping_cart</span>
            <span>Đơn hàng</span>
            <?php if (!empty($ordersByStatus['pending'])): ?>
                <span class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full"><?= $ordersByStatus['pending'] ?></span>
            <?php endif; ?>
        </a>

        <!-- QUẢN LÝ TÀI KHOẢN (Admin: Dropdown, Others: Single Link) -->
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <!-- Admin: Dropdown Menu -->
        <div class="relative dropdown-container">
            <button onclick="toggleDropdown(event)" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-50 <?= in_array($adminCurrent, ['role_manager.php', 'customers.php']) ? 'bg-green-50 text-green-700 font-medium' : '' ?>">
                <span class="material-symbols-outlined">manage_accounts</span>
                <span class="flex-1 text-left">Quản lý tài khoản</span>
                <span class="material-symbols-outlined text-sm transition-transform dropdown-icon">expand_more</span>
            </button>
            <div class="dropdown-menu hidden pl-4 mt-1 space-y-1">
                <a href="role_manager.php" class="flex items-center gap-3 px-4 py-2 rounded-lg <?= $adminCurrent === 'role_manager.php' ? 'bg-purple-50 text-purple-700 font-medium' : 'text-gray-600 hover:bg-purple-50 hover:text-purple-700' ?>">
                    <span class="material-symbols-outlined text-lg">admin_panel_settings</span>
                    <div class="flex-1">
                        <p class="text-sm font-medium">Quản lý Role</p>
                        <p class="text-xs opacity-75">Phân quyền & bảo mật</p>
                    </div>
                    <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs font-bold rounded">ADMIN</span>
                </a>
                <a href="customers.php" class="flex items-center gap-3 px-4 py-2 rounded-lg <?= $adminCurrent === 'customers.php' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-600 hover:bg-green-50 hover:text-green-700' ?>">
                    <span class="material-symbols-outlined text-lg">people</span>
                    <div class="flex-1">
                        <p class="text-sm font-medium">Khách hàng</p>
                        <p class="text-xs opacity-75">Danh sách & membership</p>
                    </div>
                </a>
            </div>
        </div>
        <?php else: ?>
        <!-- Staff/User_MA: Single Link -->
        <a href="customers.php" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $adminCurrent === 'customers.php' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
            <span class="material-symbols-outlined">people</span>
            <span>Khách hàng</span>
        </a>
        <?php endif; ?>

        <!-- Đánh giá -->
        <a href="reviews.php" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $adminCurrent === 'reviews.php' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
            <span class="material-symbols-outlined">star</span>
            <span>Đánh giá</span>
            <?php if ($pendingReviews > 0): ?>
                <span class="ml-auto bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded-full"><?= $pendingReviews ?></span>
            <?php endif; ?>
        </a>

        <!-- Thống kê -->
        <a href="statistics.php" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $adminCurrent === 'statistics.php' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
            <span class="material-symbols-outlined">analytics</span>
            <span>Thống kê</span>
        </a>

        <hr class="my-4">

        <!-- Cài đặt -->
        <a href="settings.php" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $adminCurrent === 'settings.php' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
            <span class="material-symbols-outlined">settings</span>
            <span>Cài đặt</span>
        </a>
    </nav>

    <!-- JavaScript cho Dropdown -->
    <script>
    function toggleDropdown(event) {
        event.preventDefault();
        const container = event.currentTarget.closest('.dropdown-container');
        const menu = container.querySelector('.dropdown-menu');
        const icon = container.querySelector('.dropdown-icon');
        
        // Toggle menu
        menu.classList.toggle('hidden');
        
        // Rotate icon
        if (menu.classList.contains('hidden')) {
            icon.style.transform = 'rotate(0deg)';
        } else {
            icon.style.transform = 'rotate(180deg)';
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdowns = document.querySelectorAll('.dropdown-container');
        dropdowns.forEach(container => {
            if (!container.contains(event.target)) {
                const menu = container.querySelector('.dropdown-menu');
                const icon = container.querySelector('.dropdown-icon');
                if (menu && !menu.classList.contains('hidden')) {
                    menu.classList.add('hidden');
                    if (icon) icon.style.transform = 'rotate(0deg)';
                }
            }
        });
    });

    // Auto-expand dropdown if on child page
    document.addEventListener('DOMContentLoaded', function() {
        const currentPage = '<?= $adminCurrent ?>';
        if (currentPage === 'role_manager.php' || currentPage === 'customers.php') {
            const container = document.querySelector('.dropdown-container');
            if (container) {
                const menu = container.querySelector('.dropdown-menu');
                const icon = container.querySelector('.dropdown-icon');
                if (menu) {
                    menu.classList.remove('hidden');
                    if (icon) icon.style.transform = 'rotate(180deg)';
                }
            }
        }
    });
    </script>

    <style>
    .dropdown-icon {
        transition: transform 0.2s ease;
    }
    .dropdown-menu {
        animation: slideDown 0.2s ease;
    }
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    </style>
</aside>