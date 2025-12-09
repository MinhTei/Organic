
<?php
/**
 * admin/reviews.php - Trang Quản lý Đánh giá sản phẩm
 *
 * Chức năng:
 * - Hiển thị danh sách đánh giá, lọc theo trạng thái, tìm kiếm
 * - Duyệt, từ chối, xóa đánh giá
 * - Thống kê số lượng đánh giá theo trạng thái
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
$success = '';
$error = '';

// Gợi ý lệnh SQL tạo bảng nếu thiếu bảng product_reviews
$createTableSQL = <<<'SQL'
CREATE TABLE product_reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL,
    comment TEXT,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
SQL;

// Giá trị mặc định an toàn khi lỗi DB (để trang vẫn hiển thị)
$reviews = [];
$contactMessages = [];
$stats = [
    'total' => 0,
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0,
];

// Bọc toàn bộ thao tác DB trong try/catch để tránh lỗi khi thiếu bảng
try {
    // Xử lý các hành động với đánh giá (duyệt, từ chối, xóa)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['approve_review'])) {
            $reviewId = (int)$_POST['review_id'];
            $stmt = $conn->prepare("UPDATE product_reviews SET status = 'approved' WHERE id = :id");
            if ($stmt->execute([':id' => $reviewId])) {
                $success = 'Đã duyệt đánh giá thành công!';
            }
        } elseif (isset($_POST['reject_review'])) {
            $reviewId = (int)$_POST['review_id'];
            $stmt = $conn->prepare("UPDATE product_reviews SET status = 'rejected' WHERE id = :id");
            if ($stmt->execute([':id' => $reviewId])) {
                $success = 'Đã từ chối đánh giá!';
            }
        } elseif (isset($_POST['delete_review'])) {
            $reviewId = (int)$_POST['review_id'];
            $stmt = $conn->prepare("DELETE FROM product_reviews WHERE id = :id");
            if ($stmt->execute([':id' => $reviewId])) {
                $success = 'Đã xóa đánh giá!';
            }
        }
    }

    // Lấy tham số lọc, tìm kiếm
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

    // Xây dựng điều kiện truy vấn cho đánh giá
    $where = ['1=1'];
    $params = [];
    if ($status !== 'all') {
        $where[] = "pr.status = :status";
        $params[':status'] = $status;
    }
    if ($search) {
        $where[] = "(p.name LIKE :search OR u.name LIKE :search OR pr.comment LIKE :search)";
        $params[':search'] = "%$search%";
    }
    $whereClause = implode(' AND ', $where);
    // Lấy danh sách đánh giá kèm thông tin sản phẩm, user
    $sql = "SELECT pr.*, 
        p.name as product_name, p.image as product_image, p.slug as product_slug,
        u.name as user_name, u.email as user_email
        FROM product_reviews pr
        LEFT JOIN products p ON pr.product_id = p.id
        LEFT JOIN users u ON pr.user_id = u.id
        WHERE $whereClause
        ORDER BY pr.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $reviews = $stmt->fetchAll();

    // Lấy tin nhắn liên hệ
    $contactStmt = $conn->prepare("SELECT * FROM contact_messages ORDER BY created_at DESC");
    $contactStmt->execute();
    $contactMessages = $contactStmt->fetchAll();

    // Thống kê số lượng đánh giá theo trạng thái
    $stats = [
        'total' => $conn->query("SELECT COUNT(*) FROM product_reviews")->fetchColumn(),
        'pending' => $conn->query("SELECT COUNT(*) FROM product_reviews WHERE status = 'pending'")->fetchColumn(),
        'approved' => $conn->query("SELECT COUNT(*) FROM product_reviews WHERE status = 'approved'")->fetchColumn(),
        'rejected' => $conn->query("SELECT COUNT(*) FROM product_reviews WHERE status = 'rejected'")->fetchColumn(),
    ];

} catch (PDOException $e) {
    // Thông báo lỗi thân thiện cho admin (không show stacktrace)
    $msg = $e->getMessage();
    $error = "Lỗi cơ sở dữ liệu: " . sanitize($msg) . ". Có vẻ bảng <code>product_reviews</code> chưa tồn tại. Bạn có thể tạo bảng bằng SQL sau:";
    $error .= "\n\n" . $createTableSQL;
    // $reviews và $stats đã có giá trị mặc định an toàn phía trên
}

$pageTitle = 'Quản lý đánh giá';
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
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
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
        <?php include __DIR__ . '/_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-3 sm:p-4 md:p-6">
            <?php if ($success): ?>
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="mb-8">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Quản lý đánh giá</h2>
                <p class="text-gray-600 mt-1">Kiểm duyệt và quản lý đánh giá sản phẩm</p>
            </div>

            <!-- Notification Banner -->
            <?php if ($stats['pending'] > 0): ?>
                <div class="mb-8 p-4 bg-orange-50 border-2 border-orange-200 rounded-lg flex items-center gap-3">
                    <span class="material-symbols-outlined text-2xl text-orange-600">notifications_active</span>
                    <div>
                        <p class="font-semibold text-orange-900">Có <?= $stats['pending'] ?> đánh giá chờ duyệt</p>
                        <p class="text-sm text-orange-700">Vui lòng kiểm tra và duyệt các bài đánh giá của người dùng</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Tổng đánh giá</p>
                            <h3 class="text-2xl font-bold mt-1"><?= $stats['total'] ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-blue-600">star</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Chờ duyệt</p>
                            <h3 class="text-2xl font-bold mt-1 text-orange-600"><?= $stats['pending'] ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-orange-600">schedule</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Đã duyệt</p>
                            <h3 class="text-2xl font-bold mt-1 text-green-600"><?= $stats['approved'] ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-green-600">check_circle</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Đã từ chối</p>
                            <h3 class="text-2xl font-bold mt-1 text-red-600"><?= $stats['rejected'] ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-red-600">cancel</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <form method="GET" class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[250px]">
                        <input type="text" name="search" value="<?= sanitize($search) ?>" 
                               placeholder="Tìm kiếm đánh giá, sản phẩm, người dùng..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    
                    <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Tất cả trạng thái</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                        <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Đã duyệt</option>
                        <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Đã từ chối</option>
                    </select>
                    
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">search</span>
                        Lọc
                    </button>
                </form>
            </div>

            <!-- Reviews List -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-8">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Sản phẩm</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Người đánh giá</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Đánh giá</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Nội dung</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Trạng thái</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Ngày tạo</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reviews)): ?>
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-gray-500">
                                        <span class="material-symbols-outlined text-5xl mb-2">rate_review</span>
                                        <p>Không có đánh giá nào</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reviews as $review): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-4">
                                            <?php if ($review['product_image']): ?>
                                                <img src="<?= imageUrl($review['product_image']) ?>" alt="<?= sanitize($review['product_name']) ?>"
                                                     class="w-20 h-20 rounded-lg object-cover border border-gray-300 shadow-sm" title="<?= sanitize($review['product_name']) ?>">
                                            <?php else: ?>
                                                <div class="w-20 h-20 rounded-lg bg-gray-100 flex items-center justify-center border border-gray-300">
                                                    <span class="material-symbols-outlined text-gray-400">image</span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="flex-1">
                                                <p class="font-semibold text-gray-900"><?= sanitize($review['product_name']) ?></p>
                                                <a href="<?= SITE_URL ?>/product_detail.php?slug=<?= $review['product_slug'] ?>" 
                                                   target="_blank" class="text-xs text-green-600 hover:underline flex items-center gap-1 mt-1">
                                                    <span class="material-symbols-outlined" style="font-size: 14px;">open_in_new</span>
                                                    Xem sản phẩm
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <p class="font-medium text-gray-900"><?= sanitize($review['user_name']) ?></p>
                                        <p class="text-sm text-gray-500"><?= sanitize($review['user_email']) ?></p>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-1">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="material-symbols-outlined text-lg <?= $i <= $review['rating'] ? 'text-yellow-400' : 'text-gray-300' ?>" style="font-variation-settings: 'FILL' 1;">star</span>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="text-sm text-gray-500 mt-1"><?= $review['rating'] ?>/5</p>
                                    </td>
                                    <td class="py-4 px-4 max-w-xs">
                                        <p class="text-sm text-gray-700 line-clamp-2"><?= sanitize($review['comment']) ?></p>
                                    </td>
                                    <td class="py-4 px-4">
                                        <?php
                                        $statusColors = [
                                            'pending' => 'bg-orange-100 text-orange-800',
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800'
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Chờ duyệt',
                                            'approved' => 'Đã duyệt',
                                            'rejected' => 'Đã từ chối'
                                        ];
                                        ?>
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $statusColors[$review['status']] ?>">
                                            <?= $statusLabels[$review['status']] ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <p class="text-sm text-gray-700"><?= date('d/m/Y', strtotime($review['created_at'])) ?></p>
                                        <p class="text-xs text-gray-500"><?= date('H:i', strtotime($review['created_at'])) ?></p>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-2">
                                            <?php if ($review['status'] === 'pending'): ?>
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                                    <button type="submit" name="approve_review" 
                                                            class="p-2 text-green-600 hover:bg-green-50 rounded-lg"
                                                            title="Duyệt">
                                                        <span class="material-symbols-outlined text-lg">check_circle</span>
                                                    </button>
                                                </form>
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                                    <button type="submit" name="reject_review" 
                                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg"
                                                            title="Từ chối">
                                                        <span class="material-symbols-outlined text-lg">cancel</span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" class="inline" 
                                                  onsubmit="return confirm('Bạn có chắc muốn xóa đánh giá này?')">
                                                <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                                <button type="submit" name="delete_review" 
                                                        class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg"
                                                        title="Xóa">
                                                    <span class="material-symbols-outlined text-lg">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Contact Messages List -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Họ tên</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Email</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">SĐT</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Chủ đề</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Nội dung</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Ngày gửi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($contactMessages)): ?>
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-gray-500">
                                        <span class="material-symbols-outlined text-5xl mb-2">mail</span>
                                        <p>Không có tin nhắn liên hệ nào</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($contactMessages as $msg): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-4 px-4 font-medium text-gray-900"><?= sanitize($msg['name']) ?></td>
                                    <td class="py-4 px-4 text-gray-700"><?= sanitize($msg['email']) ?></td>
                                    <td class="py-4 px-4 text-gray-700"><?= sanitize($msg['phone']) ?></td>
                                    <td class="py-4 px-4 text-gray-700"><?= sanitize($msg['subject']) ?></td>
                                    <td class="py-4 px-4 text-gray-700 max-w-xs"><?= nl2br(sanitize($msg['message'])) ?></td>
                                    <td class="py-4 px-4 text-gray-700">
                                        <?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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