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
        try {
            $conn->beginTransaction();
            
            // Khôi phục số lượng sản phẩm trong kho
            $orderItemsStmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $orderItemsStmt->execute([$orderId]);
            $orderItems = $orderItemsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($orderItems as $item) {
                $restoreStmt = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
                $restoreStmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Cập nhật trạng thái đơn hàng
            $updateStmt = $conn->prepare("UPDATE orders SET status = 'cancelled', cancelled_at = NOW() WHERE id = ?");
            $updateStmt->execute([$orderId]);
            
            $conn->commit();
            $cancelMessage = 'Đơn hàng đã được hủy thành công! Số lượng sản phẩm đã được khôi phục.';
        } catch (Exception $e) {
            $conn->rollBack();
            $cancelMessage = 'Có lỗi xảy ra khi hủy đơn hàng. Vui lòng thử lại.';
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

<main class="container" style="padding: clamp(1rem, 3vw, 2rem); max-width: 1200px;">
    <!-- Breadcrumb -->
    <div class="breadcrumb" style="font-size: clamp(0.8rem, 1.5vw, 0.95rem); margin-bottom: clamp(1rem, 2vw, 1.5rem);">
        <a href="<?= SITE_URL ?>" style="color: var(--primary); text-decoration: none;">Trang chủ</a>
        <span class="material-symbols-outlined" style="font-size: clamp(0.9rem, 1.5vw, 1rem);">chevron_right</span>
        <a href="<?= SITE_URL ?>/order_history.php" style="color: var(--primary); text-decoration: none;">Lịch sử đơn hàng</a>
        <span class="material-symbols-outlined" style="font-size: clamp(0.9rem, 1.5vw, 1rem);">chevron_right</span>
        <span class="current" style="color: var(--muted-light);"><?= htmlspecialchars($order['order_code']) ?></span>
    </div>

    <?php if (!empty($cancelMessage)): ?>
        <div style="margin-bottom: clamp(1rem, 2vw, 1.5rem); padding: clamp(0.75rem, 1.5vw, 1rem); background: rgba(34, 197, 94, 0.1); border-left: 4px solid var(--success); border-radius: 0.5rem; color: var(--success); font-size: clamp(0.85rem, 1.8vw, 0.95rem);">
            <strong>Thành công:</strong> <?= $cancelMessage ?>
        </div>
    <?php endif; ?>

    <h1 style="font-size: clamp(1.5rem, 4vw, 2.5rem); font-weight: 700; margin: clamp(1.5rem, 3vw, 2rem) 0;">Chi tiết đơn hàng #<?= htmlspecialchars($order['order_code']) ?></h1>

    <div style="display: grid; grid-template-columns: 1fr clamp(250px, 30vw, 400px); gap: clamp(1.5rem, 3vw, 2rem);"
        <!-- Left Column -->
        <div>
            <!-- Order Status Card -->
            <div style="background: white; border-radius: clamp(0.5rem, 1vw, 1rem); padding: clamp(1.25rem, 2.5vw, 2rem); margin-bottom: clamp(1.5rem, 2vw, 2rem); border: 1px solid var(--border-light);">
                <h2 style="font-size: clamp(1rem, 2vw, 1.25rem); font-weight: 700; margin-bottom: clamp(1rem, 2vw, 1.5rem); display: flex; align-items: center; gap: clamp(0.3rem, 0.8vw, 0.5rem);">
                    <span class="material-symbols-outlined" style="font-size: clamp(1rem, 2vw, 1.25rem);">info</span>
                    Thông tin đơn hàng
                </h2>

                <div style="display: grid; gap: clamp(0.75rem, 1.5vw, 1rem);">
                    <div style="display: grid; grid-template-columns: clamp(80px, 20vw, 150px) 1fr; gap: clamp(0.75rem, 1.5vw, 1rem); font-size: clamp(0.85rem, 1.8vw, 0.95rem);">
                        <span style="color: var(--muted-light);">Mã đơn hàng:</span>
                        <span style="font-weight: 600; color: var(--primary);"><?= htmlspecialchars($order['order_code']) ?></span>
                    </div>

                    <div style="display: grid; grid-template-columns: clamp(80px, 20vw, 150px) 1fr; gap: clamp(0.75rem, 1.5vw, 1rem); font-size: clamp(0.85rem, 1.8vw, 0.95rem);">
                        <span style="color: var(--muted-light);">Ngày đặt:</span>
                        <span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                    </div>

                    <div style="display: grid; grid-template-columns: clamp(80px, 20vw, 150px) 1fr; gap: clamp(0.75rem, 1.5vw, 1rem); font-size: clamp(0.85rem, 1.8vw, 0.95rem);">
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

                    <div style="display: grid; grid-template-columns: clamp(80px, 20vw, 150px) 1fr; gap: clamp(0.75rem, 1.5vw, 1rem); font-size: clamp(0.85rem, 1.8vw, 0.95rem);">
                        <span style="color: var(--muted-light);">Phương thức:</span>
                        <span><?= $order['payment_method'] === 'cod' ? 'Thanh toán khi nhận hàng (COD)' : 'Chuyển khoản ngân hàng' ?></span>
                    </div>

                    <div style="display: grid; grid-template-columns: clamp(80px, 20vw, 150px) 1fr; gap: clamp(0.75rem, 1.5vw, 1rem); font-size: clamp(0.85rem, 1.8vw, 0.95rem);">
                        <span style="color: var(--muted-light);">Trạng thái thanh toán:</span>
                        <span><?= $order['payment_status'] === 'pending' ? 'Chưa thanh toán' : ($order['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Lỗi') ?></span>
                    </div>

                    <?php if (!empty($order['tracking_number'])): ?>
                    <div style="display: grid; grid-template-columns: clamp(80px, 20vw, 150px) 1fr; gap: clamp(0.75rem, 1.5vw, 1rem); font-size: clamp(0.85rem, 1.8vw, 0.95rem);">
                        <span style="color: var(--muted-light);">Mã vận đơn:</span>
                        <span style="font-weight: 600;"><?= htmlspecialchars($order['tracking_number']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Shipping Information -->
            <div style="background: white; border-radius: clamp(0.5rem, 1vw, 1rem); padding: clamp(1.25rem, 2.5vw, 2rem); margin-bottom: clamp(1.5rem, 2vw, 2rem); border: 1px solid var(--border-light);">
                <h2 style="font-size: clamp(1rem, 2vw, 1.25rem); font-weight: 700; margin-bottom: clamp(1rem, 2vw, 1.5rem); display: flex; align-items: center; gap: clamp(0.3rem, 0.8vw, 0.5rem);">
                    <span class="material-symbols-outlined" style="font-size: clamp(1rem, 2vw, 1.25rem);">local_shipping</span>
                    Thông tin giao hàng
                </h2>

                <div style="display: grid; gap: clamp(0.75rem, 1.5vw, 1rem);">
                    <div style="display: grid; grid-template-columns: clamp(80px, 20vw, 150px) 1fr; gap: clamp(0.75rem, 1.5vw, 1rem); font-size: clamp(0.85rem, 1.8vw, 0.95rem);">
                        <span style="color: var(--muted-light);">Người nhận:</span>
                        <span style="font-weight: 600;"><?= htmlspecialchars($order['shipping_name']) ?></span>
                    </div>

                    <div style="display: grid; grid-template-columns: clamp(80px, 20vw, 150px) 1fr; gap: clamp(0.75rem, 1.5vw, 1rem); font-size: clamp(0.85rem, 1.8vw, 0.95rem);">
                        <span style="color: var(--muted-light);">Số điện thoại:</span>
                        <span><?= htmlspecialchars($order['shipping_phone']) ?></span>
                    </div>

                    <div style="display: grid; grid-template-columns: clamp(80px, 20vw, 150px) 1fr; gap: clamp(0.75rem, 1.5vw, 1rem); font-size: clamp(0.85rem, 1.8vw, 0.95rem);">
                        <span style="color: var(--muted-light);">Email:</span>
                        <span><?= htmlspecialchars($order['shipping_email'] ?? '') ?></span>
                    </div>

                    <div style="display: grid; grid-template-columns: clamp(80px, 20vw, 150px) 1fr; gap: clamp(0.75rem, 1.5vw, 1rem); font-size: clamp(0.85rem, 1.8vw, 0.95rem);">
                        <span style="color: var(--muted-light);">Địa chỉ:</span>
                        <span><?= htmlspecialchars($order['shipping_address']) ?></span>
                    </div>

                    <div style="display: grid; grid-template-columns: clamp(80px, 20vw, 150px) 1fr; gap: clamp(0.75rem, 1.5vw, 1rem); font-size: clamp(0.85rem, 1.8vw, 0.95rem);">
                        <span style="color: var(--muted-light);">Phường/Xã:</span>
                        <span><?= htmlspecialchars($order['shipping_ward']) ?></span>
                    </div>

                    <div style="display: grid; grid-template-columns: clamp(80px, 20vw, 150px) 1fr; gap: clamp(0.75rem, 1.5vw, 1rem); font-size: clamp(0.85rem, 1.8vw, 0.95rem);">
                        <span style="color: var(--muted-light);">Quận/Huyện:</span>
                        <span><?= htmlspecialchars($order['shipping_district']) ?></span>
                    </div>

                    <div style="display: grid; grid-template-columns: clamp(80px, 20vw, 150px) 1fr; gap: clamp(0.75rem, 1.5vw, 1rem); font-size: clamp(0.85rem, 1.8vw, 0.95rem);">
                        <span style="color: var(--muted-light);">Thành phố:</span>
                        <span><?= htmlspecialchars($order['shipping_city']) ?></span>
                    </div>

                    <?php if (!empty($order['note'])): ?>
                    <div style="display: grid; grid-template-columns: clamp(80px, 20vw, 150px) 1fr; gap: clamp(0.75rem, 1.5vw, 1rem); font-size: clamp(0.85rem, 1.8vw, 0.95rem);">
                        <span style="color: var(--muted-light);">Ghi chú:</span>
                        <span><?= htmlspecialchars($order['note']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Items -->
            <div style="background: white; border-radius: clamp(0.5rem, 1vw, 1rem); padding: clamp(1.25rem, 2.5vw, 2rem); border: 1px solid var(--border-light);">
                <h2 style="font-size: clamp(1rem, 2vw, 1.25rem); font-weight: 700; margin-bottom: clamp(1rem, 2vw, 1.5rem); display: flex; align-items: center; gap: clamp(0.3rem, 0.8vw, 0.5rem);">
                    <span class="material-symbols-outlined" style="font-size: clamp(1rem, 2vw, 1.25rem);">shopping_cart</span>
                    Sản phẩm
                </h2>

                <div style="display: grid; gap: clamp(1rem, 2vw, 1.5rem);">
                    <?php foreach ($orderItems as $item): ?>
                        <div style="display: grid; grid-template-columns: clamp(100px, 25vw, 150px) 1fr; gap: clamp(1rem, 2vw, 1.5rem); padding: clamp(1rem, 2vw, 1.5rem); background: #f9f9f9; border-radius: 0.5rem; align-items: start;">
                            <img src="<?= SITE_URL ?>/<?= htmlspecialchars($item['product_image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" style="width: clamp(100px, 25vw, 150px); height: clamp(100px, 25vw, 150px); object-fit: cover; border-radius: 0.5rem;">
                            
                            <div style="display: flex; flex-direction: column; justify-content: space-between; min-height: clamp(100px, 25vw, 150px);">
                                <div>
                                    <p style="font-weight: 700; font-size: clamp(0.95rem, 2vw, 1.1rem); margin-bottom: 0.5rem;"><?= htmlspecialchars($item['product_name']) ?></p>
                                    <p style="color: var(--muted-light); font-size: clamp(0.85rem, 1.8vw, 0.95rem); margin-bottom: 0.75rem;"><?= formatPrice($item['unit_price']) ?>/item</p>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: clamp(1rem, 2vw, 2rem); padding-top: clamp(0.75rem, 1.5vw, 1rem); border-top: 1px solid #e0e0e0;">
                                    <div>
                                        <p style="color: var(--muted-light); font-size: clamp(0.75rem, 1.5vw, 0.875rem); margin-bottom: 0.25rem;">Số lượng</p>
                                        <p style="font-weight: 600; font-size: clamp(0.9rem, 1.8vw, 1rem);">x<?= $item['quantity'] ?></p>
                                    </div>
                                    <div style="text-align: right;">
                                        <p style="color: var(--muted-light); font-size: clamp(0.75rem, 1.5vw, 0.875rem); margin-bottom: 0.25rem;">Tổng</p>
                                        <p style="font-weight: 700; color: var(--primary); font-size: clamp(0.95rem, 2vw, 1.1rem);"><?= formatPrice($item['total_price']) ?></p>
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
            <div style="background: white; border-radius: clamp(0.5rem, 1vw, 1rem); padding: clamp(1.25rem, 2.5vw, 2rem); border: 1px solid var(--border-light); position: sticky; top: clamp(1rem, 2vw, 2rem);">
                <h2 style="font-size: clamp(1rem, 2vw, 1.25rem); font-weight: 700; margin-bottom: clamp(1rem, 2vw, 1.5rem);">Tóm tắt đơn hàng</h2>

                <div style="display: grid; gap: clamp(0.75rem, 1.5vw, 1rem); margin-bottom: clamp(1rem, 2vw, 1.5rem); padding-bottom: clamp(1rem, 2vw, 1.5rem); border-bottom: 1px solid var(--border-light);">
                    <div style="display: flex; justify-content: space-between; font-size: clamp(0.85rem, 1.8vw, 0.95rem);">
                        <span style="color: var(--muted-light);">Tạm tính</span>
                        <span><?= formatPrice($order['total_amount']) ?></span>
                    </div>

                    <?php if ($order['discount_amount'] > 0): ?>
                    <div style="display: flex; justify-content: space-between; color: var(--success); font-size: clamp(0.85rem, 1.8vw, 0.95rem);">
                        <span>Giảm giá</span>
                        <span>-<?= formatPrice($order['discount_amount']) ?></span>
                    </div>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between; font-size: clamp(0.85rem, 1.8vw, 0.95rem);">
                        <span style="color: var(--muted-light);">Phí vận chuyển</span>
                        <span><?= $order['shipping_fee'] == 0 ? 'Miễn phí' : formatPrice($order['shipping_fee']) ?></span>
                    </div>

                    <?php if ($order['tax_amount'] > 0): ?>
                    <div style="display: flex; justify-content: space-between; font-size: clamp(0.85rem, 1.8vw, 0.95rem);">
                        <span style="color: var(--muted-light);">Thuế</span>
                        <span><?= formatPrice($order['tax_amount']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <div style="display: flex; justify-content: space-between; padding: clamp(0.75rem, 1.5vw, 1rem) 0; font-size: clamp(1rem, 2vw, 1.25rem); font-weight: 700;">
                    <span>Tổng cộng</span>
                    <span style="color: var(--primary);"><?= formatPrice($order['final_amount']) ?></span>
                </div>

                <?php if ($order['coupon_code']): ?>
                <div style="margin-top: clamp(0.75rem, 1.5vw, 1rem); padding: clamp(0.5rem, 1vw, 0.75rem); background: rgba(34, 197, 94, 0.1); border-radius: 0.5rem; font-size: clamp(0.8rem, 1.5vw, 0.875rem);">
                    Mã giảm giá: <strong><?= htmlspecialchars($order['coupon_code']) ?></strong>
                </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div style="margin-top: clamp(1.5rem, 2.5vw, 2rem); display: grid; gap: clamp(0.75rem, 1.5vw, 1rem);">
                    <a href="<?= SITE_URL ?>/order_history.php" class="btn" style="padding: clamp(0.6rem, 1vw, 0.75rem) clamp(0.8rem, 1.5vw, 1rem); background: var(--border-light); color: var(--text); text-decoration: none; border-radius: 0.5rem; text-align: center; cursor: pointer; font-size: clamp(0.85rem, 1.8vw, 0.95rem);">
                        Quay lại lịch sử 
                    </a>

                    <?php if (in_array($order['status'], ['pending', 'confirmed', 'processing'])): ?>
                    <form method="POST" onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này?');">
                        <button type="submit" name="cancel_order" value="1" class="btn" style="padding: clamp(0.6rem, 1vw, 0.75rem) clamp(0.8rem, 1.5vw, 1rem); background: var(--danger); color: white; border: none; border-radius: 0.5rem; cursor: pointer; width: 100%; font-size: clamp(0.85rem, 1.8vw, 0.95rem);">
                            Hủy đơn hàng
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    @media (min-width: 769px) and (max-width: 1024px) {
        main > div {
            grid-template-columns: 1fr !important;
        }
        
        .order-summary {
            position: static !important;
            margin-top: clamp(1.5rem, 2vw, 2rem);
        }
    }
    
    @media (max-width: 1024px) {
        main > div {
            grid-template-columns: 1fr !important;
        }
        
        .order-summary {
            position: static !important;
            margin-top: clamp(1.5rem, 2vw, 2rem);
        }
    }
    
    @media (max-width: 768px) {
        main {
            padding: clamp(0.75rem, 2vw, 1rem) !important;
        }
        
        h1 {
            font-size: clamp(1.25rem, 4vw, 1.75rem) !important;
        }
        
        h2 {
            font-size: clamp(0.9rem, 2vw, 1.1rem) !important;
        }
        
        .order-item {
            grid-template-columns: clamp(80px, 20vw, 120px) 1fr !important;
        }
        
        .order-item img {
            width: clamp(80px, 20vw, 120px) !important;
            height: clamp(80px, 20vw, 120px) !important;
        }
    }
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
