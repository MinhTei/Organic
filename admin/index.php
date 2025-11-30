<?php

/**
 * index.php - Trang chủ với slideshow banner và menu admin
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

// Get featured products
$featuredProducts = getFeaturedProducts(4);

// Get categories
$categories = getCategories();

// Get new products
$newProducts = getProducts(['is_new' => 1, 'limit' => 4])['products'];

$pageTitle = 'Rau Sạch Tận Nhà';
include __DIR__ . '/../includes/header.php';
?>

<!-- Hero Slideshow Section -->
<section style="padding: 0 1rem;">
    <div style="max-width: 1280px; margin: 2rem auto;">
        <div class="hero-slideshow" style="position: relative; min-height: 520px; border-radius: 1rem; overflow: hidden;">
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

<!-- Featured Products -->
<section style="padding: 3rem 1rem;">
    <div style="max-width: 1280px; margin: 0 auto;">
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
    </div>
</section>

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

<!-- Categories -->
<section style="padding: 3rem 1rem;">
    <div style="max-width: 1280px; margin: 0 auto;">
        <h2 class="section-title" style="margin-bottom: 1.5rem;">Khám Phá Danh Mục</h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem;">
            <?php
            foreach ($categories as $index => $cat):
                if ($index >= 4) break;
                $img = imageUrl($cat['icon'] ?? '');
            ?>
                <a href="<?= SITE_URL ?>/products.php?category=<?= $cat['id'] ?>"
                    style="position: relative; aspect-ratio: 1; border-radius: 0.75rem; overflow: hidden; display: block; transition: transform 0.3s;">
                    <img src="<?= $img ?>" alt="<?= sanitize($cat['name']) ?>"
                        style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;">
                    <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.3); transition: background 0.3s;"></div>
                    <h3 style="position: absolute; bottom: 1rem; left: 1rem; color: white; font-size: 1.25rem; font-weight: 700;">
                        <?= sanitize($cat['name']) ?>
                    </h3>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

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

<?php include __DIR__ . '/../includes/footer.php'; ?>