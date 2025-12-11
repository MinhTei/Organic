<?php

/**
 * index.php - Admin Dashboard
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/settings_helper.php';


// Xử lý tìm kiếm sản phẩm trên trang chủ
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
if ($search) {
    // Tìm kiếm sản phẩm theo tên hoặc mô tả
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE :kw1 OR description LIKE :kw2 ORDER BY id DESC LIMIT 12");
    $stmt->execute([':kw1' => "%$search%", ':kw2' => "%$search%"]);
    $searchProducts = $stmt->fetchAll();
} else {
    // Get featured products
    $featuredProducts = getFeaturedProducts(8);
}

// Get categories
$categories = getCategories();
// Get new products (Nông sản tươi mới)
$newProducts = getProducts(['is_new' => 1, 'limit' => 8])['products'];

// Admin-selected featured products (hiển thị riêng)
$adminFeatured = getFeaturedProducts(4);

// Latest news posts (admin)
$latestPosts = function_exists('getLatestPosts') ? getLatestPosts(4) : [];

$pageTitle = 'STU';
include __DIR__ . '/../includes/header.php';
?>

<!-- Hero Slideshow Section -->
<section class="px-3 sm:px-4 lg:px-8 py-4 sm:py-6">
    <div class="mx-auto max-w-6xl">
        <div class="hero-slideshow relative min-h-72 sm:min-h-96 lg:min-h-[520px] rounded-lg lg:rounded-2xl overflow-hidden">
            <!-- Slide 1 -->
            <div class="hero-slide active absolute w-full h-full opacity-0 transition-opacity duration-1000"
                 style="background: linear-gradient(90deg, rgba(247, 248, 246, 0.95) 0%, rgba(247, 248, 246, 0.3) 60%), 
                        url('https://lh3.googleusercontent.com/aida-public/AB6AXuB7jBmepfv88TypDQhRfqPxr2kmUbJLD14A9wrRaJgs5oN8_9kdiwZZM4z-ttEZx2B0haPe0Vuzp1-llKvaDmMOmAwg8huUPWtNWdnftkhN6NgZUv6DzH2yll7zsjj-jkixFIHGTE7EmvzHzi2QKDBA9gTXmD562if_DmN4u1kTCOqtqPuhPXa3hKgM-TLZVKZNq3gjxpqe3v2RTteRlstGEXRYha6AR0HDT5pUNGoLXh10RKGE5pKNEzaIm57UClSF1sFUoa5x55Og') center/cover no-repeat;">
                <div class="flex items-center h-full px-4 sm:px-6 lg:px-12 py-6 sm:py-8">
                    <div class="max-w-xs sm:max-w-md lg:max-w-2xl animate-slideInLeft">
                        <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-5xl font-black leading-tight text-gray-900 mb-2 sm:mb-3 lg:mb-6">
                            Rau Sạch<br>Tận Nhà
                        </h1>
                        <p class="text-xs sm:text-sm md:text-base lg:text-lg text-gray-700 mb-3 sm:mb-4 lg:mb-6">
                            Khám phá rau củ quả hữu cơ từ các nông trại địa phương.
                        </p>
                        <a href="<?= SITE_URL ?>/products.php" class="inline-block px-3 sm:px-4 md:px-6 py-2 sm:py-2 md:py-3 bg-primary text-black font-bold rounded-lg hover:bg-primary-dark transition text-xs sm:text-sm md:text-base">
                            Mua sắm ngay
                        </a>
                    </div>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="hero-slide absolute w-full h-full opacity-0 transition-opacity duration-1000"
                 style="background: linear-gradient(90deg, rgba(247, 248, 246, 0.95) 0%, rgba(247, 248, 246, 0.3) 60%), 
                        url('https://lh3.googleusercontent.com/aida-public/AB6AXuArr-q9KwzloOdRgoz6xxREcL6v_q_RX6EIBJAP-j5JTZsY2iajUTTnKaZ6evwiX17TyFr1w9q7mEuz2KbFPCIKsBivjHgaFoknvDoEfbWnrhVibxS-6YPcVr6JkgwLe3GTCSCt1DSS7iaxG0yET27xYyGCA-RO_yr_GAhzuCTxXWm3svbPfqCyP8tOSKpidAJtxDcIV3K1rdvWtc3E7XKfwaJDeSwelGnAkUlOIH0qV65tTVBsv56ijVGSnrsm2qbf1z_ibND92c3V') center/cover no-repeat;">
                <div class="flex items-center h-full px-4 sm:px-6 lg:px-12 py-6 sm:py-8">
                    <div class="max-w-xs sm:max-w-md lg:max-w-2xl">
                        <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-5xl font-black leading-tight text-gray-900 mb-2 sm:mb-3 lg:mb-6">
                            Tươi Ngon Từ<br>Nông Trại
                        </h1>
                        <p class="text-xs sm:text-sm md:text-base lg:text-lg text-gray-700 mb-3 sm:mb-4 lg:mb-6">
                            Giao hàng trong ngày, đảm bảo độ tươi ngon tối đa.
                        </p>
                        <a href="<?= SITE_URL ?>/products.php?is_new=1" class="inline-block px-3 sm:px-4 md:px-6 py-2 sm:py-2 md:py-3 bg-primary text-black font-bold rounded-lg hover:bg-primary-dark transition text-xs sm:text-sm md:text-base">
                            Xem hàng mới
                        </a>
                    </div>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="hero-slide absolute w-full h-full opacity-0 transition-opacity duration-1000"
                 style="background: linear-gradient(90deg, rgba(247, 248, 246, 0.95) 0%, rgba(247, 248, 246, 0.3) 60%), 
                        url('https://lh3.googleusercontent.com/aida-public/AB6AXuCVWBtAAXz_MHFMzXpn_hL-zvY2OO0MuxsmMvlzM-0q_pFKgWeutioN__AGyk9FYYwrW--4un68KrRmhgxyStSkk97ooIszU8eLgzOOT6pAr5l31M3kZFjjCmTXAkfhS_jKeuCjp_NEKJgVgAC04EKWj9L2iYd7QXNp4oLulaDQtChnDO3kRaezsEfHAqCE4Q-MDGcEwFYDXXZ8AX4x0HpUTpzZSdsU_cqEwye5buJa2SxMe6vvIbo_cNsNasYK-NQTLtGzJgVrH9LC') center/cover no-repeat;">
                <div class="flex items-center h-full px-4 sm:px-6 lg:px-12 py-6 sm:py-8">
                    <div class="max-w-xs sm:max-w-md lg:max-w-2xl">
                        <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-5xl font-black leading-tight text-gray-900 mb-2 sm:mb-3 lg:mb-6">
                            Ưu Đãi<br>Đặc Biệt
                        </h1>
                        <p class="text-xs sm:text-sm md:text-base lg:text-lg text-gray-700 mb-3 sm:mb-4 lg:mb-6">
                            Miễn phí giao hàng từ 500k. Giảm đến 30% sản phẩm chọn lọc.
                        </p>
                        <a href="<?= SITE_URL ?>/products.php?on_sale=1" class="inline-block px-3 sm:px-4 md:px-6 py-2 sm:py-2 md:py-3 bg-primary text-black font-bold rounded-lg hover:bg-primary-dark transition text-xs sm:text-sm md:text-base">
                            Khám phá ưu đãi
                        </a>
                    </div>
                </div>
            </div>

            <!-- Navigation Arrows -->
            <button onclick="changeSlide(-1)" aria-label="Previous slide" class="absolute left-2 sm:left-4 top-1/2 transform -translate-y-1/2 w-9 h-9 sm:w-11 sm:h-11 rounded-full bg-white/60 border border-white hover:bg-white/80 transition backdrop-blur cursor-pointer flex items-center justify-center z-40">
                <span class="material-symbols-outlined text-lg sm:text-2xl text-gray-900">chevron_left</span>
            </button>
            <button onclick="changeSlide(1)" aria-label="Next slide" class="absolute right-2 sm:right-4 top-1/2 transform -translate-y-1/2 w-9 h-9 sm:w-11 sm:h-11 rounded-full bg-white/60 border border-white hover:bg-white/80 transition backdrop-blur cursor-pointer flex items-center justify-center z-40">
                <span class="material-symbols-outlined text-lg sm:text-2xl text-gray-900">chevron_right</span>
            </button>

            <!-- Dots Indicator -->
            <div class="absolute bottom-3 sm:bottom-6 left-1/2 transform -translate-x-1/2 flex gap-2 z-40">
                <span class="slide-dot active w-2 h-2 sm:w-3 sm:h-3 rounded-full bg-primary cursor-pointer transition" onclick="goToSlide(0)"></span>
                <span class="slide-dot w-2 h-2 sm:w-3 sm:h-3 rounded-full bg-white/50 cursor-pointer transition hover:bg-white/70" onclick="goToSlide(1)"></span>
                <span class="slide-dot w-2 h-2 sm:w-3 sm:h-3 rounded-full bg-white/50 cursor-pointer transition hover:bg-white/70" onclick="goToSlide(2)"></span>
            </div>
        </div>
    </div>
</section>

<style>
    @keyframes slideInLeft {
        from {
            transform: translateX(-50px);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .hero-slide.active {
        opacity: 1 !important;
        z-index: 1;
    }

    .slide-dot.active {
        background: var(--primary) !important;
        transform: scale(1.3);
    }

    /* ===== TABLET: 768px - 1024px ===== */
    @media (min-width: 768px) and (max-width: 1024px) {
        /* Categories grid - auto fit full width, shrink smoothly */
        div[style*="grid-template-columns: repeat(auto-fit, minmax(clamp(90px, 20vw, 120px)"] {
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)) !important;
            gap: 0.75rem !important;
        }

        /* Featured Products - từ 150px→220px, 280px → 280px */
        .products-grid[style*="minmax(clamp(160px, 40vw, 280px)"],
        .products-grid[style*="minmax(clamp(180px, 40vw, 280px)"] {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)) !important;
        }

        /* News/Blog grid - từ 200px→220px */
        [style*="minmax(clamp(200px, 40vw, 240px)"] {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) !important;
        }

        /* Related products grid */
        [style*="minmax(clamp(200px, 40vw, 250px)"] {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) !important;
        }
    }

    /* ===== DESKTOP: >= 1025px ===== */
    @media (min-width: 1025px) {
        /* Categories grid - auto fit full width */
        div[style*="grid-template-columns: repeat(auto-fit, minmax(clamp(90px, 20vw, 120px)"] {
            grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)) !important;
            gap: 1rem !important;
        }
    }
</style>

<script>
    let currentSlide = 0;
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.slide-dot');

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.remove('active');
            if (dots[i]) dots[i].classList.remove('active');
        });

        if (index >= slides.length) currentSlide = 0;
        else if (index < 0) currentSlide = slides.length - 1;
        else currentSlide = index;

        slides[currentSlide].classList.add('active');
        if (dots[currentSlide]) dots[currentSlide].classList.add('active');
    }

    function changeSlide(direction) {
        currentSlide += direction;
        showSlide(currentSlide);
    }

    function goToSlide(index) {
        showSlide(index);
    }

    // Initialize slideshow state
    showSlide(0);

    // Auto slide every 4 seconds (4000 ms)
    setInterval(() => {
        currentSlide++;
        showSlide(currentSlide);
    }, 4000);
</script>


<!-- Categories (moved up above products) -->
<section style="padding: clamp(1.5rem, 4vw, 2rem) clamp(0.5rem, 2vw, 1rem) clamp(0.75rem, 2vw, 1rem);">
    <div style="max-width: 100%; margin: 0 auto; text-align: center; padding: 0 clamp(0.25rem, 1vw, 0.5rem);">
        <?php $catCount = count($categories); ?>
        <h2 class="section-title">Khám Phá Danh Mục <span style="font-weight:600; color:var(--muted-light); font-size:clamp(0.8rem, 2vw, 0.95rem);">(<?= $catCount ?> danh mục)</span></h2>
        <div style="margin-top:clamp(0.75rem, 2vw, 1rem); display:grid; grid-template-columns: repeat(auto-fit, minmax(clamp(90px, 20vw, 120px), 1fr)); gap:clamp(0.5rem, 1.5vw, 1rem); align-items:center; justify-items:center; padding: 0 clamp(0.25rem, 1vw, 0.5rem);">
            <?php foreach ($categories as $cat): ?>
                <a href="<?= SITE_URL ?>/products.php?category=<?= $cat['id'] ?>" style="text-align:center; width:100%; text-decoration:none; color:inherit;">
                    <div style="width:clamp(70px, 18vw, 96px); height:clamp(70px, 18vw, 96px); margin:0 auto clamp(0.35rem, 1vw, 0.5rem); border-radius:50%; overflow:hidden; display:flex; align-items:center; justify-content:center; background: #fff; box-shadow: 0 6px 18px rgba(0,0,0,0.06);">
                        <?php if (!empty($cat['icon'])): ?>
                            <img src="<?= imageUrl($cat['icon']) ?>" alt="<?= sanitize($cat['name']) ?>" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <span class="material-symbols-outlined" style="font-size:clamp(1.5rem, 5vw, 2rem); color:var(--primary-dark);">category</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-weight:700; font-size:clamp(0.75rem, 2vw, 0.95rem);">  <?= htmlspecialchars_decode(sanitize($cat['name'])) ?></div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<section style="padding: clamp(2rem, 5vw, 3rem) 1rem;">
    <div style="max-width: 1280px; margin: 0 auto;">
        <?php if ($search): ?>
            <div class="section-header">
                <h2 class="section-title">Kết quả tìm kiếm cho "<?= htmlspecialchars($search) ?>"</h2>
                <a href="<?= SITE_URL ?>/admin/" style="color: var(--primary-dark); font-weight: 600; font-size: clamp(0.85rem, 2vw, 1rem);">Đặt lại</a>
            </div>
            <?php if (!empty($searchProducts)): ?>
                <div class="products-grid" style="grid-template-columns: repeat(auto-fill, minmax(clamp(160px, 40vw, 280px), 1fr));">
                    <?php foreach ($searchProducts as $product): ?>
                        <?= renderProductCard($product) ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align:center; color:var(--muted-light); padding:clamp(1.5rem, 4vw, 3rem) 0; font-size:clamp(1rem, 3vw, 1.2rem);">
                    <span class="material-symbols-outlined" style="font-size:clamp(2rem, 8vw, 3rem); color:var(--primary-dark);">search_off</span><br>
                    Không tìm thấy sản phẩm phù hợp.
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="section-header">
                <h2 class="section-title">Nông Sản Tươi Mới</h2>
                <a href="<?= SITE_URL ?>/products.php" style="color: var(--primary-dark); font-weight: 600; font-size: clamp(0.85rem, 2vw, 1rem);">
                    Xem tất cả →
                </a>
            </div>
            <div class="products-grid" style="grid-template-columns: repeat(auto-fill, minmax(clamp(160px, 40vw, 280px), 1fr));">
                <?php foreach ($featuredProducts as $product): ?>
                    <?= renderProductCard($product) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>


<!-- Admin Featured Products -->
<?php if (!empty($adminFeatured)): ?>
<section style="padding: clamp(1.5rem, 4vw, 1.5rem) 1rem; background: transparent;">
    <div style="max-width: 1280px; margin: 0 auto;">
        <div class="section-header">
            <h2 class="section-title">Sản Phẩm Nổi Bật</h2>
            <a href="<?= SITE_URL ?>/products.php?filter=featured" style="color: var(--primary-dark); font-weight: 600; font-size: clamp(0.85rem, 2vw, 1rem);">Xem thêm →</a>
        </div>
        <div class="products-grid" style="grid-template-columns: repeat(auto-fill, minmax(clamp(180px, 40vw, 280px), 1fr));">
            <?php foreach ($adminFeatured as $p): ?>
                <?= renderProductCard($p) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- News / Blog -->
<?php if (!empty($latestPosts)): ?>
<section style="padding: clamp(1.5rem, 4vw, 2rem) 1rem;">
    <div style="max-width: 1280px; margin: 0 auto;">
        <div class="section-header">
            <h2 class="section-title">Tin Tức</h2>
            <a href="<?= SITE_URL ?>/blog.php" style="color: var(--primary-dark); font-weight: 600; font-size: clamp(0.85rem, 2vw, 1rem);">Xem tất cả →</a>
        </div>
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(clamp(200px, 40vw, 240px), 1fr)); gap:clamp(0.75rem, 2vw, 1rem);">
            <?php foreach ($latestPosts as $post): ?>
                <a href="<?= SITE_URL ?>/blog.php?slug=<?= $post['slug'] ?>" style="display:block; background: #fff; border-radius: 0.75rem; overflow:hidden; text-decoration:none; color:inherit; box-shadow: 0 6px 18px rgba(0,0,0,0.04);">
                    <?php if (!empty($post['featured_image'])): ?>
                        <img src="<?= imageUrl($post['featured_image']) ?>" alt="<?= sanitize($post['title']) ?>" style="width:100%; height:clamp(120px, 25vw, 160px); object-fit:cover;">
                    <?php endif; ?>
                    <div style="padding:clamp(0.75rem, 2vw, 1rem);">
                        <h3 style="margin:0 0 0.5rem; font-size:clamp(0.9rem, 2vw, 1.05rem); font-weight:700;"><?= sanitize($post['title']) ?></h3>
                        <p style="margin:0; color:var(--muted-light); font-size:clamp(0.8rem, 1.5vw, 0.95rem);"><?= sanitize($post['excerpt']) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Features -->
<section style="padding: clamp(2rem, 5vw, 3rem) 1rem; background: var(--card-light);">
    <div style="max-width: 1280px; margin: 0 auto;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(clamp(200px, 40vw, 250px), 1fr)); gap: clamp(1.5rem, 4vw, 2rem); text-align: center;">
            <div style="padding: clamp(1.5rem, 3vw, 2rem);">
                <div style="width: clamp(48px, 12vw, 64px); height: clamp(48px, 12vw, 64px); margin: 0 auto; border-radius: 50%; background: rgba(182, 230, 51, 0.2); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-outlined" style="font-size: clamp(1.5rem, 4vw, 2rem); color: var(--primary-dark);">eco</span>
                </div>
                <h3 style="font-size: clamp(1rem, 3vw, 1.25rem); font-weight: 700; margin-top: clamp(0.75rem, 2vw, 1rem);">100% Hữu Cơ</h3>
                <p style="color: var(--muted-light); margin-top: 0.5rem; font-size: clamp(0.8rem, 2vw, 0.875rem); line-height: 1.5;">
                    Sản phẩm được chứng nhận hữu cơ, không thuốc trừ sâu và phân bón hóa học.
                </p>
            </div>

            <div style="padding: clamp(1.5rem, 3vw, 2rem);">
                <div style="width: clamp(48px, 12vw, 64px); height: clamp(48px, 12vw, 64px); margin: 0 auto; border-radius: 50%; background: rgba(182, 230, 51, 0.2); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-outlined" style="font-size: clamp(1.5rem, 4vw, 2rem); color: var(--primary-dark);">local_shipping</span>
                </div>
                <h3 style="font-size: clamp(1rem, 3vw, 1.25rem); font-weight: 700; margin-top: clamp(0.75rem, 2vw, 1rem);">Giao Hàng Nhanh</h3>
                <p style="color: var(--muted-light); margin-top: 0.5rem; font-size: clamp(0.8rem, 2vw, 0.875rem); line-height: 1.5;">
                    Miễn phí giao hàng cho đơn từ 500.000₫. Giao trong ngày tại TP.HCM.
                </p>
            </div>

            <div style="padding: clamp(1.5rem, 3vw, 2rem);">
                <div style="width: clamp(48px, 12vw, 64px); height: clamp(48px, 12vw, 64px); margin: 0 auto; border-radius: 50%; background: rgba(182, 230, 51, 0.2); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-outlined" style="font-size: clamp(1.5rem, 4vw, 2rem); color: var(--primary-dark);">agriculture</span>
                </div>
                <h3 style="font-size: clamp(1rem, 3vw, 1.25rem); font-weight: 700; margin-top: clamp(0.75rem, 2vw, 1rem);">Đối Tác Nông Dân</h3>
                <p style="color: var(--muted-light); margin-top: 0.5rem; font-size: clamp(0.8rem, 2vw, 0.875rem); line-height: 1.5;">
                    Thu mua trực tiếp từ các nông trại gia đình địa phương, đảm bảo tươi ngon.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Banner -->
<section style="padding: clamp(2rem, 5vw, 3rem) 1rem;">
    <div style="max-width: 1280px; margin: 0 auto;">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: clamp(1rem, 3vw, 1.5rem); 
                    background: rgba(182, 230, 51, 0.15); border-radius: 1rem; padding: clamp(1.5rem, 4vw, 2rem) clamp(1.5rem, 5vw, 3rem);">
            <div>
                <h2 style="font-size: clamp(1.25rem, 4vw, 1.5rem); font-weight: 700; line-height: 1.4;">Miễn Phí Giao Hàng Cho Đơn Từ 500.000₫!</h2>
                <p style="color: var(--muted-light); margin-top: 0.5rem; font-size: clamp(0.85rem, 2vw, 1rem);">
                    Mua sắm thả ga, Xanh Organic giao hàng tận cửa miễn phí.
                </p>
            </div>
            <a href="<?= SITE_URL ?>/products.php" class="btn btn-primary" style="font-size: clamp(0.9rem, 2vw, 1rem); padding: clamp(0.6rem, 1.5vw, 0.75rem) clamp(1rem, 2vw, 1.5rem); white-space: nowrap;">
                Bắt đầu mua sắm
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

