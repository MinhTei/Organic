<?php
/**
 * reset_password.php - Đặt lại mật khẩu mới
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$success = '';
$error = '';
$validToken = false;
$email = '';

// Get and validate token
$token = isset($_GET['token']) ? sanitize($_GET['token']) : '';

if (empty($token)) {
    $error = 'Link không hợp lệ.';
} else {
    $conn = getConnection();
    
    // Check if token exists and not expired
    $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = :token AND expires_at > NOW()");
    $stmt->execute([':token' => $token]);
    $reset = $stmt->fetch();
    
    if ($reset) {
        $validToken = true;
        $email = $reset['email'];
    } else {
        $error = 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.';
    }
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password']) && $validToken) {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validation
    if (empty($password)) {
        $error = 'Vui lòng nhập mật khẩu mới.';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Update user password
        $stmt = $conn->prepare("UPDATE users SET password = :password WHERE email = :email");
        if ($stmt->execute([':password' => $hashedPassword, ':email' => $email])) {
            // Delete used token
            $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = :email");
            $stmt->execute([':email' => $email]);
            
            $success = 'Đặt lại mật khẩu thành công! Bạn có thể đăng nhập với mật khẩu mới.';
            $validToken = false; // Prevent form from showing again
        } else {
            $error = 'Có lỗi xảy ra, vui lòng thử lại.';
        }
    }
}

$pageTitle = 'Đặt lại mật khẩu';
include __DIR__ . '/includes/header.php';
?>

<section style="padding: 4rem 1rem; min-height: calc(100vh - 400px); display: flex; align-items: center; justify-content: center;">
    <div style="max-width: 500px; width: 100%;">
        
        <!-- Reset Password Card -->
        <div style="background: white; border-radius: 1rem; padding: 3rem; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
            
            <!-- Logo/Title -->
            <div style="text-align: center; margin-bottom: 2rem;">
                <?php if ($success): ?>
                    <div style="width: 80px; height: 80px; margin: 0 auto 1rem; border-radius: 50%; background: rgba(34, 197, 94, 0.2); display: flex; align-items: center; justify-content: center;">
                        <span class="material-symbols-outlined" style="font-size: 3rem; color: #22c55e;">check_circle</span>
                    </div>
                    <h1 style="font-size: 1.75rem; font-weight: 700; margin-bottom: 0.5rem;">Thành công!</h1>
                <?php else: ?>
                    <div style="width: 80px; height: 80px; margin: 0 auto 1rem; border-radius: 50%; background: rgba(182, 230, 51, 0.2); display: flex; align-items: center; justify-content: center;">
                        <span class="material-symbols-outlined" style="font-size: 3rem; color: var(--primary-dark);">vpn_key</span>
                    </div>
                    <h1 style="font-size: 1.75rem; font-weight: 700; margin-bottom: 0.5rem;">Đặt lại mật khẩu</h1>
                    <p style="color: var(--muted-light);">Nhập mật khẩu mới cho tài khoản của bạn</p>
                <?php endif; ?>
            </div>
            
            <?php if ($success): ?>
                <!-- Success Message -->
                <div class="alert alert-success" style="margin-bottom: 1.5rem;"><?= $success ?></div>
                
                <div style="text-align: center;">
                    <a href="<?= SITE_URL ?>/auth.php" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.125rem; display: inline-block;">
                        Đăng nhập ngay
                    </a>
                </div>
                
            <?php elseif ($error): ?>
                <!-- Error Message -->
                <div class="alert alert-error" style="margin-bottom: 1.5rem;"><?= $error ?></div>
                
                <div style="text-align: center;">
                    <a href="<?= SITE_URL ?>/forgot_password.php" class="btn btn-secondary" style="display: inline-block; margin-bottom: 1rem;">
                        Yêu cầu link mới
                    </a>
                    <br>
                    <a href="<?= SITE_URL ?>/auth.php" style="color: var(--primary-dark); font-weight: 600; font-size: 0.875rem;">
                        ← Quay lại đăng nhập
                    </a>
                </div>
                
            <?php elseif ($validToken): ?>
                <!-- Reset Form -->
                <form method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                            Mật khẩu mới <span style="color: var(--danger);">*</span>
                        </label>
                        <div style="position: relative;">
                            <input type="password" name="password" id="password" required
                                   style="width: 100%; padding: 0.875rem; padding-right: 3rem; border: 1px solid var(--border-light); border-radius: 0.5rem; font-size: 1rem;"
                                   placeholder="Ít nhất 6 ký tự">
                            <button type="button" onclick="togglePassword('password')" 
                                    style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;">
                                <span class="material-symbols-outlined" style="color: var(--muted-light);">visibility</span>
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                            Xác nhận mật khẩu <span style="color: var(--danger);">*</span>
                        </label>
                        <div style="position: relative;">
                            <input type="password" name="confirm_password" id="confirm_password" required
                                   style="width: 100%; padding: 0.875rem; padding-right: 3rem; border: 1px solid var(--border-light); border-radius: 0.5rem; font-size: 1rem;"
                                   placeholder="Nhập lại mật khẩu">
                            <button type="button" onclick="togglePassword('confirm_password')" 
                                    style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;">
                                <span class="material-symbols-outlined" style="color: var(--muted-light);">visibility</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Password Strength Indicator -->
                    <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 0.5rem; padding: 0.75rem;">
                        <p style="font-size: 0.75rem; color: #1e40af; margin-bottom: 0.5rem; font-weight: 600;">Mật khẩu mạnh nên có:</p>
                        <ul style="font-size: 0.75rem; color: #1e40af; margin-left: 1.5rem;">
                            <li>Ít nhất 6 ký tự</li>
                            <li>Kết hợp chữ hoa và chữ thường</li>
                            <li>Có ít nhất 1 số</li>
                        </ul>
                    </div>
                    
                    <button type="submit" name="reset_password" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.125rem;">
                        Đặt lại mật khẩu
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.nextElementSibling;
    const icon = button.querySelector('.material-symbols-outlined');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.textContent = 'visibility_off';
    } else {
        field.type = 'password';
        icon.textContent = 'visibility';
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>