<?php
/**
 * admin/customer_detail.php - Chi tiết khách hàng
 */

require_once '../config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    redirect(SITE_URL . '/auth.php');
}

$customerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$customerId) {
    redirect('customers.php');
}

$conn = getConnection();

// Get customer info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id AND role = 'customer'");
$stmt->execute([':id' => $customerId]);
$customer = $stmt->fetch();

if (!$customer) {
    redirect('customers.php');
}

// Get customer orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->execute([':user_id' => $customerId]);
$orders = $stmt->fetchAll();

// Get customer statistics
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(total_amount) as total_spent,
        AVG(total_amount) as avg_order_value
    FROM orders 
    WHERE user_id = :user_id AND status != 'cancelled'
");
$stmt->execute([':user_id' => $customerId]);
$orderStats = $stmt->fetch();

$pageTitle = 'Chi tiết khách hàng - ' . $customer['name'];
// HTML code tiếp tục...
?>