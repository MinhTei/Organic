<?php
/**
 * admin/dashboard.php - Admin Dashboard
 */

require_once '../config.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    redirect(SITE_URL . '/auth.php');
}

$conn = getConnection();

// Get statistics
$stats = [];

// Total products
$stmt = $conn->query("SELECT COUNT(*) as total FROM products");
$stats['total_products'] = $stmt->fetch()['total'];

// Total orders
$stmt = $conn->query("SELECT COUNT(*) as total, SUM(total_amount) as revenue FROM orders");
$orderStats = $stmt->fetch();
$stats['total_orders'] = $orderStats['total'];
$stats['total_revenue'] = $orderStats['revenue'] ?? 0;

// Total customers
$stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
$stats['total_customers'] = $stmt->fetch()['total'];

// New orders today
$stmt = $conn->query("SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = CURDATE()");
$stats['orders_today'] = $stmt->fetch()['total'];

// Recent orders
$stmt = $conn->query("SELECT o.*, u.name as customer_name FROM orders o 
                      LEFT JOIN users u ON o.user_id = u.id 
                      ORDER BY o.created_at DESC LIMIT 10");
$recentOrders = $stmt->fetchAll();

// Top products
$stmt = $conn->query("SELECT p.*, COUNT(oi.id) as order_count 
                      FROM products p 
                      LEFT JOIN order_items oi ON p.id = oi.product_id 
                      GROUP BY p.id 
                      ORDER BY order_count DESC 
                      LIMIT 5");
$topProducts = $stmt->fetchAll();

// Recent activities
$stmt = $conn->query("SELECT al.*, u.name as admin_name 
                      FROM admin_logs al 
                      LEFT JOIN users u ON al.admin_id = u.id 
                      ORDER BY al.created_at DESC LIMIT 10");
$recentActivities = $stmt->fetchAll();

$pageTitle = 'Dashboard Admin';
include '../includes/header.php';
?>

<main style="min-height: calc(100vh - 220px); padding: 2rem 1rem; background: var(--background-light);">
    <div style="max-width: 1400px; margin: 0 auto;">
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <!-- Admin management toolbar (visible only to admins) -->
        <div style="background: white; border: 1px solid var(--border-light); border-radius: 0.75rem; padding: 1rem; margin-bottom: 1.5rem; display:flex; gap:1rem; align-items:center;">
            <strong style="font-weight:700;">Quản lý Admin:</strong>
            <a href="<?= defined('ADMIN_URL') ? rtrim(ADMIN_URL, '/') . '/dashboard.php' : SITE_URL . '/admin/dashboard.php' ?>" style="padding:0.5rem 0.75rem; border-radius:0.5rem; background:var(--background-light);">Dashboard</a>
            <a href="<?= defined('ADMIN_URL') ? rtrim(ADMIN_URL, '/') . '/products.php' : SITE_URL . '/admin/products.php' ?>" style="padding:0.5rem 0.75rem; border-radius:0.5rem;">Sản phẩm</a>
            <a href="<?= defined('ADMIN_URL') ? rtrim(ADMIN_URL, '/') . '/orders.php' : SITE_URL . '/admin/orders.php' ?>" style="padding:0.5rem 0.75rem; border-radius:0.5rem;">Đơn hàng</a>
            <a href="<?= defined('ADMIN_URL') ? rtrim(ADMIN_URL, '/') . '/customers.php' : SITE_URL . '/admin/customers.php' ?>" style="padding:0.5rem 0.75rem; border-radius:0.5rem;">Khách hàng</a>
        </div>
        <?php endif; ?>
            
            <!-- Header -->
            <div style="margin-bottom: 2rem;">
                <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">Dashboard</h1>
                <p style="color: var(--muted-light);">Chào mừng trở lại, <?= sanitize($_SESSION['user_name']) ?>! 👋</p>
            </div>
            
            <!-- Stats Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                
                <!-- Total Revenue -->
                <div style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); padding: 1.5rem; border-radius: 1rem; color: white; box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                        <div>
                            <p style="opacity: 0.9; font-size: 0.875rem; margin-bottom: 0.5rem;">Doanh thu</p>
                            <h3 style="font-size: 1.75rem; font-weight: 700;"><?= formatPrice($stats['total_revenue']) ?></h3>
                        </div>
                        <div style="width: 50px; height: 50px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <span class="material-symbols-outlined" style="font-size: 1.75rem;">payments</span>
                        </div>
                    </div>
                    <p style="font-size: 0.875rem; opacity: 0.9;">
                        <span style="font-weight: 700;">+12.5%</span> so với tháng trước
                    </p>
                </div>
                
                <!-- Total Orders -->
                <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); padding: 1.5rem; border-radius: 1rem; color: white; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                        <div>
                            <p style="opacity: 0.9; font-size: 0.875rem; margin-bottom: 0.5rem;">Đơn hàng</p>
                            <h3 style="font-size: 1.75rem; font-weight: 700;"><?= $stats['total_orders'] ?></h3>
                        </div>
                        <div style="width: 50px; height: 50px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <span class="material-symbols-outlined" style="font-size: 1.75rem;">shopping_cart</span>
                        </div>
                    </div>
                    <p style="font-size: 0.875rem; opacity: 0.9;">
                        <span style="font-weight: 700;"><?= $stats['orders_today'] ?></span> đơn hôm nay
                    </p>
                </div>
                
                <!-- Total Products -->
                <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 1.5rem; border-radius: 1rem; color: white; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                        <div>
                            <p style="opacity: 0.9; font-size: 0.875rem; margin-bottom: 0.5rem;">Sản phẩm</p>
                            <h3 style="font-size: 1.75rem; font-weight: 700;"><?= $stats['total_products'] ?></h3>
                        </div>
                        <div style="width: 50px; height: 50px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <span class="material-symbols-outlined" style="font-size: 1.75rem;">inventory_2</span>
                        </div>
                    </div>
                    <p style="font-size: 0.875rem; opacity: 0.9;">
                        <a href="products.php" style="color: white; font-weight: 700;">Quản lý sản phẩm →</a>
                    </p>
                </div>
                
                <!-- Total Customers -->
                <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); padding: 1.5rem; border-radius: 1rem; color: white; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                        <div>
                            <p style="opacity: 0.9; font-size: 0.875rem; margin-bottom: 0.5rem;">Khách hàng</p>
                            <h3 style="font-size: 1.75rem; font-weight: 700;"><?= $stats['total_customers'] ?></h3>
                        </div>
                        <div style="width: 50px; height: 50px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <span class="material-symbols-outlined" style="font-size: 1.75rem;">groups</span>
                        </div>
                    </div>
                    <p style="font-size: 0.875rem; opacity: 0.9;">
                        <a href="customers.php" style="color: white; font-weight: 700;">Xem danh sách →</a>
                    </p>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                
                <!-- Recent Orders -->
                <div style="background: white; border-radius: 1rem; padding: 1.5rem; border: 1px solid var(--border-light);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h2 style="font-size: 1.25rem; font-weight: 700;">Đơn hàng gần đây</h2>
                        <a href="orders.php" style="color: var(--primary-dark); font-weight: 600; font-size: 0.875rem;">Xem tất cả →</a>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 2px solid var(--border-light);">
                                    <th style="padding: 0.75rem; text-align: left; font-size: 0.875rem; color: var(--muted-light);">Mã ĐH</th>
                                    <th style="padding: 0.75rem; text-align: left; font-size: 0.875rem; color: var(--muted-light);">Khách hàng</th>
                                    <th style="padding: 0.75rem; text-align: left; font-size: 0.875rem; color: var(--muted-light);">Số tiền</th>
                                    <th style="padding: 0.75rem; text-align: left; font-size: 0.875rem; color: var(--muted-light);">Trạng thái</th>
                                    <th style="padding: 0.75rem; text-align: left; font-size: 0.875rem; color: var(--muted-light);">Ngày đặt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): 
                                    $statusColors = [
                                        'pending' => '#f59e0b',
                                        'confirmed' => '#3b82f6',
                                        'shipping' => '#8b5cf6',
                                        'delivered' => '#22c55e',
                                        'cancelled' => '#ef4444'
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Chờ xác nhận',
                                        'confirmed' => 'Đã xác nhận',
                                        'shipping' => 'Đang giao',
                                        'delivered' => 'Đã giao',
                                        'cancelled' => 'Đã hủy'
                                    ];
                                ?>
                                <tr style="border-bottom: 1px solid var(--border-light);">
                                    <td style="padding: 1rem; font-weight: 600;">#<?= $order['id'] ?></td>
                                    <td style="padding: 1rem;"><?= sanitize($order['customer_name'] ?? 'Khách') ?></td>
                                    <td style="padding: 1rem; font-weight: 600; color: var(--primary-dark);"><?= formatPrice($order['total_amount']) ?></td>
                                    <td style="padding: 1rem;">
                                        <span style="padding: 0.375rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; 
                                                     background: <?= $statusColors[$order['status']] ?>20; color: <?= $statusColors[$order['status']] ?>;">
                                            <?= $statusLabels[$order['status']] ?>
                                        </span>
                                    </td>
                                    <td style="padding: 1rem; color: var(--muted-light); font-size: 0.875rem;">
                                        <?= date('d/m/Y', strtotime($order['created_at'])) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Top Products & Activities -->
                <div style="display: flex; flex-direction: column; gap: 2rem;">
                    
                    <!-- Top Products -->
                    <div style="background: white; border-radius: 1rem; padding: 1.5rem; border: 1px solid var(--border-light);">
                        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem;">Sản phẩm bán chạy</h2>
                        
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <?php foreach ($topProducts as $product): ?>
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <img src="<?= $product['image'] ?>" alt="<?= sanitize($product['name']) ?>"
                                     style="width: 50px; height: 50px; border-radius: 0.5rem; object-fit: cover;">
                                <div style="flex: 1;">
                                    <p style="font-weight: 600; font-size: 0.875rem; margin-bottom: 0.25rem;">
                                        <?= sanitize($product['name']) ?>
                                    </p>
                                    <p style="font-size: 0.75rem; color: var(--muted-light);">
                                        <?= $product['order_count'] ?> đơn hàng
                                    </p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Recent Activities -->
                    <div style="background: white; border-radius: 1rem; padding: 1.5rem; border: 1px solid var(--border-light);">
                        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem;">Hoạt động gần đây</h2>
                        
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <?php foreach ($recentActivities as $activity): ?>
                            <div style="display: flex; gap: 0.75rem;">
                                <div style="width: 8px; height: 8px; background: var(--primary); border-radius: 50%; margin-top: 0.5rem; flex-shrink: 0;"></div>
                                <div style="flex: 1;">
                                    <p style="font-size: 0.875rem; margin-bottom: 0.25rem;">
                                        <strong><?= sanitize($activity['admin_name']) ?></strong> <?= sanitize($activity['description']) ?>
                                    </p>
                                    <p style="font-size: 0.75rem; color: var(--muted-light);">
                                        <?= date('d/m/Y H:i', strtotime($activity['created_at'])) ?>
                                    </p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>