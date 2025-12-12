<?php

/**
 * thanhtoan.php - Trang thanh toán
 * 
 * Chức năng:
 * - Chọn địa chỉ giao hàng (đã lưu hoặc nhập mới)
 * - Hiển thị form thông tin người nhận
 * - Chọn phương thức thanh toán (COD, Chuyển khoản)
 * - Áp dụng mã giảm giá
 * - Tính phí vận chuyển
 * - Xác nhận và tạo đơn hàng

 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/email_functions.php';
require_once __DIR__ . '/includes/settings_helper.php';

$success = '';
$error = '';

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    redirect(SITE_URL . '/cart.php');
}

// Check login status
if (!isset($_SESSION['user_id'])) {
    redirect(SITE_URL . '/auth.php?redirect=thanhtoan');
}

// Get cart items
$conn = getConnection();
$cartItems = [];
$subtotal = 0;
$userId = $_SESSION['user_id'];

$ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$products = $stmt->fetchAll();

foreach ($products as $product) {
    $qty = $_SESSION['cart'][$product['id']];
    $price = $product['sale_price'] ?? $product['price'];
    $itemTotal = $price * $qty;
    $subtotal += $itemTotal;

    $cartItems[] = [
        'product' => $product,
        'quantity' => $qty,
        'price' => $price,
        'total' => $itemTotal
    ];
}

// Get user info and saved addresses
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$stmt = $conn->prepare("SELECT * FROM customer_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->execute([$userId]);
$savedAddresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Shipping fee
$shippingFee = (int) getSystemSetting('default_shipping_fee', 25000);
$freeShippingThreshold = (int) getSystemSetting('free_shipping_threshold', 500000);
$isFreeShipping = $subtotal >= $freeShippingThreshold;
if ($isFreeShipping) {
    $shippingFee = 0;
}

// Coupon discount
$discountAmount = 0;
$couponCode = '';
$couponError = '';

if (isset($_POST['apply_coupon'])) {
    $couponCode = sanitize($_POST['coupon_code']);

    if (!empty($couponCode)) {
        $stmt = $conn->prepare("SELECT * FROM coupons WHERE code = :code AND is_active = 1 AND (end_date IS NULL OR end_date >= NOW())");
        $stmt->execute([':code' => $couponCode]);
        $coupon = $stmt->fetch();

        if ($coupon) {
            if ($subtotal >= $coupon['min_order_value']) {
                if ($coupon['usage_limit'] && $coupon['used_count'] >= $coupon['usage_limit']) {
                    $couponError = 'Mã giảm giá đã hết lượt sử dụng.';
                } else {
                    if ($coupon['discount_type'] === 'percentage') {
                        $discountAmount = ($subtotal * $coupon['discount_value']) / 100;
                        if ($coupon['max_discount'] && $discountAmount > $coupon['max_discount']) {
                            $discountAmount = $coupon['max_discount'];
                        }
                    } else {
                        $discountAmount = $coupon['discount_value'];
                    }
                    $_SESSION['applied_coupon'] = $couponCode;
                }
            } else {
                $couponError = 'Đơn hàng chưa đủ giá trị tối thiểu ' . formatPrice($coupon['min_order_value']);
            }
        } else {
            $couponError = 'Mã giảm giá không hợp lệ hoặc đã hết hạn.';
        }
    }
}

// Remove coupon
if (isset($_POST['remove_coupon'])) {
    unset($_SESSION['applied_coupon']);
    $couponCode = '';
    $discountAmount = 0;
}

// Apply saved coupon
if (isset($_SESSION['applied_coupon']) && empty($couponCode)) {
    $couponCode = $_SESSION['applied_coupon'];
    $stmt = $conn->prepare("SELECT * FROM coupons WHERE code = :code AND is_active = 1");
    $stmt->execute([':code' => $couponCode]);
    $coupon = $stmt->fetch();

    if ($coupon && $subtotal >= $coupon['min_order_value']) {
        if ($coupon['discount_type'] === 'percentage') {
            $discountAmount = ($subtotal * $coupon['discount_value']) / 100;
            if ($coupon['max_discount'] && $discountAmount > $coupon['max_discount']) {
                $discountAmount = $coupon['max_discount'];
            }
        } else {
            $discountAmount = $coupon['discount_value'];
        }
    }
}

// Calculate final total
$total = $subtotal + $shippingFee - $discountAmount;

// Process order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $addressType = $_POST['address_type'] ?? '';
    $savedAddressId = (int)($_POST['saved_address_id'] ?? 0);

    // Determine which fields to use
    $name = '';
    $phone = '';
    $email = '';
    $address = '';
    $ward = '';
    $district = '';
    $city = '';

    if ($addressType === 'saved') {
        // Use saved address
        if ($savedAddressId <= 0) {
            $error = 'Vui lòng chọn địa chỉ giao hàng.';
        } else {
            $stmt = $conn->prepare("SELECT * FROM customer_addresses WHERE id = ? AND user_id = ?");
            $stmt->execute([$savedAddressId, $userId]);
            $selectedAddr = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$selectedAddr) {
                $error = 'Địa chỉ giao hàng không hợp lệ.';
            } else {
                // Get data from hidden inputs (sent by JavaScript) or fallback to database
                $name = sanitize($_POST['name_saved'] ?? '') ?: $selectedAddr['name'];
                $phone = sanitize($_POST['phone_saved'] ?? '') ?: $selectedAddr['phone'];
                $address = sanitize($_POST['address_saved'] ?? '') ?: $selectedAddr['address'];
                $ward = sanitize($_POST['ward_saved'] ?? '') ?: ($selectedAddr['ward'] ?? '');
                $district = sanitize($_POST['district_saved'] ?? '') ?: ($selectedAddr['district'] ?? '');
                $city = sanitize($_POST['city_saved'] ?? '') ?: ($selectedAddr['city'] ?? 'TP. Hồ Chí Minh');
                $email = sanitize($_POST['email_saved'] ?? '') ?: ($user['email'] ?? '');

                // Fallback: if email still empty, use account email
                if (empty($email)) {
                    $email = $user['email'] ?? '';
                }
            }
        }
    } elseif ($addressType === 'new') {
        // Use new address from form
        $name = sanitize($_POST['name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $ward = sanitize($_POST['ward'] ?? '');
        $district = sanitize($_POST['district'] ?? '');
        $city = sanitize($_POST['city'] ?? '');

        // Nếu email form rỗng, dùng email tài khoản
        if (empty($email)) {
            $email = $user['email'] ?? '';
        }
    } else {
        $error = 'Vui lòng chọn hình thức giao hàng.';
    }

    $note = sanitize($_POST['note'] ?? '');
    $paymentMethod = isset($_POST['payment_method']) ? sanitize($_POST['payment_method']) : 'cod';

    // Validation
    if (empty($error) && (empty($name) || empty($phone) || empty($address) || empty($city))) {
        $error = 'Vui lòng điền đầy đủ thông tin giao hàng.';
    } elseif (empty($error) && !preg_match('/^(0|\+84)(3|5|7|8|9)[0-9]{8}$/', $phone)) {
        $error = 'Số điện thoại không hợp lệ. Vui lòng nhập đúng định dạng (0XXXXXXXXXX hoặc +84XXXXXXXXX).';
    } elseif (empty($error) && !in_array($paymentMethod, ['cod', 'bank_transfer'])) {
        $error = 'Vui lòng chọn phương thức thanh toán.';
    } elseif (!$error) {
        try {
            $conn->beginTransaction();

            // Generate order code
            $orderCode = 'ORD' . date('Ymd') . rand(1000, 9999);

            // Create order
            $sql = "INSERT INTO orders (
                user_id, order_code, total_amount, discount_amount, shipping_fee, 
                final_amount, status, payment_method, payment_status,
                shipping_name, shipping_phone, shipping_email, shipping_address, shipping_ward, 
                shipping_district, shipping_city, note, coupon_code
            ) VALUES (
                :user_id, :order_code, :total_amount, :discount_amount, :shipping_fee,
                :final_amount, 'pending', :payment_method, 'pending',
                :shipping_name, :shipping_phone, :shipping_email, :shipping_address, :shipping_ward,
                :shipping_district, :shipping_city, :note, :coupon_code
            )";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':order_code' => $orderCode,
                ':total_amount' => $subtotal,
                ':discount_amount' => $discountAmount,
                ':shipping_fee' => $shippingFee,
                ':final_amount' => $total,
                ':payment_method' => $paymentMethod,
                ':shipping_name' => $name,
                ':shipping_phone' => $phone,
                ':shipping_email' => $email,
                ':shipping_address' => $address,
                ':shipping_ward' => $ward,
                ':shipping_district' => $district,
                ':shipping_city' => $city,
                ':note' => $note,
                ':coupon_code' => $couponCode ?: null
            ]);

            $orderId = $conn->lastInsertId();

            // Create order items
            $sql = "INSERT INTO order_items (order_id, product_id, product_name, product_image, quantity, unit_price, total_price) 
                    VALUES (:order_id, :product_id, :product_name, :product_image, :quantity, :unit_price, :total_price)";
            $stmt = $conn->prepare($sql);

            foreach ($cartItems as $item) {
                $stmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $item['product']['id'],
                    ':product_name' => $item['product']['name'],
                    ':product_image' => $item['product']['image'],
                    ':quantity' => $item['quantity'],
                    ':unit_price' => $item['price'],
                    ':total_price' => $item['total']
                ]);

                // Update product stock
                $updateStock = $conn->prepare("UPDATE products SET stock = stock - :qty WHERE id = :id");
                $updateStock->execute([':qty' => $item['quantity'], ':id' => $item['product']['id']]);
            }

            // Note: Địa chỉ mới chỉ được dùng tạm thời, không lưu vào customer_addresses
            // Nếu khách muốn lưu, họ phải tự thêm ở trang user_info.php

            // Update coupon usage
            if ($couponCode) {
                $stmt = $conn->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = :code");
                $stmt->execute([':code' => $couponCode]);
            }

            // Clear cart from database (within transaction)
            $conn->prepare("DELETE FROM carts WHERE user_id = ?")->execute([$userId]);

            $conn->commit();

            // Clear cart from session
            unset($_SESSION['cart']);
            unset($_SESSION['applied_coupon']);

            // Send order confirmation email
            if (!empty($email)) {
                sendOrderConfirmationEmail($email, $name, $orderId, $total);
            }

            // Redirect to success page
            $_SESSION['order_success'] = [
                'order_id' => $orderId,
                'order_code' => $orderCode,
                'total' => $total
            ];
            redirect(SITE_URL . '/order_success.php');
        } catch (Exception $e) {
            $conn->rollBack();
            $error = 'Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại.';
        }
    }
}

$pageTitle = 'Thanh toán';
include __DIR__ . '/includes/header.php';
?>

<main style="padding: clamp(1rem, 3vw, 2rem); max-width: 1200px; margin: 0 auto;">
    <!-- Breadcrumb -->
    <div style="display: flex; align-items: center; gap: clamp(0.5rem, 1vw, 0.75rem); flex-wrap: wrap; font-size: clamp(0.75rem, 1.5vw, 0.875rem); color: var(--muted-light); margin-bottom: clamp(1rem, 2vw, 1.5rem);">
        <a href="<?= SITE_URL ?>" style="color: var(--primary); text-decoration: none;">Trang chủ</a>
        <span class="material-symbols-outlined" style="font-size: clamp(0.875rem, 1.5vw, 1rem);">chevron_right</span>
        <a href="<?= SITE_URL ?>/cart.php" style="color: var(--primary); text-decoration: none;">Giỏ hàng</a>
        <span class="material-symbols-outlined" style="font-size: clamp(0.875rem, 1.5vw, 1rem);">chevron_right</span>
        <span style="color: var(--primary); font-weight: 600;">Thanh toán</span>
    </div>

    <h1 style="font-size: clamp(1.5rem, 5vw, 2rem); font-weight: 700; margin: clamp(1rem, 2vw, 2rem) 0 clamp(0.75rem, 1.5vw, 1rem); color: var(--text-light);">Thanh toán</h1>

    <?php if ($error): ?>
        <div style="margin-bottom: clamp(1rem, 2vw, 1.5rem); padding: clamp(0.75rem, 1.5vw, 1rem); background: rgba(239, 68, 68, 0.1); border-left: 4px solid var(--danger); border-radius: clamp(0.35rem, 1vw, 0.5rem); color: var(--danger); font-size: clamp(0.875rem, 2vw, 1rem);">
            <strong>Lỗi:</strong> <?= $error ?>
        </div>
    <?php endif; ?>

    <style>
        @media (max-width: 768px) {
            .checkout-form {
                grid-template-columns: 1fr !important;
            }

            .order-summary-checkout {
                position: static !important;
                margin-top: clamp(1.5rem, 3vw, 2rem) !important;
            }

            main {
                padding: 1rem !important;
            }

            .checkout-form>div {
                padding: 1rem !important;
            }

            h2 {
                font-size: 1rem !important;
            }

            .checkout-form input,
            .checkout-form select,
            .checkout-form textarea {
                font-size: 0.9rem !important;
            }
        }
    </style>
    <form method="POST" class="checkout-form" style="display: grid; grid-template-columns: 1fr minmax(280px, 400px); gap: clamp(1.5rem, 3vw, 2rem);">
        <!-- Left Column - Shipping Info -->
        <div>
            <!-- Address Selection Section -->
            <div style="background: white; border-radius: clamp(0.5rem, 1.5vw, 1rem); padding: clamp(1rem, 2vw, 2rem); margin-bottom: clamp(1rem, 2vw, 1.5rem); border: 1px solid var(--border-light);">
                <h2 style="font-size: clamp(1rem, 3vw, 1.25rem); font-weight: 700; margin-bottom: clamp(1rem, 2vw, 1.5rem); display: flex; align-items: center; gap: clamp(0.35rem, 1vw, 0.5rem); color: var(--text-light);">
                    <span class="material-symbols-outlined" style="font-size: clamp(1.25rem, 2vw, 1.5rem); color: var(--primary-dark);">location_on</span>
                    Địa chỉ giao hàng
                </h2>

                <!-- Option 1: Saved Address -->
                <div style="margin-bottom: clamp(1rem, 2vw, 1.5rem);">
                    <label style="display: flex; align-items: flex-start; gap: clamp(0.75rem, 2vw, 1rem); cursor: pointer; padding: clamp(0.75rem, 1.5vw, 1rem); border: 2px solid var(--border-light); border-radius: clamp(0.5rem, 1vw, 0.75rem); transition: all 0.3s;">
                        <input type="radio" name="address_type" value="saved" checked style="width: clamp(16px, 3vw, 20px); height: clamp(16px, 3vw, 20px); margin-top: clamp(0.15rem, 0.5vw, 0.25rem); cursor: pointer; accent-color: var(--primary);">
                        <div style="flex: 1;">
                            <p style="font-weight: 600; margin-bottom: clamp(0.35rem, 1vw, 0.5rem); font-size: clamp(0.875rem, 2vw, 1rem);">Sử dụng địa chỉ đã lưu</p>
                            <?php if (empty($savedAddresses)): ?>
                                <p style="color: var(--muted-light); font-size: clamp(0.75rem, 1.5vw, 0.9rem);">Bạn chưa lưu địa chỉ nào. <a href="<?= SITE_URL ?>/user_info.php?tab=addresses" style="color: var(--primary);">Thêm địa chỉ</a></p>
                            <?php else: ?>
                                <select name="saved_address_id" id="saved_address_id" onchange="updateAddressDisplay()" style="width: 100%; padding: clamp(0.5rem, 1vw, 0.75rem); border: 1px solid var(--border-light); border-radius: clamp(0.35rem, 1vw, 0.5rem); margin-top: clamp(0.35rem, 1vw, 0.5rem); font-size: clamp(0.875rem, 2vw, 1rem);">
                                    <?php foreach ($savedAddresses as $addr): ?>
                                        <option value="<?= $addr['id'] ?>"
                                            data-ward="<?= sanitize($addr['ward'] ?? '') ?>"
                                            data-district="<?= sanitize($addr['district'] ?? '') ?>"
                                            data-city="<?= sanitize($addr['city'] ?? 'TP. Hồ Chí Minh') ?>"
                                            <?= $addr['is_default'] ? 'selected' : '' ?>>
                                            <?= sanitize($addr['name']) ?> - <?= sanitize($addr['phone']) ?> | <?= sanitize($addr['address']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <!-- Hiển thị chi tiết địa chỉ -->
                                <div id="address_details" style="margin-top: clamp(0.75rem, 1.5vw, 1rem); padding: clamp(0.75rem, 1.5vw, 1rem); background: #f0f5ee; border-radius: clamp(0.35rem, 1vw, 0.5rem); display: none;">
                                    <p style="font-size: clamp(0.75rem, 1.5vw, 0.9rem); color: var(--muted-light); margin-bottom: clamp(0.25rem, 0.5vw, 0.35rem);"><strong>Người nhận:</strong> <span id="detail_name"></span></p>
                                    <p style="font-size: clamp(0.75rem, 1.5vw, 0.9rem); color: var(--muted-light); margin-bottom: clamp(0.25rem, 0.5vw, 0.35rem);"><strong>Điện thoại:</strong> <span id="detail_phone"></span></p>
                                    <p style="font-size: clamp(0.75rem, 1.5vw, 0.9rem); color: var(--muted-light); margin-bottom: clamp(0.25rem, 0.5vw, 0.35rem);"><strong>Địa chỉ:</strong> <span id="detail_address"></span></p>
                                    <p style="font-size: clamp(0.75rem, 1.5vw, 0.9rem); color: var(--muted-light); margin-bottom: clamp(0.25rem, 0.5vw, 0.35rem);"><strong>Phường/Xã:</strong> <span id="detail_ward"></span></p>
                                    <p style="font-size: clamp(0.75rem, 1.5vw, 0.9rem); color: var(--muted-light);"><strong>Quận/Huyện:</strong> <span id="detail_district"></span></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </label>
                </div>

                <!-- Option 2: New Address -->
                <div style="margin-bottom: clamp(0.75rem, 1.5vw, 1rem);">
                    <label style="display: flex; align-items: flex-start; gap: clamp(0.75rem, 2vw, 1rem); cursor: pointer; padding: clamp(0.75rem, 1.5vw, 1rem); border: 2px solid var(--border-light); border-radius: clamp(0.5rem, 1vw, 0.75rem); transition: all 0.3s;">
                        <input type="radio" name="address_type" value="new" style="width: clamp(16px, 3vw, 20px); height: clamp(16px, 3vw, 20px); margin-top: clamp(0.15rem, 0.5vw, 0.25rem); cursor: pointer; accent-color: var(--primary);">
                        <div style="flex: 1;">
                            <p style="font-weight: 600; margin-bottom: clamp(0.25rem, 0.5vw, 0.5rem); font-size: clamp(0.875rem, 2vw, 1rem);">Giao đến địa chỉ khác</p>
                            <p style="color: var(--muted-light); font-size: clamp(0.75rem, 1.5vw, 0.9rem);">Nhập thông tin người nhận khác</p>
                        </div>
                    </label>
                </div>

                <!-- New Address Form (Hidden by default) -->
                <div id="new-address-form" style="display: none; background: #f9f9f9; padding: clamp(1rem, 2vw, 1.5rem); border-radius: clamp(0.5rem, 1vw, 0.75rem); margin-top: clamp(1rem, 2vw, 1rem); border: 1px solid var(--border-light);">
                    <div style="display: grid; gap: clamp(0.75rem, 1.5vw, 1rem);">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: clamp(0.75rem, 1.5vw, 1rem);">
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: clamp(0.35rem, 0.75vw, 0.5rem); font-size: clamp(0.875rem, 2vw, 1rem);">
                                    Họ và tên <span style="color: var(--danger);">*</span>
                                </label>
                                <input type="text" name="name" placeholder="Nhập tên người nhận" required minlength="3" maxlength="100"
                                    style="width: 100%; padding: clamp(0.5rem, 1vw, 0.75rem); border: 1px solid var(--border-light); border-radius: clamp(0.35rem, 1vw, 0.5rem); font-size: clamp(0.875rem, 2vw, 1rem);">
                            </div>

                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: clamp(0.35rem, 0.75vw, 0.5rem); font-size: clamp(0.875rem, 2vw, 1rem);">
                                    Số điện thoại <span style="color: var(--danger);">*</span>
                                </label>
                                <input type="text" name="phone" placeholder="0xxxxxxxxxx hoặc +84xxxxxxxxx" required minlength="10" maxlength="13"
                                    style="width: 100%; padding: clamp(0.5rem, 1vw, 0.75rem); border: 1px solid var(--border-light); border-radius: clamp(0.35rem, 1vw, 0.5rem); font-size: clamp(0.875rem, 2vw, 1rem);">
                            </div>
                        </div>

                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: clamp(0.35rem, 0.75vw, 0.5rem); font-size: clamp(0.875rem, 2vw, 1rem);">Email</label>
                            <input type="email" name="email" value="<?= $user ? sanitize($user['email']) : '' ?>" placeholder="Nhập email người nhận" maxlength="100"
                                style="width: 100%; padding: clamp(0.5rem, 1vw, 0.75rem); border: 1px solid var(--border-light); border-radius: clamp(0.35rem, 1vw, 0.5rem); font-size: clamp(0.875rem, 2vw, 1rem);">
                        </div>

                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: clamp(0.35rem, 0.75vw, 0.5rem); font-size: clamp(0.875rem, 2vw, 1rem);">
                                Địa chỉ <span style="color: var(--danger);">*</span>
                            </label>
                            <input type="text" name="address" placeholder="Số nhà, tên đường" required minlength="5" maxlength="255"
                                style="width: 100%; padding: clamp(0.5rem, 1vw, 0.75rem); border: 1px solid var(--border-light); border-radius: clamp(0.35rem, 1vw, 0.5rem); font-size: clamp(0.875rem, 2vw, 1rem);">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: clamp(0.5rem, 1vw, 1rem);">
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: clamp(0.25rem, 0.5vw, 0.5rem); font-size: clamp(0.75rem, 1.5vw, 0.875rem);">Phường/Xã</label>
                                <input type="text" name="ward" maxlength="100"
                                    style="width: 100%; padding: clamp(0.5rem, 1vw, 0.75rem); border: 1px solid var(--border-light); border-radius: clamp(0.35rem, 1vw, 0.5rem); font-size: clamp(0.75rem, 1.5vw, 0.875rem);">
                            </div>

                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: clamp(0.25rem, 0.5vw, 0.5rem); font-size: clamp(0.75rem, 1.5vw, 0.875rem);">Quận/Huyện</label>
                                <input type="text" name="district" maxlength="100"
                                    style="width: 100%; padding: clamp(0.5rem, 1vw, 0.75rem); border: 1px solid var(--border-light); border-radius: clamp(0.35rem, 1vw, 0.5rem); font-size: clamp(0.75rem, 1.5vw, 0.875rem);">
                            </div>

                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: clamp(0.25rem, 0.5vw, 0.5rem); font-size: clamp(0.75rem, 1.5vw, 0.875rem);">
                                    Tỉnh/Thành phố <span style="color: var(--danger);">*</span>
                                </label>
                                <input type="text" name="city" value="TP. Hồ Chí Minh" required minlength="3" maxlength="100"
                                    style="width: 100%; padding: clamp(0.5rem, 1vw, 0.75rem); border: 1px solid var(--border-light); border-radius: clamp(0.35rem, 1vw, 0.5rem); font-size: clamp(0.75rem, 1.5vw, 0.875rem);">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping Information (displayed when using saved address) -->
            <div id="shipping-info" style="background: white; border-radius: 1rem; padding: 2rem; margin-bottom: 1.5rem; border: 1px solid var(--border-light); display: none;">
                <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span class="material-symbols-outlined" style="color: var(--primary-dark);">local_shipping</span>
                    Thông tin giao hàng
                </h2>

                <div style="display: grid; gap: 1rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Họ và tên <span style="color: var(--danger);">*</span></label>
                            <input type="text" name="name_display" readonly
                                style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem; background: #f9f9f9;">
                        </div>

                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Số điện thoại <span style="color: var(--danger);">*</span></label>
                            <input type="text" name="phone_display" readonly
                                style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem; background: #f9f9f9;">
                        </div>
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Email</label>
                        <input type="email" name="email_display" readonly
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem; background: #f9f9f9;">
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Địa chỉ <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="address_display" readonly
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem; background: #f9f9f9;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.875rem;">Phường/Xã</label>
                            <input type="text" name="ward_display" readonly
                                style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem; background: #f9f9f9;">
                        </div>

                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.875rem;">Quận/Huyện</label>
                            <input type="text" name="district_display" readonly
                                style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem; background: #f9f9f9;">
                        </div>

                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.875rem;">Tỉnh/Thành phố</label>
                            <input type="text" name="city_display" readonly
                                style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem; background: #f9f9f9;">
                        </div>
                    </div>
                </div>

                <!-- Hidden inputs to store data for submission -->
                <input type="hidden" name="name_saved" id="name_saved">
                <input type="hidden" name="phone_saved" id="phone_saved">
                <input type="hidden" name="email_saved" id="email_saved">
                <input type="hidden" name="address_saved" id="address_saved">
                <input type="hidden" name="ward_saved" id="ward_saved">
                <input type="hidden" name="district_saved" id="district_saved">
                <input type="hidden" name="city_saved" id="city_saved">
            </div>

            <!-- Payment Method -->
            <div style="background: white; border-radius: clamp(0.5rem, 1.5vw, 1rem); padding: clamp(1rem, 2vw, 2rem); border: 1px solid var(--border-light);">
                <h2 style="font-size: clamp(1rem, 3vw, 1.25rem); font-weight: 700; margin-bottom: clamp(1rem, 2vw, 1.5rem); display: flex; align-items: center; gap: clamp(0.35rem, 1vw, 0.5rem); color: var(--text-light);">
                    <span class="material-symbols-outlined" style="font-size: clamp(1.25rem, 2vw, 1.5rem); color: var(--primary-dark);">payments</span>
                    Phương thức thanh toán
                </h2>

                <div style="display: flex; flex-direction: column; gap: clamp(0.75rem, 1.5vw, 1rem);">
                    <!-- COD -->
                    <label style="display: flex; gap: clamp(0.75rem, 2vw, 1rem); padding: clamp(0.75rem, 1.5vw, 1rem); border: 2px solid var(--border-light); border-radius: clamp(0.5rem, 1vw, 0.75rem); cursor: pointer; transition: all 0.3s;"
                        onclick="this.querySelector('input').checked = true; updatePaymentBorder();">
                        <input type="radio" name="payment_method" value="cod" checked
                            style="width: clamp(16px, 3vw, 20px); height: clamp(16px, 3vw, 20px); accent-color: var(--primary);">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: clamp(0.35rem, 1vw, 0.5rem); margin-bottom: clamp(0.25rem, 0.75vw, 0.5rem); font-size: clamp(0.875rem, 2vw, 1rem);">
                                <span class="material-symbols-outlined" style="font-size: clamp(1.125rem, 2vw, 1.25rem); color: var(--primary-dark);">local_atm</span>
                                <strong>Thanh toán khi nhận hàng (COD)</strong>
                            </div>
                            <p style="font-size: clamp(0.75rem, 1.5vw, 0.875rem); color: var(--muted-light);">
                                Thanh toán bằng tiền mặt khi nhận hàng
                            </p>
                        </div>
                    </label>

                    <!-- Bank Transfer -->
                    <label style="display: flex; gap: clamp(0.75rem, 2vw, 1rem); padding: clamp(0.75rem, 1.5vw, 1rem); border: 2px solid var(--border-light); border-radius: clamp(0.5rem, 1vw, 0.75rem); cursor: pointer; transition: all 0.3s;"
                        onclick="this.querySelector('input').checked = true; updatePaymentBorder();">
                        <input type="radio" name="payment_method" value="bank_transfer"
                            style="width: clamp(16px, 3vw, 20px); height: clamp(16px, 3vw, 20px); accent-color: var(--primary);">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: clamp(0.35rem, 1vw, 0.5rem); margin-bottom: clamp(0.25rem, 0.75vw, 0.5rem); font-size: clamp(0.875rem, 2vw, 1rem);">
                                <span class="material-symbols-outlined" style="font-size: clamp(1.125rem, 2vw, 1.25rem); color: var(--primary-dark);">account_balance</span>
                                <strong>Chuyển khoản ngân hàng</strong>
                            </div>
                            <p style="font-size: 0.875rem; color: var(--muted-light); margin-bottom: 0.5rem;">
                                Chuyển khoản trước khi nhận hàng
                            </p>
                            <div style="background: rgba(182, 230, 51, 0.1); padding: 0.75rem; border-radius: 0.5rem; font-size: 0.875rem;">
                                <p><strong>Ngân hàng:</strong> <?= getSystemSetting('bank_name', 'Vietcombank') ?></p>
                                <p><strong>Số TK:</strong> <?= getSystemSetting('bank_account', '1234567890') ?></p>
                                <p><strong>Chủ TK:</strong> <?= getSystemSetting('bank_account_name', 'CONG TY XANH ORGANIC') ?></p>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Order Note -->
            <div style="background: white; border-radius: 1rem; padding: 2rem; margin-top: 1.5rem; border: 1px solid var(--border-light);">
                <label style="display: block; font-weight: 600; margin-bottom: 1rem;">Ghi chú đơn hàng (tuỳ chọn)</label>
                <textarea name="note" rows="3" placeholder="Ghi chú về đơn hàng, ví dụ: thời gian hay chỉ dẫn địa điểm giao hàng chi tiết hơn"
                    style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem;"></textarea>
            </div>
        </div>

        <!-- Right Column - Order Summary -->
        <div class="order-summary-checkout" style="position: sticky; top: clamp(60px, 10vw, 100px); height: fit-content;">
            <div style="background: white; border-radius: clamp(0.5rem, 1.5vw, 1rem); padding: clamp(1rem, 2vw, 1.5rem); border: 1px solid var(--border-light);">
                <h3 style="font-size: clamp(1rem, 3vw, 1.25rem); font-weight: 700; margin-bottom: clamp(1rem, 1.5vw, 1.5rem); color: var(--text-light);">Tóm tắt đơn hàng</h3>

                <!-- Products List -->
                <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-light);">
                    <?php foreach ($cartItems as $item): ?>
                        <div style="display: flex; gap: 1rem;">
                            <div style="position: relative;">
                                <img src="<?= imageUrl($item['product']['image']) ?>" alt="<?= sanitize($item['product']['name']) ?>"
                                    style="width: 60px; height: 60px; border-radius: 0.5rem; object-fit: cover;">
                                <span style="position: absolute; top: -8px; right: -8px; width: 24px; height: 24px; border-radius: 50%; background: var(--primary); color: white; font-size: 0.75rem; font-weight: 700; display: flex; align-items: center; justify-content: center;">
                                    <?= $item['quantity'] ?>
                                </span>
                            </div>
                            <div style="flex: 1;">
                                <p style="font-weight: 600; font-size: 0.875rem; margin-bottom: 0.25rem;"><?= sanitize($item['product']['name']) ?></p>
                                <p style="font-size: 0.875rem; color: var(--muted-light);"><?= formatPrice($item['price']) ?> / <?= sanitize($item['product']['unit']) ?></p>
                            </div>
                            <p style="font-weight: 700; color: var(--primary-dark);"><?= formatPrice($item['total']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Demo Coupon -->
                <div class="mb-6 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-blue-600 flex-shrink-0 text-sm" style="font-size: 18px;">info</span>
                        <div class="text-xs text-blue-900">
                            <p class="font-semibold mb-1">Mã Giảm Giá Demo:</p>
                            <div class="space-y-1 bg-white/60 p-1.5 rounded border border-blue-100 text-xs">
                                <p><strong>FREESHIP</strong></p>
                                <p>Miễn phí vận chuyển cho đơn hàng từ 200.000đ</p>
                                <p><strong>WELCOME10</strong></p>
                                <p>Giảm 10% cho đơn hàng đầu tiên </p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Coupon Code -->
                <div style="margin-bottom: clamp(1rem, 2vw, 1.5rem);">
                    <?php if ($couponCode && !$couponError): ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: clamp(0.5rem, 1vw, 0.75rem); background: rgba(182, 230, 51, 0.1); border-radius: clamp(0.35rem, 1vw, 0.5rem);">
                            <div style="display: flex; align-items: center; gap: clamp(0.35rem, 1vw, 0.5rem); font-size: clamp(0.875rem, 2vw, 1rem);">
                                <span class="material-symbols-outlined" style="font-size: clamp(1rem, 2vw, 1.25rem); color: var(--primary-dark);">confirmation_number</span>
                                <span style="font-weight: 600;"><?= sanitize($couponCode) ?></span>
                            </div>
                            <button type="submit" name="remove_coupon" style="background: none; border: none; color: var(--danger); cursor: pointer; font-size: clamp(0.75rem, 1.5vw, 0.875rem);">
                                Xóa
                            </button>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; gap: clamp(0.35rem, 1vw, 0.5rem); flex-wrap: wrap;">
                            <input type="text" name="coupon_code" placeholder="Mã giảm giá"
                                style="flex: 1; min-width: 100px; padding: clamp(0.5rem, 1vw, 0.75rem); border: 1px solid var(--border-light); border-radius: clamp(0.35rem, 1vw, 0.5rem); font-size: clamp(0.75rem, 1.5vw, 0.875rem);">
                            <button type="submit" name="apply_coupon" style="padding: clamp(0.5rem, 1vw, 0.75rem) clamp(0.75rem, 1.5vw, 1rem); background: var(--primary); color: var(--text-light); border: none; border-radius: clamp(0.35rem, 1vw, 0.5rem); font-weight: 600; cursor: pointer; font-size: clamp(0.75rem, 1.5vw, 0.875rem);">
                                Áp dụng
                            </button>
                        </div>
                        <?php if ($couponError): ?>
                            <p style="color: var(--danger); font-size: clamp(0.75rem, 1.5vw, 0.875rem); margin-top: clamp(0.35rem, 0.75vw, 0.5rem);"><?= $couponError ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Price Summary -->
                <div style="display: flex; flex-direction: column; gap: clamp(0.5rem, 1vw, 0.75rem); margin-bottom: clamp(1rem, 2vw, 1.5rem); font-size: clamp(0.875rem, 2vw, 1rem);">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--muted-light);">Tạm tính</span>
                        <span style="font-weight: 500;"><?= formatPrice($subtotal) ?></span>
                    </div>

                    <?php if ($discountAmount > 0): ?>
                        <div style="display: flex; justify-content: space-between; color: var(--primary-dark);">
                            <span>Giảm giá</span>
                            <span style="font-weight: 600;">-<?= formatPrice($discountAmount) ?></span>
                        </div>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--muted-light);">Phí vận chuyển</span>
                        <span style="font-weight: 500; color: <?= $isFreeShipping ? 'var(--success)' : 'inherit' ?>;">
                            <?= $isFreeShipping ? 'Miễn phí' : formatPrice($shippingFee) ?>
                        </span>
                    </div>

                    <?php if (!$isFreeShipping && $subtotal < $freeShippingThreshold): ?>
                        <div style="padding: clamp(0.5rem, 1vw, 0.75rem); background: rgba(182, 230, 51, 0.1); border-radius: clamp(0.35rem, 1vw, 0.5rem); font-size: clamp(0.75rem, 1.5vw, 0.875rem);">
                            Mua thêm <strong><?= formatPrice($freeShippingThreshold - $subtotal) ?></strong> để được miễn phí vận chuyển!
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Total -->
                <div style="display: flex; justify-content: space-between; padding: clamp(1rem, 1.5vw, 1.5rem) 0; border-top: 1px solid var(--border-light); font-size: clamp(1rem, 3vw, 1.25rem); font-weight: 700; color: var(--text-light);">
                    <span>Tổng cộng</span>
                    <span style="color: var(--primary-dark);"><?= formatPrice($total) ?></span>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="place_order" value="1" style="width: 100%; padding: clamp(0.75rem, 1.5vw, 1rem); font-size: clamp(0.875rem, 2vw, 1rem); background: var(--primary); color: var(--text-light); font-weight: 600; border: none; border-radius: clamp(0.35rem, 1vw, 0.5rem); cursor: pointer; pointer-events: auto;">
                    Đặt hàng
                </button>
                <a href="<?= SITE_URL ?>/products.php" style="display: block; text-align: center; margin-top: clamp(0.75rem, 1.5vw, 1rem); color: var(--muted-light); font-size: clamp(0.75rem, 1.5vw, 0.875rem); text-decoration: none;">
                    ← Tiếp tục mua sắm
                </a>

                <p style="text-align: center; font-size: clamp(0.75rem, 1.5vw, 0.875rem); color: var(--muted-light); margin-top: clamp(0.75rem, 1.5vw, 1rem);">
                    Bằng việc đặt hàng, bạn đồng ý với
                    <a href="#" style="color: var(--primary-dark);">Điều khoản sử dụng</a> của chúng tôi
                </p>
            </div>
        </div>
    </form>
</main>

<script>
    // Update address type display
    function updateAddressType() {
        const addressType = document.querySelector('input[name="address_type"]:checked').value;
        const newAddressForm = document.getElementById('new-address-form');
        const shippingInfo = document.getElementById('shipping-info');
        const savedAddressSelect = document.querySelector('select[name="saved_address_id"]');

        // Update border for address type radio buttons
        document.querySelectorAll('input[name="address_type"]').forEach(radio => {
            const label = radio.closest('label');
            if (label) {
                if (radio.checked) {
                    label.style.borderColor = 'var(--primary)';
                    label.style.backgroundColor = 'rgba(182, 230, 51, 0.05)';
                } else {
                    label.style.borderColor = 'var(--border-light)';
                    label.style.backgroundColor = 'transparent';
                }
            }
        });

        if (addressType === 'new') {
            newAddressForm.style.display = 'block';
            shippingInfo.style.display = 'none';

            // Thêm required cho các input trong new address form
            document.querySelector('input[name="name"]').setAttribute('required', 'required');
            document.querySelector('input[name="phone"]').setAttribute('required', 'required');
            document.querySelector('input[name="address"]').setAttribute('required', 'required');

            // Clear the form inputs - để trống tất cả để người dùng tự nhập
            document.querySelector('input[name="name"]').value = '';
            document.querySelector('input[name="phone"]').value = '';
            document.querySelector('input[name="email"]').value = '';
            document.querySelector('input[name="address"]').value = '';
            document.querySelector('input[name="ward"]').value = '';
            document.querySelector('input[name="district"]').value = '';
            document.querySelector('input[name="city"]').value = ''; // Để trống, không mặc định TP. Hồ Chí Minh
            document.querySelector('textarea[name="note"]').value = '';

            // Remove readonly từ email input
            const emailInput = document.querySelector('input[name="email"]');
            emailInput.removeAttribute('readonly');
            emailInput.style.backgroundColor = 'transparent';
        } else {
            newAddressForm.style.display = 'none';
            shippingInfo.style.display = 'block';

            // Khi sử dụng địa chỉ đã lưu, set email từ tài khoản
            const emailInput = document.querySelector('input[name="email"]');
            emailInput.value = '<?= $user ? sanitize($user['email']) : '' ?>';
            emailInput.setAttribute('readonly', 'readonly'); // Để readonly để không người dùng sửa
            emailInput.style.backgroundColor = '#f9f9f9';

            // Loại bỏ required khi form ẩn
            document.querySelector('input[name="name"]').removeAttribute('required');
            document.querySelector('input[name="phone"]').removeAttribute('required');
            document.querySelector('input[name="address"]').removeAttribute('required');

            // Populate shipping info from selected address
            if (savedAddressSelect && savedAddressSelect.options.length > 0) {
                const selectedOption = savedAddressSelect.options[savedAddressSelect.selectedIndex];
                const text = selectedOption.text;
                const parts = text.split('|');

                if (parts.length >= 2) {
                    const namePhone = parts[0].trim().split('-');
                    const name = namePhone[0].trim();
                    const phone = namePhone[1].trim() || '';

                    const nameDisplayEl = document.querySelector('input[name="name_display"]');
                    const phoneDisplayEl = document.querySelector('input[name="phone_display"]');

                    if (nameDisplayEl) {
                        nameDisplayEl.value = name;
                        document.getElementById('name_saved').value = name;
                    }
                    if (phoneDisplayEl) {
                        phoneDisplayEl.value = phone;
                        document.getElementById('phone_saved').value = phone;
                    }
                }

                const addressDisplayEl = document.querySelector('input[name="address_display"]');
                const address = (parts[1] || '').trim();
                if (addressDisplayEl) {
                    addressDisplayEl.value = address;
                    document.getElementById('address_saved').value = address;
                }

                const emailDisplayEl = document.querySelector('input[name="email_display"]');
                const email = '<?= $user ? sanitize($user['email']) : '' ?>';
                if (emailDisplayEl) {
                    emailDisplayEl.value = email;
                    document.getElementById('email_saved').value = email;
                }

                // Get ward, district, city from data attributes
                const ward = selectedOption.getAttribute('data-ward') || '';
                const district = selectedOption.getAttribute('data-district') || '';
                const city = selectedOption.getAttribute('data-city') || '';

                const wardDisplayEl = document.querySelector('input[name="ward_display"]');
                const districtDisplayEl = document.querySelector('input[name="district_display"]');
                const cityDisplayEl = document.querySelector('input[name="city_display"]');

                if (wardDisplayEl) {
                    wardDisplayEl.value = ward;
                    document.getElementById('ward_saved').value = ward;
                }
                if (districtDisplayEl) {
                    districtDisplayEl.value = district;
                    document.getElementById('district_saved').value = district;
                }
                if (cityDisplayEl) {
                    cityDisplayEl.value = city;
                    document.getElementById('city_saved').value = city;
                }
            }
        }
    }

    // Update payment method border on selection
    function updatePaymentBorder() {
        const labels = document.querySelectorAll('label');
        labels.forEach(label => {
            const input = label.querySelector('input[type="radio"]');
            if (input && input.name === 'payment_method') {
                if (input.checked) {
                    label.style.borderColor = 'var(--primary)';
                    label.style.backgroundColor = 'rgba(182, 230, 51, 0.05)';
                } else {
                    label.style.borderColor = 'var(--border-light)';
                    label.style.backgroundColor = 'transparent';
                }
            }
        });
    }

    // Validate phone number format: (0|+84)(3|5|7|8|9)[0-9]{8}
    function validatePhoneNumber(phone) {
        return /^(0|\+84)(3|5|7|8|9)[0-9]{8}$/.test(phone.trim());
    }

    // Show phone error message
    function showPhoneError(phoneInput, message) {
        let errorEl = phoneInput.nextElementSibling;

        // Remove existing error if it exists
        if (errorEl && errorEl.classList && errorEl.classList.contains('phone-error')) {
            errorEl.remove();
        }

        if (message) {
            const error = document.createElement('div');
            error.className = 'phone-error';
            error.style.cssText = 'color: var(--danger); font-size: 0.875rem; margin-top: 0.25rem; display: flex; align-items: center; gap: 0.25rem;';
            error.innerHTML = '<span class="material-symbols-outlined" style="font-size: 1rem;">error</span><span>' + message + '</span>';
            phoneInput.parentNode.insertBefore(error, phoneInput.nextSibling);
            phoneInput.style.borderColor = 'var(--danger)';
            phoneInput.style.backgroundColor = 'rgba(239, 68, 68, 0.05)';
        } else {
            phoneInput.style.borderColor = 'var(--border-light)';
            phoneInput.style.backgroundColor = 'transparent';
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateAddressType();
        updatePaymentBorder();

        // Phone number validation
        const phoneInputs = document.querySelectorAll('input[name="phone"]');
        phoneInputs.forEach(phoneInput => {
            // Clear error on focus
            phoneInput.addEventListener('focus', function() {
                showPhoneError(this, '');
            });

            // Real-time validation while typing
            phoneInput.addEventListener('input', function() {
                const value = this.value.trim();
                if (value.length > 0 && !validatePhoneNumber(value)) {
                    showPhoneError(this, 'Định dạng: 0XXXXXXXXXX (10 số) hoặc +84XXXXXXXXX (11 ký tự)');
                } else {
                    showPhoneError(this, '');
                }
            });
        });

        // Update shipping info when saved address changes
        const savedAddressSelect = document.querySelector('select[name="saved_address_id"]');
        if (savedAddressSelect) {
            savedAddressSelect.addEventListener('change', updateAddressType);
        }

        // Update address type border when radio changes
        document.querySelectorAll('input[name="address_type"]').forEach(radio => {
            radio.addEventListener('change', updateAddressType);
        });
    });

    // Update on radio change for payment method
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', updatePaymentBorder);
    });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>