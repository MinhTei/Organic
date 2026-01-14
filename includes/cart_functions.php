<?php

/**
 * includes/cart_functions.php - Các hàm xử lý giỏ hàng
 */

/**
 * tải giỏ hàng từ database vào session
 */
function loadCartFromDatabase($userId)
{
    if (!$userId) return;

    $conn = getConnection();
    $stmt = $conn->prepare("SELECT product_id, quantity FROM carts WHERE user_id = ?");
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $_SESSION['cart'] = [];
    foreach ($cartItems as $item) {
        $_SESSION['cart'][$item['product_id']] = $item['quantity'];
    }
}

/**
 * lưu giỏ hàng từ session vào database
 */
function saveCartToDatabase($userId)
{
    if (!$userId) return;

    $conn = getConnection();

    //Xóa giỏ hàng hiện tại của người dùng trong database
    $conn->prepare("DELETE FROM carts WHERE user_id = ?")->execute([$userId]);

    // Nếu session cart trống, không cần chèn gì
    if (empty($_SESSION['cart'])) return;

    // chèn các mục giỏ hàng mới
    $stmt = $conn->prepare("INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, ?)");
    foreach ($_SESSION['cart'] as $productId => $quantity) {
        $stmt->execute([$userId, $productId, $quantity]);
    }
}

/**
 * gop giỏ hàng khi người dùng đăng nhập
 * Nếu sản phẩm tồn tại cả ở session và database, sử dụng số lượng lớn hơn
 */
function mergeCartOnLogin($userId)
{
    if (!$userId) return;

    $conn = getConnection();

    // Nếu session cart trống, chỉ load từ database
    if (empty($_SESSION['cart'])) {
        loadCartFromDatabase($userId);
        return;
    }

    // Lấy giỏ hàng hiện tại từ database
    $stmt = $conn->prepare("SELECT product_id, quantity FROM carts WHERE user_id = ?");
    $stmt->execute([$userId]);
    $dbCart = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Merge: nếu sản phẩm tồn tại ở cả hai, lấy số lượng lớn hơn
    foreach ($dbCart as $item) {
        $productId = $item['product_id'];
        $dbQty = $item['quantity'];

        if (isset($_SESSION['cart'][$productId])) {
            // Cả session và database đều có sản phẩm này
            // Lấy số lượng lớn hơn
            $_SESSION['cart'][$productId] = max($_SESSION['cart'][$productId], $dbQty);
        } else {
            // Chỉ database có sản phẩm này
            $_SESSION['cart'][$productId] = $dbQty;
        }
    }

    // Lưu giỏ hàng đã merge vào database
    saveCartToDatabase($userId);
}
