
<?php
/**
 * products.php - Trang danh sách sản phẩm với phân trang và bộ lọc
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// =========================
// Lấy tham số bộ lọc
// =========================
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search     = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$sort       = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';
$page       = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$onSale     = isset($_GET['on_sale']) ? 1 : 0;
$isNew      = isset($_GET['is_new']) ? 1 : 0;
$isOrganic  = isset($_GET['is_organic']) ? 1 : 0;
$minPrice   = isset($_GET['min_price']) ? (int)$_GET['min_price'] : null;
$maxPrice   = isset($_GET['max_price']) ? (int)$_GET['max_price'] : null;

// =========================
// Lấy danh mục cho sidebar
// =========================
$categories = getCategories();

// =========================
// Xác định tên danh mục hiện tại
// =========================
$currentCategoryName = 'Tất cả sản phẩm';
if ($categoryId) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $categoryId) {
            // sanitize and decode HTML entities to avoid showing &amp; for names like "Trứng & Bơ sữa"
            $currentCategoryName = htmlspecialchars_decode(sanitize($cat['name']));
            break;
        }
    }
}

// =========================
// Lấy sản phẩm theo bộ lọc
// =========================
$result = getProducts([
    'page'        => $page,
    'category_id' => $categoryId,
    'search'      => $search,
    'sort'        => $sort,
    'on_sale'     => $onSale,
    'is_new'      => $isNew,
    'is_organic'  => $isOrganic,
    'min_price'   => $minPrice,
    'max_price'   => $maxPrice
]);

$products      = $result['products'];
$totalPages    = $result['pages'];
$totalProducts = $result['total'];

// =========================
// Tiêu đề trang
// =========================
$pageTitle = $search ? "Tìm kiếm: $search" : $currentCategoryName;

// =========================
// Include header
// =========================
include __DIR__ . '/includes/header.php';
?>

<main class="grid grid-cols-1 md:grid-cols-4 gap-4 sm:gap-6 lg:gap-8 px-4 sm:px-6 lg:px-8 py-6 sm:py-8 max-w-7xl mx-auto" style="width: 100%; max-width: 100%; box-sizing: border-box; overflow-x: hidden;">
    <!-- Sidebar Filters - Hidden on mobile, visible from md breakpoint -->
    <aside class="hidden md:block md:col-span-1">
        <div class="sticky top-20 bg-white rounded-xl border border-gray-200 p-4 sm:p-6">
            <h2 class="text-base sm:text-lg font-bold text-gray-900 mb-1">Bộ lọc</h2>
            <p class="text-xs sm:text-sm text-gray-600 mb-4">Tùy chỉnh lựa chọn</p>

            <form action="" method="GET" id="filterForm" class="space-y-4">
                <?php if ($search): ?>
                    <input type="hidden" name="search" value="<?= $search ?>">
                <?php endif; ?>

                <!-- Categories -->
                <div class="border-b pb-4">
                    <h3 class="font-bold text-sm mb-3 text-gray-900">Danh mục</h3>
                    <div class="space-y-2">
                        <a href="?<?= http_build_query(array_merge($_GET, ['category' => '', 'page' => 1])) ?>"
                           class="block px-3 py-2 rounded text-sm <?= !$categoryId ? 'bg-primary/20 text-primary font-semibold' : 'text-gray-700 hover:bg-gray-100' ?> transition">
                            Tất cả
                        </a>
                        <?php foreach ($categories as $cat): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['category' => $cat['id'], 'page' => 1])) ?>"
                           class="flex items-center gap-2 px-3 py-2 rounded text-sm <?= $categoryId == $cat['id'] ? 'bg-primary/20 text-primary font-semibold' : 'text-gray-700 hover:bg-gray-100' ?> transition">
                            <?php if (!empty($cat['icon'])): ?>
                                <img src="<?= imageUrl($cat['icon']) ?>" alt="<?= sanitize($cat['name']) ?>" class="w-5 h-5 rounded object-cover">
                            <?php endif; ?>
                            <span class="truncate"><?= str_replace('&amp;', '&', sanitize($cat['name'])) ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Price Range -->
                <div class="border-b pb-4">
                    <h3 class="font-bold text-sm mb-3 text-gray-900">Khoảng giá</h3>
                    <div class="flex gap-1.5 items-center">
                        <input type="number" name="min_price" placeholder="Từ"
                               value="<?= $minPrice ?>"
                               class="flex-1 min-w-0 px-2 py-1 text-xs border border-gray-300 rounded outline-none focus:ring-2 focus:ring-primary/50">
                        <span class="text-gray-500 text-xs">-</span>
                        <input type="number" name="max_price" placeholder="Đến"
                               value="<?= $maxPrice ?>"
                               class="flex-1 min-w-0 px-2 py-1 text-xs border border-gray-300 rounded outline-none focus:ring-2 focus:ring-primary/50">
                    </div>
                </div>

                <!-- Checkboxes -->
                <div class="space-y-2 pb-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="on_sale" value="1" <?= $onSale ? 'checked' : '' ?> class="w-4 h-4">
                        <span class="text-sm text-gray-700">Đang giảm giá</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_new" value="1" <?= $isNew ? 'checked' : '' ?> class="w-4 h-4">
                        <span class="text-sm text-gray-700">Hàng mới về</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_organic" value="1" <?= $isOrganic ? 'checked' : '' ?> class="w-4 h-4">
                        <span class="text-sm text-gray-700">Chứng nhận hữu cơ</span>
                    </label>
                </div>

                <!-- Apply Button -->
                <button type="submit" class="w-full px-4 py-2 bg-primary text-black font-bold rounded-lg hover:bg-primary-dark transition text-sm">
                    Áp dụng
                </button>
            </form>
        </div>
    </aside>

    <!-- Products Content -->
    <div class="md:col-span-3">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-600 mb-4 sm:mb-6">
            <a href="<?= SITE_URL ?>" class="hover:text-primary transition">Trang chủ</a>
            <span class="material-symbols-outlined text-sm">chevron_right</span>
            <?php if ($search): ?>
                <span class="text-primary font-semibold">Tìm kiếm: "<?= htmlspecialchars($search) ?>"</span>
            <?php else: ?>
                <span class="text-primary font-semibold"><?= htmlspecialchars($currentCategoryName) ?></span>
            <?php endif; ?>
        </div>

        <!-- Section Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6 sm:mb-8">
            <div class="flex-1">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900"><?= htmlspecialchars($currentCategoryName) ?></h1>
                <p class="text-xs sm:text-sm text-gray-600 mt-1">
                    <?= number_format($totalProducts) ?> sản phẩm
                </p>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                <!-- Mobile Filter Button -->
                <button type="button" onclick="document.getElementById('mobileFilter').classList.toggle('hidden'); document.getElementById('filterToggleIcon').textContent = document.getElementById('mobileFilter').classList.contains('hidden') ? 'expand_more' : 'expand_less';" class="md:hidden px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg flex items-center gap-2 hover:bg-gray-100 transition whitespace-nowrap" style="display: none;">
                    <style>
                        @media (max-width: 767px) {
                            button[style*="display: none"] { display: flex !important; }
                        }
                    </style>
                    <span class="material-symbols-outlined text-lg">tune</span>
                    <span class="text-xs font-semibold text-gray-900">Lọc</span>
                    <span class="material-symbols-outlined text-lg" id="filterToggleIcon">expand_more</span>
                </button>

                <label class="text-xs sm:text-sm text-gray-700 font-medium whitespace-nowrap">Sắp xếp:</label>
                <select class="px-3 py-2 text-xs sm:text-sm border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-primary/50" onchange="window.location.href=this.value">
                    <option value="?<?= http_build_query(array_merge($_GET, ['sort' => 'newest'])) ?>" <?= $sort == 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                    <option value="?<?= http_build_query(array_merge($_GET, ['sort' => 'price_asc'])) ?>" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Giá: Thấp → Cao</option>
                    <option value="?<?= http_build_query(array_merge($_GET, ['sort' => 'price_desc'])) ?>" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Giá: Cao → Thấp</option>
                    <option value="?<?= http_build_query(array_merge($_GET, ['sort' => 'name_asc'])) ?>" <?= $sort == 'name_asc' ? 'selected' : '' ?>>Tên A-Z</option>
                </select>
            </div>
        </div>

        <!-- Mobile Filter - Collapsible -->
        <div id="mobileFilter" class="hidden mb-6 md:hidden bg-white border border-gray-200 rounded-lg p-4">
            <form action="" method="GET" class="space-y-4">
                <?php if ($search): ?>
                    <input type="hidden" name="search" value="<?= $search ?>">
                <?php endif; ?>

                <!-- Categories -->
                <div class="border-b pb-4">
                    <h3 class="font-bold text-sm mb-3 text-gray-900">Danh mục</h3>
                    <div class="space-y-2">
                        <a href="?<?= http_build_query(array_merge($_GET, ['category' => '', 'page' => 1])) ?>"
                           class="block px-3 py-2 rounded text-xs sm:text-sm <?= !$categoryId ? 'bg-primary/20 text-primary font-semibold' : 'text-gray-700 hover:bg-gray-100' ?> transition">
                            Tất cả
                        </a>
                        <?php foreach ($categories as $cat): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['category' => $cat['id'], 'page' => 1])) ?>"
                           class="flex items-center gap-2 px-3 py-2 rounded text-xs sm:text-sm <?= $categoryId == $cat['id'] ? 'bg-primary/20 text-primary font-semibold' : 'text-gray-700 hover:bg-gray-100' ?> transition">
                            <?php if (!empty($cat['icon'])): ?>
                                <img src="<?= imageUrl($cat['icon']) ?>" alt="<?= sanitize($cat['name']) ?>" class="w-4 h-4 rounded object-cover flex-shrink-0">
                            <?php endif; ?>
                            <span class="truncate"><?= str_replace('&amp;', '&', sanitize($cat['name'])) ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Price Range -->
                <div class="border-b pb-4">
                    <h3 class="font-bold text-sm mb-3 text-gray-900">Khoảng giá</h3>
                    <div class="flex gap-1.5 items-center">
                        <input type="number" name="min_price" placeholder="Từ"
                               value="<?= $minPrice ?>"
                               class="flex-1 min-w-0 px-2 py-1 text-xs border border-gray-300 rounded outline-none focus:ring-2 focus:ring-primary/50">
                        <span class="text-gray-500 text-xs">-</span>
                        <input type="number" name="max_price" placeholder="Đến"
                               value="<?= $maxPrice ?>"
                               class="flex-1 min-w-0 px-2 py-1 text-xs border border-gray-300 rounded outline-none focus:ring-2 focus:ring-primary/50">
                    </div>
                </div>

                <!-- Checkboxes -->
                <div class="space-y-2 pb-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="on_sale" value="1" <?= $onSale ? 'checked' : '' ?> class="w-4 h-4">
                        <span class="text-xs sm:text-sm text-gray-700">Đang giảm giá</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_new" value="1" <?= $isNew ? 'checked' : '' ?> class="w-4 h-4">
                        <span class="text-xs sm:text-sm text-gray-700">Hàng mới về</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_organic" value="1" <?= $isOrganic ? 'checked' : '' ?> class="w-4 h-4">
                        <span class="text-xs sm:text-sm text-gray-700">Chứng nhận hữu cơ</span>
                    </label>
                </div>

                <!-- Apply Button -->
                <button type="submit" class="w-full px-4 py-2 bg-primary text-black font-bold rounded-lg hover:bg-primary-dark transition text-xs sm:text-sm">
                    Áp dụng
                </button>
            </form>
        </div>

        <!-- Products Grid -->
        <?php if (empty($products)): ?>
            <div class="text-center py-12">
                <span class="material-symbols-outlined text-5xl sm:text-6xl text-gray-300 block mb-4">inventory_2</span>
                <p class="text-gray-600 mb-4">Không tìm thấy sản phẩm nào.</p>
                <a href="<?= SITE_URL ?>/products.php" class="inline-block px-6 py-2 bg-primary text-black font-bold rounded-lg hover:bg-primary-dark transition">
                    Xem tất cả sản phẩm
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-6">
                <?php foreach ($products as $product): ?>
                    <?= renderProductCard($product) ?>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?= renderPagination($page, $totalPages, $_GET) ?>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>