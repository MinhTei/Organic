<?php

/**
 * admin/download_template.php - Tải file template import (CSV)
 */

// PHẢI gọi session trước config để tránh session headers gây can thiệp
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra quyền admin trước khi require config
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Nếu chưa login, chuyển hướng
    require_once __DIR__ . '/../includes/config.php';
    redirect(SITE_URL . '/auth.php');
}

// PHẢI set headers TRƯỚC require_once (tránh session headers)
// Output UTF-8 BOM TRƯỚC Content-Type header
ob_clean();  // Xóa bất kì output nào có thể
echo "\xEF\xBB\xBF";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="template_import_san_pham_' . date('Y-m-d') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Header columns - phải wrap với quotes để Excel nhận diện UTF-8 đúng
$headers = [
    'Tên sản phẩm',
    'Giá',
    'Giá giảm',
    'Danh mục',
    'Đơn vị',
    'Tồn kho',
    'Mô tả',
    'Hữu cơ',
    'Mới'
];

// Write header row - wrap tất cả fields với quotes vì có tiếng Việt
$headerRow = array_map(function ($field) {
    return '"' . str_replace('"', '""', $field) . '"';
}, $headers);
echo implode(',', $headerRow) . "\r\n";

// Sample data
$sampleData = [
    ['Sầu riêng', 60000, '', 'Trái cây', 'kg', 30, 'Sầu riêng ri 6', 'yes', 'no'],
];

// Write sample data - wrap fields có tiếng Việt với quotes
foreach ($sampleData as $row) {
    $escapedRow = array_map(function ($field) {
        // Nếu là số và không phải chuỗi, giữ nguyên; nếu có ký tự đặc biệt hoặc tiếng Việt thì wrap
        if ($field === '' || (is_numeric($field) && strpos($field, '"') === false && strpos($field, ',') === false)) {
            return $field;
        }
        // Wrap với quotes nếu chứa comma, quote, newline, hoặc ký tự tiếng Việt
        return '"' . str_replace('"', '""', $field) . '"';
    }, $row);

    echo implode(',', $escapedRow) . "\r\n";
}

exit;
