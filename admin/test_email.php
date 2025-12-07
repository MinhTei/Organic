<?php
/**
 * admin/test_email.php - Test SMTP Email Connection
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    redirect(SITE_URL . '/auth.php');
}

$test_result = null;
$test_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_smtp'])) {
    $recipient_email = sanitize($_POST['recipient_email'] ?? '');
    
    if (empty($recipient_email) || !filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
        $test_result = 'error';
        $test_message = 'Vui lòng nhập email hợp lệ';
    } else {
        // Get SMTP config from .env
        $smtp_host = getenv('SMTP_HOST');
        $smtp_port = getenv('SMTP_PORT');
        $smtp_username = getenv('SMTP_USERNAME');
        $smtp_password = getenv('SMTP_PASSWORD');
        
        // Test email content
        $subject = "Test Email - " . SITE_NAME . " - " . date('Y-m-d H:i:s');
        $message = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .success { background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; padding: 15px; border-radius: 6px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="success">
            <h2>✅ Test Email Success!</h2>
            <p>Nếu bạn nhận được email này, SMTP đã được cấu hình đúng.</p>
            <p><strong>Gửi từ:</strong> ' . SITE_NAME . '</p>
            <p><strong>Thời gian:</strong> ' . date('Y-m-d H:i:s') . '</p>
        </div>
    </div>
</body>
</html>';

        // Try to send test email
        try {
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                
                $mail->isSMTP();
                $mail->Host = $smtp_host ?: 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $smtp_username;
                $mail->Password = $smtp_password;
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $smtp_port ?: 587;
                $mail->setFrom(SITE_EMAIL, SITE_NAME);
                $mail->addAddress($recipient_email);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $message;
                $mail->CharSet = 'UTF-8';
                
                if ($mail->send()) {
                    $test_result = 'success';
                    $test_message = '✅ Email đã gửi thành công đến: ' . htmlspecialchars($recipient_email) . ' 
                    <br><br>
                    <strong>SMTP Details:</strong><br>
                    Host: ' . htmlspecialchars($smtp_host) . '<br>
                    Port: ' . htmlspecialchars($smtp_port) . '<br>
                    Username: ' . htmlspecialchars($smtp_username);
                } else {
                    $test_result = 'error';
                    $test_message = 'Lỗi PHPMailer: ' . $mail->ErrorInfo;
                }
            } else {
                $test_result = 'error';
                $test_message = 'PHPMailer không được cài đặt. Chạy: composer require phpmailer/phpmailer';
            }
        } catch (\Exception $e) {
            $test_result = 'error';
            $test_message = 'Lỗi: ' . $e->getMessage() . 
                            '<br><br><strong>SMTP Config:</strong><br>' .
                            'Host: ' . htmlspecialchars($smtp_host) . '<br>' .
                            'Port: ' . htmlspecialchars($smtp_port) . '<br>' .
                            'Username: ' . htmlspecialchars($smtp_username) . '<br>' .
                            'Password: ' . (empty($smtp_password) ? '❌ KHÔNG CÓ' : '✅ Có giá trị');
        }
    }
}

$pageTitle = 'Test Email Configuration';
include __DIR__ . '/../includes/header.php';
?>

<section style="padding: 2rem 1rem; min-height: calc(100vh - 400px);">
    <div style="max-width: 800px; margin: 0 auto;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 style="font-size: 2rem; font-weight: 700;">📧 Test SMTP Email</h1>
            <a href="<?= SITE_URL ?>/admin/dashboard.php" class="btn btn-secondary" style="padding: 0.75rem 1.5rem;">← Back</a>
        </div>
        
        <div style="background: white; border-radius: 1rem; padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            
            <h2 style="margin-top: 0; color: var(--primary-dark);">Current SMTP Configuration</h2>
            
            <div style="background: #f5f5f5; padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem; font-family: monospace; font-size: 0.9rem;">
                <div style="margin-bottom: 0.75rem;">
                    <strong>SMTP_HOST:</strong> 
                    <?php 
                        $host = getenv('SMTP_HOST');
                        echo empty($host) ? '<span style="color: #ef4444;">❌ NOT SET</span>' : '<span style="color: #22c55e;">✅ ' . htmlspecialchars($host) . '</span>';
                    ?>
                </div>
                <div style="margin-bottom: 0.75rem;">
                    <strong>SMTP_PORT:</strong> 
                    <?php 
                        $port = getenv('SMTP_PORT');
                        echo empty($port) ? '<span style="color: #ef4444;">❌ NOT SET</span>' : '<span style="color: #22c55e;">✅ ' . htmlspecialchars($port) . '</span>';
                    ?>
                </div>
                <div style="margin-bottom: 0.75rem;">
                    <strong>SMTP_USERNAME:</strong> 
                    <?php 
                        $user = getenv('SMTP_USERNAME');
                        echo empty($user) ? '<span style="color: #ef4444;">❌ NOT SET</span>' : '<span style="color: #22c55e;">✅ ' . htmlspecialchars($user) . '</span>';
                    ?>
                </div>
                <div>
                    <strong>SMTP_PASSWORD:</strong> 
                    <?php 
                        $pass = getenv('SMTP_PASSWORD');
                        echo empty($pass) ? '<span style="color: #ef4444;">❌ NOT SET</span>' : '<span style="color: #22c55e;">✅ (Set - ' . strlen($pass) . ' characters)</span>';
                    ?>
                </div>
            </div>
            
            <?php if ($test_result === 'success'): ?>
                <div style="background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem;">
                    <?= $test_message ?>
                </div>
            <?php elseif ($test_result === 'error'): ?>
                <div style="background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem;">
                    <strong>❌ Error:</strong><br>
                    <?= $test_message ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div style="background: white; border-radius: 1rem; padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            
            <h2 style="margin-top: 0;">Send Test Email</h2>
            
            <form method="POST">
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Recipient Email</label>
                    <input type="email" name="recipient_email" required
                           style="width: 100%; padding: 0.875rem; border: 1px solid var(--border-light); border-radius: 0.5rem; font-size: 1rem; box-sizing: border-box;"
                           placeholder="test@example.com"
                           value="<?= isset($_POST['recipient_email']) ? htmlspecialchars($_POST['recipient_email']) : '' ?>">
                    <small style="color: var(--muted-light); display: block; margin-top: 0.25rem;">Email nơi bạn muốn nhận test email</small>
                </div>
                
                <button type="submit" name="test_smtp" class="btn btn-primary" 
                        style="padding: 0.875rem 2rem; font-size: 1rem; background: var(--primary-color); color: white; border: none; border-radius: 0.5rem; cursor: pointer;">
                    🚀 Send Test Email
                </button>
            </form>
        </div>
        
        <div style="margin-top: 2rem; background: #fef3c7; border-left: 4px solid #f59e0b; padding: 1.5rem; border-radius: 0.5rem;">
            <h3 style="margin-top: 0;">💡 Hướng dẫn cấu hình</h3>
            <ol style="margin: 0; padding-left: 1.5rem;">
                <li>Mở file <code>.env</code> ở thư mục gốc</li>
                <li>Thay đổi các giá trị SMTP_* với thông tin Gmail:
                    <ul>
                        <li>SMTP_HOST: smtp.gmail.com</li>
                        <li>SMTP_PORT: 587</li>
                        <li>SMTP_USERNAME: your-email@gmail.com</li>
                        <li>SMTP_PASSWORD: (16 ký tự từ Google App Passwords)</li>
                    </ul>
                </li>
                <li>Lưu file .env</li>
                <li>Nhập email test ở trên và click "Send Test Email"</li>
                <li>Kiểm tra email (có thể vào Spam)</li>
            </ol>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
