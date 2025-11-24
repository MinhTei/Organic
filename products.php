<?php
/**
 * products.php - Trang danh sách sản phẩm với phân trang và bộ lọc
 */

require_once __DIR__ . '/includes/config.php';
require_once 'includes/functions.php';

// Get filter parameters
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$onSale = isset($_GET['on_sale']) ? 1 : 0;
$isNew = isset($_GET['is_new']) ? 1 : 0;
$isOrganic = isset($_GET['is_organic']) ? 1 : 0;
$minPrice = isset($_GET['min_price']) ? (int)$_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) ? (int)$_GET['max_price'] : null;

// Get categories for sidebar
$categories = getCategories();

// Get current category name
$currentCategoryName = 'Tất cả sản phẩm';
if ($categoryId) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $categoryId) {
            $currentCategoryName = $cat['name'];
            break;
        }
    }
}

// Get products with filters
$result = getProducts([
    'page' => $page,
    'category_id' => $categoryId,
    'search' => $search,
    'sort' => $sort,
    'on_sale' => $onSale,
    'is_new' => $isNew,
    'is_organic' => $isOrganic,
    'min_price' => $minPrice,
    'max_price' => $maxPrice
]);

$products = $result['products'];
$totalPages = $result['pages'];
$totalProducts = $result['total'];

// Page title
$pageTitle = $search ? "Tìm kiếm: $search" : $currentCategoryName;

// Include header
include 'includes/header.php';
?>

<main class="main-layout">
    <!-- Sidebar Filters -->
    <aside class="sidebar">
        <div class="filter-card">
            <h2 class="filter-title">Bộ lọc</h2>
            <p class="filter-subtitle">Tùy chỉnh lựa chọn của bạn</p>
            
            <form action="" method="GET" id="filterForm">
                <?php if ($search): ?>
                    <input type="hidden" name="search" value="<?= $search ?>">
                <?php endif; ?>
                
                <!-- Categories -->
                <div class="filter-section">
                    <h3 class="filter-section-title">Danh mục</h3>
                    <div class="category-list">
                        <a href="?<?= http_build_query(array_merge($_GET, ['category' => '', 'page' => 1])) ?>" 
                           class="category-item <?= !$categoryId ? 'active' : '' ?>">
                            <span class="material-symbols-outlined">apps</span>
                            <span class="category-name">Tất cả</span>
                        </a>
                        <?php foreach ($categories as $cat): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['category' => $cat['id'], 'page' => 1])) ?>" 
                           class="category-item <?= $categoryId == $cat['id'] ? 'active' : '' ?>">
                            <span class="material-symbols-outlined"><?= $cat['icon'] ?></span>
                            <span class="category-name"><?= sanitize($cat['name']) ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Price Range -->
                <div class="filter-section">
                    <h3 class="filter-section-title">Khoảng giá</h3>
                    <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                        <input type="number" name="min_price" placeholder="Từ" 
                               value="<?= $minPrice ?>" 
                               style="width: 50%; padding: 0.5rem; border: 1px solid var(--border-light); border-radius: 0.5rem;">
                        <input type="number" name="max_price" placeholder="Đến" 
                               value="<?= $maxPrice ?>"
                               style="width: 50%; padding: 0.5rem; border: 1px solid var(--border-light); border-radius: 0.5rem;">
                    </div>
                </div>
                
                <!-- Checkboxes -->
                <div class="filter-section">
                    <div class="checkbox-group">
                        <label class="checkbox-item">
                            <input type="checkbox" name="on_sale" value="1" <?= $onSale ? 'checked' : '' ?>>
                            <span>Đang giảm giá</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="is_new" value="1" <?= $isNew ? 'checked' : '' ?>>
                            <span>Hàng mới về</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="is_organic" value="1" <?= $isOrganic ? 'checked' : '' ?>>
                            <span>Chứng nhận hữu cơ</span>
                        </label>
                    </div>
                </div>
                
                <!-- Apply Button -->
                <div class="filter-section">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        Áp dụng bộ lọc
                    </button>
                </div>
            </form>
        </div>
    </aside>
    
    <!-- Products Content -->
    <div class="products-section">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>">Trang chủ</a>
            <span class="material-symbols-outlined" style="font-size: 1rem;">chevron_right</span>
            <?php if ($search): ?>
                <span class="current">Tìm kiếm: "<?= $search ?>"</span>
            <?php else: ?>
                <span class="current"><?= sanitize($currentCategoryName) ?></span>
            <?php endif; ?>
        </div>
        
        <!-- Section Header -->
        <div class="section-header">
            <div>
                <h1 class="section-title"><?= sanitize($currentCategoryName) ?></h1>
                <p style="color: var(--muted-light); font-size: 0.875rem;">
                    <?= $totalProducts ?> sản phẩm
                </p>
            </div>
            
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <label style="font-size: 0.875rem; color: var(--muted-light);">Sắp xếp:</label>
                <select class="sort-select" onchange="window.location.href=this.value">
                    <option value="?<?= http_build_query(array_merge($_GET, ['sort' => 'newest'])) ?>" <?= $sort == 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                    <option value="?<?= http_build_query(array_merge($_GET, ['sort' => 'price_asc'])) ?>" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Giá: Thấp đến cao</option>
                    <option value="?<?= http_build_query(array_merge($_GET, ['sort' => 'price_desc'])) ?>" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Giá: Cao đến thấp</option>
                    <option value="?<?= http_build_query(array_merge($_GET, ['sort' => 'name_asc'])) ?>" <?= $sort == 'name_asc' ? 'selected' : '' ?>>Tên A-Z</option>
                </select>
            </div>
        </div>
        
        <!-- Products Grid -->
        <?php if (empty($products)): ?>
            <div style="text-align: center; padding: 3rem;">
                <span class="material-symbols-outlined" style="font-size: 4rem; color: var(--muted-light);">inventory_2</span>
                <p style="margin-top: 1rem; color: var(--muted-light);">Không tìm thấy sản phẩm nào.</p>
                <a href="<?= SITE_URL ?>/products.php" class="btn btn-primary" style="margin-top: 1rem;">
                    Xem tất cả sản phẩm
                </a>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <?= renderProductCard($product) ?>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?= renderPagination($page, $totalPages, $_GET) ?>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>