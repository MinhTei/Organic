<?php
// includes/functions.php - Các hàm dùng chung cho website

/**
 * Lấy tất cả danh mục sản phẩm
 * @return array
 */
function getCategories()
{
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
function getProducts($options = [])
{
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
        $search = trim($options['search']);
        // Tách từ khóa thành từng từ và tìm kiếm theo từ hoàn chỉnh
        $keywords = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);

        if (!empty($keywords)) {
            $searchConditions = [];
            $keywordIndex = 0;

            // Với mỗi từ khóa, tạo điều kiện tìm kiếm trong tên sản phẩm
            // Sử dụng REGEXP để tìm từ hoàn chỉnh (không phải ký tự đơn lẻ)
            foreach ($keywords as $keyword) {
                $paramKey = ':search' . $keywordIndex;
                // Tìm từ khóa được bao quanh bởi ranh giới từ hoặc khoảng trắng
                $searchConditions[] = "p.name REGEXP :regexp$keywordIndex";
                $params[':regexp' . $keywordIndex] = '(^|[[:space:]]+)' . preg_quote($keyword, '/') . '([[:space:]]+|$)';
                $keywordIndex++;
            }

            // Sử dụng AND để tìm sản phẩm chứa tất cả các từ khóa
            $where[] = "(" . implode(' AND ', $searchConditions) . ")";
        }
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
function getProduct($idOrSlug)
{
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
function getFeaturedProducts($limit = 4)
{
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
function getLatestPosts($limit = 4)
{
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
function imageUrl($path)
{
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
function getRelatedProducts($productId, $categoryId, $limit = 4)
{
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
function buildPaginationUrl($page, $params = [])
{
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
function renderPagination($currentPage, $totalPages, $params = [])
{
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
function renderProductCard($product)
{
    $price = $product['sale_price'] ?? $product['price'];
    $hasDiscount = !empty($product['sale_price']);
    $discountPercent = $hasDiscount ? round((1 - $product['sale_price'] / $product['price']) * 100) : 0;

    // Kiểm tra sản phẩm có trong wishlist không
    $isInWishlist = false;
    $wishlistClass = '';
    $heartFill = '';
    $heartColor = '';
    if (isset($_SESSION['user_id'])) {
        require_once __DIR__ . '/wishlist_functions.php';
        $isInWishlist = isInWishlist($_SESSION['user_id'], $product['id']);
        if ($isInWishlist) {
            $wishlistClass = 'in-wishlist';
            $heartFill = "style=\"font-variation-settings: 'FILL' 1; color: #ef4444;\"";
        }
    }

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
            <button class="product-favorite <?= $wishlistClass ?>" onclick="toggleFavorite(<?= $product['id'] ?>)" data-product-id="<?= $product['id'] ?>" title="<?= $isInWishlist ? 'Xóa khỏi yêu thích' : 'Thêm vào yêu thích' ?>">
                <span class="material-symbols-outlined" <?= $heartFill ?>>favorite</span>
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

/**
 * Lấy thông tin trạng thái đơn hàng (nhãn, màu, CSS class)
 * 
 * @param string $status Trạng thái đơn hàng (pending, confirmed, processing, shipping, delivered, cancelled, refunded)
 * @return array Mảng chứa label, color, css_class
 */
function getOrderStatusInfo($status)
{
    $statuses = [
        'pending' => [
            'label' => 'Chờ xác nhận',
            'color' => '#f59e0b',
            'css_class' => 'bg-yellow-100 text-yellow-800'
        ],
        'confirmed' => [
            'label' => 'Đã xác nhận',
            'color' => '#3b82f6',
            'css_class' => 'bg-blue-100 text-blue-800'
        ],
        'processing' => [
            'label' => 'Đang xử lý',
            'color' => '#06b6d4',
            'css_class' => 'bg-cyan-100 text-cyan-800'
        ],
        'shipping' => [
            'label' => 'Đang giao',
            'color' => '#06b6d4',
            'css_class' => 'bg-cyan-100 text-cyan-800'
        ],
        'delivered' => [
            'label' => 'Đã giao',
            'color' => '#22c55e',
            'css_class' => 'bg-green-100 text-green-800'
        ],
        'cancelled' => [
            'label' => 'Đã hủy',
            'color' => '#ef4444',
            'css_class' => 'bg-red-100 text-red-800'
        ],
        'refunded' => [
            'label' => 'Đã hoàn tiền',
            'color' => '#8b5cf6',
            'css_class' => 'bg-purple-100 text-purple-800'
        ]
    ];

    return $statuses[$status] ?? $statuses['pending'];
}

/**
 * Lấy thông tin phương thức thanh toán
 * 
 * @param string $method Phương thức (cod, bank_transfer)
 * @return string Nhãn phương thức thanh toán
 */
function getPaymentMethodLabel($method)
{
    $methods = [
        'cod' => 'Thanh toán khi nhận',
        'bank_transfer' => 'Chuyển khoản'
    ];

    return $methods[$method] ?? 'Không xác định';
}
?>