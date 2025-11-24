<?php
/**
 * wishlist.php - Trang danh sách yêu thích
 */

require_once __DIR__ . '/includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/wishlist_functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    redirect(SITE_URL . '/auth.php');
}

$userId = $_SESSION['user_id'];
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Get wishlist
$result = getUserWishlist($userId, $page);
$products = $result['products'];
$totalPages = $result['pages'];
$totalProducts = $result['total'];

$pageTitle = 'Sản phẩm yêu thích';
include 'includes/header.php';
?>

<main class="container" style="padding: 2rem 1rem;">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?= SITE_URL ?>">Trang chủ</a>
        <span class="material-symbols-outlined" style="font-size: 1rem;">chevron_right</span>
        <span class="current">Yêu thích</span>
    </div>
    
    <!-- Page Header -->
    <div style="margin-top: 2rem; margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">Sản phẩm yêu thích</h1>
        <p style="color: var(--muted-light);"><?= $totalProducts ?> sản phẩm</p>
    </div>
    
    <?php if (empty($products)): ?>
        <!-- Empty Wishlist -->
        <div style="text-align: center; padding: 4rem 2rem; background: var(--card-light); border-radius: 1rem;">
            <span class="material-symbols-outlined" style="font-size: 5rem; color: var(--muted-light);">favorite</span>
            <h2 style="margin-top: 1rem; font-size: 1.5rem;">Chưa có sản phẩm yêu thích</h2>
            <p style="color: var(--muted-light); margin-top: 0.5rem;">Thêm sản phẩm yêu thích để dễ dàng tìm lại sau này.</p>
            <a href="<?= SITE_URL ?>/products.php" class="btn btn-primary" style="margin-top: 1.5rem;">
                Khám phá sản phẩm
            </a>
        </div>
    <?php else: ?>
        <!-- Products Grid -->
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card" data-product-id="<?= $product['id'] ?>">
                    <div class="product-image">
                        <a href="<?= SITE_URL ?>/product_detail.php?slug=<?= $product['slug'] ?>">
                            <img src="<?= imageUrl($product['image']) ?>" alt="<?= sanitize($product['name']) ?>">
                        </a>
                        
                        <?php if (!empty($product['sale_price'])): ?>
                            <?php $discount = round((1 - $product['sale_price'] / $product['price']) * 100); ?>
                            <span class="product-badge badge-sale">-<?= $discount ?>%</span>
                        <?php elseif ($product['is_new']): ?>
                            <span class="product-badge badge-new">Mới</span>
                        <?php endif; ?>
                        
                        <!-- Remove from wishlist button -->
                        <button class="product-favorite" onclick="removeFromWishlist(<?= $product['id'] ?>)" 
                                style="background: rgba(239, 68, 68, 0.1);">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1; color: #ef4444;">favorite</span>
                        </button>
                    </div>
                    
                    <div class="product-info">
                        <h3 class="product-name">
                            <a href="<?= SITE_URL ?>/product_detail.php?slug=<?= $product['slug'] ?>">
                                <?= sanitize($product['name']) ?>
                            </a>
                        </h3>
                        <p class="product-unit">/<?= sanitize($product['unit']) ?></p>
                        
                        <div class="product-price">
                            <?php $currentPrice = $product['sale_price'] ?? $product['price']; ?>
                            <span class="price-current"><?= formatPrice($currentPrice) ?></span>
                            <?php if (!empty($product['sale_price'])): ?>
                                <span class="price-original"><?= formatPrice($product['price']) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <button class="btn-add-cart primary" onclick="addToCart(<?= $product['id'] ?>)">
                            Thêm vào giỏ hàng
                        </button>
                        
                        <p style="font-size: 0.75rem; color: var(--muted-light); margin-top: 0.5rem; text-align: center;">
                            Đã thêm <?= date('d/m/Y', strtotime($product['added_at'])) ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <?= renderPagination($page, $totalPages, $_GET) ?>
        <?php endif; ?>
        
        <!-- Quick Actions -->
        <div style="margin-top: 3rem; padding: 2rem; background: rgba(182, 230, 51, 0.1); border-radius: 1rem; text-align: center;">
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;">Bạn thích tất cả sản phẩm?</h3>
            <button onclick="addAllToCart()" class="btn btn-primary">
                Thêm tất cả vào giỏ hàng
            </button>
        </div>
    <?php endif; ?>
</main>

<script>
// Remove from wishlist
function removeFromWishlist(productId) {
    if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi danh sách yêu thích?')) {
        return;
    }
    
    fetch('<?= SITE_URL ?>/api/wishlist.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=toggle&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            location.reload();
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra', 'error');
    });
}

// Add all to cart
function addAllToCart() {
    const productCards = document.querySelectorAll('.product-card');
    let count = 0;
    
    productCards.forEach(card => {
        const productId = card.dataset.productId;
        addToCart(productId);
        count++;
    });
    
    if (count > 0) {
        showNotification(`Đã thêm ${count} sản phẩm vào giỏ hàng`, 'success');
        setTimeout(() => {
            window.location.href = '<?= SITE_URL ?>/cart.php';
        }, 1500);
    }
}
</script>

<?php include 'includes/footer.php'; ?>