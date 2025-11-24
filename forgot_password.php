<?php
/**
 * forgot_password.php - Quên mật khẩu
 */

require_once __DIR__ . '/includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/email_functions.php';

$success = '';
$error = '';
$step = 'request'; // request, sent, reset

// Handle password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_reset'])) {
    $email = sanitize($_POST['email']);
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Vui lòng nhập email hợp lệ.';
    } else {
        $conn = getConnection();
        
        // Check if email exists
        $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+3 minutes'));
            // Delete old tokens for this email
            $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = :email");
            $stmt->execute([':email' => $email]);
            // Insert new token
            $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)");
            $stmt->execute([
                ':email' => $email,
                ':token' => $token,
                ':expires_at' => $expiresAt
            ]);
            // Tạo link reset để hiển thị ngay bên dưới ô nhập email
            $resetLink = SITE_URL . "/reset_password.php?token=" . $token;
            $showResetLink = true;
        } else {
            $showResetLink = false;
        }
    }
}

$pageTitle = 'Quên mật khẩu';
include 'includes/header.php';
?>

<section style="padding: 4rem 1rem; min-height: calc(100vh - 400px); display: flex; align-items: center; justify-content: center;">
    <div style="max-width: 500px; width: 100%;">
        
        <!-- Forgot Password Card -->
        <div style="background: white; border-radius: 1rem; padding: 3rem; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
            
            <!-- Logo/Title -->
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="width: 80px; height: 80px; margin: 0 auto 1rem; border-radius: 50%; background: rgba(182, 230, 51, 0.2); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-outlined" style="font-size: 3rem; color: var(--primary-dark);">lock_reset</span>
                </div>
                <h1 style="font-size: 1.75rem; font-weight: 700; margin-bottom: 0.5rem;">Quên mật khẩu?</h1>
                <p style="color: var(--muted-light);">Nhập email để nhận link đặt lại mật khẩu</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom: 1.5rem;"><?= $error ?></div>
            <?php endif; ?>
            
            <!-- Luôn hiển thị form nhập email và link reset nếu có -->
            <form method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Email</label>
                    <input type="email" name="email" required
                           style="width: 100%; padding: 0.875rem; border: 1px solid var(--border-light); border-radius: 0.5rem; font-size: 1rem;"
                           placeholder="Nhập email đã đăng ký"
                           value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
                    <?php if (isset($showResetLink) && $showResetLink && isset($resetLink)): ?>
                        <div style="margin-top: 0.75rem;">
                            <span style="font-size: 0.95rem; color: #22c55e; font-weight: 600;">Link đặt lại mật khẩu của bạn:</span><br>
                            <a href="<?= htmlspecialchars($resetLink) ?>" style="word-break: break-all; color: #2563eb; text-decoration: underline;">
                                <?= htmlspecialchars($resetLink) ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="submit" name="request_reset" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.125rem;">
                    Gửi link đặt lại mật khẩu
                </button>
                <div style="text-align: center; color: var(--muted-light); font-size: 0.875rem;">
                    <a href="<?= SITE_URL ?>/auth.php" style="color: var(--primary-dark); font-weight: 600;">← Quay lại đăng nhập</a>
                </div>
            </form>
            <!-- Không còn giao diện kiểm tra email, chỉ còn form và link reset nếu có -->
        </div>
        
        <!-- Help Text -->
        <div style="margin-top: 2rem; text-align: center; color: var(--muted-light); font-size: 0.875rem;">
            <p>Link đặt lại mật khẩu sẽ hết hạn sau 1 giờ</p>
            <p style="margin-top: 0.5rem;">
                Cần trợ giúp? <a href="<?= SITE_URL ?>/contact.php" style="color: var(--primary-dark); font-weight: 600;">Liên hệ hỗ trợ</a>
            </p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>