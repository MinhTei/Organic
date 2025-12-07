<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

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
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="margin-top: clamp(1.5rem, 4vw, 2rem); margin-bottom: clamp(2rem, 5vw, 3rem); padding: clamp(1rem, 3vw, 2rem);">
    <h1 style="margin-bottom: clamp(1.5rem, 3vw, 2rem); font-size: clamp(1.75rem, 5vw, 2.5rem);">Lịch sử đơn hàng</h1>

    <?php if (empty($orders)): ?>
        <div style="text-align: center; padding: clamp(2rem, 5vw, 3rem) clamp(1rem, 3vw, 1.5rem);">
            <p style="font-size: clamp(0.95rem, 2vw, 1rem); color: #666;">Bạn chưa có đơn hàng nào</p>
            <a href="<?= SITE_URL ?>/products.php" class="btn" style="margin-top: clamp(1rem, 2vw, 1.5rem); display: inline-block; padding: clamp(0.5rem, 1vw, 0.75rem) clamp(1rem, 2vw, 1.5rem);">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <!-- Desktop Table View -->
        <div class="order-table-desktop" style="overflow-x: auto; border-radius: clamp(0.5rem, 1vw, 0.75rem); display: none;">
            <table style="width: 100%; border-collapse: collapse; background: white;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: clamp(0.75rem, 1.5vw, 1rem); text-align: left; font-weight: 600; font-size: clamp(0.8rem, 1.5vw, 0.95rem);">Mã đơn hàng</th>
                        <th style="padding: clamp(0.75rem, 1.5vw, 1rem); text-align: left; font-weight: 600; font-size: clamp(0.8rem, 1.5vw, 0.95rem);">Ngày đặt</th>
                        <th style="padding: clamp(0.75rem, 1.5vw, 1rem); text-align: left; font-weight: 600; font-size: clamp(0.8rem, 1.5vw, 0.95rem);">Tổng tiền</th>
                        <th style="padding: clamp(0.75rem, 1.5vw, 1rem); text-align: left; font-weight: 600; font-size: clamp(0.8rem, 1.5vw, 0.95rem);">Phương thức thanh toán</th>
                        <th style="padding: clamp(0.75rem, 1.5vw, 1rem); text-align: left; font-weight: 600; font-size: clamp(0.8rem, 1.5vw, 0.95rem);">Trạng thái</th>
                        <th style="padding: clamp(0.75rem, 1.5vw, 1rem); text-align: center; font-weight: 600; font-size: clamp(0.8rem, 1.5vw, 0.95rem);">Hành động</th>
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
                            <td style="padding: clamp(0.75rem, 1.5vw, 1rem); font-weight: 500; font-size: clamp(0.8rem, 1.5vw, 0.95rem);"><?= htmlspecialchars($order['order_code']) ?></td>
                            <td style="padding: clamp(0.75rem, 1.5vw, 1rem); font-size: clamp(0.8rem, 1.5vw, 0.95rem);"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                            <td style="padding: clamp(0.75rem, 1.5vw, 1rem); font-weight: 500; color: #e74c3c; font-size: clamp(0.8rem, 1.5vw, 0.95rem);"><?= formatPrice($order['final_amount']) ?></td>
                            <td style="padding: clamp(0.75rem, 1.5vw, 1rem); font-size: clamp(0.8rem, 1.5vw, 0.95rem);"><?= htmlspecialchars($paymentLabel) ?></td>
                            <td style="padding: clamp(0.75rem, 1.5vw, 1rem);">
                                <span style="
                                    display: inline-block;
                                    background: <?= $statusColor ?>;
                                    color: white;
                                    padding: clamp(0.3rem, 0.5vw, 0.4rem) clamp(0.6rem, 1vw, 0.75rem);
                                    border-radius: 20px;
                                    font-size: clamp(0.7rem, 1.2vw, 0.85rem);
                                    font-weight: 500;
                                "><?= htmlspecialchars($statusLabel) ?></span>
                            </td>
                            <td style="padding: clamp(0.75rem, 1.5vw, 1rem); text-align: center;">
                                <a href="<?= SITE_URL ?>/order_detail.php?id=<?= $order['id'] ?>" class="btn" style="
                                    padding: clamp(0.4rem, 0.8vw, 0.6rem) clamp(0.8rem, 1.5vw, 1rem);
                                    background: #0066cc;
                                    color: white;
                                    border: none;
                                    border-radius: 5px;
                                    cursor: pointer;
                                    text-decoration: none;
                                    font-size: clamp(0.75rem, 1.2vw, 0.9rem);
                                    display: inline-block;
                                ">Xem chi tiết</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="order-cards-mobile" style="display: grid; gap: clamp(1rem, 2vw, 1.5rem);">
            <?php foreach ($orders as $order): ?>
                <?php
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

                $paymentMethods = [
                    'cod' => 'Thanh toán khi nhận',
                    'bank_transfer' => 'Chuyển khoản'
                ];
                $paymentLabel = $paymentMethods[$order['payment_method']] ?? $order['payment_method'];
                ?>
                <div style="background: white; border: 1px solid #dee2e6; border-radius: clamp(0.5rem, 1vw, 0.75rem); padding: clamp(1rem, 2vw, 1.5rem);">
                    <!-- Header -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: clamp(1rem, 1.5vw, 1.25rem); padding-bottom: clamp(0.75rem, 1.5vw, 1rem); border-bottom: 1px solid #f0f0f0;">
                        <div>
                            <p style="font-size: clamp(0.75rem, 1.5vw, 0.85rem); color: #999; margin: 0 0 clamp(0.25rem, 0.5vw, 0.5rem) 0;">Mã đơn hàng</p>
                            <p style="font-size: clamp(0.9rem, 2vw, 1.1rem); font-weight: 700; margin: 0; color: #0066cc;"><?= htmlspecialchars($order['order_code']) ?></p>
                        </div>
                        <span style="
                            display: inline-block;
                            background: <?= $statusColor ?>;
                            color: white;
                            padding: clamp(0.4rem, 0.8vw, 0.5rem) clamp(0.75rem, 1.2vw, 1rem);
                            border-radius: 20px;
                            font-size: clamp(0.7rem, 1.2vw, 0.8rem);
                            font-weight: 600;
                        "><?= htmlspecialchars($statusLabel) ?></span>
                    </div>

                    <!-- Details -->
                    <div style="display: grid; gap: clamp(0.75rem, 1.5vw, 1rem); margin-bottom: clamp(1rem, 1.5vw, 1.25rem);">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: clamp(0.8rem, 1.5vw, 0.9rem); color: #666;">Ngày đặt:</span>
                            <span style="font-size: clamp(0.8rem, 1.5vw, 0.9rem); font-weight: 500;"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: clamp(0.8rem, 1.5vw, 0.9rem); color: #666;">Phương thức:</span>
                            <span style="font-size: clamp(0.8rem, 1.5vw, 0.9rem); font-weight: 500;"><?= htmlspecialchars($paymentLabel) ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: clamp(0.75rem, 1.5vw, 1rem); border-top: 1px solid #f0f0f0;">
                            <span style="font-size: clamp(0.75rem, 1.5vw, 0.9rem); color: #666; font-weight: 500;">Tổng tiền:</span>
                            <span style="font-size: clamp(0.95rem, 2vw, 1.1rem); font-weight: 700; color: #e74c3c;"><?= formatPrice($order['final_amount']) ?></span>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <a href="<?= SITE_URL ?>/order_detail.php?id=<?= $order['id'] ?>" style="
                        display: block;
                        padding: clamp(0.6rem, 1vw, 0.75rem);
                        background: #0066cc;
                        color: white;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                        text-decoration: none;
                        font-size: clamp(0.85rem, 1.5vw, 0.95rem);
                        text-align: center;
                        font-weight: 500;
                    ">Xem chi tiết</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div style="margin-top: clamp(1.5rem, 3vw, 2rem); text-align: left;">
        <a href="<?= SITE_URL ?>/user_info.php" style="color: #0066cc; text-decoration: none; font-size: clamp(0.9rem, 1.8vw, 1rem);">← Quay lại tài khoản</a>
    </div>
</div>

<style>
    /* Desktop: Show table, hide cards */
    @media (min-width: 769px) {
        .order-table-desktop {
            display: block !important;
        }
        
        .order-cards-mobile {
            display: none !important;
        }
    }
    
    /* Tablet: Show cards with optimized sizing */
    @media (min-width: 481px) and (max-width: 768px) {
        .order-table-desktop {
            display: none !important;
        }
        
        .order-cards-mobile {
            display: grid !important;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important;
            gap: clamp(1rem, 2vw, 1.5rem) !important;
        }
    }
    
    /* Mobile: Show cards */
    @media (max-width: 480px) {
        .order-table-desktop {
            display: none !important;
        }
        
        .order-cards-mobile {
            display: grid !important;
            grid-template-columns: 1fr !important;
        }
        
        h1 {
            font-size: clamp(1.25rem, 5vw, 1.75rem) !important;
        }
    }
    
    /* General table styling for desktop */
    @media (min-width: 769px) {
        table {
            font-size: clamp(0.8rem, 1.5vw, 0.95rem);
        }
        
        th, td {
            padding: clamp(0.75rem, 1.5vw, 1rem);
        }
        
        .btn {
            padding: clamp(0.4rem, 0.8vw, 0.6rem) clamp(0.8rem, 1.5vw, 1rem);
            font-size: clamp(0.75rem, 1.2vw, 0.9rem);
        }
    }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
