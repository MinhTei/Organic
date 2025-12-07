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

// Handle cart actions via AJAX
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
            echo json_encode(['success' => true, 'message' => 'Đã thêm vào giỏ hàng', 'cart_count' => array_sum($_SESSION['cart'])]);
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
            // Tính lại giá trị giỏ hàng sau cập nhật
            $cartCount = array_sum($_SESSION['cart']);
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
            $freeShippingThreshold = 500000;
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
                'unitPrice' => $unitPrice
            ]);
            exit;
            
        case 'remove':
            unset($_SESSION['cart'][$productId]);
            echo json_encode(['success' => true, 'message' => 'Đã xóa khỏi giỏ hàng']);
            exit;
            
        case 'clear':
            $_SESSION['cart'] = [];
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

$total = $subtotal + ($subtotal > 0 ? $shippingFee : 0);

// Free shipping threshold
$freeShippingThreshold = 500000;
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
            }
        </style>
        <div class="cart-grid" style="display: grid; grid-template-columns: 1fr minmax(300px, 350px); gap: clamp(1.5rem, 3vw, 2rem);">
            <!-- Cart Items -->
            <div>
                <?php foreach ($cartItems as $item): ?>
                 <div class="cart-item" data-product-id="<?= $item['product']['id'] ?>"
                     style="display: grid; grid-template-columns: clamp(80px, 20vw, 120px) 1fr; gap: clamp(0.75rem, 2vw, 1rem); padding: clamp(1rem, 2vw, 1.5rem); background: var(--card-light); border-radius: clamp(0.5rem, 1vw, 0.75rem); margin-bottom: clamp(0.75rem, 2vw, 1rem); border: 1px solid var(--border-light);">
                    
                    <!-- Product Image -->
                    <a href="<?= SITE_URL ?>/product_detail.php?slug=<?= $item['product']['slug'] ?>"
                       style="width: 100%; aspect-ratio: 1; border-radius: clamp(0.35rem, 1vw, 0.5rem); overflow: hidden;">
                        <img src="<?= $item['product']['image'] ?>" alt="<?= sanitize($item['product']['name']) ?>"
                             style="width: 100%; height: 100%; object-fit: cover;">
                    </a>
                    
                    <!-- Product Info -->
                    <div style="display: flex; flex-direction: column; gap: clamp(0.25rem, 1vw, 0.5rem); grid-column: 1 / -1;">
                        <a href="<?= SITE_URL ?>/product_detail.php?slug=<?= $item['product']['slug'] ?>"
                           style="font-weight: 600; font-size: clamp(0.875rem, 2vw, 1rem); color: var(--text-light);">
                            <?= sanitize($item['product']['name']) ?>
                        </a>
                        <p class="item-price" data-unit-price="<?= $item['price'] ?>" style="color: var(--muted-light); font-size: clamp(0.75rem, 1.5vw, 0.875rem);">
                            <?= formatPrice($item['price']) ?> / <?= $item['product']['unit'] ?>
                        </p>
                        
                        <!-- Quantity Controls -->
                        <div style="display: flex; align-items: center; gap: clamp(0.5rem, 2vw, 1rem); margin-top: auto; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; border: 1px solid var(--border-light); border-radius: clamp(0.35rem, 1vw, 0.5rem);">
                                <button type="button" class="qty-decrease" data-product-id="<?= $item['product']['id'] ?>"
                                    style="padding: clamp(0.35rem, 1vw, 0.5rem) clamp(0.5rem, 1vw, 0.75rem); background: none; border: none; cursor: pointer; font-size: clamp(0.875rem, 2vw, 1.2rem); font-weight: 600;">−</button>
                                <input type="number" min="1" max="<?= $item['product']['stock'] ?>" 
                                       class="cart-qty-input" 
                                       data-product-id="<?= $item['product']['id'] ?>" 
                                       data-stock="<?= $item['product']['stock'] ?>" 
                                       value="<?= $item['quantity'] ?>" 
                                       style="width: clamp(40px, 10vw, 60px); text-align: center; border: none; font-size: clamp(0.875rem, 2vw, 1rem);" 
                                       onkeypress="handleCartEnterKey(event, <?= $item['product']['id'] ?>)" />
                                <button type="button" class="qty-increase" data-product-id="<?= $item['product']['id'] ?>"
                                    style="padding: clamp(0.35rem, 1vw, 0.5rem) clamp(0.5rem, 1vw, 0.75rem); background: none; border: none; cursor: pointer; font-size: clamp(0.875rem, 2vw, 1.2rem); font-weight: 600;">+</button>
                            </div>
                            
                            <button onclick="removeFromCart(<?= $item['product']['id'] ?>)"
                                    style="color: var(--danger); background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: clamp(0.15rem, 1vw, 0.25rem); font-size: clamp(0.75rem, 1.5vw, 0.875rem);">
                                <span class="material-symbols-outlined" style="font-size: clamp(1rem, 2vw, 1.25rem);">delete</span>
                                Xóa
                            </button>
                        </div>
                        
                        <!-- Quantity Error Message -->
                        <div class="qty-error" data-product-id="<?= $item['product']['id'] ?>" style="display: none; margin-top: clamp(0.25rem, 1vw, 0.5rem); padding: clamp(0.35rem, 1vw, 0.5rem) clamp(0.5rem, 1vw, 0.75rem); background: rgba(220, 38, 38, 0.1); border-left: 3px solid var(--danger); border-radius: 0.25rem; font-size: clamp(0.75rem, 1.5vw, 0.875rem); color: var(--danger);"></div>
                    </div>
                    
                    <!-- Item Total -->
                    <div class="item-total" style="font-weight: 700; color: var(--primary-dark); font-size: clamp(0.875rem, 2vw, 1rem); grid-column: 2; text-align: right; padding-top: clamp(0.5rem, 1vw, 0.75rem);">
                        <?= formatPrice($item['total']) ?>
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
            
            <!-- Order Summary -->
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
                    <div style="margin: clamp(0.75rem, 1.5vw, 1rem) 0; padding: clamp(0.5rem, 1vw, 0.75rem); background: rgba(182, 230, 51, 0.1); border-radius: clamp(0.35rem, 0.5vw, 0.5rem);">
                        <p style="font-size: clamp(0.75rem, 1.5vw, 0.875rem); color: var(--text-light);">
                            Mua thêm <strong><?= formatPrice($freeShippingThreshold - $subtotal) ?></strong> để được miễn phí vận chuyển!
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
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=update&product_id=${productId}&quantity=${quantity}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (typeof showNotification === 'function') showNotification('Đã cập nhật số lượng', 'success');
            if (typeof updateCartCount === 'function' && data.cart_count !== undefined) updateCartCount(data.cart_count);
            // Update input value in UI for better UX
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

            // Update subtotal, shipping, grand total
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
        } else {
            if (typeof showNotification === 'function') showNotification(data.message || 'Kho hiện tại không còn đủ', 'error');
            // Update max stock if provided
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
            updateCart(productId, qty + 1);
        }
        if (e.target.classList.contains('qty-decrease')) {
            const productId = e.target.dataset.productId;
            const input = document.querySelector(`.cart-qty-input[data-product-id='${productId}']`);
            let qty = parseInt(input.value) || 1;
            updateCart(productId, qty > 1 ? qty - 1 : 1);
        }
    });
});

function removeFromCart(productId) {
    if (confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
        fetch('<?= SITE_URL ?>/cart.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
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
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
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