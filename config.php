<?php
// config.php - Cấu hình database và các hằng số

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'organic_db');

// Site Configuration
define('SITE_NAME', 'Xanh Organic');
define('SITE_URL', 'http://localhost/organic');
define('ITEMS_PER_PAGE', 6);
// Admin panel URL (can be a separate domain like admin.oho)
define('ADMIN_URL', 'http://admin.oho');

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
    // Ensure we don't pass null to number_format (PHP 8.1+ deprecates passing null)
    if ($price === null || $price === '') {
        return '0₫';
    }

    // If price is not numeric, try to cast; otherwise default to 0
    if (!is_numeric($price)) {
        $price = 0;
    }

    return number_format((float)$price, 0, ',', '.') . '₫';
}

function sanitize($data) {
    // Safely handle null or non-string values to avoid trim(null) deprecation
    if ($data === null) return '';
    if (!is_string($data)) $data = (string)$data;
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>