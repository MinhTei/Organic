
<?php
// includes/functions.php - Các hàm dùng chung cho website

/**
 * Lấy tất cả danh mục sản phẩm
 * @return array
 */
function getCategories() {
    $conn = getConnection();
    // Lấy danh sách danh mục, sắp xếp theo id tăng dần
    $stmt = $conn->query("SELECT * FROM categories ORDER BY id ASC");
    return $stmt->fetchAll();
}

/**
 * Lấy danh sách sản phẩm với phân trang và các bộ lọc
 * @param array $options
 * @return array
 */
function getProducts($options = []) {
    $conn = getConnection();
    
    $page = isset($options['page']) ? (int)$options['page'] : 1;
    $limit = isset($options['limit']) ? (int)$options['limit'] : ITEMS_PER_PAGE;
    $offset = ($page - 1) * $limit;
    
    $where = ["1=1"];
    $params = [];
    
    // Lọc theo danh mục
    if (!empty($options['category_id'])) {
        $where[] = "p.category_id = :category_id";
        $params[':category_id'] = $options['category_id'];
    }
    // Lọc theo từ khóa tìm kiếm
    if (!empty($options['search'])) {
        $where[] = "(p.name LIKE :search1 OR p.description LIKE :search2)";
        $params[':search1'] = '%' . $options['search'] . '%';
        $params[':search2'] = '%' . $options['search'] . '%';
    }
    // Lọc sản phẩm đang giảm giá
    if (!empty($options['on_sale'])) {
        $where[] = "p.sale_price IS NOT NULL";
    }
    // Lọc sản phẩm mới
    if (!empty($options['is_new'])) {
        $where[] = "p.is_new = 1";
    }
    // Lọc sản phẩm hữu cơ
    if (!empty($options['is_organic'])) {
        $where[] = "p.is_organic = 1";
    }
    // Lọc theo khoảng giá
    if (!empty($options['min_price'])) {
        $where[] = "COALESCE(p.sale_price, p.price) >= :min_price";
        $params[':min_price'] = $options['min_price'];
    }
    if (!empty($options['max_price'])) {
        $where[] = "COALESCE(p.sale_price, p.price) <= :max_price";
        $params[':max_price'] = $options['max_price'];
    }
    
    $whereClause = implode(' AND ', $where);
    
    // Sắp xếp
    $orderBy = "p.created_at DESC";
    if (!empty($options['sort'])) {
        switch ($options['sort']) {
            case 'price_asc':
                $orderBy = "COALESCE(p.sale_price, p.price) ASC";
                break;
            case 'price_desc':
                $orderBy = "COALESCE(p.sale_price, p.price) DESC";
                break;
            case 'name_asc':
                $orderBy = "p.name ASC";
                break;
            case 'newest':
                $orderBy = "p.created_at DESC";
                break;
        }
    }
    
    // Đếm tổng số sản phẩm
    $countSql = "SELECT COUNT(*) FROM products p WHERE $whereClause";
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();
    
    // Lấy danh sách sản phẩm
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE $whereClause 
            ORDER BY $orderBy 
            LIMIT $limit OFFSET $offset";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    return [
        'products' => $products,
        'total' => $total,
        'pages' => ceil($total / $limit),
        'current_page' => $page,
        'per_page' => $limit
    ];
}

/**
 * Lấy thông tin sản phẩm theo ID hoặc slug
 * @param int|string $idOrSlug
 * @return array|false
 */
function getProduct($idOrSlug) {
    $conn = getConnection();
    $field = is_numeric($idOrSlug) ? 'id' : 'slug';
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.$field = :value";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':value' => $idOrSlug]);
    return $stmt->fetch();
}

/**
 * Lấy sản phẩm nổi bật
 * @param int $limit
 * @return array
 */
function getFeaturedProducts($limit = 4) {
    $conn = getConnection();
    $sql = "SELECT * FROM products WHERE is_featured = 1 ORDER BY created_at DESC LIMIT :limit";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Lấy các bài viết mới nhất (tin tức) từ admin
 * @param int $limit
 * @return array
 */
function getLatestPosts($limit = 4) {
    $conn = getConnection();
    $sql = "SELECT id, title, slug, excerpt, featured_image, published_at FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC LIMIT :limit";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Chuẩn hóa đường dẫn ảnh sản phẩm
 * - Nếu là URL đầy đủ thì trả về luôn
 * - Nếu là đường dẫn tuyệt đối/relative thì thêm SITE_URL
 * - Nếu rỗng thì trả về ảnh mặc định
 * @param string $path
 * @return string
 */
function imageUrl($path) {
    if (empty($path)) {
        return rtrim(SITE_URL, '/') . '/images/placeholder.png';
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    if (strpos($path, '/') === 0) {
        return rtrim(SITE_URL, '/') . $path;
    }
    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Lấy sản phẩm liên quan cùng danh mục
 * @param int $productId
 * @param int $categoryId
 * @param int $limit
 * @return array
 */
function getRelatedProducts($productId, $categoryId, $limit = 4) {
    $conn = getConnection();
    $sql = "SELECT * FROM products 
            WHERE category_id = :category_id AND id != :product_id 
            ORDER BY RAND() LIMIT :limit";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
    $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Tạo URL cho phân trang
 * @param int $page
 * @param array $params
 * @return string
 */
function buildPaginationUrl($page, $params = []) {
    $params['page'] = $page;
    return '?' . http_build_query($params);
}

/**
 * Hiển thị HTML phân trang
 * @param int $currentPage
 * @param int $totalPages
 * @param array $params
 * @return string
 */
function renderPagination($currentPage, $totalPages, $params = []) {
    if ($totalPages <= 1) return '';
    unset($params['page']);
    $html = '<nav class="pagination">';
    // Nút lùi trang
    if ($currentPage > 1) {
        $html .= '<a href="' . buildPaginationUrl($currentPage - 1, $params) . '"><span class="material-symbols-outlined">chevron_left</span></a>';
    } else {
        $html .= '<a class="disabled"><span class="material-symbols-outlined">chevron_left</span></a>';
    }
    // Hiển thị số trang
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $currentPage) {
            $html .= '<span class="active">' . $i . '</span>';
        } else {
            $html .= '<a href="' . buildPaginationUrl($i, $params) . '">' . $i . '</a>';
        }
    }
    // Nút tiến trang
    if ($currentPage < $totalPages) {
        $html .= '<a href="' . buildPaginationUrl($currentPage + 1, $params) . '"><span class="material-symbols-outlined">chevron_right</span></a>';
    } else {
        $html .= '<a class="disabled"><span class="material-symbols-outlined">chevron_right</span></a>';
    }
    $html .= '</nav>';
    return $html;
}

/**
 * Hiển thị thẻ sản phẩm (product card)
 * @param array $product
 * @return string
 */
function renderProductCard($product) {
    $price = $product['sale_price'] ?? $product['price'];
    $hasDiscount = !empty($product['sale_price']);
    $discountPercent = $hasDiscount ? round((1 - $product['sale_price'] / $product['price']) * 100) : 0;
    ob_start();
    ?>
    <div class="product-card">
        <div class="product-image">
            <a href="<?= SITE_URL ?>/product_detail.php?slug=<?= $product['slug'] ?>">
                <img src="<?= imageUrl($product['image']) ?>" alt="<?= sanitize($product['name']) ?>">
            </a>
            <?php if ($hasDiscount): ?>
                <span class="product-badge badge-sale">-<?= $discountPercent ?>%</span>
            <?php elseif ($product['is_new']): ?>
                <span class="product-badge badge-new">Mới</span>
            <?php endif; ?>
            <button class="product-favorite" onclick="toggleFavorite(<?= $product['id'] ?>)">
                <span class="material-symbols-outlined">favorite</span>
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
                <span class="price-current"><?= formatPrice($price) ?></span>
                <?php if ($hasDiscount): ?>
                    <span class="price-original"><?= formatPrice($product['price']) ?></span>
                <?php endif; ?>
            </div>
            <button class="btn-add-cart primary" onclick="addToCart(<?= $product['id'] ?>)">
                Thêm vào giỏ hàng
            </button>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>