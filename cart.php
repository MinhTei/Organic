<?php

/**
 * giohang.php - Trang giỏ hàng
 */

// Start session first before anything else
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/settings_helper.php';
require_once __DIR__ . '/includes/cart_functions.php';

// Load cart from database if user is logged in
if (isset($_SESSION['user_id'])) {
    loadCartFromDatabase($_SESSION['user_id']);
}

// Gui xử lý AJAX cho các hành động giỏ hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');


    $action = $_POST['action'];
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    error_log('Cart action: ' . $action . ', productId: ' . $productId . ', quantity: ' . $quantity);

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    switch ($action) {
        case 'add':
            if (!isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId] = 0;
            }
            $_SESSION['cart'][$productId] += $quantity;
            // Save to database if user is logged in
            if (isset($_SESSION['user_id'])) {
                saveCartToDatabase($_SESSION['user_id']);
            }
            echo json_encode(['success' => true, 'message' => 'Đã thêm vào giỏ hàng', 'cart_count' => count($_SESSION['cart'])]);
            exit;
            break;

        case 'update':
            // Kiểm tra stock từ database trước khi update
            if ($quantity > 0) {
                $conn = getConnection();
                $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
                $stmt->execute([$productId]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$product) {
                    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại.']);
                    exit;
                }

                // Kiểm tra nếu vượt quá stock
                if ($quantity > $product['stock']) {
                    echo json_encode([
                        'success' => false,
                        'message' => "Kho hiện tại không còn đủ. Chỉ còn {$product['stock']} sản phẩm.",
                        'max_stock' => $product['stock']
                    ]);
                    exit;
                }

                $_SESSION['cart'][$productId] = $quantity;
            } else {
                unset($_SESSION['cart'][$productId]);
            }
            // Save to database if user is logged in
            if (isset($_SESSION['user_id'])) {
                saveCartToDatabase($_SESSION['user_id']);
            }
            // Tính lại giá trị giỏ hàng sau cập nhật
            $cartCount = count($_SESSION['cart']);
            $subtotal = 0;
            $shippingFee = 25000;
            $total = 0;
            $unitPrice = 0;
            if (!empty($_SESSION['cart'])) {
                $conn = getConnection();
                $ids = array_keys($_SESSION['cart']);
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
                $stmt->execute($ids);
                $products = $stmt->fetchAll();
                foreach ($products as $product) {
                    $qty = $_SESSION['cart'][$product['id']];
                    $price = $product['sale_price'] ?? $product['price'];
                    if ($product['id'] == $productId) {
                        $unitPrice = $price;
                    }
                    $itemTotal = $price * $qty;
                    $subtotal += $itemTotal;
                }
            }
            $freeShippingThreshold = (int) getSystemSetting('free_shipping_threshold', 500000);
            $isFreeShipping = $subtotal >= $freeShippingThreshold;
            if ($isFreeShipping) {
                $shippingFee = 0;
            }
            $total = $subtotal + ($subtotal > 0 ? $shippingFee : 0);
            if ($isFreeShipping) {
                $total = $subtotal;
            }
            echo json_encode([
                'success' => true,
                'message' => 'Đã cập nhật giỏ hàng',
                'cart_count' => $cartCount,
                'subtotal' => $subtotal,
                'shippingFee' => $shippingFee,
                'total' => $total,
                'unitPrice' => $unitPrice,
                'isFreeShipping' => $isFreeShipping,
                'freeShippingThreshold' => $freeShippingThreshold,
                'remainingAmount' => max(0, $freeShippingThreshold - $subtotal)
            ]);
            exit;

        case 'remove':
            unset($_SESSION['cart'][$productId]);
            // Save to database if user is logged in
            if (isset($_SESSION['user_id'])) {
                saveCartToDatabase($_SESSION['user_id']);
            }
            echo json_encode(['success' => true, 'message' => 'Đã xóa khỏi giỏ hàng']);
            exit;

        case 'clear':
            $_SESSION['cart'] = [];
            // Clear from database if user is logged in
            if (isset($_SESSION['user_id'])) {
                $conn = getConnection();
                $conn->prepare("DELETE FROM carts WHERE user_id = ?")->execute([$_SESSION['user_id']]);
            }
            echo json_encode(['success' => true, 'message' => 'Đã xóa giỏ hàng']);
            exit;

        default:
            echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
            exit;
    }
    exit;
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POST nhưng không có action
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Thiếu tham số action', 'received_post' => $_POST]);
    exit;
}

// Get cart items
$cartItems = [];
$subtotal = 0;
$shippingFee = 25000;

if (!empty($_SESSION['cart'])) {
    $conn = getConnection();
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    foreach ($products as $product) {
        $qty = $_SESSION['cart'][$product['id']];
        $price = $product['sale_price'] ?? $product['price'];
        $itemTotal = $price * $qty;
        $subtotal += $itemTotal;

        $cartItems[] = [
            'product' => $product,
            'quantity' => $qty,
            'price' => $price,
            'total' => $itemTotal
        ];
    }
}

// lấy phí vận chuyển mặc định từ cài đặt hệ thống 
$shippingFee = (int) getSystemSetting('default_shipping_fee', 25000);

$total = $subtotal + ($subtotal > 0 ? $shippingFee : 0);

// Kiểm tra điều kiện miễn phí vận chuyển 500k
$freeShippingThreshold = (int) getSystemSetting('free_shipping_threshold', 500000);
$isFreeShipping = $subtotal >= $freeShippingThreshold;
if ($isFreeShipping) {
    $shippingFee = 0;
    $total = $subtotal;
}

$pageTitle = 'Giỏ hàng';
include __DIR__ . '/includes/header.php';
?>

<main style="padding: clamp(1rem, 3vw, 2rem); max-width: 1200px; margin: 0 auto;">
    <h1 style="font-size: clamp(1.5rem, 5vw, 2.5rem); font-weight: 700; margin-bottom: clamp(1rem, 3vw, 2rem); color: var(--text-light);">Giỏ hàng của bạn</h1>

    <?php if (empty($cartItems)): ?>
        <!-- Empty Cart -->
        <div style="text-align: center; padding: clamp(2rem, 5vw, 4rem); background: var(--card-light); border-radius: clamp(0.5rem, 2vw, 1rem); border: 1px solid var(--border-light);">
            <span class="material-symbols-outlined" style="font-size: clamp(3rem, 15vw, 5rem); color: var(--muted-light);">shopping_cart</span>
            <h2 style="margin-top: clamp(0.5rem, 2vw, 1rem); font-size: clamp(1.25rem, 4vw, 1.5rem); font-weight: 600;">Giỏ hàng trống</h2>
            <p style="color: var(--muted-light); margin-top: clamp(0.25rem, 1vw, 0.5rem); font-size: clamp(0.875rem, 2vw, 1rem);">Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm.</p>
            <a href="<?= SITE_URL ?>/products.php" style="display: inline-block; margin-top: clamp(1rem, 3vw, 1.5rem); padding: clamp(0.5rem, 2vw, 0.75rem) clamp(1rem, 3vw, 1.5rem); background: var(--primary); color: var(--text-light); font-weight: 600; border-radius: 0.5rem; text-decoration: none; font-size: clamp(0.875rem, 2vw, 1rem);">
                Tiếp tục mua sắm
            </a>
        </div>
    <?php else: ?>
        <style>
            @media (max-width: 768px) {
                .cart-grid {
                    grid-template-columns: 1fr !important;
                }

                .order-summary-mobile {
                    position: static !important;
                    margin-top: clamp(1.5rem, 3vw, 2rem) !important;
                }

                .cart-item-inner {
                    grid-template-columns: 80px 1fr !important;
                    gap: 0.75rem !important;
                    padding: 0.75rem !important;
                }

                .cart-item-image {
                    width: 80px !important;
                    height: 80px !important;
                }

                .cart-item-details {
                    gap: 0.5rem !important;
                }

                .cart-item-details p {
                    font-size: 0.8rem !important;
                }

                .cart-item-details a {
                    font-size: 0.85rem !important;
                }
            }
        </style>
        <div class="cart-grid" style="display: grid; grid-template-columns: 1fr minmax(300px, 350px); gap: clamp(1.5rem, 3vw, 2rem);">
            <!-- Cart Items -->
            <div>
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item" data-product-id="<?= $item['product']['id'] ?>"
                        style="background: var(--card-light); border-radius: 12px; margin-bottom: 1rem; border: 1px solid var(--border-light); overflow: hidden; transition: box-shadow 0.3s ease;">

                        <div class="cart-item-inner" style="display: grid; grid-template-columns: 100px 1fr; gap: 1rem; padding: 1.25rem;">

                            <!-- Product Image -->
                            <a href="<?= SITE_URL ?>/product_detail.php?slug=<?= $item['product']['slug'] ?>"
                                class="cart-item-image"
                                style="width: 100px; height: 100px; border-radius: 8px; overflow: hidden; flex-shrink: 0; background: #f5f5f5;">
                                <img src="<?= imageUrl($item['product']['image']) ?>"
                                    alt="<?= sanitize($item['product']['name']) ?>"
                                    style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;"
                                    onmouseover="this.style.transform='scale(1.05)'"
                                    onmouseout="this.style.transform='scale(1)'">
                            </a>

                            <!-- Product Details -->
                            <div class="cart-item-details" style="display: flex; flex-direction: column; gap: 0.75rem; min-width: 0;">

                                <!-- Header: Name and Remove Button -->
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem;">
                                    <div style="flex: 1; min-width: 0;">
                                        <a href="<?= SITE_URL ?>/product_detail.php?slug=<?= $item['product']['slug'] ?>"
                                            style="font-weight: 600; font-size: 1rem; color: var(--text-light); text-decoration: none; display: block; margin-bottom: 0.25rem; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; line-height: 1.4;">
                                            <?= sanitize($item['product']['name']) ?>
                                        </a>
                                        <p class="item-price" data-unit-price="<?= $item['price'] ?>"
                                            style="color: var(--muted-light); font-size: 0.875rem; margin: 0;">
                                            <?= formatPrice($item['price']) ?> / <?= $item['product']['unit'] ?>
                                        </p>
                                    </div>

                                    <!-- Remove Button (Desktop) -->
                                    <button onclick="removeFromCart(<?= $item['product']['id'] ?>)"
                                        class="btn-remove-desktop"
                                        style="color: var(--muted-light); background: none; border: none; cursor: pointer; padding: 0.5rem; margin: -0.5rem -0.5rem -0.5rem 0; border-radius: 6px; transition: all 0.2s ease; display: flex; align-items: center; justify-content: center;"
                                        onmouseover="this.style.background='rgba(220, 38, 38, 0.1)'; this.style.color='var(--danger)'"
                                        onmouseout="this.style.background='none'; this.style.color='var(--muted-light)'">
                                        <span class="material-symbols-outlined" style="font-size: 1.25rem;">delete</span>
                                    </button>
                                </div>

                                <!-- Số lượng khi lỗi -->
                                <div class="qty-error" data-product-id="<?= $item['product']['id'] ?>"
                                    style="display: none; padding: 0.5rem 0.75rem; background: rgba(220, 38, 38, 0.1); border-left: 3px solid var(--danger); border-radius: 4px; font-size: 0.8125rem; color: var(--danger); line-height: 1.4;"></div>

                                <!-- Footer: Quantity Controls and Price -->
                                <div class="cart-item-footer" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-top: auto;">

                                    <!-- Quantity Controls -->
                                    <div style="display: flex; align-items: center; border: 1px solid var(--border-light); border-radius: 8px; background: white;">
                                        <button type="button"
                                            class="qty-decrease"
                                            data-product-id="<?= $item['product']['id'] ?>"
                                            style="padding: 0.5rem 0.75rem; background: none; border: none; cursor: pointer; font-size: 1.125rem; font-weight: 600; color: var(--text-light); transition: background 0.2s ease; border-radius: 7px 0 0 7px;"
                                            onmouseover="this.style.background='#f5f5f5'"
                                            onmouseout="this.style.background='none'">−</button>

                                        <input type="number"
                                            min="1"
                                            max="<?= $item['product']['stock'] ?>"
                                            class="cart-qty-input"
                                            data-product-id="<?= $item['product']['id'] ?>"
                                            data-stock="<?= $item['product']['stock'] ?>"
                                            data-price="<?= $item['price'] ?>"
                                            value="<?= $item['quantity'] ?>"
                                            style="width: 50px; text-align: center; border: none; font-size: 0.9375rem; font-weight: 500; padding: 0.5rem 0; outline: none;"
                                            onkeypress="handleCartEnterKey(event, <?= $item['product']['id'] ?>)" />

                                        <button type="button"
                                            class="qty-increase"
                                            data-product-id="<?= $item['product']['id'] ?>"
                                            style="padding: 0.5rem 0.75rem; background: none; border: none; cursor: pointer; font-size: 1.125rem; font-weight: 600; color: var(--text-light); transition: background 0.2s ease; border-radius: 0 7px 7px 0;"
                                            onmouseover="this.style.background='#f5f5f5'"
                                            onmouseout="this.style.background='none'">+</button>
                                    </div>

                                    <!-- Item Total Price -->
                                    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.125rem;">
                                        <span style="font-size: 0.75rem; color: var(--muted-light); text-transform: uppercase; letter-spacing: 0.5px;">Thành tiền</span>
                                        <span class="item-total" style="font-weight: 700; color: var(--primary-dark); font-size: 1.125rem;">
                                            <?= formatPrice($item['total']) ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Remove Button (Mobile) -->
                                <button onclick="removeFromCart(<?= $item['product']['id'] ?>)"
                                    class="btn-remove-mobile"
                                    style="display: none; color: var(--danger); background: rgba(220, 38, 38, 0.05); border: 1px solid rgba(220, 38, 38, 0.2); cursor: pointer; padding: 0.625rem; border-radius: 6px; font-size: 0.875rem; font-weight: 500; transition: all 0.2s ease; align-items: center; justify-content: center; gap: 0.375rem; margin-top: 0.5rem;"
                                    onmouseover="this.style.background='rgba(220, 38, 38, 0.1)'"
                                    onmouseout="this.style.background='rgba(220, 38, 38, 0.05)'">
                                    <span class="material-symbols-outlined" style="font-size: 1.125rem;">delete</span>
                                    Xóa sản phẩm
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Clear Cart -->
                <button onclick="clearCart()"
                    style="color: var(--muted-light); background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: clamp(0.15rem, 1vw, 0.25rem); margin-top: clamp(0.75rem, 2vw, 1rem); font-size: clamp(0.875rem, 1.5vw, 1rem);">
                    <span class="material-symbols-outlined" style="font-size: clamp(1rem, 2vw, 1.25rem);">delete_sweep</span>
                    Xóa tất cả
                </button>
            </div>

            <!-- Hóa đơn tổng cộng -->
            <div class="order-summary-mobile" style="position: sticky; top: clamp(60px, 10vw, 100px); height: fit-content;">
                <div style="background: var(--card-light); border-radius: clamp(0.5rem, 1vw, 0.75rem); padding: clamp(1rem, 2vw, 1.5rem); border: 1px solid var(--border-light);">
                    <h3 style="font-size: clamp(1rem, 3vw, 1.25rem); font-weight: 700; margin-bottom: clamp(1rem, 2vw, 1.5rem); color: var(--text-light);">Tóm tắt đơn hàng</h3>

                    <div style="display: flex; flex-direction: column; gap: clamp(0.5rem, 1vw, 0.75rem); padding-bottom: clamp(0.75rem, 1vw, 1rem); border-bottom: 1px solid var(--border-light);">
                        <div style="display: flex; justify-content: space-between; font-size: clamp(0.875rem, 2vw, 1rem);">
                            <span style="color: var(--muted-light);">Tạm tính</span>
                            <span class="cart-subtotal" style="font-weight: 500;"><?= formatPrice($subtotal) ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: clamp(0.875rem, 2vw, 1rem);">
                            <span style="color: var(--muted-light);">Phí vận chuyển</span>
                            <span class="cart-shipping" style="color: var(--success); font-weight: 500;">
                                <?php if ($isFreeShipping): ?>
                                    Miễn phí
                                <?php else: ?>
                                    <?= formatPrice($shippingFee) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <?php if (!$isFreeShipping): ?>
                        <div id="freeShippingHint" style="margin: clamp(0.75rem, 1.5vw, 1rem) 0; padding: clamp(0.5rem, 1vw, 0.75rem); background: rgba(182, 230, 51, 0.1); border-radius: clamp(0.35rem, 0.5vw, 0.5rem);">
                            <p style="font-size: clamp(0.75rem, 1.5vw, 0.875rem); color: var(--text-light);">
                                Mua thêm <strong id="freeShippingAmount"><?= formatPrice($freeShippingThreshold - $subtotal) ?></strong> để được miễn phí vận chuyển!
                            </p>
                        </div>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between; margin-top: clamp(0.75rem, 1.5vw, 1rem); font-size: clamp(1rem, 3vw, 1.25rem); font-weight: 700;">
                        <span style="color: var(--text-light);">Tổng cộng</span>
                        <span class="cart-grandtotal" style="color: var(--primary-dark);"><?= formatPrice($total) ?></span>
                    </div>

                    <a href="<?= SITE_URL ?>/thanhtoan.php" style="display: block; text-align: center; margin-top: clamp(1rem, 2vw, 1.5rem); padding: clamp(0.5rem, 1.5vw, 0.75rem) clamp(1rem, 2vw, 1.5rem); background: var(--primary); color: var(--text-light); font-weight: 600; border-radius: clamp(0.35rem, 1vw, 0.5rem); text-decoration: none; font-size: clamp(0.875rem, 2vw, 1rem); border: none; cursor: pointer;">
                        Tiến hành thanh toán
                    </a>

                    <a href="<?= SITE_URL ?>/products.php" style="display: block; text-align: center; margin-top: clamp(0.75rem, 1.5vw, 1rem); color: var(--muted-light); font-size: clamp(0.75rem, 1.5vw, 0.875rem); text-decoration: none;">
                        ← Tiếp tục mua sắm
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<style>
    /* Ẩn nút tăng giảm mặc định của input number */
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type="number"] {
        appearance: textfield;
    }
</style>

<script>
    function updateCart(productId, quantity) {
        fetch('<?= SITE_URL ?>/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=update&product_id=${productId}&quantity=${quantity}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (typeof showNotification === 'function') showNotification('Đã cập nhật số lượng', 'success');
                    if (typeof updateCartCount === 'function' && data.cart_count !== undefined) updateCartCount(data.cart_count);
                    // Cập nhật lại giá trị input
                    const input = document.querySelector(`.cart-qty-input[data-product-id='${productId}']`);
                    if (input) input.value = quantity;

                    // Update item total price
                    const itemRow = input.closest('.cart-item');
                    if (itemRow && data.unitPrice !== undefined) {
                        const totalText = itemRow.querySelector('.item-total');
                        if (totalText) {
                            totalText.textContent = formatPrice(data.unitPrice * quantity);
                        }
                    }

                    //Cập nhật lại tổng giá trị giỏ hàng
                    if (data.subtotal !== undefined) {
                        const subtotalEl = document.querySelector('.cart-subtotal');
                        if (subtotalEl) subtotalEl.textContent = formatPrice(data.subtotal);
                    }
                    if (data.shippingFee !== undefined) {
                        const shippingEl = document.querySelector('.cart-shipping');
                        if (shippingEl) shippingEl.textContent = data.shippingFee === 0 ? 'Miễn phí' : formatPrice(data.shippingFee);
                    }
                    if (data.total !== undefined) {
                        const totalEl = document.querySelector('.cart-grandtotal');
                        if (totalEl) totalEl.textContent = formatPrice(data.total);
                    }

                    // cập nhật gợi ý miễn phí vận chuyển
                    if (data.isFreeShipping !== undefined) {
                        const hintEl = document.getElementById('freeShippingHint');
                        const amountEl = document.getElementById('freeShippingAmount');
                        if (hintEl && data.isFreeShipping) {
                            // Ân gợi ý nếu đã đủ điều kiện
                            hintEl.style.display = 'none';
                        } else if (hintEl && !data.isFreeShipping && data.remainingAmount !== undefined) {
                            // hiên thị gợi ý nếu chưa đủ điều kiện
                            hintEl.style.display = 'block';
                            if (amountEl) amountEl.textContent = formatPrice(data.remainingAmount);
                        }
                    }
                } else {
                    if (typeof showNotification === 'function') showNotification(data.message || 'Kho hiện tại không còn đủ', 'error');
                    // Cập nhật lại giá trị input về số lượng tối đa trong kho nếu có
                    if (data.max_stock !== undefined) {
                        const input = document.querySelector(`.cart-qty-input[data-product-id='${productId}']`);
                        if (input) input.dataset.stock = data.max_stock;
                    }
                }
            });
    }

    // Sự kiện tăng/giảm số lượng
    document.addEventListener('DOMContentLoaded', function() {
        document.body.addEventListener('click', function(e) {
            if (e.target.classList.contains('qty-increase')) {
                const productId = e.target.dataset.productId;
                const input = document.querySelector(`.cart-qty-input[data-product-id='${productId}']`);
                let qty = parseInt(input.value) || 1;
                const stock = parseInt(input.dataset.stock || "0");

                // Check if increasing would exceed stock
                if (stock > 0 && qty + 1 > stock) {
                    showNotification(`Số lượng vượt quá tồn kho. Chỉ còn ${stock} sản phẩm.`, 'error');
                } else {
                    updateCart(productId, qty + 1);
                }
            }
            if (e.target.classList.contains('qty-decrease')) {
                const productId = e.target.dataset.productId;
                const input = document.querySelector(`.cart-qty-input[data-product-id='${productId}']`);
                let qty = parseInt(input.value) || 1;
                const newQty = qty - 1;

                if (newQty <= 0) {
                    // Trigger change event on input with qty=0 to use js/scripts.js logic
                    input.value = 0;
                    input.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                } else {
                    updateCart(productId, newQty);
                }
            }
        });
    });

    function removeFromCart(productId) {
        if (confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
            fetch('<?= SITE_URL ?>/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=remove&product_id=${productId}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (typeof showNotification === 'function') showNotification('Đã xóa sản phẩm', 'success');
                        if (typeof updateCartCount === 'function') updateCartCount(0);

                        // Remove item from DOM
                        const cartItem = document.querySelector(`.cart-item[data-product-id='${productId}']`);
                        if (cartItem) {
                            cartItem.style.transition = 'opacity 0.3s ease';
                            cartItem.style.opacity = '0';
                            setTimeout(() => cartItem.remove(), 300);
                        }

                        // Reload page to recalculate totals
                        setTimeout(() => location.reload(), 500);
                    } else {
                        if (typeof showNotification === 'function') showNotification('Có lỗi khi xóa', 'error');
                    }
                });
        }
    }

    function clearCart() {
        if (confirm('Bạn có chắc muốn xóa tất cả sản phẩm?')) {
            // Fade out all cart items first
            const cartItems = document.querySelectorAll('.cart-item');
            cartItems.forEach((item, index) => {
                item.style.transition = `opacity 0.3s ease ${index * 50}ms`;
                item.style.opacity = '0';
            });

            // Wait for animation to complete, then make request
            setTimeout(() => {
                fetch('<?= SITE_URL ?>/cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'action=clear'
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (typeof showNotification === 'function') showNotification('Đã xóa tất cả sản phẩm', 'success');
                            if (typeof updateCartCount === 'function') updateCartCount(0);

                            // Reload page after a short delay
                            setTimeout(() => location.reload(), 300);
                        } else {
                            if (typeof showNotification === 'function') showNotification('Có lỗi khi xóa', 'error');
                            // Reset opacity on error
                            cartItems.forEach(item => {
                                item.style.opacity = '1';
                            });
                        }
                    });
            }, 400);
        }
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>