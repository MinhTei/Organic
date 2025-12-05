<?php
/**
 * contact.php - Trang liên hệ
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    // Validation
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ.';
    } else {
        // Lưu vào bảng contact_messages
        try {
            $conn = getConnection();
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, created_at, status) VALUES (?, ?, ?, ?, ?, NOW(), 'pending')");
            $stmt->execute([$name, $email, $phone, $subject, $message]);
            $success = 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong thời gian sớm nhất.';
            // Clear form
            $name = $email = $phone = $subject = $message = '';
        } catch (PDOException $e) {
            $error = 'Lỗi lưu tin nhắn: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Liên Hệ';
include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section style="padding: 3rem 1rem; background: rgba(182, 230, 51, 0.05);">
    <div style="max-width: 1280px; margin: 0 auto; text-align: center;">
        <h1 style="font-size: 3rem; font-weight: 900; margin-bottom: 1rem;">Liên Hệ Với Chúng Tôi</h1>
        <p style="font-size: 1.125rem; color: var(--muted-light);">
            Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn
        </p>
    </div>
</section>

<!-- Contact Content -->
<section style="padding: 4rem 1rem;">
    <div style="max-width: 1280px; margin: 0 auto;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem;">
            
            <!-- Contact Form -->
            <div>
                <h2 style="font-size: 2rem; font-weight: 700; margin-bottom: 1rem;">Gửi Tin Nhắn</h2>
                <p style="color: var(--muted-light); margin-bottom: 2rem;">
                    Điền thông tin vào form dưới đây, chúng tôi sẽ phản hồi trong vòng 24 giờ.
                </p>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                            Họ và tên <span style="color: var(--danger);">*</span>
                        </label>
                        <input type="text" name="name" required
                               value="<?= htmlspecialchars($name ?? '') ?>"
                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem; font-size: 1rem;"
                               placeholder="Nhập họ và tên của bạn">
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                                Email <span style="color: var(--danger);">*</span>
                            </label>
                            <input type="email" name="email" required
                                   value="<?= htmlspecialchars($email ?? '') ?>"
                                   style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem; font-size: 1rem;"
                                   placeholder="email@example.com">
                        </div>
                        
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                                Số điện thoại
                            </label>
                            <input type="tel" name="phone"
                                   value="<?= htmlspecialchars($phone ?? '') ?>"
                                   style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem; font-size: 1rem;"
                                   placeholder="0901234567">
                        </div>
                    </div>
                    
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                            Chủ đề
                        </label>
                        <select name="subject"
                                style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem; font-size: 1rem;">
                            <option value="">Chọn chủ đề</option>
                            <option value="product">Thắc mắc về sản phẩm</option>
                            <option value="order">Đơn hàng của tôi</option>
                            <option value="partnership">Hợp tác kinh doanh</option>
                            <option value="feedback">Góp ý, phản hồi</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>
                    
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                            Nội dung <span style="color: var(--danger);">*</span>
                        </label>
                        <textarea name="message" rows="6" required
                                  style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem; font-size: 1rem; resize: vertical;"
                                  placeholder="Nhập nội dung tin nhắn..."><?= htmlspecialchars($message ?? '') ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.125rem;">
                        <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 0.5rem;">send</span>
                        Gửi tin nhắn
                    </button>
                </form>
            </div>
            
            <!-- Contact Info -->
            <div>
                <h2 style="font-size: 2rem; font-weight: 700; margin-bottom: 1rem;">Thông Tin Liên Hệ</h2>
                <p style="color: var(--muted-light); margin-bottom: 2rem;">
                    Bạn có thể liên hệ với chúng tôi qua các kênh sau:
                </p>
                
                <div style="display: flex; flex-direction: column; gap: 2rem;">
                    <!-- Address -->
                    <div style="display: flex; gap: 1rem;">
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(182, 230, 51, 0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <span class="material-symbols-outlined" style="color: var(--primary-dark);">location_on</span>
                        </div>
                        <div>
                            <h3 style="font-weight: 700; margin-bottom: 0.5rem;">Địa chỉ</h3>
                            <p style="color: var(--muted-light);">
                                <?= function_exists('getSystemSetting') ? nl2br(htmlspecialchars(getSystemSetting('site_address', '123 Đường Xanh, Phường 1, Quận 1, TP. Hồ Chí Minh'))) : '123 Đường Xanh, Phường 1, Quận 1, TP. Hồ Chí Minh' ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Phone -->
                    <div style="display: flex; gap: 1rem;">
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(182, 230, 51, 0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <span class="material-symbols-outlined" style="color: var(--primary-dark);">call</span>
                        </div>
                        <div>
                            <h3 style="font-weight: 700; margin-bottom: 0.5rem;">Điện thoại</h3>
                            <p style="color: var(--muted-light);">
                                Hotline: <a href="tel:<?= function_exists('getSystemSetting') ? htmlspecialchars(getSystemSetting('site_phone', '1900123456')) : '1900123456' ?>" style="color: var(--primary-dark); font-weight: 600;">
                                    <?= function_exists('getSystemSetting') ? htmlspecialchars(getSystemSetting('site_phone', '1900 123 456')) : '1900 123 456' ?>
                                </a><br>
                                (Thứ 2 - Chủ nhật, 8:00 - 22:00)
                            </p>
                        </div>
                    </div>
                    
                    <!-- Email -->
                    <div style="display: flex; gap: 1rem;">
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(182, 230, 51, 0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <span class="material-symbols-outlined" style="color: var(--primary-dark);">mail</span>
                        </div>
                        <div>
                            <h3 style="font-weight: 700; margin-bottom: 0.5rem;">Email</h3>
                            <p style="color: var(--muted-light);">
                                <a href="mailto:<?= function_exists('getSystemSetting') ? htmlspecialchars(getSystemSetting('site_email', 'info@xanhorganic.vn')) : 'info@xanhorganic.vn' ?>" style="color: var(--primary-dark); font-weight: 600;">
                                    <?= function_exists('getSystemSetting') ? htmlspecialchars(getSystemSetting('site_email', 'info@xanhorganic.vn')) : 'info@xanhorganic.vn' ?>
                                </a>
                                <?php if (function_exists('getSystemSetting') && getSystemSetting('support_email')): ?>
                                    <br><a href="mailto:<?= htmlspecialchars(getSystemSetting('support_email')) ?>" style="color: var(--primary-dark); font-weight: 600;">
                                        <?= htmlspecialchars(getSystemSetting('support_email')) ?>
                                    </a>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Social Media -->
                    <div style="display: flex; gap: 1rem;">
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(182, 230, 51, 0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <span class="material-symbols-outlined" style="color: var(--primary-dark);">share</span>
                        </div>
                        <div>
                            <h3 style="font-weight: 700; margin-bottom: 0.5rem;">Mạng xã hội</h3>
                            <div style="display: flex; gap: 1rem; margin-top: 0.75rem;">
                                <?php if (function_exists('getSystemSetting') && getSystemSetting('social_facebook')): ?>
                                    <a href="<?= htmlspecialchars(getSystemSetting('social_facebook')) ?>" target="_blank" style="width: 40px; height: 40px; border-radius: 50%; background: #1877f2; display: flex; align-items: center; justify-content: center; color: white;">
                                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                        </svg>
                                    </a>
                                <?php endif; ?>
                                <?php if (function_exists('getSystemSetting') && getSystemSetting('social_instagram')): ?>
                                    <a href="<?= htmlspecialchars(getSystemSetting('social_instagram')) ?>" target="_blank" style="width: 40px; height: 40px; border-radius: 50%; background: #E4405F; display: flex; align-items: center; justify-content: center; color: white;">
                                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                        </svg>
                                    </a>
                                <?php endif; ?>
                                <?php if (function_exists('getSystemSetting') && getSystemSetting('social_zalo')): ?>
                                    <a href="<?= htmlspecialchars(getSystemSetting('social_zalo')) ?>" target="_blank" style="width: 40px; height: 40px; border-radius: 50%; background: #0068ff; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.25rem;">Z</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Map -->
                <div style="margin-top: 3rem;">
                    <h3 style="font-weight: 700; margin-bottom: 1rem;">Vị trí của chúng tôi</h3>
                    <div style="width: 100%; height: 300px; border-radius: 1rem; overflow: hidden; border: 1px solid var(--border-light);">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.4943078086524!2d106.69746731533459!3d10.77264926226956!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f4b3330bcc7%3A0xc1fd2781577bff14!2zVHLGsOG7nW5nIMSQ4bqhaSBI4buNYyBLaG9hIEjhu41jIFThu7Egbmhpw6puIFRQLiBIQ00!5e0!3m2!1svi!2s!4v1234567890123!5m2!1svi!2s" 
                                width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section style="padding: 4rem 1rem; background: rgba(182, 230, 51, 0.05);">
    <div style="max-width: 900px; margin: 0 auto;">
        <h2 style="font-size: 2rem; font-weight: 700; margin-bottom: 2rem; text-align: center;">Câu Hỏi Thường Gặp</h2>
        
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <details style="background: white; padding: 1.5rem; border-radius: 0.75rem; cursor: pointer;">
                <summary style="font-weight: 700; font-size: 1.125rem;">Làm sao để đặt hàng?</summary>
                <p style="margin-top: 1rem; color: var(--muted-light); line-height: 1.6;">
                    Bạn có thể đặt hàng trực tiếp trên website hoặc gọi hotline 1900 123 456. Chúng tôi hỗ trợ đặt hàng 24/7.
                </p>
            </details>
            
            <details style="background: white; padding: 1.5rem; border-radius: 0.75rem; cursor: pointer;">
                <summary style="font-weight: 700; font-size: 1.125rem;">Thời gian giao hàng là bao lâu?</summary>
                <p style="margin-top: 1rem; color: var(--muted-light); line-height: 1.6;">
                    Đơn hàng trong nội thành TP.HCM sẽ được giao trong vòng 24h. Các tỉnh thành khác từ 2-3 ngày.
                </p>
            </details>
            
            <details style="background: white; padding: 1.5rem; border-radius: 0.75rem; cursor: pointer;">
                <summary style="font-weight: 700; font-size: 1.125rem;">Sản phẩm có được chứng nhận hữu cơ không?</summary>
                <p style="margin-top: 1rem; color: var(--muted-light); line-height: 1.6;">
                    Tất cả sản phẩm của chúng tôi đều được chứng nhận hữu cơ VietGAP và đáp ứng tiêu chuẩn quốc tế.
                </p>
            </details>
            
            <details style="background: white; padding: 1.5rem; border-radius: 0.75rem; cursor: pointer;">
                <summary style="font-weight: 700; font-size: 1.125rem;">Chính sách đổi trả như thế nào?</summary>
                <p style="margin-top: 1rem; color: var(--muted-light); line-height: 1.6;">
                    Chúng tôi chấp nhận đổi trả trong vòng 24h nếu sản phẩm có vấn đề về chất lượng hoặc giao sai hàng.
                </p>
            </details>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>