<?php
/**
 * order_detail.php - Chi tiết đơn hàng
 * 
 * Chức năng:
 * - Hiển thị thông tin chi tiết một đơn hàng
 * - Hiển thị danh sách sản phẩm trong đơn hàng
 * - Hiển thị thông tin giao hàng
 * - Hiển thị phương thức thanh toán và trạng thái
 * - Cho phép hủy đơn hàng (nếu còn có thể hủy)
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Check login status
if (!isset($_SESSION['user_id'])) {
    redirect(SITE_URL . '/auth.php?redirect=order_detail');
}

$userId = $_SESSION['user_id'];
$orderId = (int)($_GET['id'] ?? 0);

if (!$orderId) {
    redirect(SITE_URL . '/order_history.php');
}

$conn = getConnection();

// Handle cancel order request
$cancelMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$orderId, $userId]);
    $cancelOrder = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cancelOrder && in_array($cancelOrder['status'], ['pending', 'confirmed', 'processing'])) {
        $updateStmt = $conn->prepare("UPDATE orders SET status = 'cancelled', cancelled_at = NOW() WHERE id = ?");
        if ($updateStmt->execute([$orderId])) {
            $cancelMessage = 'Đơn hàng đã được hủy thành công!';
        }
    }
}

// Get order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    redirect(SITE_URL . '/order_history.php');
}

// Get order items
$stmt = $conn->prepare("
    SELECT oi.*, p.name as product_name, p.image as product_image 
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$pageTitle = 'Chi tiết đơn hàng ' . $order['order_code'];
include __DIR__ . '/includes/header.php';
?>

<main class="container" style="padding: 2rem 1rem; max-width: 1200px;">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?= SITE_URL ?>">Trang chủ</a>
        <span class="material-symbols-outlined" style="font-size: 1rem;">chevron_right</span>
        <a href="<?= SITE_URL ?>/order_history.php">Lịch sử đơn hàng</a>
        <span class="material-symbols-outlined" style="font-size: 1rem;">chevron_right</span>
        <span class="current"><?= htmlspecialchars($order['order_code']) ?></span>
    </div>

    <?php if (!empty($cancelMessage)): ?>
        <div style="margin-bottom: 1.5rem; padding: 1rem; background: rgba(34, 197, 94, 0.1); border-left: 4px solid var(--success); border-radius: 0.5rem; color: var(--success);">
            <strong>Thành công:</strong> <?= $cancelMessage ?>
        </div>
    <?php endif; ?>

    <h1 style="font-size: 2rem; font-weight: 700; margin: 2rem 0;">Chi tiết đơn hàng #<?= htmlspecialchars($order['order_code']) ?></h1>

    <div style="display: grid; grid-template-columns: 1fr 400px; gap: 2rem;">
        <!-- Left Column -->
        <div>
            <!-- Order Status Card -->
            <div style="background: white; border-radius: 1rem; padding: 2rem; margin-bottom: 2rem; border: 1px solid var(--border-light);">
                <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span class="material-symbols-outlined">info</span>
                    Thông tin đơn hàng
                </h2>

                <div style="display: grid; gap: 1rem;">
                    <div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem;">
                        <span style="color: var(--muted-light);">Mã đơn hàng:</span>
                        <span style="font-weight: 600; color: var(--primary);"><?= htmlspecialchars($order['order_code']) ?></span>
                    </div>

                    <div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem;">
                        <span style="color: var(--muted-light);">Ngày đặt:</span>
                        <span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                    </div>

                    <div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem;">
                        <span style="color: var(--muted-light);">Trạng thái:</span>
                        <span style="display: inline-block; width: fit-content;">
                            <?php
                            $statusColor = 'var(--muted-light)';
                            $statusLabel = $order['status'];
                            
                            switch ($order['status']) {
                                case 'pending':
                                    $statusColor = '#f59e0b';
                                    $statusLabel = 'Chờ xác nhận';
                                    break;
                                case 'confirmed':
                                    $statusColor = '#3b82f6';
                                    $statusLabel = 'Đã xác nhận';
                                    break;
                                case 'processing':
                                    $statusColor = '#8b5cf6';
                                    $statusLabel = 'Đang chuẩn bị';
                                    break;
                                case 'shipping':
                                    $statusColor = '#06b6d4';
                                    $statusLabel = 'Đang giao';
                                    break;
                                case 'delivered':
                                    $statusColor = 'var(--success)';
                                    $statusLabel = 'Đã giao';
                                    break;
                                case 'cancelled':
                                    $statusColor = 'var(--danger)';
                                    $statusLabel = 'Đã hủy';
                                    break;
                                case 'refunded':
                                    $statusColor = '#ef4444';
                                    $statusLabel = 'Hoàn tiền';
                                    break;
                            }
                            ?>
                            <span style="display: inline-block; padding: 0.4rem 0.8rem; background: <?= $statusColor ?>20; color: <?= $statusColor ?>; border-radius: 0.25rem; font-weight: 600;">
                                <?= htmlspecialchars($statusLabel) ?>
                            </span>
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem;">
                        <span style="color: var(--muted-light);">Phương thức:</span>
                        <span><?= $order['payment_method'] === 'cod' ? 'Thanh toán khi nhận hàng (COD)' : 'Chuyển khoản ngân hàng' ?></span>
                    </div>

                    <div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem;">
                        <span style="color: var(--muted-light);">Trạng thái thanh toán:</span>
                        <span><?= $order['payment_status'] === 'pending' ? 'Chưa thanh toán' : ($order['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Lỗi') ?></span>
                    </div>

                    <?php if (!empty($order['tracking_number'])): ?>
                    <div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem;">
                        <span style="color: var(--muted-light);">Mã vận đơn:</span>
                        <span style="font-weight: 600;"><?= htmlspecialchars($order['tracking_number']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Shipping Information -->
            <div style="background: white; border-radius: 1rem; padding: 2rem; margin-bottom: 2rem; border: 1px solid var(--border-light);">
                <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span class="material-symbols-outlined">local_shipping</span>
                    Thông tin giao hàng
                </h2>

                <div style="display: grid; gap: 1rem;">
                    <div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem;">
                        <span style="color: var(--muted-light);">Người nhận:</span>
                        <span style="font-weight: 600;"><?= htmlspecialchars($order['shipping_name']) ?></span>
                    </div>

                    <div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem;">
                        <span style="color: var(--muted-light);">Số điện thoại:</span>
                        <span><?= htmlspecialchars($order['shipping_phone']) ?></span>
                    </div>

                    <div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem;">
                        <span style="color: var(--muted-light);">Địa chỉ:</span>
                        <span><?= htmlspecialchars($order['shipping_address']) ?></span>
                    </div>

                    <div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem;">
                        <span style="color: var(--muted-light);">Phường/Xã:</span>
                        <span><?= htmlspecialchars($order['shipping_ward']) ?></span>
                    </div>

                    <div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem;">
                        <span style="color: var(--muted-light);">Quận/Huyện:</span>
                        <span><?= htmlspecialchars($order['shipping_district']) ?></span>
                    </div>

                    <div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem;">
                        <span style="color: var(--muted-light);">Thành phố:</span>
                        <span><?= htmlspecialchars($order['shipping_city']) ?></span>
                    </div>

                    <?php if (!empty($order['note'])): ?>
                    <div style="display: grid; grid-template-columns: 150px 1fr; gap: 1rem;">
                        <span style="color: var(--muted-light);">Ghi chú:</span>
                        <span><?= htmlspecialchars($order['note']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Items -->
            <div style="background: white; border-radius: 1rem; padding: 2rem; border: 1px solid var(--border-light);">
                <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span class="material-symbols-outlined">shopping_cart</span>
                    Sản phẩm
                </h2>

                <div style="display: grid; gap: 1.5rem;">
                    <?php foreach ($orderItems as $item): ?>
                        <div style="display: grid; grid-template-columns: 150px 1fr; gap: 1.5rem; padding: 1.5rem; background: #f9f9f9; border-radius: 0.5rem; align-items: start;">
                            <img src="<?= SITE_URL ?>/<?= htmlspecialchars($item['product_image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" style="width: 150px; height: 150px; object-fit: cover; border-radius: 0.5rem;">
                            
                            <div style="display: flex; flex-direction: column; justify-content: space-between; height: 150px;">
                                <div>
                                    <p style="font-weight: 700; font-size: 1.1rem; margin-bottom: 0.5rem;"><?= htmlspecialchars($item['product_name']) ?></p>
                                    <p style="color: var(--muted-light); font-size: 0.95rem; margin-bottom: 0.75rem;"><?= formatPrice($item['unit_price']) ?>/item</p>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; padding-top: 1rem; border-top: 1px solid #e0e0e0;">
                                    <div>
                                        <p style="color: var(--muted-light); font-size: 0.875rem; margin-bottom: 0.25rem;">Số lượng</p>
                                        <p style="font-weight: 600; font-size: 1rem;">x<?= $item['quantity'] ?></p>
                                    </div>
                                    <div style="text-align: right;">
                                        <p style="color: var(--muted-light); font-size: 0.875rem; margin-bottom: 0.25rem;">Tổng</p>
                                        <p style="font-weight: 700; color: var(--primary); font-size: 1.1rem;"><?= formatPrice($item['total_price']) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Right Column - Summary -->
        <div>
            <!-- Order Summary -->
            <div style="background: white; border-radius: 1rem; padding: 2rem; border: 1px solid var(--border-light); position: sticky; top: 2rem;">
                <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem;">Tóm tắt đơn hàng</h2>

                <div style="display: grid; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-light);">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--muted-light);">Tạm tính</span>
                        <span><?= formatPrice($order['total_amount']) ?></span>
                    </div>

                    <?php if ($order['discount_amount'] > 0): ?>
                    <div style="display: flex; justify-content: space-between; color: var(--success);">
                        <span>Giảm giá</span>
                        <span>-<?= formatPrice($order['discount_amount']) ?></span>
                    </div>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--muted-light);">Phí vận chuyển</span>
                        <span><?= $order['shipping_fee'] == 0 ? 'Miễn phí' : formatPrice($order['shipping_fee']) ?></span>
                    </div>

                    <?php if ($order['tax_amount'] > 0): ?>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--muted-light);">Thuế</span>
                        <span><?= formatPrice($order['tax_amount']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <div style="display: flex; justify-content: space-between; padding: 1rem 0; font-size: 1.25rem; font-weight: 700;">
                    <span>Tổng cộng</span>
                    <span style="color: var(--primary);"><?= formatPrice($order['final_amount']) ?></span>
                </div>

                <?php if ($order['coupon_code']): ?>
                <div style="margin-top: 1rem; padding: 0.75rem; background: rgba(34, 197, 94, 0.1); border-radius: 0.5rem; font-size: 0.875rem;">
                    Mã giảm giá: <strong><?= htmlspecialchars($order['coupon_code']) ?></strong>
                </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div style="margin-top: 2rem; display: grid; gap: 1rem;">
                    <a href="<?= SITE_URL ?>/order_history.php" class="btn" style="padding: 0.75rem 1rem; background: var(--border-light); color: var(--text); text-decoration: none; border-radius: 0.5rem; text-align: center; cursor: pointer;">
                        Quay lại lịch sử 
                    </a>

                    <?php if (in_array($order['status'], ['pending', 'confirmed', 'processing'])): ?>
                    <form method="POST" onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này?');">
                        <button type="submit" name="cancel_order" value="1" class="btn" style="padding: 0.75rem 1rem; background: var(--danger); color: white; border: none; border-radius: 0.5rem; cursor: pointer; width: 100%;">
                            Hủy đơn hàng
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
