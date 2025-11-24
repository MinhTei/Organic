<?php
// includes/footer.php - Updated với Settings từ DB
?>

<footer class="footer">
    <div class="footer-container">
        <div class="footer-grid">
            <!-- Brand -->
            <div class="footer-brand">
                <a href="<?= SITE_URL ?>" class="logo">
                    <?php if (function_exists('getSystemSetting') && getSystemSetting('site_logo')): ?>
                        <img src="<?= SITE_URL . '/' . getSystemSetting('site_logo') ?>" 
                             alt="<?= SITE_NAME ?>" 
                             style="height: 32px; width: auto;">
                    <?php else: ?>
                        <svg class="w-8 h-8 text-primary" fill="currentColor" viewBox="0 0 48 48">
                            <path d="M39.5563 34.1455V13.8546C39.5563 15.708 36.8773 17.3437 32.7927 18.3189C30.2914 18.916 27.263 19.2655 24 19.2655C20.737 19.2655 17.7086 18.916 15.2073 18.3189C11.1227 17.3437 8.44365 15.708 8.44365 13.8546V34.1455C8.44365 35.9988 11.1227 37.6346 15.2073 38.6098C17.7086 39.2069 20.737 39.5564 24 39.5564C27.263 39.5564 30.2914 39.2069 32.7927 38.6098C36.8773 37.6346 39.5563 35.9988 39.5563 34.1455Z"></path>
                        </svg>
                    <?php endif; ?>
                    <span class="logo-text"><?= SITE_NAME ?></span>
                </a>
                <p><?= function_exists('getSystemSetting') ? getSystemSetting('site_description', 'Mang những sản phẩm hữu cơ tươi ngon nhất từ trang trại đến bàn ăn của bạn.') : 'Mang những sản phẩm hữu cơ tươi ngon nhất từ trang trại đến bàn ăn của bạn.' ?></p>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h3 class="footer-title">Liên kết nhanh</h3>
                <div class="footer-links">
                    <a href="<?= SITE_URL ?>/about.php">Về chúng tôi</a>
                    <a href="<?= SITE_URL ?>/products.php">Sản phẩm</a>
                    <a href="<?= SITE_URL ?>/contact.php">Liên hệ</a>
                    <a href="#">Chính sách bảo mật</a>
                    <a href="#">Điều khoản sử dụng</a>
                </div>
            </div>
            
            <!-- Contact -->
            <div>
                <h3 class="footer-title">Liên hệ</h3>
                <div class="footer-links">
                    <a href="tel:<?= SITE_PHONE ?>">
                        <span class="material-symbols-outlined" style="vertical-align: middle; font-size: 1rem; margin-right: 0.25rem;">call</span>
                        Hotline: <?= SITE_PHONE ?>
                    </a>
                    <a href="mailto:<?= SITE_EMAIL ?>">
                        <span class="material-symbols-outlined" style="vertical-align: middle; font-size: 1rem; margin-right: 0.25rem;">mail</span>
                        <?= SITE_EMAIL ?>
                    </a>
                    <span>
                        <span class="material-symbols-outlined" style="vertical-align: middle; font-size: 1rem; margin-right: 0.25rem;">location_on</span>
                        <?= function_exists('getSystemSetting') ? getSystemSetting('site_address', '123 Đường Xanh, Q.1, TP.HCM') : '123 Đường Xanh, Q.1, TP.HCM' ?>
                    </span>
                </div>
            </div>
            
            <!-- Social Media -->
            <div>
                <h3 class="footer-title">Theo dõi chúng tôi</h3>
                <div style="display: flex; gap: 0.75rem; margin-top: 0.75rem;">
                    <?php if (function_exists('getSystemSetting') && getSystemSetting('social_facebook')): ?>
                        <a href="<?= getSystemSetting('social_facebook') ?>" target="_blank"
                           style="width: 40px; height: 40px; border-radius: 50%; background: #1877f2; display: flex; align-items: center; justify-content: center; color: white;">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (function_exists('getSystemSetting') && getSystemSetting('social_instagram')): ?>
                        <a href="<?= getSystemSetting('social_instagram') ?>" target="_blank"
                           style="width: 40px; height: 40px; border-radius: 50%; background: #E4405F; display: flex; align-items: center; justify-content: center; color: white;">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (function_exists('getSystemSetting') && getSystemSetting('social_zalo')): ?>
                        <a href="<?= getSystemSetting('social_zalo') ?>" target="_blank"
                           style="width: 40px; height: 40px; border-radius: 50%; background: #0068ff; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.25rem;">
                            Z
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>© <?= date('Y') ?> <?= SITE_NAME ?>. Mọi quyền được bảo lưu.</p>
        </div>
    </div>
</footer>

<!-- Custom JS -->
<script src="<?= SITE_URL ?>/js/scripts.js"></script>
</body>
</html>