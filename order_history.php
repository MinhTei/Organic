<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    redirect(SITE_URL . '/auth.php');
}

$userId = $_SESSION['user_id'];
$conn = getConnection();

$stmt = $conn->prepare("
    SELECT id, order_code, created_at, final_amount, payment_method, status
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Lịch sử đơn hàng';
require_once 'includes/header.php';
?>

<div class="container" style="margin-top: 30px; margin-bottom: 50px;">
    <h1 style="margin-bottom: 30px;">Lịch sử đơn hàng</h1>

    <?php if (empty($orders)): ?>
        <div style="text-align: center; padding: 50px 20px;">
            <p style="font-size: 16px; color: #666;">Bạn chưa có đơn hàng nào</p>
            <a href="<?= SITE_URL ?>/products.php" class="btn" style="margin-top: 20px; display: inline-block;">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; background: white;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Mã đơn hàng</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Ngày đặt</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Tổng tiền</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Phương thức thanh toán</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Trạng thái</th>
                        <th style="padding: 15px; text-align: center; font-weight: 600;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <?php
                        // Xác định màu sắc trạng thái
                        $statusColors = [
                            'pending' => '#f59e0b',
                            'confirmed' => '#3b82f6',
                            'processing' => '#06b6d4',
                            'shipping' => '#06b6d4',
                            'delivered' => '#22c55e',
                            'cancelled' => '#ef4444',
                            'refunded' => '#8b5cf6'
                        ];
                        $statusColor = $statusColors[$order['status']] ?? '#6b7280';

                        // Dịch trạng thái
                        $statusLabels = [
                            'pending' => 'Chờ xác nhận',
                            'confirmed' => 'Đã xác nhận',
                            'processing' => 'Đang xử lý',
                            'shipping' => 'Đang giao',
                            'delivered' => 'Đã giao',
                            'cancelled' => 'Đã hủy',
                            'refunded' => 'Đã hoàn tiền'
                        ];
                        $statusLabel = $statusLabels[$order['status']] ?? $order['status'];

                        // Dịch phương thức thanh toán
                        $paymentMethods = [
                            'cod' => 'Thanh toán khi nhận',
                            'bank_transfer' => 'Chuyển khoản'
                        ];
                        $paymentLabel = $paymentMethods[$order['payment_method']] ?? $order['payment_method'];
                        ?>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 15px; font-weight: 500;"><?= htmlspecialchars($order['order_code']) ?></td>
                            <td style="padding: 15px;"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                            <td style="padding: 15px; font-weight: 500; color: #e74c3c;"><?= formatPrice($order['final_amount']) ?></td>
                            <td style="padding: 15px;"><?= htmlspecialchars($paymentLabel) ?></td>
                            <td style="padding: 15px;">
                                <span style="
                                    display: inline-block;
                                    background: <?= $statusColor ?>;
                                    color: white;
                                    padding: 5px 12px;
                                    border-radius: 20px;
                                    font-size: 12px;
                                    font-weight: 500;
                                "><?= htmlspecialchars($statusLabel) ?></span>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <a href="<?= SITE_URL ?>/order_detail.php?id=<?= $order['id'] ?>" class="btn" style="
                                    padding: 8px 16px;
                                    background: #0066cc;
                                    color: white;
                                    border: none;
                                    border-radius: 5px;
                                    cursor: pointer;
                                    text-decoration: none;
                                    font-size: 12px;
                                    display: inline-block;
                                ">Xem chi tiết</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div style="margin-top: 30px; text-align: left;">
        <a href="<?= SITE_URL ?>/user_info.php" style="color: #0066cc; text-decoration: none;">← Quay lại tài khoản</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
