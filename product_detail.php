<?php

/**
 * product_detail.php - Trang chi tiết sản phẩm
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/wishlist_functions.php';

// Lấy sản phẩm trước khi xử lý đánh giá
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = $slug ? getProduct($slug) : getProduct($id);

// Đánh giá sản phẩm
$reviewSuccess = '';
$reviewError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        $reviewError = 'Bạn cần đăng nhập để đánh giá.';
    } else {
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = sanitize($_POST['comment'] ?? '');
        if ($rating < 1 || $rating > 5) {
            $reviewError = 'Vui lòng chọn số sao từ 1 đến 5.';
        } elseif (empty($comment)) {
            $reviewError = 'Vui lòng nhập nội dung đánh giá.';
        } else {
            try {
                $conn = getConnection();
                $stmt = $conn->prepare("INSERT INTO product_reviews (product_id, user_id, rating, comment, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
                $stmt->execute([$product['id'], $_SESSION['user_id'], $rating, $comment]);
                $reviewSuccess = 'Đánh giá của bạn đã được gửi và chờ duyệt!';
            } catch (PDOException $e) {
                $reviewError = 'Lỗi gửi đánh giá: ' . $e->getMessage();
            }
        }
    }
}

if (!$product) {
    header('HTTP/1.0 404 Not Found');
    $pageTitle = 'Không tìm thấy sản phẩm';
    include __DIR__ . '/includes/header.php';
    echo '<div class="container" style="text-align: center; padding: 5rem 1rem;">
            <h1>Sản phẩm không tồn tại</h1>
            <p>Sản phẩm bạn tìm kiếm không tồn tại hoặc đã bị xóa.</p>
            <a href="' . SITE_URL . '/products.php" class="btn btn-primary" style="margin-top: 1rem;">Quay lại cửa hàng</a>
          </div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Get related products
$relatedProducts = getRelatedProducts($product['id'], $product['category_id'], 4);
$approvedReviews = [];
try {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT r.*, u.name as user_name FROM product_reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC");
    $stmt->execute([$product['id']]);
    $approvedReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Có thể log lỗi nếu cần
}

// Calculate prices
$currentPrice = $product['sale_price'] ?? $product['price'];
$hasDiscount = !empty($product['sale_price']);
$discountPercent = $hasDiscount ? round((1 - $product['sale_price'] / $product['price']) * 100) : 0;

$pageTitle = $product['name'];
include __DIR__ . '/includes/header.php';
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
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div style="display: flex; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; border: 1px solid var(--border-light); border-radius: 0.5rem;">
                            <button onclick="changeQty(-1)" style="padding: 0.75rem 1rem; background: none; border: none; cursor: pointer; font-size: 1.2rem; font-weight: 600;">−</button>
                            <input type="number" id="quantity" value="1" min="1" max="<?= $product['stock'] ?>"
                                style="width: 60px; text-align: center; border: none; font-size: 1rem;"
                                onkeypress="handleEnterKey(event, <?= $product['id'] ?>)">
                            <button onclick="changeQty(1)" style="padding: 0.75rem 1rem; background: none; border: none; cursor: pointer; font-size: 1.2rem; font-weight: 600;">+</button>
                        </div>

                        <button onclick="addToCartWithQty(<?= $product['id'] ?>)"
                            class="btn btn-primary" style="flex: 1; min-width: 200px;"
                            <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                            <span class="material-symbols-outlined" style="margin-right: 0.5rem;">add_shopping_cart</span>
                            Thêm vào giỏ hàng
                        </button>

                        <!-- Wishlist Button -->
                        <?php
                        // Kiểm tra sản phẩm có trong wishlist không
                        $isInWishlist = false;
                        $wishlistBtnClass = '';
                        $wishlistBtnTitle = 'Thêm vào yêu thích';
                        $heartStyle = '';

                        if (isset($_SESSION['user_id'])) {
                            $isInWishlist = isInWishlist($_SESSION['user_id'], $product['id']);
                            if ($isInWishlist) {
                                $wishlistBtnClass = 'in-wishlist';
                                $wishlistBtnTitle = 'Xóa khỏi yêu thích';
                                $heartStyle = "style=\"font-variation-settings: 'FILL' 1; color: #ef4444;\"";
                            }
                        }
                        ?>
                        <button class="product-favorite-btn <?= $wishlistBtnClass ?>" onclick="toggleFavorite(<?= $product['id'] ?>, event)" data-product-id="<?= $product['id'] ?>" title="<?= $wishlistBtnTitle ?>"
                            style="padding: 0.75rem 1.25rem; border: 2px solid var(--border-light); border-radius: 0.5rem; background: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                            <span class="material-symbols-outlined" <?= $heartStyle ?>>favorite</span>
                        </button>
                    </div>

                    <!-- Quantity Error Message -->
                    <div id="quantity-error" style="color: var(--danger); font-size: 0.875rem; display: none; padding: 0.5rem; background: #fee2e2; border-radius: 0.5rem; border-left: 3px solid var(--danger);"></div>
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

    <!-- Đánh giá sản phẩm -->
    <section style="margin-top: 4rem; display: flex; gap: 2rem; align-items: flex-start;">
        <div style="flex: 1; min-width: 320px;">
            <h2 class="section-title">Đánh giá sản phẩm</h2>
            <?php if ($reviewSuccess): ?>
                <div style="margin-bottom:1rem; padding:1rem; background:#e7fbe7; color:#166534; border-radius:0.5rem;">
                    <?= $reviewSuccess ?>
                </div>
            <?php endif; ?>
            <?php if ($reviewError): ?>
                <div style="margin-bottom:1rem; padding:1rem; background:#fee2e2; color:#b91c1c; border-radius:0.5rem;">
                    <?= $reviewError ?>
                </div>
            <?php endif; ?>
            <form method="POST" style="margin-bottom:2rem; background:#f7f8f6; border-radius:0.75rem; padding:2rem; max-width:500px;">
                <div style="margin-bottom:1rem;">
                    <label for="rating" style="font-weight:600;">Đánh giá của bạn:</label><br>
                    <div id="star-rating" style="display:flex; gap:0.5rem; margin-top:0.5rem; cursor:pointer;">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star" data-value="<?= $i ?>" style="font-size:2rem; color:#d1d5db;">★</span>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="review_rating" value="5" required>
                </div>
                <div style="margin-bottom:1rem;">
                    <label for="comment" style="font-weight:600;">Nội dung đánh giá:</label><br>
                    <textarea name="comment" rows="4" style="width:100%; padding:0.75rem; border-radius:0.5rem; border:1px solid #e5e7eb;" required></textarea>
                </div>
                <button type="submit" name="submit_review" class="btn btn-primary">Gửi đánh giá</button>
            </form>
            <script>
                // Xử lý chọn sao
                document.addEventListener('DOMContentLoaded', function() {
                    const stars = document.querySelectorAll('#star-rating .star');
                    const ratingInput = document.getElementById('review_rating');
                    let currentRating = 5;

                    function setStars(rating) {
                        stars.forEach((star, idx) => {
                            if (idx < rating) {
                                star.style.color = '#fbbf24';
                            } else {
                                star.style.color = '#d1d5db';
                            }
                        });
                    }
                    setStars(currentRating);
                    stars.forEach(star => {
                        star.addEventListener('click', function() {
                            const rating = parseInt(this.getAttribute('data-value'));
                            currentRating = rating;
                            ratingInput.value = rating;
                            setStars(rating);
                        });
                        star.addEventListener('mouseover', function() {
                            setStars(parseInt(this.getAttribute('data-value')));
                        });
                        star.addEventListener('mouseout', function() {
                            setStars(currentRating);
                        });
                    });
                });
            </script>
        </div>
        <div style="flex: 1; min-width: 320px;">
            <h3 style="font-size:1.25rem; font-weight:600; margin-bottom:1rem;">Đánh giá đã duyệt</h3>
            <?php if (empty($approvedReviews)): ?>
                <div style="color:#6b7280;">Chưa có đánh giá nào cho sản phẩm này.</div>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:1.5rem;">
                    <?php foreach ($approvedReviews as $review): ?>
                        <div style="background:#fff; border-radius:0.75rem; box-shadow:0 1px 4px rgba(0,0,0,0.04); padding:1rem;">
                            <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem;">
                                <span style="font-weight:600; color:#2563eb;">
                                    <?= htmlspecialchars($review['user_name']) ?>
                                </span>
                                <span style="color:#fbbf24; font-size:1.2rem;">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?= $i <= $review['rating'] ? '★' : '☆' ?>
                                    <?php endfor; ?>
                                </span>
                                <span style="color:#6b7280; font-size:0.9rem; margin-left:auto;">
                                    <?= date('d/m/Y', strtotime($review['created_at'])) ?>
                                </span>
                            </div>
                            <div style="color:#374151; font-size:1rem;">"<?= htmlspecialchars($review['comment']) ?>"</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

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
    const maxStock = <?= $product['stock'] ?>;

    function changeQty(delta) {
        const input = document.getElementById('quantity');
        let val = parseInt(input.value) || 0;

        // Nếu để trống, mặc định là 1
        if (isNaN(val) || val < 0) {
            val = 1;
        }

        val += delta;

        if (val < 1) {
            val = 1;
        }
        if (val > maxStock) {
            val = maxStock;
        }

        input.value = val;
        hideError();
    }

    function validateQuantity() {
        const input = document.getElementById('quantity');
        const val = input.value.trim();

        // Kiểm tra nếu để trống
        if (val === '' || val === '0') {
            showError('Vui lòng chọn ít nhất 1 sản phẩm.');
            return false;
        }

        const qty = parseInt(val);

        // Kiểm tra nếu không phải số hoặc nhỏ hơn 1
        if (isNaN(qty) || qty < 1) {
            showError('Số lượng phải lớn hơn 0.');
            return false;
        }

        // Kiểm tra nếu vượt quá stock
        if (qty > maxStock) {
            showError(`Chỉ còn ${maxStock} sản phẩm trong kho. Vui lòng chọn số lượng nhỏ hơn.`);
            return false;
        }

        hideError();
        return true;
    }

    function showError(message) {
        const errorEl = document.getElementById('quantity-error');
        errorEl.textContent = message;
        errorEl.style.display = 'block';
    }

    function hideError() {
        const errorEl = document.getElementById('quantity-error');
        errorEl.style.display = 'none';
    }

    function handleEnterKey(event, productId) {
        if (event.key === 'Enter' || event.keyCode === 13) {
            event.preventDefault();
            addToCartWithQty(productId);
        }
    }

    function addToCartWithQty(productId) {
        const input = document.getElementById('quantity');

        // Validate khi nhấn nút hoặc Enter
        if (!validateQuantity()) {
            return;
        }

        const qty = parseInt(input.value);

        // Nếu validate thành công, thêm vào giỏ hàng
        addToCart(productId, qty);
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>