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
    }
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

<main class="container" style="padding: 2rem 1rem; max-width: 1000px;">
    <h1 class="section-title" style="margin-bottom: 2rem;">Giỏ hàng của bạn</h1>
    
    <?php if (empty($cartItems)): ?>
        <!-- Empty Cart -->
        <div style="text-align: center; padding: 4rem 2rem; background: var(--card-light); border-radius: 1rem;">
            <span class="material-symbols-outlined" style="font-size: 5rem; color: var(--muted-light);">shopping_cart</span>
            <h2 style="margin-top: 1rem; font-size: 1.5rem;">Giỏ hàng trống</h2>
            <p style="color: var(--muted-light); margin-top: 0.5rem;">Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm.</p>
            <a href="<?= SITE_URL ?>/products.php" class="btn btn-primary" style="margin-top: 1.5rem;">
                Tiếp tục mua sắm
            </a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem;">
            <!-- Cart Items -->
            <div>
                <?php foreach ($cartItems as $item): ?>
                 <div class="cart-item" data-product-id="<?= $item['product']['id'] ?>"
                     style="display: flex; gap: 1rem; padding: 1.5rem; background: var(--card-light); border-radius: 0.75rem; margin-bottom: 1rem; border: 1px solid var(--border-light);">
                    
                    <!-- Product Image -->
                    <a href="<?= SITE_URL ?>/product_detail.php?slug=<?= $item['product']['slug'] ?>"
                       style="width: 100px; height: 100px; border-radius: 0.5rem; overflow: hidden; flex-shrink: 0;">
                        <img src="<?= $item['product']['image'] ?>" alt="<?= sanitize($item['product']['name']) ?>"
                             style="width: 100%; height: 100%; object-fit: cover;">
                    </a>
                    
                    <!-- Product Info -->
                    <div style="flex: 1; display: flex; flex-direction: column; gap: 0.5rem;">
                        <a href="<?= SITE_URL ?>/product_detail.php?slug=<?= $item['product']['slug'] ?>"
                           style="font-weight: 600; font-size: 1rem;">
                            <?= sanitize($item['product']['name']) ?>
                        </a>
                        <p class="item-price" data-unit-price="<?= $item['price'] ?>" style="color: var(--muted-light); font-size: 0.875rem;">
                            <?= formatPrice($item['price']) ?> / <?= $item['product']['unit'] ?>
                        </p>
                        
                        <!-- Quantity Controls -->
                        <div style="display: flex; align-items: center; gap: 1rem; margin-top: auto; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; border: 1px solid var(--border-light); border-radius: 0.5rem;">
                                <button type="button" class="qty-decrease" data-product-id="<?= $item['product']['id'] ?>"
                                    style="padding: 0.5rem 0.75rem; background: none; border: none; cursor: pointer; font-size: 1.2rem; font-weight: 600;">−</button>
                                <input type="number" min="1" max="<?= $item['product']['stock'] ?>" 
                                       class="cart-qty-input" 
                                       data-product-id="<?= $item['product']['id'] ?>" 
                                       data-stock="<?= $item['product']['stock'] ?>" 
                                       value="<?= $item['quantity'] ?>" 
                                       style="width: 60px; text-align: center; border: none; font-size: 1rem;" 
                                       onkeypress="handleCartEnterKey(event, <?= $item['product']['id'] ?>)" />
                                <button type="button" class="qty-increase" data-product-id="<?= $item['product']['id'] ?>"
                                    style="padding: 0.5rem 0.75rem; background: none; border: none; cursor: pointer; font-size: 1.2rem; font-weight: 600;">+</button>
                            </div>
                            
                            <button onclick="removeFromCart(<?= $item['product']['id'] ?>)"
                                    style="color: var(--danger); background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.25rem;">
                                <span class="material-symbols-outlined" style="font-size: 1.25rem;">delete</span>
                                Xóa
                            </button>
                        </div>
                        
                        <!-- Quantity Error Message -->
                        <div class="qty-error" data-product-id="<?= $item['product']['id'] ?>" style="display: none; margin-top: 0.5rem; padding: 0.5rem 0.75rem; background: rgba(220, 38, 38, 0.1); border-left: 3px solid var(--danger); border-radius: 0.25rem; font-size: 0.875rem; color: var(--danger);"></div>
                    </div>
                    
                    <!-- Item Total -->
                    <div class="item-total" style="font-weight: 700; color: var(--primary-dark);">
                        <?= formatPrice($item['total']) ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <!-- Clear Cart -->
                <button onclick="clearCart()" 
                        style="color: var(--muted-light); background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.25rem; margin-top: 1rem;">
                    <span class="material-symbols-outlined">delete_sweep</span>
                    Xóa tất cả
                </button>
            </div>
            
            <!-- Order Summary -->
            <div style="position: sticky; top: 100px; height: fit-content;">
                <div style="background: var(--card-light); border-radius: 0.75rem; padding: 1.5rem; border: 1px solid var(--border-light);">
                    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem;">Tóm tắt đơn hàng</h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 0.75rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-light);">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--muted-light);">Tạm tính</span>
                            <span class="cart-subtotal"><?= formatPrice($subtotal) ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--muted-light);">Phí vận chuyển</span>
                            <span class="cart-shipping" style="color: var(--success);">
                                <?php if ($isFreeShipping): ?>
                                    Miễn phí
                                <?php else: ?>
                                    <?= formatPrice($shippingFee) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php if (!$isFreeShipping): ?>
                    <div style="margin: 1rem 0; padding: 0.75rem; background: rgba(182, 230, 51, 0.1); border-radius: 0.5rem;">
                        <p style="font-size: 0.875rem; color: var(--text-light);">
                            Mua thêm <strong><?= formatPrice($freeShippingThreshold - $subtotal) ?></strong> để được miễn phí vận chuyển!
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <div style="display: flex; justify-content: space-between; margin-top: 1rem; font-size: 1.25rem; font-weight: 700;">
                        <span>Tổng cộng</span>
                        <span class="cart-grandtotal" style="color: var(--primary-dark);"><?= formatPrice($total) ?></span>
                    </div>
                    
                    <a href="<?= SITE_URL ?>/thanhtoan.php" class="btn btn-primary" style="width: 100%; margin-top: 1.5rem;">
                        Tiến hành thanh toán
                    </a>
                    
                    <a href="<?= SITE_URL ?>/products.php" style="display: block; text-align: center; margin-top: 1rem; color: var(--muted-light); font-size: 0.875rem;">
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