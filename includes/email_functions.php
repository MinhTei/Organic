<?php

/**
 * email_functions.php - Các hàm gửi email
 * 
 * Hỗ trợ:
 * - Gửi email xác nhận đơn hàng
 * - Gửi email đặt lại mật khẩu
 * - Fallback: PHPMailer → mail() → lưu file (development mode)
 */

/**
 * Gửi email xác nhận đơn hàng
 * 
 * @param string $email Email người nhận
 * @param string $name Tên người nhận
 * @param int $orderId ID đơn hàng
 * @param float $orderTotal Tổng tiền
 * @return bool
 */
function sendOrderConfirmationEmail($email, $name, $orderId, $orderTotal)
{
    $subject = "Xác nhận đơn hàng #$orderId - " . SITE_NAME;

    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f5f5f5; }
            .container { max-width: 650px; margin: 0 auto; background: #fff; }
            .hero { background: linear-gradient(135deg, #a8e6c1 0%, #56d679 50%, #2ecc71 100%); padding: 40px 30px; text-align: center; }
            .hero-icon { font-size: 50px; margin-bottom: 15px; }
            .hero h1 { color: #fff; margin: 0; font-size: 28px; font-weight: bold; letter-spacing: 0.5px; }
            .hero p { color: rgba(255,255,255,0.95); margin: 8px 0 0 0; font-size: 14px; }
            .content { padding: 30px; }
            .section { background: #f8fffe; border: 1px solid #e8f5f0; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
            .section-title { display: flex; align-items: center; gap: 10px; color: #2d3436; font-size: 16px; font-weight: bold; margin: 0 0 15px 0; }
            .section-title-icon { font-size: 20px; }
            .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
            .info-label { color: #636e72; font-size: 12px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 3px; }
            .info-value { color: #2d3436; font-size: 14px; font-weight: 600; }
            .info-value.highlight { color: #2ecc71; font-size: 18px; }
            .info-full .info-label { color: #636e72; font-size: 12px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 3px; }
            .info-full .info-value { color: #2d3436; font-size: 14px; font-weight: 600; margin-bottom: 8px; }
            .steps { background: #f8fffe; border: 1px solid #e8f5f0; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
            .steps h3 { display: flex; align-items: center; gap: 10px; color: #2d3436; font-size: 16px; font-weight: bold; margin: 0 0 12px 0; }
            .steps ul { margin: 0; padding-left: 20px; }
            .steps li { color: #636e72; font-size: 13px; margin: 8px 0; line-height: 1.5; }
            .button-wrap { text-align: center; margin: 25px 0; }
            .button { display: inline-block; padding: 13px 32px; background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); color: white; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; border: none; cursor: pointer; }
            .footer { padding: 20px 30px; border-top: 1px solid #e8f5f0; text-align: center; color: #636e72; font-size: 12px; }
            .footer p { margin: 5px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="hero">
                <div class="hero-icon">✅</div>
                <h1>Đặt Hàng Thành Công!</h1>
                <p>Cảm ơn bạn đã tin tưởng và mua sắm tại ' . htmlspecialchars(SITE_NAME) . '</p>
            </div>

            <div class="content">
                <p style="color: #636e72; font-size: 14px; margin: 0 0 20px 0;">Xin chào <strong>' . htmlspecialchars($name) . '</strong>,</p>

                <div class="section">
                    <h3 class="section-title">
                        <span class="section-title-icon">📦</span>
                        Thông Tin Đơn Hàng
                    </h3>
                    <div class="info-grid">
                        <div>
                            <div class="info-label">Mã đơn hàng</div>
                            <div class="info-value">#' . htmlspecialchars($orderId) . '</div>
                        </div>
                        <div>
                            <div class="info-label">Ngày đặt hàng</div>
                            <div class="info-value">' . date('d/m/Y H:i') . '</div>
                        </div>
                        <div>
                            <div class="info-label">Tổng tiền</div>
                            <div class="info-value highlight">' . formatPrice($orderTotal) . '</div>
                        </div>
                        <div>
                            <div class="info-label">Phương thức</div>
                            <div class="info-value">Thanh toán khi nhận hàng</div>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h3 class="section-title">
                        <span class="section-title-icon">👤</span>
                        Thông Tin Nhận Hàng
                    </h3>
                    <div class="info-full">
                        <div class="info-label">Người nhận</div>
                        <div class="info-value">' . htmlspecialchars($name) . '</div>
                    </div>
                </div>

                <div class="steps">
                    <h3>
                        <span class="section-title-icon">📋</span>
                        Các Bước Tiếp Theo
                    </h3>
                    <ul>
                        <li>Chúng tôi sẽ xác nhận chi tiết đơn hàng trong vòng 30 phút</li>
                        <li>Đơn hàng sẽ được chuẩn bị và đóng gói cẩn thận</li>
                        <li>Giao hàng nhanh chóng (2-4 giờ tại TP.HCM)</li>
                        <li>Bạn sẽ nhận thông báo qua email khi đơn hàng được giao</li>
                    </ul>
                </div>

                <div class="button-wrap">
                    <a href="' . SITE_URL . '/user_info.php?tab=orders" class="button">Xem Chi Tiết Đơn Hàng</a>
                </div>

                <p style="color: #636e72; font-size: 13px; line-height: 1.6; margin: 20px 0 0 0;">Nếu có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi. Chúng tôi luôn sẵn sàng hỗ trợ!</p>
            </div>

            <div class="footer">
                <p><strong>Trân trọng,</strong></p>
                <p>Đội ngũ ' . htmlspecialchars(SITE_NAME) . '</p>
                <p style="margin-top: 10px; color: #adb5bd;">&copy; ' . date('Y') . ' ' . htmlspecialchars(SITE_NAME) . '. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';

    return sendEmail($email, $subject, $message);
}

/**
 * Gửi email đặt lại mật khẩu
 * 
 * @param string $email Email người nhận
 * @param string $name Tên người dùng
 * @param string $resetLink Link đặt lại mật khẩu
 * @return bool
 */
function sendPasswordResetEmail($email, $name, $resetLink)
{
    $subject = "Đặt lại mật khẩu - " . SITE_NAME;

    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f5f5f5; }
            .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; }
            .header { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); padding: 30px; text-align: center; border-radius: 8px; margin-bottom: 20px; }
            .header h1 { color: white; margin: 0; font-size: 24px; }
            .content { color: #333; }
            .button { display: inline-block; padding: 12px 30px; background: #2ecc71; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 20px 0; }
            .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px; }
            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; text-align: center; color: #636e72; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🔐 Đặt Lại Mật Khẩu</h1>
            </div>

            <div class="content">
                <p>Xin chào <strong>' . htmlspecialchars($name) . '</strong>,</p>
                
                <p>Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản tại ' . htmlspecialchars(SITE_NAME) . '. Hãy click vào link dưới để tiến hành đặt lại mật khẩu:</p>
                
                <div style="text-align: center;">
                    <a href="' . htmlspecialchars($resetLink) . '" class="button">Đặt Lại Mật Khẩu</a>
                </div>

                <p>Link này sẽ hết hạn trong vòng 24 giờ. Nếu bạn không yêu cầu việc này, vui lòng bỏ qua email này.</p>

                <div class="warning">
                    <strong>⚠️ Lưu ý:</strong> Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng liên hệ với chúng tôi ngay để bảo vệ tài khoản của bạn.
                </div>
            </div>

            <div class="footer">
                <p>Trân trọng,<br><strong>Đội ngũ ' . htmlspecialchars(SITE_NAME) . '</strong></p>
                <p>&copy; ' . date('Y') . ' ' . htmlspecialchars(SITE_NAME) . '. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';

    return sendEmail($email, $subject, $message);
}

/**
 * Hàm gửi email chính
 * 
 * Ưu tiên: PHPMailer → mail() → lưu file (development mode)
 * 
 * @param string $to Email người nhận
 * @param string $subject Tiêu đề email
 * @param string $message Nội dung HTML
 * @return bool
 */
function sendEmail($to, $subject, $message)
{
    // Thử sử dụng PHPMailer nếu được cài đặt
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            // SMTP configuration from .env or config constants
            $mail->isSMTP();
            $mail->Host = getenv('SMTP_HOST') ?: (defined('MAIL_HOST') ? MAIL_HOST : 'smtp.gmail.com');
            $mail->SMTPAuth = true;
            $mail->Username = getenv('SMTP_USERNAME') ?: (defined('MAIL_USERNAME') ? MAIL_USERNAME : '');
            $mail->Password = getenv('SMTP_PASSWORD') ?: (defined('MAIL_PASSWORD') ? MAIL_PASSWORD : '');

            // Use encryption from .env or default to STARTTLS
            $encryption = getenv('SMTP_ENCRYPTION') ?: 'tls';
            if ($encryption === 'tls') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($encryption === 'ssl') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            }

            $mail->SMTPAutoTLS = true;
            $mail->Port = (int)(getenv('SMTP_PORT') ?: (defined('MAIL_PORT') ? MAIL_PORT : 587));

            // Use SMTP username as From address (Gmail requirement)
            $fromAddress = getenv('SMTP_USERNAME') ?: (defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : SITE_EMAIL);
            $fromName = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : SITE_NAME;
            $mail->setFrom($fromAddress, $fromName);
            $mail->addReplyTo(SITE_EMAIL, SITE_NAME);

            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->CharSet = 'UTF-8';

            $result = $mail->send();
            error_log("Email sent successfully to {$to}");
            return $result;
        } catch (\Exception $e) {
            // Nếu PHPMailer lỗi, log error đầy đủ và dùng fallback
            error_log('PHPMailer Error: ' . $e->getMessage());
            error_log('PHPMailer Error Code: ' . $e->getCode());
            return sendEmailWithPHPMail($to, $subject, $message);
        }
    } else {
        // Nếu PHPMailer không được cài, dùng mail() function
        return sendEmailWithPHPMail($to, $subject, $message);
    }
}

/**
 * Gửi email qua mail() function (fallback)
 * 
 * @param string $to Email người nhận
 * @param string $subject Tiêu đề
 * @param string $message Nội dung HTML
 * @return bool
 */
function sendEmailWithPHPMail($to, $subject, $message)
{
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . SITE_EMAIL . "\r\n";

    // Thử gửi email thông qua mail() function
    $result = @mail($to, $subject, $message, $headers);

    // Nếu mail() thất bại hoặc không được cấu hình, lưu email vào file để testing
    if (!$result) {
        return logEmailToFile($to, $subject, $message, $headers);
    }

    return $result;
}

/**
 * Lưu email vào file (development mode)
 * 
 * Khi mail() hoặc SMTP không hoạt động, email sẽ được lưu vào:
 * /storage/emails/email_YYYY-MM-DD_HH-MM-SS_MD5HASH.html
 * 
 * @param string $to Email người nhận
 * @param string $subject Tiêu đề
 * @param string $message Nội dung
 * @param string $headers Headers
 * @return bool
 */
function logEmailToFile($to, $subject, $message, $headers = '')
{
    $emailDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'emails';

    // Tạo thư mục nếu chưa tồn tại
    if (!is_dir($emailDir)) {
        @mkdir($emailDir, 0755, true);
    }

    $filename = $emailDir . DIRECTORY_SEPARATOR . 'email_' . date('Y-m-d_H-i-s_') . md5($to) . '.html';

    $content = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { background: white; max-width: 800px; margin: 0 auto; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #2ecc71; color: white; padding: 20px; border-radius: 4px; margin-bottom: 20px; }
        .info { background: #f9f9f9; padding: 15px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #2ecc71; }
        .info-row { margin: 10px 0; font-family: monospace; font-size: 12px; }
        .label { font-weight: bold; color: #333; }
        .value { color: #666; word-break: break-all; }
        .body-content { margin-top: 20px; border-top: 2px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>📧 Email Log (Development Mode)</h2>
            <p>Email được lưu vì server không thể gửi trực tiếp</p>
        </div>
        
        <div class="info">
            <div class="info-row"><span class="label">To:</span> <span class="value">' . htmlspecialchars($to) . '</span></div>
            <div class="info-row"><span class="label">Subject:</span> <span class="value">' . htmlspecialchars($subject) . '</span></div>
            <div class="info-row"><span class="label">Time:</span> <span class="value">' . date('Y-m-d H:i:s') . '</span></div>
            <div class="info-row"><span class="label">Headers:</span> <span class="value">' . htmlspecialchars($headers) . '</span></div>
        </div>
        
        <div class="body-content">
            <h3>Email Body:</h3>
            ' . $message . '
        </div>
    </div>
</body>
</html>';

    file_put_contents($filename, $content);

    return true;
}
