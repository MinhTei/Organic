<?php
/**
 * api/wishlist.php - API xử lý wishlist
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/wishlist_functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để sử dụng chức năng này.'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];

// Handle different actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    
    if (!$productId) {
        echo json_encode([
            'success' => false,
            'message' => 'Sản phẩm không hợp lệ.'
        ]);
        exit;
    }
    
    switch ($action) {
        case 'toggle':
            $result = toggleWishlist($userId, $productId);
            
            if ($result === 'added') {
                echo json_encode([
                    'success' => true,
                    'action' => 'added',
                    'message' => 'Đã thêm vào yêu thích.',
                    'count' => getWishlistCount($userId)
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'action' => 'removed',
                    'message' => 'Đã xóa khỏi yêu thích.',
                    'count' => getWishlistCount($userId)
                ]);
            }
            break;
            
        case 'check':
            $isInWishlist = isInWishlist($userId, $productId);
            echo json_encode([
                'success' => true,
                'in_wishlist' => $isInWishlist
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Hành động không hợp lệ.'
            ]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get wishlist count
    $count = getWishlistCount($userId);
    echo json_encode([
        'success' => true,
        'count' => $count
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Phương thức không được hỗ trợ.'
    ]);
}