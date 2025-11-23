<?php
/**
 * giohang.php - Trang giỏ hàng
 */

require_once 'config.php';
require_once 'includes/functions.php';

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
            break;
            
        case 'update':
            if ($quantity > 0) {
                $_SESSION['cart'][$productId] = $quantity;
            } else {
                unset($_SESSION['cart'][$productId]);
            }
            echo json_encode(['success' => true, 'message' => 'Đã cập nhật giỏ hàng']);
            break;
            
        case 'remove':
            unset($_SESSION['cart'][$productId]);
            echo json_encode(['success' => true, 'message' => 'Đã xóa khỏi giỏ hàng']);
            break;
            
        case 'clear':
            $_SESSION['cart'] = [];
            echo json_encode(['success' => true, 'message' => 'Đã xóa giỏ hàng']);
            break;
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
include 'includes/header.php';
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
                        <p style="color: var(--muted-light); font-size: 0.875rem;">
                            <?= formatPrice($item['price']) ?> / <?= $item['product']['unit'] ?>
                        </p>
                        
                        <!-- Quantity Controls -->
                        <div style="display: flex; align-items: center; gap: 1rem; margin-top: auto;">
                            <div style="display: flex; align-items: center; border: 1px solid var(--border-light); border-radius: 0.5rem;">
                                <button onclick="updateCart(<?= $item['product']['id'] ?>, <?= $item['quantity'] - 1 ?>)"
                                        style="padding: 0.5rem 0.75rem; background: none; border: none; cursor: pointer;">-</button>
                                <span style="padding: 0 0.5rem; min-width: 30px; text-align: center;"><?= $item['quantity'] ?></span>
                                <button onclick="updateCart(<?= $item['product']['id'] ?>, <?= $item['quantity'] + 1 ?>)"
                                        style="padding: 0.5rem 0.75rem; background: none; border: none; cursor: pointer;">+</button>
                            </div>
                            
                            <button onclick="removeFromCart(<?= $item['product']['id'] ?>)"
                                    style="color: var(--danger); background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.25rem;">
                                <span class="material-symbols-outlined" style="font-size: 1.25rem;">delete</span>
                                Xóa
                            </button>
                        </div>
                    </div>
                    
                    <!-- Item Total -->
                    <div style="font-weight: 700; color: var(--primary-dark);">
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
                            <span><?= formatPrice($subtotal) ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--muted-light);">Phí vận chuyển</span>
                            <?php if ($isFreeShipping): ?>
                                <span style="color: var(--success);">Miễn phí</span>
                            <?php else: ?>
                                <span><?= formatPrice($shippingFee) ?></span>
                            <?php endif; ?>
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
                        <span style="color: var(--primary-dark);"><?= formatPrice($total) ?></span>
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

<script>
function updateCart(productId, quantity) {
    fetch('<?= SITE_URL ?>/giohang.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=update&product_id=${productId}&quantity=${quantity}`
    }).then(() => location.reload());
}

function removeFromCart(productId) {
    if (confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
        fetch('<?= SITE_URL ?>/giohang.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=remove&product_id=${productId}`
        }).then(() => location.reload());
    }
}

function clearCart() {
    if (confirm('Bạn có chắc muốn xóa tất cả sản phẩm?')) {
        fetch('<?= SITE_URL ?>/giohang.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=clear'
        }).then(() => location.reload());
    }
}
</script>

<?php include 'includes/footer.php'; ?>