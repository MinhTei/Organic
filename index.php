<?php

/**
 * index.php - Trang chủ với slideshow banner
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/settings_helper.php';


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

$pageTitle = 'Rau Sạch Tận Nhà';
include __DIR__ . '/includes/header.php';
?>

<!-- Search Section removed: chỉ dùng thanh tìm kiếm ở header -->

<!-- Hero Slideshow Section -->
<section style="padding: 0 1rem;">
    <div style="max-width: 1280px; margin: 2rem auto; margin-top: 0;">
        <div class="hero-slideshow" style="position: relative; min-height: 520px; border-radius: 1rem; overflow: hidden; margin-top: 1.5rem;">
            <!-- Slide 1 -->
            <div class="hero-slide active" style="position: absolute; width: 100%; height: 100%; opacity: 0; transition: opacity 1s ease-in-out;
                        background: linear-gradient(90deg, rgba(247, 248, 246, 0.95) 0%, rgba(247, 248, 246, 0.3) 60%), 
                        url('https://lh3.googleusercontent.com/aida-public/AB6AXuB7jBmepfv88TypDQhRfqPxr2kmUbJLD14A9wrRaJgs5oN8_9kdiwZZM4z-ttEZx2B0haPe0Vuzp1-llKvaDmMOmAwg8huUPWtNWdnftkhN6NgZUv6DzH2yll7zsjj-jkixFIHGTE7EmvzHzi2QKDBA9gTXmD562if_DmN4u1kTCOqtqPuhPXa3hKgM-TLZVKZNq3gjxpqe3v2RTteRlstGEXRYha6AR0HDT5pUNGoLXh10RKGE5pKNEzaIm57UClSF1sFUoa5x55Og') center/cover no-repeat;">
                <div style="display: flex; align-items: center; height: 100%; padding: 3rem;">
                    <div style="max-width: 600px; animation: slideInLeft 1s ease;">
                        <h1 style="font-size: 3rem; font-weight: 900; line-height: 1.1; color: var(--text-light); margin-bottom: 1rem;">
                            Rau Sạch Tận Nhà,<br>Cho Bữa Cơm Lành
                        </h1>
                        <p style="font-size: 1.125rem; color: var(--muted-light); margin-bottom: 2rem;">
                            Khám phá rau củ quả 100% hữu cơ, được nuôi trồng bền vững từ các nông trại địa phương.
                        </p>
                        <a href="<?= SITE_URL ?>/products.php" class="btn btn-primary">
                            Mua sắm ngay
                        </a>
                    </div>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="hero-slide" style="position: absolute; width: 100%; height: 100%; opacity: 0; transition: opacity 1s ease-in-out;
                        background: linear-gradient(90deg, rgba(247, 248, 246, 0.95) 0%, rgba(247, 248, 246, 0.3) 60%), 
                        url('https://lh3.googleusercontent.com/aida-public/AB6AXuArr-q9KwzloOdRgoz6xxREcL6v_q_RX6EIBJAP-j5JTZsY2iajUTTnKaZ6evwiX17TyFr1w9q7mEuz2KbFPCIKsBivjHgaFoknvDoEfbWnrhVibxS-6YPcVr6JkgwLe3GTCSCt1DSS7iaxG0yET27xYyGCA-RO_yr_GAhzuCTxXWm3svbPfqCyP8tOSKpidAJtxDcIV3K1rdvWtc3E7XKfwaJDeSwelGnAkUlOIH0qV65tTVBsv56ijVGSnrsm2qbf1z_ibND92c3V') center/cover no-repeat;">
                <div style="display: flex; align-items: center; height: 100%; padding: 3rem;">
                    <div style="max-width: 600px;">
                        <h1 style="font-size: 3rem; font-weight: 900; line-height: 1.1; color: var(--text-light); margin-bottom: 1rem;">
                            Tươi Ngon Từ Nông Trại
                        </h1>
                        <p style="font-size: 1.125rem; color: var(--muted-light); margin-bottom: 2rem;">
                            Giao hàng trong ngày, đảm bảo độ tươi ngon tối đa cho mọi sản phẩm.
                        </p>
                        <a href="<?= SITE_URL ?>/products.php?is_new=1" class="btn btn-primary">
                            Xem hàng mới
                        </a>
                    </div>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="hero-slide" style="position: absolute; width: 100%; height: 100%; opacity: 0; transition: opacity 1s ease-in-out;
                        background: linear-gradient(90deg, rgba(247, 248, 246, 0.95) 0%, rgba(247, 248, 246, 0.3) 60%), 
                        url('https://lh3.googleusercontent.com/aida-public/AB6AXuCVWBtAAXz_MHFMzXpn_hL-zvY2OO0MuxsmMvlzM-0q_pFKgWeutioN__AGyk9FYYwrW--4un68KrRmhgxyStSkk97ooIszU8eLgzOOT6pAr5l31M3kZFjjCmTXAkfhS_jKeuCjp_NEKJgVgAC04EKWj9L2iYd7QXNp4oLulaDQtChnDO3kRaezsEfHAqCE4Q-MDGcEwFYDXXZ8AX4x0HpUTpzZSdsU_cqEwye5buJa2SxMe6vvIbo_cNsNasYK-NQTLtGzJgVrH9LC') center/cover no-repeat;">
                <div style="display: flex; align-items: center; height: 100%; padding: 3rem;">
                    <div style="max-width: 600px;">
                        <h1 style="font-size: 3rem; font-weight: 900; line-height: 1.1; color: var(--text-light); margin-bottom: 1rem;">
                            Ưu Đãi Đặc Biệt
                        </h1>
                        <p style="font-size: 1.125rem; color: var(--muted-light); margin-bottom: 2rem;">
                            Miễn phí giao hàng cho đơn từ 500.000₫. Giảm giá đến 30% cho sản phẩm chọn lọc.
                        </p>
                        <a href="<?= SITE_URL ?>/products.php?on_sale=1" class="btn btn-primary">
                            Khám phá ưu đãi
                        </a>
                    </div>
                </div>
            </div>

            <!-- Navigation Arrows -->
            <button onclick="changeSlide(-1)" aria-label="Previous slide" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); 
                    width: 44px; height: 44px; border-radius: 50%; background: rgba(255,255,255,0.6); border: none; 
                    cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                    transition: all 0.18s; z-index: 40; backdrop-filter: blur(4px); color: rgba(0,0,0,0.85);">
                <span class="material-symbols-outlined">chevron_left</span>
            </button>
            <button onclick="changeSlide(1)" aria-label="Next slide" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); 
                    width: 44px; height: 44px; border-radius: 50%; background: rgba(255,255,255,0.6); border: none; 
                    cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                    transition: all 0.18s; z-index: 40; backdrop-filter: blur(4px); color: rgba(0,0,0,0.85);">
                <span class="material-symbols-outlined">chevron_right</span>
            </button>

            <!-- Dots Indicator -->
            <div style="position: absolute; bottom: 2rem; left: 50%; transform: translateX(-50%); display: flex; gap: 0.5rem;">
                <span class="slide-dot active" onclick="goToSlide(0)" style="width: 12px; height: 12px; border-radius: 50%; 
                      background: var(--primary); cursor: pointer; transition: all 0.3s;"></span>
                <span class="slide-dot" onclick="goToSlide(1)" style="width: 12px; height: 12px; border-radius: 50%; 
                      background: rgba(255,255,255,0.5); cursor: pointer; transition: all 0.3s;"></span>
                <span class="slide-dot" onclick="goToSlide(2)" style="width: 12px; height: 12px; border-radius: 50%; 
                      background: rgba(255,255,255,0.5); cursor: pointer; transition: all 0.3s;"></span>
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

    /* Mobile responsive - push hero down to avoid search bar overlap */
    @media (max-width: 767px) {
        .hero-slideshow {
            margin-top: 2rem !important;
            min-height: 380px;
        }
    }

    @media (min-width: 768px) {
        .hero-slideshow {
            margin-top: 1.5rem !important;
            min-height: 520px;
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


<!-- Search Results or Featured Products -->
<!-- Categories (moved up above products) -->
<section style="padding: 2rem 1rem 1rem;">
    <div style="max-width: 1280px; margin: 0 auto; text-align: center;">
        <?php $catCount = count($categories); ?>
        <h2 class="section-title">Khám Phá Danh Mục <span style="font-weight:600; color:var(--muted-light); font-size:0.95rem;">(<?= $catCount ?> danh mục)</span></h2>
        <div style="margin-top:1rem; display:grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap:1rem; align-items:center; justify-items:center;">
            <?php foreach ($categories as $cat): ?>
                <a href="<?= SITE_URL ?>/products.php?category=<?= $cat['id'] ?>" style="text-align:center; width:140px; text-decoration:none; color:inherit;">
                    <div style="width:96px; height:96px; margin:0 auto 0.5rem; border-radius:50%; overflow:hidden; display:flex; align-items:center; justify-content:center; background: #fff; box-shadow: 0 6px 18px rgba(0,0,0,0.06);">
                        <?php if (!empty($cat['icon'])): ?>
                            <img src="<?= imageUrl($cat['icon']) ?>" alt="<?= sanitize($cat['name']) ?>" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <span class="material-symbols-outlined" style="font-size:2rem; color:var(--primary-dark);">category</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-weight:700;"><?= htmlspecialchars_decode(sanitize($cat['name'])) ?></div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<section style="padding: 3rem 1rem;">
    <div style="max-width: 1280px; margin: 0 auto;">
        <?php if ($search): ?>
            <div class="section-header">
                <h2 class="section-title">Kết quả tìm kiếm cho "<?= htmlspecialchars($search) ?>"</h2>
                <a href="index.php" style="color: var(--primary-dark); font-weight: 600;">Đặt lại</a>
            </div>
            <?php if (!empty($searchProducts)): ?>
                <div class="products-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                    <?php foreach ($searchProducts as $product): ?>
                        <?= renderProductCard($product) ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align:center; color:var(--muted-light); padding:3rem 0; font-size:1.2rem;">
                    <span class="material-symbols-outlined" style="font-size:3rem; color:var(--primary-dark);">search_off</span><br>
                    Không tìm thấy sản phẩm phù hợp.
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="section-header">
                <h2 class="section-title">Nông Sản Tươi Mới</h2>
                <a href="<?= SITE_URL ?>/products.php" style="color: var(--primary-dark); font-weight: 600;">
                    Xem tất cả →
                </a>
            </div>
            <div class="products-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                <?php foreach ($featuredProducts as $product): ?>
                    <?= renderProductCard($product) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>


<!-- Admin Featured Products -->
<?php if (!empty($adminFeatured)): ?>
<section style="padding: 1.5rem 1rem; background: transparent;">
    <div style="max-width: 1280px; margin: 0 auto;">
        <div class="section-header">
            <h2 class="section-title">Sản Phẩm Nổi Bật</h2>
            <a href="<?= SITE_URL ?>/products.php?filter=featured" style="color: var(--primary-dark); font-weight: 600;">Xem thêm →</a>
        </div>
        <div class="products-grid" style="grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));">
            <?php foreach ($adminFeatured as $p): ?>
                <?= renderProductCard($p) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- News / Blog -->
<?php if (!empty($latestPosts)): ?>
<section style="padding: 2rem 1rem;">
    <div style="max-width: 1280px; margin: 0 auto;">
        <div class="section-header">
            <h2 class="section-title">Tin Tức</h2>
            <a href="<?= SITE_URL ?>/blog.php" style="color: var(--primary-dark); font-weight: 600;">Xem tất cả →</a>
        </div>
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:1rem;">
            <?php foreach ($latestPosts as $post): ?>
                <a href="<?= SITE_URL ?>/blog.php?slug=<?= $post['slug'] ?>" style="display:block; background: #fff; border-radius: 0.75rem; overflow:hidden; text-decoration:none; color:inherit; box-shadow: 0 6px 18px rgba(0,0,0,0.04);">
                    <?php if (!empty($post['featured_image'])): ?>
                        <img src="<?= imageUrl($post['featured_image']) ?>" alt="<?= sanitize($post['title']) ?>" style="width:100%; height:160px; object-fit:cover;">
                    <?php endif; ?>
                    <div style="padding:1rem;">
                        <h3 style="margin:0 0 0.5rem; font-size:1.05rem; font-weight:700;"><?= sanitize($post['title']) ?></h3>
                        <p style="margin:0; color:var(--muted-light); font-size:0.95rem;"><?= sanitize($post['excerpt']) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Features -->
<section style="padding: 3rem 1rem; background: var(--card-light);">
    <div style="max-width: 1280px; margin: 0 auto;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; text-align: center;">
            <div style="padding: 2rem;">
                <div style="width: 64px; height: 64px; margin: 0 auto; border-radius: 50%; background: rgba(182, 230, 51, 0.2); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-outlined" style="font-size: 2rem; color: var(--primary-dark);">eco</span>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-top: 1rem;">100% Hữu Cơ</h3>
                <p style="color: var(--muted-light); margin-top: 0.5rem; font-size: 0.875rem;">
                    Sản phẩm được chứng nhận hữu cơ, không thuốc trừ sâu và phân bón hóa học.
                </p>
            </div>

            <div style="padding: 2rem;">
                <div style="width: 64px; height: 64px; margin: 0 auto; border-radius: 50%; background: rgba(182, 230, 51, 0.2); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-outlined" style="font-size: 2rem; color: var(--primary-dark);">local_shipping</span>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-top: 1rem;">Giao Hàng Nhanh</h3>
                <p style="color: var(--muted-light); margin-top: 0.5rem; font-size: 0.875rem;">
                    Miễn phí giao hàng cho đơn từ 500.000₫. Giao trong ngày tại TP.HCM.
                </p>
            </div>

            <div style="padding: 2rem;">
                <div style="width: 64px; height: 64px; margin: 0 auto; border-radius: 50%; background: rgba(182, 230, 51, 0.2); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-outlined" style="font-size: 2rem; color: var(--primary-dark);">agriculture</span>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-top: 1rem;">Đối Tác Nông Dân</h3>
                <p style="color: var(--muted-light); margin-top: 0.5rem; font-size: 0.875rem;">
                    Thu mua trực tiếp từ các nông trại gia đình địa phương, đảm bảo tươi ngon.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Categories
<section style="padding: 3rem 1rem;">
    <div style="max-width: 1280px; margin: 0 auto;">
        <h2 class="section-title" style="margin-bottom: 1.5rem;">Khám Phá Danh Mục</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem;">
            <?php 
            // Hiển thị hình ảnh danh mục từ admin upload (icon)
            foreach ($categories as $index => $cat): 
                if ($index >= 4) break;
            ?>
            <a href="<?= SITE_URL ?>/products.php?category=<?= $cat['id'] ?>" 
               style="position: relative; aspect-ratio: 1; border-radius: 0.75rem; overflow: hidden; display: block; transition: transform 0.3s;">
                <img src="<?= imageUrl($cat['icon']) ?>" alt="<?= sanitize($cat['name']) ?>" 
                     style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;">
                <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.3); transition: background 0.3s;"></div>
                <h3 style="position: absolute; bottom: 1rem; left: 1rem; color: white; font-size: 1.25rem; font-weight: 700;">
                    <?= sanitize($cat['name']) ?>
                </h3>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section> -->

<!-- CTA Banner -->
<section style="padding: 3rem 1rem;">
    <div style="max-width: 1280px; margin: 0 auto;">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.5rem; 
                    background: rgba(182, 230, 51, 0.15); border-radius: 1rem; padding: 2rem 3rem;">
            <div>
                <h2 style="font-size: 1.5rem; font-weight: 700;">Miễn Phí Giao Hàng Cho Đơn Từ 500.000₫!</h2>
                <p style="color: var(--muted-light); margin-top: 0.5rem;">
                    Mua sắm thả ga, Xanh Organic giao hàng tận cửa miễn phí.
                </p>
            </div>
            <a href="<?= SITE_URL ?>/products.php" class="btn btn-primary">
                Bắt đầu mua sắm
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

<style>
    /* Mobile Product Grid - 2 columns only */
    @media (max-width: 640px) {
        .products-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }
</style>