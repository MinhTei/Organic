<?php
/**
 * admin/role_manager.php - Quản lý Role và Quyền người dùng
 * - Đổi role dễ dàng cho user
 * - Xem danh sách theo role
 * - Khóa/mở khóa tài khoản
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    redirect(SITE_URL . '/auth.php');
}

$conn = getConnection();
$success = '';
$error = '';

// Xử lý thay đổi role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
    $userId = (int)$_POST['user_id'];
    $newRole = sanitize($_POST['new_role']);
    
    if (in_array($newRole, ['admin', 'staff', 'customer', 'user_ma'])) {
        $stmt = $conn->prepare("UPDATE users SET role = :role WHERE id = :id");
        if ($stmt->execute([':role' => $newRole, ':id' => $userId])) {
            // Log activity
            try {
                $logStmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, description, ip_address) VALUES (:admin_id, 'change_role', :desc, :ip)");
                $logStmt->execute([
                    ':admin_id' => $_SESSION['user_id'],
                    ':desc' => "Đổi role user #$userId thành $newRole",
                    ':ip' => $_SERVER['REMOTE_ADDR']
                ]);
            } catch (PDOException $e) {}
            
            $success = 'Đã cập nhật role thành công!';
        }
    }
}

// Xử lý khóa/mở khóa tài khoản
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $userId = (int)$_POST['user_id'];
    
    $stmt = $conn->prepare("SELECT status FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $currentStatus = $stmt->fetchColumn();
    
    $newStatus = ($currentStatus === 'active') ? 'blocked' : 'active';
    
    $stmt = $conn->prepare("UPDATE users SET status = :status WHERE id = :id");
    if ($stmt->execute([':status' => $newStatus, ':id' => $userId])) {
        $success = $newStatus === 'blocked' ? 'Đã khóa tài khoản!' : 'Đã mở khóa tài khoản!';
    }
}

// Lấy danh sách users
$filterRole = $_GET['role'] ?? 'all';
$search = $_GET['search'] ?? '';

$where = [];
$params = [];

if ($filterRole !== 'all') {
    $where[] = "role = :role";
    $params[':role'] = $filterRole;
}

if ($search) {
    $where[] = "email LIKE :search";
    $params[':search'] = "%$search%";
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $conn->prepare("SELECT * FROM users $whereClause ORDER BY created_at DESC LIMIT 100");
$stmt->execute($params);
$users = $stmt->fetchAll();

// Thống kê
$stats = [
    'admin' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn(),
    'staff' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'staff'")->fetchColumn(),
    'user_ma' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'user_ma'")->fetchColumn(),
    'customer' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
];

$roleLabels = [
    'admin' => 'Admin',
    'staff' => 'Nhân viên',
    'user_ma' => 'Quản lý User',
    'customer' => 'Khách hàng'
];

$roleColors = [
    'admin' => 'bg-purple-100 text-purple-800',
    'staff' => 'bg-blue-100 text-blue-800',
    'user_ma' => 'bg-orange-100 text-orange-800',
    'customer' => 'bg-green-100 text-green-800'
];

$pageTitle = 'Quản lý Role & Quyền';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= SITE_NAME ?></title>
    <link href="<?= SITE_URL ?>/css/tailwind.css" rel="stylesheet"/>
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
                <h2 class="text-2xl font-bold text-gray-900">Quản lý Role & Quyền</h2>
                <p class="text-gray-600 mt-1">Thay đổi role và phân quyền cho người dùng</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Admin</p>
                            <h3 class="text-3xl font-bold mt-1"><?= $stats['admin'] ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-2xl">admin_panel_settings</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Nhân viên</p>
                            <h3 class="text-3xl font-bold mt-1"><?= $stats['staff'] ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-2xl">badge</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-orange-100 text-sm font-medium">User MA</p>
                            <h3 class="text-3xl font-bold mt-1"><?= $stats['user_ma'] ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-2xl">manage_accounts</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Khách hàng</p>
                            <h3 class="text-3xl font-bold mt-1"><?= $stats['customer'] ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-2xl">person</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
                <form method="GET" class="flex gap-4">
                    <input type="text" name="search" value="<?= sanitize($search) ?>" 
                           placeholder="Tìm theo tên, email..."
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    
                    <select name="role" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="all" <?= $filterRole === 'all' ? 'selected' : '' ?>>Tất cả role</option>
                        <option value="admin" <?= $filterRole === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="staff" <?= $filterRole === 'staff' ? 'selected' : '' ?>>Nhân viên</option>
                        <option value="user_ma" <?= $filterRole === 'user_ma' ? 'selected' : '' ?>>User MA</option>
                        <option value="customer" <?= $filterRole === 'customer' ? 'selected' : '' ?>>Khách hàng</option>
                    </select>
                    
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Lọc
                    </button>
                </form>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left py-3 px-4 font-semibold text-sm">ID</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm">Người dùng</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm">Email</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm">Role hiện tại</th>
                            <th class="text-left py-3 px-4 font-semibold text-sm">Trạng thái</th>
                            <th class="text-center py-3 px-4 font-semibold text-sm">Đổi Role</th>
                            <th class="text-center py-3 px-4 font-semibold text-sm">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-4 px-4 font-medium">#<?= $user['id'] ?></td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-green-100 text-green-700 flex items-center justify-center font-bold">
                                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="font-medium"><?= sanitize($user['name']) ?></p>
                                        <p class="text-sm text-gray-500"><?= sanitize($user['phone']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-sm"><?= sanitize($user['email']) ?></td>
                            <td class="py-4 px-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $roleColors[$user['role']] ?>">
                                    <?= $roleLabels[$user['role']] ?>
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <?php $status = $user['status'] ?? 'active'; ?>
                                <?php if ($status === 'active'): ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Hoạt động</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">Đã khóa</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-4">
                                <form method="POST" class="flex items-center justify-center gap-2">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <select name="new_role" class="px-3 py-1 border border-gray-300 rounded text-sm">
                                        <?php foreach ($roleLabels as $key => $label): ?>
                                            <option value="<?= $key ?>" <?= $user['role'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="change_role" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                                        Đổi
                                    </button>
                                </form>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-center gap-2">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" name="toggle_status" 
                                                class="p-2 <?= ($user['status'] ?? 'active') === 'active' ? 'text-red-600 hover:bg-red-50' : 'text-green-600 hover:bg-green-50' ?> rounded-lg"
                                                title="<?= ($user['status'] ?? 'active') === 'active' ? 'Khóa tài khoản' : 'Mở khóa' ?>">
                                            <span class="material-symbols-outlined text-lg">
                                                <?= ($user['status'] ?? 'active') === 'active' ? 'lock' : 'lock_open' ?>
                                            </span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <style>
        /* ===== RESPONSIVE FOR ADMIN TABLES ===== */
        /* Mobile: < 768px */
        @media (max-width: 767px) {
            table {
                font-size: 0.75rem !important;
            }
            
            th, td {
                padding: 0.5rem 0.25rem !important;
            }
            
            .btn {
                padding: 0.25rem 0.5rem !important;
                font-size: 0.7rem !important;
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

