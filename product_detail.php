<?php
/**
 * product_detail.php - Trang chi tiết sản phẩm
 */

require_once __DIR__ . '/includes/config.php';
require_once 'includes/functions.php';

// Get product by slug or ID
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$product = $slug ? getProduct($slug) : getProduct($id);

if (!$product) {
    header('HTTP/1.0 404 Not Found');
    $pageTitle = 'Không tìm thấy sản phẩm';
    include 'includes/header.php';
    echo '<div class="container" style="text-align: center; padding: 5rem 1rem;">
            <h1>Sản phẩm không tồn tại</h1>
            <p>Sản phẩm bạn tìm kiếm không tồn tại hoặc đã bị xóa.</p>
            <a href="' . SITE_URL . '/products.php" class="btn btn-primary" style="margin-top: 1rem;">Quay lại cửa hàng</a>
          </div>';
    include 'includes/footer.php';
    exit;
}

// Get related products
$relatedProducts = getRelatedProducts($product['id'], $product['category_id'], 4);

// Calculate prices
$currentPrice = $product['sale_price'] ?? $product['price'];
$hasDiscount = !empty($product['sale_price']);
$discountPercent = $hasDiscount ? round((1 - $product['sale_price'] / $product['price']) * 100) : 0;

$pageTitle = $product['name'];
include 'includes/header.php';
?>

<main class="container" style="padding: 2rem 1rem;">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?= SITE_URL ?>">Trang chủ</a>
        <span class="material-symbols-outlined" style="font-size: 1rem;">chevron_right</span>
        <a href="<?= SITE_URL ?>/products.php?category=<?= $product['category_id'] ?>"><?= sanitize($product['category_name']) ?></a>
        <span class="material-symbols-outlined" style="font-size: 1rem;">chevron_right</span>
        <span class="current"><?= sanitize($product['name']) ?></span>
    </div>
    
    <!-- Product Detail -->
    <div style="display: grid; grid-template-columns: 1fr; gap: 3rem; margin-top: 2rem;">
        
        <!-- Product Images -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <div>
                <div style="position: relative; aspect-ratio: 1; border-radius: 1rem; overflow: hidden;">
                    <img src="<?= $product['image'] ?>" alt="<?= sanitize($product['name']) ?>" 
                         style="width: 100%; height: 100%; object-fit: cover;">
                    
                    <?php if ($hasDiscount): ?>
                        <span class="product-badge badge-sale" style="position: absolute; top: 1rem; left: 1rem;">
                            -<?= $discountPercent ?>%
                        </span>
                    <?php elseif ($product['is_new']): ?>
                        <span class="product-badge badge-new" style="position: absolute; top: 1rem; left: 1rem;">
                            Mới
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Product Info -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div>
                    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">
                        <?= sanitize($product['name']) ?>
                    </h1>
                    <p style="color: var(--muted-light);">
                        <?= sanitize($product['category_name']) ?>
                    </p>
                </div>
                
                <!-- Price -->
                <div style="font-size: 1.75rem; font-weight: 700;">
                    <span style="color: var(--primary-dark);"><?= formatPrice($currentPrice) ?></span>
                    <?php if ($hasDiscount): ?>
                        <span style="font-size: 1.25rem; color: var(--muted-light); text-decoration: line-through; margin-left: 0.5rem;">
                            <?= formatPrice($product['price']) ?>
                        </span>
                    <?php endif; ?>
                    <span style="font-size: 1rem; color: var(--muted-light); font-weight: 400;">
                        / <?= sanitize($product['unit']) ?>
                    </span>
                </div>
                
                <!-- Stock Status -->
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <?php if ($product['stock'] > 0): ?>
                        <span class="material-symbols-outlined" style="color: var(--success);">check_circle</span>
                        <span style="color: var(--success);">Còn hàng (<?= $product['stock'] ?> <?= $product['unit'] ?>)</span>
                    <?php else: ?>
                        <span class="material-symbols-outlined" style="color: var(--danger);">cancel</span>
                        <span style="color: var(--danger);">Hết hàng</span>
                    <?php endif; ?>
                </div>
                
                <!-- Quantity & Add to Cart -->
                <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; border: 1px solid var(--border-light); border-radius: 0.5rem;">
                        <button onclick="changeQty(-1)" style="padding: 0.75rem 1rem; background: none; border: none; cursor: pointer;">-</button>
                        <input type="number" id="quantity" value="1" min="1" max="<?= $product['stock'] ?>" 
                               style="width: 60px; text-align: center; border: none; font-size: 1rem;">
                        <button onclick="changeQty(1)" style="padding: 0.75rem 1rem; background: none; border: none; cursor: pointer;">+</button>
                    </div>
                    
                    <button onclick="addToCartWithQty(<?= $product['id'] ?>)" 
                            class="btn btn-primary" style="flex: 1; min-width: 200px;"
                            <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                        <span class="material-symbols-outlined" style="margin-right: 0.5rem;">add_shopping_cart</span>
                        Thêm vào giỏ hàng
                    </button>
                </div>
                
                <!-- Description -->
                <div style="border-top: 1px solid var(--border-light); padding-top: 1.5rem; margin-top: 1rem;">
                    <h3 style="font-size: 1.125rem; font-weight: 700; margin-bottom: 0.75rem;">Mô tả sản phẩm</h3>
                    <p style="color: var(--text-light); line-height: 1.8;">
                        <?= nl2br(sanitize($product['description'])) ?>
                    </p>
                </div>
                
                <!-- Badges -->
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    <?php if ($product['is_organic']): ?>
                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.5rem 1rem; background: rgba(34, 197, 94, 0.1); color: #166534; border-radius: 9999px; font-size: 0.875rem;">
                            <span class="material-symbols-outlined" style="font-size: 1rem;">eco</span>
                            Hữu cơ
                        </span>
                    <?php endif; ?>
                    
                    <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.5rem 1rem; background: rgba(59, 130, 246, 0.1); color: #1e40af; border-radius: 9999px; font-size: 0.875rem;">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">local_shipping</span>
                        Giao hàng nhanh
                    </span>
                    
                    <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.5rem 1rem; background: rgba(245, 158, 11, 0.1); color: #92400e; border-radius: 9999px; font-size: 0.875rem;">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">verified</span>
                        Chất lượng đảm bảo
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
    <section style="margin-top: 4rem;">
        <h2 class="section-title">Sản phẩm tương tự</h2>
        <div class="products-grid" style="margin-top: 1.5rem;">
            <?php foreach ($relatedProducts as $related): ?>
                <?= renderProductCard($related) ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</main>

<script>
function changeQty(delta) {
    const input = document.getElementById('quantity');
    let val = parseInt(input.value) + delta;
    if (val < 1) val = 1;
    if (val > <?= $product['stock'] ?>) val = <?= $product['stock'] ?>;
    input.value = val;
}

function addToCartWithQty(productId) {
    const qty = document.getElementById('quantity').value;
    addToCart(productId, qty);
}
</script>

<?php include 'includes/footer.php'; ?>