<?php
// includes/footer.php - Updated với Settings từ DB
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/config.php';
}
?>

<footer style="background-color: var(--background-light); color: var(--text-light); width: 100%; box-sizing: border-box; overflow-x: hidden;" class="py-8 sm:py-12 lg:py-16 mt-12 sm:mt-16 lg:mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" style="width: 100%; max-width: 100%; box-sizing: border-box;">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8 mb-8 sm:mb-12">
            <!-- Brand -->
            <div class="col-span-1 sm:col-span-1 lg:col-span-1">
                <a href="<?= SITE_URL ?>" class="logo inline-block mb-3">
                    <?php if (function_exists('getSystemSetting') && getSystemSetting('site_logo')): ?>
                        <img src="<?= SITE_URL . '/' . getSystemSetting('site_logo') ?>" 
                             alt="<?= SITE_NAME ?>" 
                             class="h-20 sm:h-24 lg:h-28 w-auto">
                    <?php else: ?>
                        <svg class="w-20 h-20 sm:w-24 sm:h-24 lg:w-28 lg:h-28 text-primary" fill="currentColor" viewBox="0 0 48 48">
                            <path d="M39.5563 34.1455V13.8546C39.5563 15.708 36.8773 17.3437 32.7927 18.3189C30.2914 18.916 27.263 19.2655 24 19.2655C20.737 19.2655 17.7086 18.916 15.2073 18.3189C11.1227 17.3437 8.44365 15.708 8.44365 13.8546V34.1455C8.44365 35.9988 11.1227 37.6346 15.2073 38.6098C17.7086 39.2069 20.737 39.5564 24 39.5564C27.263 39.5564 30.2914 39.2069 32.7927 38.6098C36.8773 37.6346 39.5563 35.9988 39.5563 34.1455Z"></path>
                        </svg>
                    <?php endif; ?>
                </a>
                <p class="text-xs sm:text-sm" style="color: var(--muted-light);">
                    <?= function_exists('getSystemSetting') ? getSystemSetting('site_description', 'Mang những sản phẩm hữu cơ tươi ngon nhất từ trang trại đến bàn ăn của bạn.') : 'Mang những sản phẩm hữu cơ tươi ngon nhất từ trang trại đến bàn ăn của bạn.' ?>
                </p>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h3 class="text-xs sm:text-sm font-bold mb-3 sm:mb-4">Liên kết nhanh</h3>
                <div class="space-y-2">
                    <a href="<?= SITE_URL ?>/about.php" class="block text-xs sm:text-sm hover:text-primary transition" style="color: var(--muted-light);">Về chúng tôi</a>
                    <a href="<?= SITE_URL ?>/products.php" class="block text-xs sm:text-sm hover:text-primary transition" style="color: var(--muted-light);">Sản phẩm</a>
                    <a href="<?= SITE_URL ?>/contact.php" class="block text-xs sm:text-sm hover:text-primary transition" style="color: var(--muted-light);">Liên hệ</a>
                    <a href="#" class="block text-xs sm:text-sm hover:text-primary transition" style="color: var(--muted-light);">Chính sách bảo mật</a>
                    <a href="#" class="block text-xs sm:text-sm hover:text-primary transition" style="color: var(--muted-light);">Điều khoản sử dụng</a>
                </div>
            </div>
            
            <!-- Contact -->
            <div>
                <h3 class="text-xs sm:text-sm font-bold mb-3 sm:mb-4">Liên hệ</h3>
                <div class="space-y-2 sm:space-y-3">
                    <a href="tel:<?= SITE_PHONE ?>" class="flex items-center gap-2 text-xs sm:text-sm hover:text-primary transition" style="color: var(--muted-light);">
                        <span class="material-symbols-outlined text-sm sm:text-base flex-shrink-0">call</span>
                        <span class="truncate"><?= SITE_PHONE ?></span>
                    </a>
                    <a href="mailto:<?= SITE_EMAIL ?>" class="flex items-center gap-2 text-xs sm:text-sm hover:text-primary transition" style="color: var(--muted-light);">
                        <span class="material-symbols-outlined text-sm sm:text-base flex-shrink-0">mail</span>
                        <span class="truncate"><?= SITE_EMAIL ?></span>
                    </a>
                    <div class="flex items-start gap-2 text-xs sm:text-sm" style="color: var(--muted-light);">
                        <span class="material-symbols-outlined text-sm sm:text-base flex-shrink-0 mt-0.5">location_on</span>
                        <span><?= function_exists('getSystemSetting') ? getSystemSetting('site_address', '123 Đường Xanh, Q.1, TP.HCM') : '123 Đường Xanh, Q.1, TP.HCM' ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Social Media -->
            <div>
                <h3 class="text-xs sm:text-sm font-bold mb-3 sm:mb-4">Theo dõi chúng tôi</h3>
                <div class="flex gap-2 flex-wrap">
                    <?php if (function_exists('getSystemSetting') && getSystemSetting('social_facebook')): ?>
                        <a href="<?= getSystemSetting('social_facebook') ?>" target="_blank" rel="noopener noreferrer"
                           class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-blue-600 hover:bg-blue-700 flex items-center justify-center transition flex-shrink-0">
                            <svg width="16" height="16" class="sm:w-[18px] sm:h-[18px]" fill="white" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (function_exists('getSystemSetting') && getSystemSetting('social_instagram')): ?>
                        <a href="<?= getSystemSetting('social_instagram') ?>" target="_blank" rel="noopener noreferrer"
                           class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-pink-600 hover:bg-pink-700 flex items-center justify-center transition flex-shrink-0">
                            <svg width="16" height="16" class="sm:w-[18px] sm:h-[18px]" fill="white" viewBox="0 0 24 24">
                                <path d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4c0 3.2-2.6 5.8-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8C2 4.6 4.6 2 7.8 2m-.3 2c-2.1 0-3.8 1.7-3.8 3.8v8.4c0 2.1 1.7 3.8 3.8 3.8h8.4c2.1 0 3.8-1.7 3.8-3.8V7.8c0-2.1-1.7-3.8-3.8-3.8H7.5m9.6 1.6c.6 0 1.1.5 1.1 1.1s-.5 1.1-1.1 1.1-1.1-.5-1.1-1.1.5-1.1 1.1-1.1M12 7c2.8 0 5 2.2 5 5s-2.2 5-5 5-5-2.2-5-5 2.2-5 5-5m0 2c-1.7 0-3 1.3-3 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (function_exists('getSystemSetting') && getSystemSetting('social_zalo')): ?>
                        <a href="<?= getSystemSetting('social_zalo') ?>" target="_blank" rel="noopener noreferrer"
                           class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-blue-500 hover:bg-blue-600 flex items-center justify-center transition text-white font-bold text-sm sm:text-base flex-shrink-0">
                            Z
                        </a>
                    <?php endif; ?>

                    <?php if (function_exists('getSystemSetting') && getSystemSetting('social_youtube')): ?>
                        <a href="<?= getSystemSetting('social_youtube') ?>" target="_blank" rel="noopener noreferrer"
                           class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-red-600 hover:bg-red-700 flex items-center justify-center transition flex-shrink-0">
                            <svg width="16" height="16" class="sm:w-[18px] sm:h-[18px]" fill="white" viewBox="0 0 24 24">
                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="border-t pt-6 sm:pt-8 flex flex-col sm:flex-row justify-between items-center gap-4 sm:gap-0" style="border-color: var(--border-light);">
            <p class="text-xs sm:text-sm" style="color: var(--muted-light);">
                © <?= date('Y') ?> <?= SITE_NAME ?>. Mọi quyền được bảo lưu.
            </p>
            <!-- Back to Top Button -->
            <button id="backToTopBtn" class="hidden fixed bottom-8 right-8 bg-primary hover:bg-primary-dark text-text-light p-3 rounded-full shadow-lg transition z-50" onclick="backToTop()" title="Lên đầu trang">
                <span class="material-symbols-outlined" style="font-size: 1.5rem;">arrow_upward</span>
            </button>
        </div>
    </div>
</footer>

<!-- Back to Top Script -->
<script>
// Show/hide back to top button
window.addEventListener('scroll', function() {
    const backToTopBtn = document.getElementById('backToTopBtn');
    if (window.pageYOffset > 300) {
        backToTopBtn.classList.remove('hidden');
    } else {
        backToTopBtn.classList.add('hidden');
    }
});

// Scroll to top function
function backToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}
</script>

</body>
</html>