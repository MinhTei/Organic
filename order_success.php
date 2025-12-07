
<?php
/**
 * order_success.php - Trang xác nhận đặt hàng thành công (đầy đủ thông tin)
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Lấy thông tin đơn hàng từ session
$order = $_SESSION['order_success'] ?? null;
if (!$order) {
    redirect(SITE_URL);
}

// Lấy chi tiết đơn hàng từ DB
$conn = getConnection();

$stmt = $conn->prepare("SELECT o.*, u.email as user_email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = :id");
$stmt->execute([':id' => $order['order_id']]);
$orderInfo = $stmt->fetch();

$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
$stmt->execute([':order_id' => $order['order_id']]);
$items = $stmt->fetchAll();

$pageTitle = 'Đặt hàng thành công';
include __DIR__ . '/includes/header.php';
?>

<main style="max-width: clamp(300px, 90vw, 700px); margin: clamp(1.5rem, 5vw, 3rem) auto; padding: clamp(1rem, 3vw, 2rem);">
    <div style="background: linear-gradient(135deg, #e7fbe7 0%, #b6e633 100%); border-radius: clamp(0.75rem, 2vw, 1rem); box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: clamp(1.5rem, 3vw, 2.5rem) clamp(1rem, 2vw, 2rem);">
        <div style="text-align: center; margin-bottom: clamp(1.5rem, 3vw, 2rem);">
            <span class="material-symbols-outlined" style="font-size: clamp(2rem, 12vw, 3rem); color: var(--success); margin-bottom: clamp(0.75rem, 1.5vw, 1rem); display: block;">check_circle</span>
            <h1 style="font-size: clamp(1.5rem, 5vw, 2rem); font-weight: 700; margin-bottom: clamp(0.35rem, 1vw, 0.5rem); color: var(--success);">Đặt Hàng Thành Công!</h1>
            <p style="font-size: clamp(0.95rem, 2.5vw, 1.1rem); color: var(--muted-light);">Cảm ơn bạn đã tin tưởng và mua sắm tại Xanh Organic</p>
        </div>

        <!-- Thông tin đơn hàng -->
        <div style="background: var(--background-light); border-radius: clamp(0.5rem, 1.5vw, 0.75rem); padding: clamp(1rem, 2vw, 1.5rem); margin-bottom: clamp(1.5rem, 3vw, 2rem);">
            <h2 style="font-size: clamp(1rem, 2.5vw, 1.1rem); font-weight: 700; margin-bottom: clamp(0.75rem, 1.5vw, 1rem); display: flex; align-items: center; gap: clamp(0.35rem, 1vw, 0.5rem); color: var(--text-light);">
                <span class="material-symbols-outlined" style="font-size: clamp(1.125rem, 2vw, 1.25rem); color: var(--primary-dark);">receipt_long</span>
                Thông Tin Đơn Hàng
            </h2>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: clamp(1rem, 2vw, 1.5rem);">
                <div style="font-size: clamp(0.875rem, 1.5vw, 1rem);">
                    <p style="margin-bottom: clamp(0.35rem, 0.75vw, 0.5rem);"><strong>Mã đơn hàng:</strong></p>
                    <p style="color: var(--primary); font-weight: 600; word-break: break-all;"><?= htmlspecialchars($orderInfo['order_code']) ?></p>
                    <p style="margin-top: clamp(0.5rem, 1vw, 0.75rem); margin-bottom: clamp(0.35rem, 0.75vw, 0.5rem);"><strong>Tổng tiền:</strong></p>
                    <p style="color: var(--danger); font-size: clamp(1rem, 2.5vw, 1.15rem); font-weight: 700;"><?= formatPrice($orderInfo['final_amount']) ?></p>
                </div>
                <div style="font-size: clamp(0.875rem, 1.5vw, 1rem);">
                    <p style="margin-bottom: clamp(0.35rem, 0.75vw, 0.5rem);"><strong>Ngày đặt hàng:</strong></p>
                    <p><?= date('d/m/Y H:i', strtotime($orderInfo['created_at'])) ?></p>
                    <p style="margin-top: clamp(0.5rem, 1vw, 0.75rem); margin-bottom: clamp(0.35rem, 0.75vw, 0.5rem);"><strong>Phương thức:</strong></p>
                    <p><?= $orderInfo['payment_method'] === 'cod' ? 'Thanh toán khi nhận hàng' : 'Chuyển khoản ngân hàng' ?></p>
                </div>
            </div>
        </div>

        <!-- Thông tin nhận hàng -->
        <div style="background: #f7f8f6; border-radius: clamp(0.5rem, 1.5vw, 0.75rem); padding: clamp(1rem, 2vw, 1.5rem); margin-bottom: clamp(1.5rem, 3vw, 2rem); font-size: clamp(0.875rem, 1.5vw, 1rem);">
            <h2 style="font-size: clamp(1rem, 2.5vw, 1.1rem); font-weight: 700; margin-bottom: clamp(0.75rem, 1.5vw, 1rem); display: flex; align-items: center; gap: clamp(0.35rem, 1vw, 0.5rem); color: var(--text-light);">
                <span class="material-symbols-outlined" style="font-size: clamp(1.125rem, 2vw, 1.25rem); color: var(--primary-dark);">person</span>
                Thông Tin Nhận Hàng
            </h2>
            <div style="display: grid; gap: clamp(0.5rem, 1vw, 0.75rem);">
                <p><strong>Người nhận:</strong> <?= htmlspecialchars($orderInfo['shipping_name']) ?></p>
                <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($orderInfo['shipping_phone']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($orderInfo['shipping_email'] ?? $orderInfo['user_email'] ?? '') ?></p>
                <p><strong>Địa chỉ:</strong> <span style="word-break: break-word;"><?= htmlspecialchars($orderInfo['shipping_address']) ?><?= $orderInfo['shipping_ward'] ? ', ' . htmlspecialchars($orderInfo['shipping_ward']) : '' ?><?= $orderInfo['shipping_district'] ? ', ' . htmlspecialchars($orderInfo['shipping_district']) : '' ?><?= $orderInfo['shipping_city'] ? ', ' . htmlspecialchars($orderInfo['shipping_city']) : '' ?></span></p>
                <?php if (!empty($orderInfo['note'])): ?>
                <p><strong>Ghi chú:</strong> <?= htmlspecialchars($orderInfo['note']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Chi tiết sản phẩm -->
        <div style="background: white; border-radius: clamp(0.5rem, 1.5vw, 0.75rem); border: 1px solid var(--border-light); padding: clamp(1rem, 2vw, 1.5rem); margin-bottom: clamp(1.5rem, 3vw, 2rem);">
            <h2 style="font-size: clamp(1rem, 2.5vw, 1.1rem); font-weight: 700; margin-bottom: clamp(0.75rem, 1.5vw, 1rem); display: flex; align-items: center; gap: clamp(0.35rem, 1vw, 0.5rem); color: var(--text-light);">
                <span class="material-symbols-outlined" style="font-size: clamp(1.125rem, 2vw, 1.25rem); color: var(--primary-dark);">inventory_2</span>
                Chi Tiết Sản Phẩm
            </h2>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: clamp(0.75rem, 1.5vw, 0.9rem);">
                    <thead>
                        <tr style="background: var(--background-light);">
                            <th style="padding: clamp(0.5rem, 1vw, 0.75rem); text-align: left; font-weight: 600;">Sản phẩm</th>
                            <th style="padding: clamp(0.5rem, 1vw, 0.75rem); text-align: center; font-weight: 600;">Số lượng</th>
                            <th style="padding: clamp(0.5rem, 1vw, 0.75rem); text-align: right; font-weight: 600;">Đơn giá</th>
                            <th style="padding: clamp(0.5rem, 1vw, 0.75rem); text-align: right; font-weight: 600;">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td style="padding: clamp(0.5rem, 1vw, 0.75rem); border-bottom: 1px solid var(--border-light);">
                                <?= htmlspecialchars($item['product_name']) ?>
                            </td>
                            <td style="padding: clamp(0.5rem, 1vw, 0.75rem); text-align: center; border-bottom: 1px solid var(--border-light);">
                                <?= $item['quantity'] ?>
                            </td>
                            <td style="padding: clamp(0.5rem, 1vw, 0.75rem); text-align: right; border-bottom: 1px solid var(--border-light);">
                                <?= formatPrice($item['unit_price']) ?>
                            </td>
                            <td style="padding: clamp(0.5rem, 1vw, 0.75rem); text-align: right; border-bottom: 1px solid var(--border-light);">
                                <?= formatPrice($item['total_price']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="padding: clamp(0.5rem, 1vw, 0.75rem); text-align: right; font-weight: 700;">Tổng cộng:</td>
                            <td style="padding: clamp(0.5rem, 1vw, 0.75rem); text-align: right; color: var(--danger); font-weight: 700; font-size: clamp(0.875rem, 2vw, 1.1rem);">
                                <?= formatPrice($orderInfo['final_amount']) ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Các bước tiếp theo -->
        <div style="background: #e7fbe7; border-radius: clamp(0.5rem, 1.5vw, 0.75rem); padding: clamp(1rem, 2vw, 1.5rem); margin-bottom: clamp(1.5rem, 3vw, 2rem);">
            <h2 style="font-size: clamp(1rem, 2.5vw, 1.1rem); font-weight: 700; margin-bottom: clamp(0.75rem, 1.5vw, 1rem); display: flex; align-items: center; gap: clamp(0.35rem, 1vw, 0.5rem); color: var(--success);">
                <span class="material-symbols-outlined" style="font-size: clamp(1.125rem, 2vw, 1.25rem); color: var(--success);">task_alt</span>
                Các Bước Tiếp Theo
            </h2>
            <ul style="margin-left: clamp(1rem, 2vw, 1.5rem); color: var(--muted-light); font-size: clamp(0.875rem, 1.5vw, 1rem); line-height: 1.6;">
                <li>Chúng tôi sẽ xác nhận đơn hàng của bạn trong vòng 30 phút</li>
                <li>Đơn hàng sẽ được chuẩn bị và đóng gói cẩn thận</li>
                <li>Giao hàng nhanh trong 2-4 giờ tại TP.HCM</li>
                <li>Bạn sẽ nhận được thông báo qua SMS/Email khi đơn hàng được giao</li>
            </ul>
        </div>

        <!-- Nút điều hướng -->
        <div style="display: flex; gap: clamp(0.5rem, 1vw, 1rem); justify-content: center; flex-wrap: wrap;">
            <a href="<?= SITE_URL ?>/user_info.php?tab=orders" style="padding: clamp(0.6rem, 1.5vw, 0.75rem) clamp(1rem, 2vw, 1.25rem); background: #ffd966; color: #333; text-decoration: none; border-radius: clamp(0.35rem, 1vw, 0.5rem); font-weight: 600; font-size: clamp(0.8rem, 1.5vw, 0.9rem); transition: background 0.3s;">Xem Đơn Hàng</a>
            <a href="<?= SITE_URL ?>/products.php" style="padding: clamp(0.6rem, 1.5vw, 0.75rem) clamp(1rem, 2vw, 1.25rem); background: #52a32d; color: white; text-decoration: none; border-radius: clamp(0.35rem, 1vw, 0.5rem); font-weight: 600; font-size: clamp(0.8rem, 1.5vw, 0.9rem); transition: background 0.3s;">Tiếp Tục Mua Sắm</a>
            <a href="<?= SITE_URL ?>" style="padding: clamp(0.6rem, 1.5vw, 0.75rem) clamp(1rem, 2vw, 1.25rem); background: #d3d3d3; color: #333; text-decoration: none; border-radius: clamp(0.35rem, 1vw, 0.5rem); font-weight: 600; font-size: clamp(0.8rem, 1.5vw, 0.9rem); transition: background 0.3s;">Về Trang Chủ</a>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<?php unset($_SESSION['order_success']); ?>
