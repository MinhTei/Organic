<?php
/**
 * auth.php - Đăng nhập và đăng ký với phân quyền
 */

require_once 'config.php';
require_once 'includes/functions.php';

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'login';
$success = '';
$error = '';

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin.';
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_membership'] = $user['membership'];
            $_SESSION['user_role'] = $user['role'];
            
            // Log admin login
            if ($user['role'] === 'admin') {
                $logStmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, description, ip_address) VALUES (:admin_id, :action, :description, :ip)");
                $logStmt->execute([
                    ':admin_id' => $user['id'],
                    ':action' => 'login',
                    ':description' => 'Admin đăng nhập hệ thống',
                    ':ip' => $_SERVER['REMOTE_ADDR']
                ]);
            }
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                // Redirect to admin dashboard
                redirect(SITE_URL . '/admin/dashboard.php');
            } else {
                // Redirect to customer homepage
                redirect(SITE_URL . '/index.php');
            }
        } else {
            $error = 'Email hoặc mật khẩu không đúng.';
        }
    }
}

// Handle Registration (customers only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ.';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } else {
        $conn = getConnection();
        
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        
        if ($stmt->fetch()) {
            $error = 'Email đã được sử dụng.';
        } else {
            // Insert new user (always as customer)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, membership, role) VALUES (:name, :email, :phone, :password, 'bronze', 'customer')");
            
            if ($stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':password' => $hashed_password
            ])) {
                $success = 'Đăng ký thành công! Bạn có thể đăng nhập ngay.';
                $mode = 'login';
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại.';
            }
        }
    }
}

$pageTitle = $mode === 'login' ? 'Đăng nhập' : 'Đăng ký';
include 'includes/header.php';
?>

<section style="padding: 4rem 1rem; min-height: calc(100vh - 400px);">
    <div style="max-width: 500px; margin: 0 auto;">
        
        <!-- Auth Card -->
        <div style="background: white; border-radius: 1rem; padding: 3rem; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
            
            <!-- Logo/Title -->
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="width: 80px; height: 80px; margin: 0 auto 1rem; border-radius: 50%; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-outlined" style="font-size: 3rem; color: white;">eco</span>
                </div>
                <h1 style="font-size: 1.75rem; font-weight: 700; margin-bottom: 0.5rem;">Xanh Organic</h1>
                <p style="color: var(--muted-light);">Đăng nhập để trải nghiệm đầy đủ</p>
            </div>
            
            <!-- Tabs -->
            <div style="display: flex; gap: 0; margin-bottom: 2rem; border-bottom: 2px solid var(--border-light);">
                <a href="?mode=login" 
                   style="flex: 1; text-align: center; padding: 1rem; font-weight: 700; border-bottom: 3px solid <?= $mode === 'login' ? 'var(--primary)' : 'transparent' ?>; color: <?= $mode === 'login' ? 'var(--primary-dark)' : 'var(--muted-light)' ?>; margin-bottom: -2px; transition: all 0.3s;">
                    Đăng nhập
                </a>
                <a href="?mode=register" 
                   style="flex: 1; text-align: center; padding: 1rem; font-weight: 700; border-bottom: 3px solid <?= $mode === 'register' ? 'var(--primary)' : 'transparent' ?>; color: <?= $mode === 'register' ? 'var(--primary-dark)' : 'var(--muted-light)' ?>; margin-bottom: -2px; transition: all 0.3s;">
                    Đăng ký
                </a>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success" style="margin-bottom: 1.5rem;"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom: 1.5rem;"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if ($mode === 'login'): ?>
                <!-- Login Form -->
                <form method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Email</label>
                        <input type="email" name="email" required
                               style="width: 100%; padding: 0.875rem; border: 1px solid var(--border-light); border-radius: 0.5rem; font-size: 1rem;"
                               placeholder="Nhập email của bạn">
                    </div>
                    
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Mật khẩu</label>
                        <input type="password" name="password" required
                               style="width: 100%; padding: 0.875rem; border: 1px solid var(--border-light); border-radius: 0.5rem; font-size: 1rem;"
                               placeholder="Nhập mật khẩu">
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="remember" style="width: 18px; height: 18px; accent-color: var(--primary);">
                            <span style="font-size: 0.875rem;">Ghi nhớ đăng nhập</span>
                        </label>
                        <a href="forgot_password.php" style="font-size: 0.875rem; color: var(--primary-dark); font-weight: 600;">Quên mật khẩu?</a>
                    </div>
                    
                    <button type="submit" name="login" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.125rem;">
                        Đăng nhập
                    </button>
                    
                    <div style="text-align: center; color: var(--muted-light); font-size: 0.875rem;">
                        Chưa có tài khoản? <a href="?mode=register" style="color: var(--primary-dark); font-weight: 600;">Đăng ký ngay</a>
                    </div>
                </form>
                
                <!-- Demo Accounts Info -->
                <div style="margin-top: 2rem; padding: 1rem; background: rgba(182, 230, 51, 0.1); border-radius: 0.5rem; border-left: 4px solid var(--primary);">
                    <p style="font-size: 0.875rem; font-weight: 700; margin-bottom: 0.5rem;">📌 Tài khoản demo:</p>
                    <p style="font-size: 0.75rem; margin: 0.25rem 0;"><strong>Admin:</strong> admin@xanhorganic.vn / admin123</p>
                    <p style="font-size: 0.75rem; margin: 0.25rem 0;"><strong>Khách:</strong> lean@email.com / 123456</p>
                </div>
                
            <?php else: ?>
                <!-- Register Form -->
                <form method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                            Họ và tên <span style="color: var(--danger);">*</span>
                        </label>
                        <input type="text" name="name" required
                               style="width: 100%; padding: 0.875rem; border: 1px solid var(--border-light); border-radius: 0.5rem; font-size: 1rem;"
                               placeholder="Nhập họ và tên">
                    </div>
                    
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                            Email <span style="color: var(--danger);">*</span>
                        </label>
                        <input type="email" name="email" required
                               style="width: 100%; padding: 0.875rem; border: 1px solid var(--border-light); border-radius: 0.5rem; font-size: 1rem;"
                               placeholder="email@example.com">
                    </div>
                    
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Số điện thoại</label>
                        <input type="tel" name="phone"
                               style="width: 100%; padding: 0.875rem; border: 1px solid var(--border-light); border-radius: 0.5rem; font-size: 1rem;"
                               placeholder="0901234567">
                    </div>
                    
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                            Mật khẩu <span style="color: var(--danger);">*</span>
                        </label>
                        <input type="password" name="password" required
                               style="width: 100%; padding: 0.875rem; border: 1px solid var(--border-light); border-radius: 0.5rem; font-size: 1rem;"
                               placeholder="Ít nhất 6 ký tự">
                    </div>
                    
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                            Xác nhận mật khẩu <span style="color: var(--danger);">*</span>
                        </label>
                        <input type="password" name="confirm_password" required
                               style="width: 100%; padding: 0.875rem; border: 1px solid var(--border-light); border-radius: 0.5rem; font-size: 1rem;"
                               placeholder="Nhập lại mật khẩu">
                    </div>
                    
                    <div>
                        <label style="display: flex; align-items: start; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" required style="width: 18px; height: 18px; margin-top: 2px; accent-color: var(--primary);">
                            <span style="font-size: 0.875rem; color: var(--muted-light);">
                                Tôi đồng ý với <a href="#" style="color: var(--primary-dark); font-weight: 600;">Điều khoản dịch vụ</a> 
                                và <a href="#" style="color: var(--primary-dark); font-weight: 600;">Chính sách bảo mật</a>
                            </span>
                        </label>
                    </div>
                    
                    <button type="submit" name="register" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.125rem;">
                        Đăng ký
                    </button>
                    
                    <div style="text-align: center; color: var(--muted-light); font-size: 0.875rem;">
                        Đã có tài khoản? <a href="?mode=login" style="color: var(--primary-dark); font-weight: 600;">Đăng nhập</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        
        <!-- Benefits -->
        <div style="margin-top: 3rem; display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; text-align: center;">
            <div>
                <div style="width: 60px; height: 60px; margin: 0 auto 0.75rem; border-radius: 50%; background: rgba(182, 230, 51, 0.15); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-outlined" style="color: var(--primary-dark); font-size: 2rem;">shopping_bag</span>
                </div>
                <p style="font-size: 0.875rem; color: var(--muted-light);">Đặt hàng dễ dàng</p>
            </div>
            <div>
                <div style="width: 60px; height: 60px; margin: 0 auto 0.75rem; border-radius: 50%; background: rgba(182, 230, 51, 0.15); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-outlined" style="color: var(--primary-dark); font-size: 2rem;">history</span>
                </div>
                <p style="font-size: 0.875rem; color: var(--muted-light);">Theo dõi đơn hàng</p>
            </div>
            <div>
                <div style="width: 60px; height: 60px; margin: 0 auto 0.75rem; border-radius: 50%; background: rgba(182, 230, 51, 0.15); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-outlined" style="color: var(--primary-dark); font-size: 2rem;">card_giftcard</span>
                </div>
                <p style="font-size: 0.875rem; color: var(--muted-light);">Ưu đãi độc quyền</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>