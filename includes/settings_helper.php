<?php
/**
 * settings_helper.php - Helper functions để đọc và sử dụng settings
 * 
 * Sử dụng:
 * require_once 'includes/settings_helper.php';
 * echo getSystemSetting('site_name');
 */

// Nếu `getConnection` chưa tồn tại, require config để khởi tạo kết nối và session
if (!function_exists('getConnection')) {
    if (file_exists(__DIR__ . '/../config.php')) {
        require_once __DIR__ . '/config.php';
    }
}

// Cache settings trong session để giảm query DB
function loadSystemSettings($forceReload = false) {
    if (!$forceReload && isset($_SESSION['_system_settings'])) {
        return $_SESSION['_system_settings'];
    }
    
    $conn = getConnection();
    $stmt = $conn->query("SELECT setting_key, setting_value FROM settings");
    
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    $_SESSION['_system_settings'] = $settings;
    return $settings;
}

/**
 * Lấy giá trị setting từ database
 * @param string $key - Key của setting
 * @param mixed $default - Giá trị mặc định nếu không tìm thấy
 * @return mixed
 */
function getSystemSetting($key, $default = '') {
    $settings = loadSystemSettings();
    return $settings[$key] ?? $default;
}

/**
 * Cập nhật setting
 * @param string $key
 * @param mixed $value
 * @return bool
 */
function updateSystemSetting($key, $value) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("
        INSERT INTO settings (setting_key, setting_value) 
        VALUES (:key, :value)
        ON DUPLICATE KEY UPDATE setting_value = :value2
    ");
    
    $result = $stmt->execute([
        ':key' => $key,
        ':value' => $value,
        ':value2' => $value
    ]);
    
    if ($result) {
        // Clear cache
        unset($_SESSION['_system_settings']);
        loadSystemSettings(true);
    }
    
    return $result;
}

/**
 * Lấy nhiều settings cùng lúc
 * @param array $keys - Mảng các key cần lấy
 * @return array
 */
function getSystemSettings($keys) {
    $settings = loadSystemSettings();
    $result = [];
    
    foreach ($keys as $key) {
        $result[$key] = $settings[$key] ?? '';
    }
    
    return $result;
}

/**
 * Check xem có bật setting boolean không
 * @param string $key
 * @return bool
 */
function isSettingEnabled($key) {
    $value = getSystemSetting($key, '0');
    return in_array($value, ['1', 'true', 'yes', 'on'], true);
}

/**
 * Format số tiền từ setting
 * @param string $key
 * @return int
 */
function getSettingAmount($key, $default = 0) {
    return (int) getSystemSetting($key, $default);
}

// Tự động load settings khi include file này
loadSystemSettings();