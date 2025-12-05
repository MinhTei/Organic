
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

<main class="container" style="max-width: 700px; margin: 3rem auto; padding: 2rem 1rem;">
    <div style="background: linear-gradient(135deg, #e7fbe7 0%, #b6e633 100%); border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 2.5rem 2rem;">
        <div style="text-align:center; margin-bottom:2rem;">
            <span class="material-symbols-outlined" style="font-size: 3rem; color: var(--success); margin-bottom: 1rem;">check_circle</span>
            <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; color:var(--success);">Đặt Hàng Thành Công!</h1>
            <p style="font-size: 1.1rem; color: var(--muted-light);">Cảm ơn bạn đã tin tưởng và mua sắm tại Xanh Organic</p>
        </div>

        <!-- Thông tin đơn hàng -->
        <div style="background: var(--background-light); border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 2rem;">
            <h2 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="color:var(--primary-dark);">receipt_long</span>
                Thông Tin Đơn Hàng
            </h2>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
                <div>
                    <p><strong>Mã đơn hàng:</strong> <?= htmlspecialchars($orderInfo['order_code']) ?></p>
                    <p><strong>Tổng tiền:</strong> <span style="color:var(--danger); font-size:1.15rem; font-weight:700;"><?= formatPrice($orderInfo['final_amount']) ?></span></p>
                </div>
                <div>
                    <p><strong>Ngày đặt hàng:</strong> <?= date('d/m/Y H:i', strtotime($orderInfo['created_at'])) ?></p>
                    <p><strong>Phương thức thanh toán:</strong> <?= $orderInfo['payment_method'] === 'cod' ? 'Thanh toán khi nhận hàng' : 'Chuyển khoản ngân hàng' ?></p>
                </div>
            </div>
        </div>

        <!-- Thông tin nhận hàng -->
        <div style="background: #f7f8f6; border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 2rem;">
            <h2 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="color:var(--primary-dark);">person</span>
                Thông Tin Nhận Hàng
            </h2>
            <p><strong>Người nhận:</strong> <?= htmlspecialchars($orderInfo['shipping_name']) ?></p>
            <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($orderInfo['shipping_phone']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($orderInfo['shipping_email'] ?? $orderInfo['user_email'] ?? '') ?></p>
            <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($orderInfo['shipping_address']) ?><?= $orderInfo['shipping_ward'] ? ', ' . htmlspecialchars($orderInfo['shipping_ward']) : '' ?><?= $orderInfo['shipping_district'] ? ', ' . htmlspecialchars($orderInfo['shipping_district']) : '' ?><?= $orderInfo['shipping_city'] ? ', ' . htmlspecialchars($orderInfo['shipping_city']) : '' ?></p>
            <?php if (!empty($orderInfo['note'])): ?>
            <p><strong>Ghi chú:</strong> <?= htmlspecialchars($orderInfo['note']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Chi tiết sản phẩm -->
        <div style="background: white; border-radius: 0.75rem; border:1px solid var(--border-light); padding: 1.5rem; margin-bottom: 2rem;">
            <h2 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem; display:flex; align-items:center; gap:0.5rem;">
                <span class="material-symbols-outlined" style="color:var(--primary-dark);">inventory_2</span>
                Chi Tiết Sản Phẩm
            </h2>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:var(--background-light);">
                        <th style="padding:0.75rem; text-align:left; font-weight:600;">Sản phẩm</th>
                        <th style="padding:0.75rem; text-align:center; font-weight:600;">Số lượng</th>
                        <th style="padding:0.75rem; text-align:right; font-weight:600;">Đơn giá</th>
                        <th style="padding:0.75rem; text-align:right; font-weight:600;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td style="padding:0.75rem; border-bottom:1px solid var(--border-light);">
                            <?= htmlspecialchars($item['product_name']) ?>
                        </td>
                        <td style="padding:0.75rem; text-align:center; border-bottom:1px solid var(--border-light);">
                            <?= $item['quantity'] ?>
                        </td>
                        <td style="padding:0.75rem; text-align:right; border-bottom:1px solid var(--border-light);">
                            <?= formatPrice($item['unit_price']) ?>
                        </td>
                        <td style="padding:0.75rem; text-align:right; border-bottom:1px solid var(--border-light);">
                            <?= formatPrice($item['total_price']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="padding:0.75rem; text-align:right; font-weight:700;">Tổng cộng:</td>
                        <td style="padding:0.75rem; text-align:right; color:var(--danger); font-weight:700; font-size:1.1rem;">
                            <?= formatPrice($orderInfo['final_amount']) ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Các bước tiếp theo -->
        <div style="background: #e7fbe7; border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 2rem;">
            <h2 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem; display:flex; align-items:center; gap:0.5rem; color:var(--success);">
                <span class="material-symbols-outlined" style="color:var(--success);">task_alt</span>
                Các Bước Tiếp Theo
            </h2>
            <ul style="margin-left:1.5rem; color:var(--muted-light); font-size:1rem;">
                <li>Chúng tôi sẽ xác nhận đơn hàng của bạn trong vòng 30 phút</li>
                <li>Đơn hàng sẽ được chuẩn bị và đóng gói cẩn thận</li>
                <li>Giao hàng nhanh trong 2-4 giờ tại TP.HCM</li>
                <li>Bạn sẽ nhận được thông báo qua SMS/Email khi đơn hàng được giao</li>
            </ul>
        </div>

        <!-- Nút điều hướng -->
        <div style="display:flex; gap:1rem; justify-content:center;">
            <a href="<?= SITE_URL ?>/user_info.php?tab=orders" class="btn btn-secondary">Xem Đơn Hàng</a>
            <a href="<?= SITE_URL ?>/products.php" class="btn btn-primary">Tiếp Tục Mua Sắm</a>
            <a href="<?= SITE_URL ?>" class="btn btn-secondary">Về Trang Chủ</a>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<?php unset($_SESSION['order_success']); ?>
