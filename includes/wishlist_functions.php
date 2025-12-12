<?php

/**
 * wishlist_functions.php - Xử lý wishlist
 */

/**
 * Thêm sản phẩm vào wishlist
 */
function addToWishlist($userId, $productId)
{
    $conn = getConnection();

    try {
        $stmt = $conn->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$userId, $productId]);
        return true;
    } catch (PDOException $e) {
        // Duplicate entry
        if ($e->getCode() == 23000) {
            return false;
        }
        throw $e;
    }
}

/**
 * Xóa sản phẩm khỏi wishlist
 */
function removeFromWishlist($userId, $productId)
{
    $conn = getConnection();

    $stmt = $conn->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?");
    return $stmt->execute([$userId, $productId]);
}

/**
 * Kiểm tra sản phẩm có trong wishlist không
 */
function isInWishlist($userId, $productId)
{
    $conn = getConnection();

    $stmt = $conn->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);

    return $stmt->fetchColumn() > 0;
}

/**
 * Lấy danh sách wishlist của user
 */
function getUserWishlist($userId, $page = 1, $limit = 12)
{
    $conn = getConnection();
    $offset = ($page - 1) * $limit;

    // Count total
    $stmt = $conn->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ?");
    $stmt->execute([$userId]);
    $total = $stmt->fetchColumn();

    // Get products
    $sql = "SELECT p.*, w.created_at as added_at
            FROM wishlists w
            INNER JOIN products p ON w.product_id = p.id
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();

    return [
        'products' => $stmt->fetchAll(),
        'total' => $total,
        'pages' => ceil($total / $limit),
        'current_page' => $page
    ];
}

/**
 * Đếm số sản phẩm trong wishlist
 */
function getWishlistCount($userId)
{
    $conn = getConnection();

    $stmt = $conn->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ?");
    $stmt->execute([$userId]);

    return $stmt->fetchColumn();
}

/**
 * Toggle wishlist (thêm nếu chưa có, xóa nếu đã có)
 */
function toggleWishlist($userId, $productId)
{
    if (isInWishlist($userId, $productId)) {
        removeFromWishlist($userId, $productId);
        return 'removed';
    } else {
        addToWishlist($userId, $productId);
        return 'added';
    }
}
