<?php
/**
 * forgot_password.php - Quên mật khẩu
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/email_functions.php';

$success = '';
$error = '';
$step = 'request'; // request, sent, reset

//      Xử lý form gửi yêu cầu đặt lại mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_reset'])) {
    $email = sanitize($_POST['email']);
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Vui lòng nhập email hợp lệ.';
    } else {
        $conn = getConnection();
        
        //  Kiểm tra email có tồn tại không
        $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        
        if ($user) {
            //  Tạo token đặt lại mật khẩu và lưu vào database
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            //  Xóa các token cũ nếu có
            $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = :email");
            $stmt->execute([':email' => $email]);
            
            //  Lưu token mới
            $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)");
            $stmt->execute([
                ':email' => $email,
                ':token' => $token,
                ':expires_at' => $expiresAt
            ]);
            
            //  Tạo link đặt lại mật khẩu
            $resetLink = SITE_URL . "/reset_password.php?token=" . $token;
            
            //      Gửi email đặt lại mật khẩu
            $subject = "Đặt lại mật khẩu - " . SITE_NAME;
            $message = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #b6e633 0%, #9acc2a 100%); padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                    .header h1 { color: white; margin: 0; font-size: 24px; }
                    .content { background: #ffffff; padding: 30px; border: 1px solid #e3e5dc; border-top: none; }
                    .button { display: inline-block; padding: 12px 30px; background: #b6e633; color: #161811; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 20px 0; }
                    .footer { text-align: center; padding: 20px; color: #7e8863; font-size: 12px; }
                    .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px; margin: 20px 0; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>🌱 ' . SITE_NAME . '</h1>
                    </div>
                    <div class="content">
                        <p>Xin chào <strong>' . htmlspecialchars($user['name']) . '</strong>,</p>
                        
                        <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
                        
                        <p>Vui lòng nhấn nút dưới đây để đặt lại mật khẩu:</p>
                        
                        <center>
                            <a href="' . htmlspecialchars($resetLink) . '" class="button">Đặt lại mật khẩu</a>
                        </center>
                        
                        <p>Hoặc sao chép liên kết này vào trình duyệt của bạn:</p>
                        <p style="word-break: break-all; color: #2563eb;">
                            ' . htmlspecialchars($resetLink) . '
                        </p>
                        
                        <div class="warning">
                            <strong>⚠️ Lưu ý:</strong> Link này sẽ hết hạn sau 24 giờ. Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.
                        </div>
                        
                        <p style="margin-top: 2rem; color: #7e8863;">
                            Trân trọng,<br>
                            <strong>' . SITE_NAME . '</strong>
                        </p>
                    </div>
                    <div class="footer">
                        <p>© ' . date('Y') . ' ' . SITE_NAME . '. Tất cả quyền được bảo lưu.</p>
                    </div>
                </div>
            </body>
            </html>';
            
            // Send email using sendEmail function with fallback to mail()
            if (sendEmail($email, $subject, $message)) {
                $success = '✅ Email đặt lại mật khẩu đã được gửi! Vui lòng kiểm tra hộp thư của bạn.';
                $step = 'sent';
            } else {
                $error = 'Không thể gửi email. Vui lòng thử lại sau.';
            }
        } else {
            // Email không tồn tại - hiển thị lỗi
            $error = 'Email này không được đăng ký trong hệ thống. Vui lòng kiểm tra lại hoặc <a href="' . SITE_URL . '/auth.php" style="color: var(--primary-dark); font-weight: 600;">đăng ký tài khoản mới</a>.';
        }
    }
}

$pageTitle = 'Quên mật khẩu';
include __DIR__ . '/includes/header.php';
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
            
            <?php if ($success): ?>
                <div class="alert alert-success" style="margin-bottom: 1.5rem; background-color: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; padding: 1rem; border-radius: 0.5rem;"><?= $success ?></div>
            <?php endif; ?>
            
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

<?php include __DIR__ . '/includes/footer.php'; ?>