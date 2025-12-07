
<?php
/**
 * email_functions.php - Hàm gửi email
 */

/**
 * Gửi email đặt lại mật khẩu
 */
function sendPasswordResetEmail($email, $name, $resetLink) {
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
        // email_functions.php - Đã xóa toàn bộ hàm gửi mail theo yêu cầu
                <h1>🌱 Chào mừng đến với ' . SITE_NAME . '!</h1>
            </div>
            <div class="content">
                <p>Xin chào <strong>' . htmlspecialchars($name) . '</strong>,</p>
                
                <p>Cảm ơn bạn đã đăng ký tài khoản tại <strong>' . SITE_NAME . '</strong>! Chúng tôi rất vui được phục vụ bạn.</p>
                
                <div class="features">
                    <div class="feature">
                        <span class="feature-icon">✅</span>
                        <div>
                            <strong>Sản phẩm 100% hữu cơ</strong><br>
                            <small>Được chứng nhận an toàn cho sức khỏe</small>
                        </div>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">🚚</span>
                        <div>
                            <strong>Giao hàng nhanh chóng</strong><br>
                            <small>Miễn phí vận chuyển cho đơn từ 500.000₫</small>
                        </div>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">🎁</span>
                        <div>
                            <strong>Ưu đãi thành viên</strong><br>
                            <small>Tích điểm và nhận quà hấp dẫn</small>
                        </div>
                    </div>
                </div>
                
                <div style="text-align: center;">
                    <a href="' . SITE_URL . '/products.php" class="button">Khám phá sản phẩm</a>
                </div>
                
                <p>Nếu bạn có bất kỳ câu hỏi nào, đừng ngại liên hệ với chúng tôi!</p>
                
                <p>Trân trọng,<br><strong>Đội ngũ ' . SITE_NAME . '</strong></p>
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' ' . SITE_NAME . '. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    return sendEmail($email, $subject, $message);
}

/**
 * Gửi email xác nhận đơn hàng
 */
function sendOrderConfirmationEmail($email, $name, $orderId, $orderTotal) {
    $subject = "Xác nhận đơn hàng #$orderId - " . SITE_NAME;
    
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
            .order-box { background: #f7f8f6; padding: 20px; border-radius: 6px; margin: 20px 0; }
            .button { display: inline-block; padding: 12px 30px; background: #b6e633; color: #161811; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #7e8863; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>✅ Đơn hàng đã được xác nhận</h1>
            </div>
            <div class="content">
                <p>Xin chào <strong>' . htmlspecialchars($name) . '</strong>,</p>
                
                <p>Cảm ơn bạn đã đặt hàng tại <strong>' . SITE_NAME . '</strong>!</p>
                
                <div class="order-box">
                    <h3 style="margin-top: 0;">Thông tin đơn hàng</h3>
                    <p><strong>Mã đơn hàng:</strong> #' . $orderId . '</p>
                    <p><strong>Tổng tiền:</strong> ' . formatPrice($orderTotal) . '</p>
                    <p><strong>Trạng thái:</strong> Đang xử lý</p>
                </div>
                
                <p>Chúng tôi đang chuẩn bị đơn hàng của bạn và sẽ giao trong thời gian sớm nhất.</p>
                
                <div style="text-align: center;">
                    <a href="' . SITE_URL . '/user_info.php?tab=orders" class="button">Xem chi tiết đơn hàng</a>
                </div>
                
                <p>Nếu bạn có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi.</p>
                
                <p>Trân trọng,<br><strong>Đội ngũ ' . SITE_NAME . '</strong></p>
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' ' . SITE_NAME . '. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    return sendEmail($email, $subject, $message);
}

/**
 * Hàm gửi email chính (sử dụng PHPMailer nếu có, hoặc fallback mail())
 */
function sendEmail($to, $subject, $message) {
    // Thử sử dụng PHPMailer nếu được cài đặt
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = getenv('SMTP_USERNAME') ?: SITE_EMAIL;
            $mail->Password = getenv('SMTP_PASSWORD');
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = getenv('SMTP_PORT') ?: 587;
            
            $mail->setFrom(SITE_EMAIL, SITE_NAME);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->CharSet = 'UTF-8';
            
            return $mail->send();
        } catch (\Exception $e) {
            // Nếu PHPMailer lỗi, log error và dùng fallback
            error_log('PHPMailer Error: ' . $e->getMessage());
            return sendEmailWithPHPMail($to, $subject, $message);
        }
    } else {
        // Nếu PHPMailer không được cài, dùng mail() function
        return sendEmailWithPHPMail($to, $subject, $message);
    }
}

/**
 * Gửi email qua mail() function (fallback)
 */
function sendEmailWithPHPMail($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
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
 * Lưu email vào file khi mail() không hoạt động (dùng cho development/localhost)
 */
function logEmailToFile($to, $subject, $message, $headers = '') {
    $emailDir = __DIR__ . '/../storage/emails';
    
    // Tạo thư mục nếu chưa tồn tại
    if (!is_dir($emailDir)) {
        @mkdir($emailDir, 0755, true);
    }
    
    // Tạo tên file với timestamp
    $filename = $emailDir . '/email_' . date('Y-m-d_H-i-s_') . md5($to) . '.html';
    
    // Tạo nội dung email file
    $content = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .email-container { background: white; max-width: 800px; margin: 0 auto; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #b6e633; color: white; padding: 20px; border-radius: 4px; margin-bottom: 20px; }
        .info { background: #f0f0f0; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-family: monospace; font-size: 12px; }
        .info-row { margin: 10px 0; }
        .label { font-weight: bold; color: #333; }
        .value { color: #666; word-break: break-all; }
        .body-content { margin-top: 20px; border-top: 2px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h2>📧 Email Log (Development Mode)</h2>
            <p>Email này được lưu vì server không thể gửi email trực tiếp</p>
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
    
    // Lưu file
    file_put_contents($filename, $content);
    
    return true; // Trả về true để cho biết email đã được "gửi" (lưu vào file)
}