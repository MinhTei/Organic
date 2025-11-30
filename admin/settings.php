<?php
/**
 * admin/settings.php - Trang Cài đặt Hệ thống
 *
 * Chức năng:
 * - Cài đặt thông tin website
 * - Cấu hình email SMTP
 * - Cài đặt vận chuyển
 * - Cài đặt thanh toán
 * - Quản lý banner và slider
 * - Giao diện đồng bộ với các trang quản trị khác
 */

require_once '../config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    redirect(SITE_URL . '/auth.php');
}

$conn = getConnection();
$success = '';
$error = '';

// Flash messages (after redirect)
if (isset($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}
if (isset($_SESSION['flash_error'])) {
    $error = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

// Xử lý lưu cài đặt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $settings = $_POST['settings'] ?? [];

    // Các khóa boolean (checkbox) — nếu không có trong POST thì mặc định 0
    $booleanKeys = [
        'cod_enabled', 'payment_cod', 'payment_bank', 'payment_vnpay'
    ];
    foreach ($booleanKeys as $bk) {
        if (!array_key_exists($bk, $settings)) {
            $settings[$bk] = '0';
        }
    }

    try {
        foreach ($settings as $key => $value) {
            // Sử dụng helper để cập nhật và refresh cache
            if (!function_exists('updateSystemSetting')) {
                // fallback: direct query
                $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE setting_value = :value2");
                $stmt->execute([':key' => $key, ':value' => $value, ':value2' => $value]);
            } else {
                updateSystemSetting($key, $value);
            }
        }

        // reload local copy
        if (function_exists('loadSystemSettings')) {
            $currentSettings = loadSystemSettings(true);
        } else {
            $stmt = $conn->query("SELECT setting_key, setting_value FROM settings");
            $currentSettings = [];
            while ($row = $stmt->fetch()) {
                $currentSettings[$row['setting_key']] = $row['setting_value'];
            }
        }

        // Use flash + redirect to force a fresh request so constants/settings reload
        $_SESSION['flash_success'] = 'Lưu cài đặt thành công!';
        redirect('settings.php');
    } catch (Exception $e) {
        $_SESSION['flash_error'] = 'Lưu cài đặt thất bại: ' . $e->getMessage();
        redirect('settings.php');
    }
}

// Lấy tất cả cài đặt hiện tại
$stmt = $conn->query("SELECT setting_key, setting_value FROM settings");
$currentSettings = [];
while ($row = $stmt->fetch()) {
    $currentSettings[$row['setting_key']] = $row['setting_value'];
}

// Hàm lấy giá trị setting
function getSetting($key, $default = '') {
    global $currentSettings;
    return $currentSettings[$key] ?? $default;
}

// Xử lý upload logo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../images/logo/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $origName = basename($_FILES['site_logo']['name']);
    $safeName = 'logo_' . time() . '_' . preg_replace('/[^a-z0-9_\-\.]/i', '_', $origName);
    $targetPath = $uploadDir . $safeName;
    
    if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $targetPath)) {
        $logoPath = 'images/logo/' . $safeName;
        
        $stmt = $conn->prepare("
            INSERT INTO settings (setting_key, setting_value) 
            VALUES ('site_logo', :value)
            ON DUPLICATE KEY UPDATE setting_value = :value2
        ");
        $stmt->execute([':value' => $logoPath, ':value2' => $logoPath]);
        
        // Use flash + redirect to avoid duplicate upload on refresh and reload settings globally
        $_SESSION['flash_success'] = 'Upload logo thành công!';
        redirect('settings.php');
    }
}

$pageTitle = 'Cài đặt hệ thống';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <style>
        body { font-family: 'Be Vietnam Pro', sans-serif; }
        .tab-button {
            padding: 1rem 1.5rem;
            border-bottom: 3px solid transparent;
            font-weight: 600;
            color: #6b7280;
            transition: all 0.3s;
        }
        .tab-button:hover {
            color: #111827;
            background: rgba(182, 230, 51, 0.1);
        }
        .tab-button.active {
            color: #9acc2a;
            border-bottom-color: #b6e633;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-50 font-['Be_Vietnam_Pro']">
    
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-green-600 text-3xl">admin_panel_settings</span>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">Admin Dashboard</h1>
                        <p class="text-xs text-gray-500">Xanh Organic</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="<?= SITE_URL ?>" class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-1">
                        <span class="material-symbols-outlined text-lg">storefront</span>
                        <span>Về trang chủ</span>
                    </a>
                    <div class="flex items-center gap-2 pl-3 border-l border-gray-200">
                        <div class="w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center font-bold">
                            <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
                        </div>
                        <span class="text-sm font-medium text-gray-700"><?= sanitize($_SESSION['user_name']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="flex">
        <!-- Sidebar -->
        <?php include __DIR__ . '/_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <?php if ($success): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Cài đặt hệ thống</h2>
                <p class="text-gray-600 mt-1">Quản lý cấu hình website và hệ thống</p>
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <!-- Tab Headers -->
                <div class="flex border-b border-gray-200 overflow-x-auto">
                    <button class="tab-button active" onclick="switchTab(event, 'general')">
                        <span class="material-symbols-outlined text-lg" style="vertical-align: middle; margin-right: 0.5rem;">settings</span>
                        Chung
                    </button>
                    <button class="tab-button" onclick="switchTab(event, 'email')">
                        <span class="material-symbols-outlined text-lg" style="vertical-align: middle; margin-right: 0.5rem;">mail</span>
                        Email
                    </button>
                    <button class="tab-button" onclick="switchTab(event, 'shipping')">
                        <span class="material-symbols-outlined text-lg" style="vertical-align: middle; margin-right: 0.5rem;">local_shipping</span>
                        Vận chuyển
                    </button>
                    <button class="tab-button" onclick="switchTab(event, 'payment')">
                        <span class="material-symbols-outlined text-lg" style="vertical-align: middle; margin-right: 0.5rem;">payment</span>
                        Thanh toán
                    </button>
                </div>

                <form method="POST" enctype="multipart/form-data" class="p-6">
                    <!-- General Settings Tab -->
                    <div id="tab-general" class="tab-content active">
                        <h3 class="text-lg font-bold mb-4">Thông tin website</h3>
                        
                        <div class="space-y-4">
                            <!-- Logo Upload -->
                            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Logo website</label>
                                <?php if (getSetting('site_logo')): ?>
                                    <div class="mb-3">
                                        <img src="<?= SITE_URL . '/' . getSetting('site_logo') ?>" alt="Logo" class="h-16 object-contain">
                                    </div>
                                <?php endif; ?>
                                <div class="flex items-center gap-3">
                                    <input type="file" name="site_logo" accept="image/*" class="flex-1">
                                    <button type="submit" name="upload_logo" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                        Upload
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Tên website</label>
                                <input type="text" name="settings[site_name]" 
                                       value="<?= sanitize(getSetting('site_name', 'Xanh Organic')) ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Email liên hệ</label>
                                <input type="email" name="settings[site_email]" 
                                       value="<?= sanitize(getSetting('site_email', 'info@xanhorganic.vn')) ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Số điện thoại</label>
                                <input type="tel" name="settings[site_phone]" 
                                       value="<?= sanitize(getSetting('site_phone', '1900123456')) ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Địa chỉ</label>
                                <textarea name="settings[site_address]" rows="2"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"><?= sanitize(getSetting('site_address', '123 Đường Xanh, Q.1, TP.HCM')) ?></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Mô tả website</label>
                                <textarea name="settings[site_description]" rows="3"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"><?= sanitize(getSetting('site_description', 'Rau sạch hữu cơ, giao tận nhà')) ?></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Keywords SEO</label>
                                <input type="text" name="settings[site_keywords]" 
                                       value="<?= sanitize(getSetting('site_keywords', 'rau sạch, thực phẩm hữu cơ, organic')) ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            </div>

                            <hr class="my-6">

                            <h4 class="text-md font-bold mb-3">Mạng xã hội</h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Facebook URL</label>
                                    <input type="url" name="settings[social_facebook]" 
                                           value="<?= sanitize(getSetting('social_facebook')) ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                           placeholder="https://facebook.com/yourpage">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Instagram URL</label>
                                    <input type="url" name="settings[social_instagram]" 
                                           value="<?= sanitize(getSetting('social_instagram')) ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                           placeholder="https://instagram.com/yourpage">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Zalo URL</label>
                                    <input type="url" name="settings[social_zalo]" 
                                           value="<?= sanitize(getSetting('social_zalo')) ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                           placeholder="https://zalo.me/yourpage">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">YouTube URL</label>
                                    <input type="url" name="settings[social_youtube]" 
                                           value="<?= sanitize(getSetting('social_youtube')) ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                           placeholder="https://youtube.com/yourchannel">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Email Settings Tab -->
                    <div id="tab-email" class="tab-content">
                        <h3 class="text-lg font-bold mb-4">Cấu hình Email SMTP</h3>
                        
                        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-800">
                                <strong>Lưu ý:</strong> Để gửi email tự động, bạn cần cấu hình SMTP server. 
                                Nếu sử dụng Gmail, bạn cần tạo App Password trong cài đặt tài khoản.
                            </p>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">SMTP Host</label>
                                <input type="text" name="settings[smtp_host]" 
                                       value="<?= sanitize(getSetting('smtp_host', 'smtp.gmail.com')) ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                       placeholder="smtp.gmail.com">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">SMTP Port</label>
                                    <input type="number" name="settings[smtp_port]" 
                                           value="<?= sanitize(getSetting('smtp_port', '587')) ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                           placeholder="587">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Encryption</label>
                                    <select name="settings[smtp_encryption]"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                        <option value="tls" <?= getSetting('smtp_encryption') === 'tls' ? 'selected' : '' ?>>TLS</option>
                                        <option value="ssl" <?= getSetting('smtp_encryption') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">SMTP Username (Email)</label>
                                <input type="email" name="settings[smtp_username]" 
                                       value="<?= sanitize(getSetting('smtp_username')) ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                       placeholder="your-email@gmail.com">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">SMTP Password</label>
                                <input type="password" name="settings[smtp_password]" 
                                       value="<?= sanitize(getSetting('smtp_password')) ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                       placeholder="App Password">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">From Email</label>
                                    <input type="email" name="settings[mail_from_address]" 
                                           value="<?= sanitize(getSetting('mail_from_address', 'noreply@xanhorganic.vn')) ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">From Name</label>
                                    <input type="text" name="settings[mail_from_name]" 
                                           value="<?= sanitize(getSetting('mail_from_name', 'Xanh Organic')) ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Settings Tab -->
                    <div id="tab-shipping" class="tab-content">
                        <h3 class="text-lg font-bold mb-4">Cài đặt vận chuyển</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Phí vận chuyển mặc định (₫)</label>
                                <input type="number" name="settings[default_shipping_fee]" 
                                       value="<?= sanitize(getSetting('default_shipping_fee', '25000')) ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Miễn phí ship từ (₫)</label>
                                <input type="number" name="settings[free_shipping_threshold]" 
                                       value="<?= sanitize(getSetting('free_shipping_threshold', '500000')) ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                <p class="text-sm text-gray-500 mt-1">Đơn hàng đạt giá trị này sẽ được miễn phí vận chuyển</p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Thời gian giao hàng dự kiến</label>
                                <input type="text" name="settings[estimated_delivery]" 
                                       value="<?= sanitize(getSetting('estimated_delivery', '2-3 ngày')) ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                       placeholder="2-3 ngày">
                            </div>

                            <div>
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="settings[cod_enabled]" value="1"
                                           <?= getSetting('cod_enabled', '1') == '1' ? 'checked' : '' ?>
                                           class="w-5 h-5 accent-green-600">
                                    <span class="text-sm font-semibold text-gray-700">Cho phép thanh toán khi nhận hàng (COD)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Settings Tab -->
                    <div id="tab-payment" class="tab-content">
                        <h3 class="text-lg font-bold mb-4">Cài đặt thanh toán</h3>
                        
                        <div class="space-y-6">
                            <!-- COD -->
                            <div class="p-4 border border-gray-200 rounded-lg">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="settings[payment_cod]" value="1"
                                           <?= getSetting('payment_cod', '1') == '1' ? 'checked' : '' ?>
                                           class="w-5 h-5 accent-green-600">
                                    <div>
                                        <p class="font-semibold">Thanh toán khi nhận hàng (COD)</p>
                                        <p class="text-sm text-gray-500">Khách hàng thanh toán tiền mặt khi nhận hàng</p>
                                    </div>
                                </label>
                            </div>

                            <!-- Bank Transfer -->
                            <div class="p-4 border border-gray-200 rounded-lg">
                                <label class="flex items-center gap-3 cursor-pointer mb-3">
                                    <input type="checkbox" name="settings[payment_bank]" value="1"
                                           <?= getSetting('payment_bank', '1') == '1' ? 'checked' : '' ?>
                                           class="w-5 h-5 accent-green-600">
                                    <div>
                                        <p class="font-semibold">Chuyển khoản ngân hàng</p>
                                        <p class="text-sm text-gray-500">Khách hàng chuyển khoản trước khi nhận hàng</p>
                                    </div>
                                </label>
                                
                                <div class="ml-8 space-y-3">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tên ngân hàng</label>
                                        <input type="text" name="settings[bank_name]" 
                                               value="<?= sanitize(getSetting('bank_name', 'Vietcombank')) ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Số tài khoản</label>
                                        <input type="text" name="settings[bank_account]" 
                                               value="<?= sanitize(getSetting('bank_account', '1234567890')) ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Chủ tài khoản</label>
                                        <input type="text" name="settings[bank_account_name]" 
                                               value="<?= sanitize(getSetting('bank_account_name', 'CONG TY XANH ORGANIC')) ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                    </div>
                                </div>
                            </div>

                            <!-- VNPay -->
                            <div class="p-4 border border-gray-200 rounded-lg">
                                <label class="flex items-center gap-3 cursor-pointer mb-3">
                                    <input type="checkbox" name="settings[payment_vnpay]" value="1"
                                           <?= getSetting('payment_vnpay') == '1' ? 'checked' : '' ?>
                                           class="w-5 h-5 accent-green-600">
                                    <div>
                                        <p class="font-semibold">VNPay</p>
                                        <p class="text-sm text-gray-500">Thanh toán qua cổng VNPay</p>
                                    </div>
                                </label>
                                
                                <div class="ml-8 space-y-3">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">VNPay TmnCode</label>
                                        <input type="text" name="settings[vnpay_tmn_code]" 
                                               value="<?= sanitize(getSetting('vnpay_tmn_code')) ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">VNPay Hash Secret</label>
                                        <input type="password" name="settings[vnpay_hash_secret]" 
                                               value="<?= sanitize(getSetting('vnpay_hash_secret')) ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="mt-6 flex justify-end gap-3">
                        <a href="dashboard.php" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Hủy
                        </a>
                        <button type="submit" name="save_settings" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Lưu cài đặt
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
    function switchTab(e, tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Show selected tab
        document.getElementById('tab-' + tabName).classList.add('active');
        if (e && e.currentTarget) e.currentTarget.classList.add('active');
    }
    </script>

</body>
</html>