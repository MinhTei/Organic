<?php
// includes/footer.php
?>

<footer class="footer">
    <div class="footer-container">
        <div class="footer-grid">
            <!-- Brand -->
            <div class="footer-brand">
                <a href="<?= SITE_URL ?>" class="logo">
                    <svg class="w-8 h-8 text-primary" fill="currentColor" viewBox="0 0 48 48">
                        <path d="M39.5563 34.1455V13.8546C39.5563 15.708 36.8773 17.3437 32.7927 18.3189C30.2914 18.916 27.263 19.2655 24 19.2655C20.737 19.2655 17.7086 18.916 15.2073 18.3189C11.1227 17.3437 8.44365 15.708 8.44365 13.8546V34.1455C8.44365 35.9988 11.1227 37.6346 15.2073 38.6098C17.7086 39.2069 20.737 39.5564 24 39.5564C27.263 39.5564 30.2914 39.2069 32.7927 38.6098C36.8773 37.6346 39.5563 35.9988 39.5563 34.1455Z"></path>
                    </svg>
                    <span class="logo-text"><?= SITE_NAME ?></span>
                </a>
                <p>Mang những sản phẩm hữu cơ tươi ngon nhất từ trang trại đến bàn ăn của bạn.</p>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h3 class="footer-title">Liên kết nhanh</h3>
                <div class="footer-links">
                    <a href="<?= SITE_URL ?>/about.php">Về chúng tôi</a>
                    <a href="<?= SITE_URL ?>/products.php">Sản phẩm</a>
                    <a href="#">Câu hỏi thường gặp</a>
                    <a href="#">Chính sách bảo mật</a>
                </div>
            </div>
            
            <!-- Contact -->
            <div>
                <h3 class="footer-title">Liên hệ</h3>
                <div class="footer-links">
                    <a href="tel:1900123456">Hotline: 1900 123 456</a>
                    <a href="mailto:info@xanhorganic.vn">info@xanhorganic.vn</a>
                    <span>123 Đường Xanh, Q.1, TP.HCM</span>
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