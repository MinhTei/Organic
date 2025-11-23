
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
 * Hàm gửi email chính (sử dụng PHPMailer hoặc mail() function)
 */
function sendEmail($to, $subject, $message) {
    // Luôn dùng mail() function của PHP để gửi email
    return sendEmailWithPHPMail($to, $subject, $message);
}

/**
 * Gửi email qua mail() function (fallback)
 */
function sendEmailWithPHPMail($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . SITE_NAME . " <noreply@xanhorganic.vn>" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}