<?php
// config.php - Cấu hình database và các hằng số (Updated)

// Load Composer autoloader (for PHPMailer and other packages)
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env file
if (file_exists(__DIR__ . '/../.env')) {
    $env_file = __DIR__ . '/../.env';
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue; // Skip comments
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            putenv("$key=$value");
        }
    }
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'organic_db');

// Site Configuration
define('SITE_URL', 'http://localhost/organic');
define('ITEMS_PER_PAGE', 6);
define('ADMIN_URL', SITE_URL . '/admin');

// Database Connection
function getConnection() {
    static $conn = null;
    if ($conn === null) {
        try {
            $conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Lỗi kết nối database: " . $e->getMessage());
        }
    }
    return $conn;
}

// Helper Functions
function formatPrice($price) {
    if ($price === null || $price === '') {
        return '0₫';
    }
    if (!is_numeric($price)) {
        $price = 0;
    }
    return number_format((float)$price, 0, ',', '.') . '₫';
}

function sanitize($data) {
    if ($data === null) return '';
    if (!is_string($data)) $data = (string)$data;
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

// Thiết lập timezone cho PHP về Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load settings helper (sau khi có connection)
if (file_exists(__DIR__ . '/settings_helper.php')) {
    require_once __DIR__ . '/settings_helper.php';
    
    // Override SITE_NAME từ database nếu có
    if (!defined('SITE_NAME')) {
        define('SITE_NAME', getSystemSetting('site_name', 'Xanh Organic'));
    }
    
    // Định nghĩa các constants từ settings
    if (!defined('SITE_EMAIL')) {
        define('SITE_EMAIL', getSystemSetting('site_email', 'info@xanhorganic.vn'));
    }
    
    if (!defined('SITE_PHONE')) {
        define('SITE_PHONE', getSystemSetting('site_phone', '1900123456'));
    }

    if (!defined('SITE_LOGO')) {
        // site_logo is stored as a relative path like 'images/logo.png'
        define('SITE_LOGO', getSystemSetting('site_logo', ''));
    }
    
    if (!defined('FREE_SHIPPING_THRESHOLD')) {
        define('FREE_SHIPPING_THRESHOLD', getSettingAmount('free_shipping_threshold', 500000));
    }
    
    if (!defined('DEFAULT_SHIPPING_FEE')) {
        define('DEFAULT_SHIPPING_FEE', getSettingAmount('default_shipping_fee', 25000));
    }
} else {
    // Fallback values nếu chưa có settings helper
    if (!defined('SITE_NAME')) {
        define('SITE_NAME', 'Xanh Organic');
    }
    if (!defined('SITE_EMAIL')) {
        define('SITE_EMAIL', 'info@xanhorganic.vn');
    }
    if (!defined('SITE_PHONE')) {
        define('SITE_PHONE', '1900123456');
    }
    if (!defined('FREE_SHIPPING_THRESHOLD')) {
        define('FREE_SHIPPING_THRESHOLD', 500000);
    }
    if (!defined('DEFAULT_SHIPPING_FEE')) {
        define('DEFAULT_SHIPPING_FEE', 25000);
    }
}

// Mail Configuration (từ settings hoặc fallback)
if (!defined('MAIL_HOST')) {
    define('MAIL_HOST', function_exists('getSystemSetting') ? getSystemSetting('smtp_host', 'smtp.gmail.com') : 'smtp.gmail.com');
}
if (!defined('MAIL_PORT')) {
    define('MAIL_PORT', function_exists('getSystemSetting') ? getSystemSetting('smtp_port', '587') : '587');
}
if (!defined('MAIL_USERNAME')) {
    define('MAIL_USERNAME', function_exists('getSystemSetting') ? getSystemSetting('smtp_username', '') : '');
}
if (!defined('MAIL_PASSWORD')) {
    define('MAIL_PASSWORD', function_exists('getSystemSetting') ? getSystemSetting('smtp_password', '') : '');
}
if (!defined('MAIL_FROM_ADDRESS')) {
    define('MAIL_FROM_ADDRESS', function_exists('getSystemSetting') ? getSystemSetting('mail_from_address', 'noreply@xanhorganic.vn') : 'noreply@xanhorganic.vn');
}
if (!defined('MAIL_FROM_NAME')) {
    define('MAIL_FROM_NAME', function_exists('getSystemSetting') ? getSystemSetting('mail_from_name', 'Xanh Organic') : 'Xanh Organic');
}
