
<?php
/**
 * admin/customers.php - Trang Quản lý Khách hàng
 *
 * Chức năng:
 * - Hiển thị danh sách khách hàng, tìm kiếm, lọc theo hạng thành viên
 * - Cập nhật hạng thành viên, xóa, khóa/mở khóa tài khoản
 * - Thống kê số lượng khách hàng theo hạng
 * - Giao diện đồng bộ với các trang quản trị khác
 *
 * Hướng dẫn:
 * - Sử dụng sidebar chung (_sidebar.php)
 * - Header hiển thị avatar, tên admin, link về trang chủ
 */

require_once '../config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    redirect(SITE_URL . '/auth.php');
}

$conn = getConnection();
$success = '';
$error = '';

// Xử lý các hành động với khách hàng (cập nhật hạng, xóa, khóa/mở khóa)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cập nhật hạng thành viên
    if (isset($_POST['update_membership'])) {
        $userId = (int)$_POST['user_id'];
        $membership = sanitize($_POST['membership']);
        
        $stmt = $conn->prepare("UPDATE users SET membership = :membership WHERE id = :id");
        if ($stmt->execute([':membership' => $membership, ':id' => $userId])) {
            $success = 'Cập nhật hạng thành viên thành công!';
        }
    }
    
    // Xóa khách hàng
    if (isset($_POST['delete_customer'])) {
        $userId = (int)$_POST['user_id'];
        
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id AND role = 'customer'");
        if ($stmt->execute([':id' => $userId])) {
            $success = 'Xóa khách hàng thành công!';
        } else {
            $error = 'Không thể xóa khách hàng này.';
        }
    }
    
    // Khóa/mở khóa tài khoản
    if (isset($_POST['toggle_status'])) {
        $userId = (int)$_POST['user_id'];
        
        // Kiểm tra trạng thái hiện tại
        $stmt = $conn->prepare("SELECT status FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $currentStatus = $stmt->fetchColumn();
        
        $newStatus = ($currentStatus === 'active') ? 'blocked' : 'active';
        
        $stmt = $conn->prepare("UPDATE users SET status = :status WHERE id = :id");
        if ($stmt->execute([':status' => $newStatus, ':id' => $userId])) {
            $success = $newStatus === 'blocked' ? 'Đã khóa tài khoản!' : 'Đã mở khóa tài khoản!';
        }
    }
}

// Lấy tham số lọc, tìm kiếm, phân trang
$search = $_GET['search'] ?? '';
$membership = $_GET['membership'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Xây dựng điều kiện truy vấn theo bộ lọc
$where = ["role = 'customer'"];
$params = [];

if ($search) {
    $where[] = "(name LIKE :search OR email LIKE :search OR phone LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($membership) {
    $where[] = "membership = :membership";
    $params[':membership'] = $membership;
}

$whereClause = implode(' AND ', $where);

// Lấy tổng số khách hàng (phục vụ phân trang)
$countStmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE $whereClause");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Lấy danh sách khách hàng theo trang, bộ lọc
$stmt = $conn->prepare("
    SELECT u.*, 
           COUNT(DISTINCT o.id) as total_orders,
           COALESCE(SUM(o.total_amount), 0) as total_spent
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    WHERE $whereClause
    GROUP BY u.id
    ORDER BY u.created_at DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$customers = $stmt->fetchAll();

// Thống kê số lượng khách hàng theo hạng thành viên
$stats = [
    'total' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
    'bronze' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'customer' AND membership = 'bronze'")->fetchColumn(),
    'silver' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'customer' AND membership = 'silver'")->fetchColumn(),
    'gold' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'customer' AND membership = 'gold'")->fetchColumn(),
];

$membershipLabels = [
    'bronze' => 'Đồng',
    'silver' => 'Bạc',
    'gold' => 'Vàng'
];

$membershipColors = [
    'bronze' => 'bg-orange-100 text-orange-800',
    'silver' => 'bg-gray-100 text-gray-800',
    'gold' => 'bg-yellow-100 text-yellow-800'
];

$pageTitle = 'Quản lý Khách hàng';
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
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Quản lý Khách hàng</h2>
                <p class="text-gray-600 mt-1">Quản lý thông tin và hạng thành viên</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Tổng khách hàng</p>
                            <h3 class="text-2xl font-bold mt-1"><?= number_format($stats['total']) ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-blue-600">people</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Thành viên Đồng</p>
                            <h3 class="text-2xl font-bold mt-1"><?= number_format($stats['bronze']) ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-orange-600">workspace_premium</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Thành viên Bạc</p>
                            <h3 class="text-2xl font-bold mt-1"><?= number_format($stats['silver']) ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-gray-600">workspace_premium</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Thành viên Vàng</p>
                            <h3 class="text-2xl font-bold mt-1"><?= number_format($stats['gold']) ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-yellow-600">workspace_premium</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
                <form method="GET" class="flex gap-4">
                    <input type="text" name="search" value="<?= sanitize($search) ?>" 
                           placeholder="Tìm theo tên, email, số điện thoại..."
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    
                    <select name="membership" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">Tất cả hạng thành viên</option>
                        <option value="bronze" <?= $membership === 'bronze' ? 'selected' : '' ?>>Đồng</option>
                        <option value="silver" <?= $membership === 'silver' ? 'selected' : '' ?>>Bạc</option>
                        <option value="gold" <?= $membership === 'gold' ? 'selected' : '' ?>>Vàng</option>
                    </select>
                    
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Lọc
                    </button>
                </form>
            </div>

            <!-- Customers Table -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left py-3 px-4 font-semibold text-sm">Khách hàng</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm">Liên hệ</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm">Hạng TV</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm">Đơn hàng</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm">Tổng chi</th>
                            <th class="text-center py-3 px-4 font-semibold text-sm">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-green-100 text-green-700 flex items-center justify-center font-bold">
                                        <?= strtoupper(substr($customer['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="font-medium"><?= sanitize($customer['name']) ?></p>
                                        <p class="text-sm text-gray-500">ID: <?= $customer['id'] ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <p class="text-sm"><?= sanitize($customer['email']) ?></p>
                                <p class="text-sm text-gray-500"><?= sanitize($customer['phone']) ?></p>
                            </td>
                            <td class="py-4 px-4">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="user_id" value="<?= $customer['id'] ?>">
                                    <select name="membership" onchange="this.form.submit()"
                                            class="px-3 py-1 rounded-full text-xs font-semibold border-none cursor-pointer <?= $membershipColors[$customer['membership']] ?>">
                                        <?php foreach ($membershipLabels as $key => $label): ?>
                                            <option value="<?= $key ?>" <?= $customer['membership'] === $key ? 'selected' : '' ?>>
                                                <?= $label ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="update_membership" value="1">
                                </form>
                            </td>
                            <td class="py-4 px-4 font-medium">
                                <?= number_format($customer['total_orders']) ?> đơn
                            </td>
                            <td class="py-4 px-4 font-semibold text-green-600">
                                <?= formatPrice($customer['total_spent']) ?>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="customer_detail.php?id=<?= $customer['id'] ?>" 
                                       class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg"
                                       title="Chi tiết">
                                        <span class="material-symbols-outlined text-lg">visibility</span>
                                    </a>
                                    
                                    <form method="POST" class="inline" 
                                          onsubmit="return confirm('Bạn có chắc muốn xóa khách hàng này?')">
                                        <input type="hidden" name="user_id" value="<?= $customer['id'] ?>">
                                        <button type="submit" name="delete_customer" 
                                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg"
                                                title="Xóa">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </button>
                                    </form>
                                </div>
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
                        <a href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $membership ? '&membership=' . $membership : '' ?>"
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