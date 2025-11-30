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
 * - Lưu địa chỉ mới với note "địa chỉ người nhận gần đây"
 */

require_once __DIR__ . '/includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/email_functions.php';

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
$shippingFee = defined('DEFAULT_SHIPPING_FEE') ? DEFAULT_SHIPPING_FEE : 25000;
$freeShippingThreshold = defined('FREE_SHIPPING_THRESHOLD') ? FREE_SHIPPING_THRESHOLD : 500000;
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
                $name = $selectedAddr['name'];
                $phone = $selectedAddr['phone'];
                $address = $selectedAddr['address'];
                $ward = $selectedAddr['ward'] ?? '';
                $district = $selectedAddr['district'] ?? '';
                $email = $user['email'] ?? '';
                $city = 'TP. Hồ Chí Minh'; // Thành phố mặc định
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
    } else {
        $error = 'Vui lòng chọn hình thức giao hàng.';
    }
    
    $note = sanitize($_POST['note'] ?? '');
    $paymentMethod = isset($_POST['payment_method']) ? sanitize($_POST['payment_method']) : 'cod';
    
    // Validation
    if (empty($error) && (empty($name) || empty($phone) || empty($address) || empty($city))) {
        $error = 'Vui lòng điền đầy đủ thông tin giao hàng.';
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
                shipping_name, shipping_phone, shipping_address, shipping_ward, 
                shipping_district, shipping_city, note, coupon_code
            ) VALUES (
                :user_id, :order_code, :total_amount, :discount_amount, :shipping_fee,
                :final_amount, 'pending', :payment_method, 'pending',
                :shipping_name, :shipping_phone, :shipping_address, :shipping_ward,
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
            
            // If new address was used, save it to customer_addresses
            if ($addressType === 'new' && !empty($name) && !empty($phone)) {
                $stmtAddr = $conn->prepare("INSERT INTO customer_addresses (user_id, name, phone, address, note, is_default, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
                $stmtAddr->execute([$userId, $name, $phone, $address, 'địa chỉ người nhận gần đây']);
            }
            
            // Update coupon usage
            if ($couponCode) {
                $stmt = $conn->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = :code");
                $stmt->execute([':code' => $couponCode]);
            }
            
            $conn->commit();
            
            // Clear cart and coupon
            unset($_SESSION['cart']);
            unset($_SESSION['applied_coupon']);
            
            // Send order confirmation email (optional)
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
include 'includes/header.php';
?>

<main class="container" style="padding: 2rem 1rem; max-width: 1200px;">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?= SITE_URL ?>">Trang chủ</a>
        <span class="material-symbols-outlined" style="font-size: 1rem;">chevron_right</span>
        <a href="<?= SITE_URL ?>/cart.php">Giỏ hàng</a>
        <span class="material-symbols-outlined" style="font-size: 1rem;">chevron_right</span>
        <span class="current">Thanh toán</span>
    </div>

    <h1 style="font-size: 2rem; font-weight: 700; margin: 2rem 0 1rem;">Thanh toán</h1>

    <?php if ($error): ?>
        <div class="alert alert-error" style="margin-bottom: 1.5rem; padding: 1rem; background: rgba(239, 68, 68, 0.1); border-left: 4px solid var(--danger); border-radius: 0.5rem; color: var(--danger);">
            <strong>Lỗi:</strong> <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST" style="display: grid; grid-template-columns: 1fr 400px; gap: 2rem;">
        <!-- Left Column - Shipping Info -->
        <div>
            <!-- Address Selection Section -->
            <div style="background: white; border-radius: 1rem; padding: 2rem; margin-bottom: 1.5rem; border: 1px solid var(--border-light);">
                <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span class="material-symbols-outlined" style="color: var(--primary-dark);">location_on</span>
                    Địa chỉ giao hàng
                </h2>

                <!-- Option 1: Saved Address -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: flex; align-items: flex-start; gap: 1rem; cursor: pointer; padding: 1rem; border: 2px solid var(--border-light); border-radius: 0.75rem; transition: all 0.3s;">
                        <input type="radio" name="address_type" value="saved" checked style="width: 20px; height: 20px; margin-top: 0.25rem; cursor: pointer; accent-color: var(--primary);">
                        <div style="flex: 1;">
                            <p style="font-weight: 600; margin-bottom: 0.5rem;">Sử dụng địa chỉ đã lưu</p>
                            <?php if (empty($savedAddresses)): ?>
                                <p style="color: var(--muted-light); font-size: 0.9rem;">Bạn chưa lưu địa chỉ nào. <a href="<?= SITE_URL ?>/user_info.php" style="color: var(--primary);">Thêm địa chỉ</a></p>
                            <?php else: ?>
                                <select name="saved_address_id" id="saved_address_id" onchange="updateAddressDisplay()" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem; margin-top: 0.5rem; font-size: 1rem;">
                                    <?php foreach ($savedAddresses as $addr): ?>
                                        <option value="<?= $addr['id'] ?>" <?= $addr['is_default'] ? 'selected' : '' ?>>
                                            <?= sanitize($addr['name']) ?> - <?= sanitize($addr['phone']) ?> | <?= sanitize($addr['address']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <!-- Hiển thị chi tiết địa chỉ -->
                                <div id="address_details" style="margin-top: 1rem; padding: 1rem; background: #f0f5ee; border-radius: 0.5rem; display: none;">
                                    <p style="font-size: 0.9rem; color: var(--muted-light);"><strong>Người nhận:</strong> <span id="detail_name"></span></p>
                                    <p style="font-size: 0.9rem; color: var(--muted-light);"><strong>Điện thoại:</strong> <span id="detail_phone"></span></p>
                                    <p style="font-size: 0.9rem; color: var(--muted-light);"><strong>Địa chỉ:</strong> <span id="detail_address"></span></p>
                                    <p style="font-size: 0.9rem; color: var(--muted-light);"><strong>Phường/Xã:</strong> <span id="detail_ward"></span></p>
                                    <p style="font-size: 0.9rem; color: var(--muted-light);"><strong>Quận/Huyện:</strong> <span id="detail_district"></span></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </label>
                </div>

                <!-- Option 2: New Address -->
                <div style="margin-bottom: 1rem;">
                    <label style="display: flex; align-items: flex-start; gap: 1rem; cursor: pointer; padding: 1rem; border: 2px solid var(--border-light); border-radius: 0.75rem; transition: all 0.3s;">
                        <input type="radio" name="address_type" value="new" style="width: 20px; height: 20px; margin-top: 0.25rem; cursor: pointer; accent-color: var(--primary);">
                        <div style="flex: 1;">
                            <p style="font-weight: 600; margin-bottom: 0.5rem;">Giao đến địa chỉ khác</p>
                            <p style="color: var(--muted-light); font-size: 0.9rem;">Nhập thông tin người nhận khác</p>
                        </div>
                    </label>
                </div>

                <!-- New Address Form (Hidden by default) -->
                <div id="new-address-form" style="display: none; background: #f9f9f9; padding: 1.5rem; border-radius: 0.75rem; margin-top: 1rem; border: 1px solid var(--border-light);">
                    <div style="display: grid; gap: 1rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                                    Họ và tên <span style="color: var(--danger);">*</span>
                                </label>
                                    <input type="text" name="name" placeholder="Nhập tên người nhận" required
                                       style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem;">
                            </div>

                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                                    Số điện thoại <span style="color: var(--danger);">*</span>
                                </label>
                                    <input type="tel" name="phone" placeholder="Nhập số điện thoại" required
                                       style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem;">
                            </div>
                        </div>

                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Email</label>
                            <input type="email" name="email" value="<?= $user ? sanitize($user['email']) : '' ?>" placeholder="Nhập email người nhận"
                                   style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem;">
                        </div>

                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                                Địa chỉ <span style="color: var(--danger);">*</span>
                            </label>
                            <input type="text" name="address" placeholder="Số nhà, tên đường"
                                   style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem;">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Phường/Xã</label>
                                <input type="text" name="ward"
                                       style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem;">
                            </div>

                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Quận/Huyện</label>
                                <input type="text" name="district"
                                       style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem;">
                            </div>

                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                                    Tỉnh/Thành phố <span style="color: var(--danger);">*</span>
                                </label>
                                <input type="text" name="city" value="TP. Hồ Chí Minh" required
                                       style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem;">
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
                </div>
            </div>

            <!-- Payment Method -->
            <div style="background: white; border-radius: 1rem; padding: 2rem; border: 1px solid var(--border-light);">
                <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span class="material-symbols-outlined" style="color: var(--primary-dark);">payments</span>
                    Phương thức thanh toán
                </h2>

                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <!-- COD -->
                    <label style="display: flex; gap: 1rem; padding: 1rem; border: 2px solid var(--border-light); border-radius: 0.75rem; cursor: pointer; transition: all 0.3s;"
                           onclick="this.querySelector('input').checked = true; updatePaymentBorder();">
                        <input type="radio" name="payment_method" value="cod" checked
                               style="width: 20px; height: 20px; accent-color: var(--primary);">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <span class="material-symbols-outlined" style="color: var(--primary-dark);">local_atm</span>
                                <strong>Thanh toán khi nhận hàng (COD)</strong>
                            </div>
                            <p style="font-size: 0.875rem; color: var(--muted-light);">
                                Thanh toán bằng tiền mặt khi nhận hàng
                            </p>
                        </div>
                    </label>

                    <!-- Bank Transfer -->
                    <label style="display: flex; gap: 1rem; padding: 1rem; border: 2px solid var(--border-light); border-radius: 0.75rem; cursor: pointer; transition: all 0.3s;"
                           onclick="this.querySelector('input').checked = true; updatePaymentBorder();">
                        <input type="radio" name="payment_method" value="bank_transfer"
                               style="width: 20px; height: 20px; accent-color: var(--primary);">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <span class="material-symbols-outlined" style="color: var(--primary-dark);">account_balance</span>
                                <strong>Chuyển khoản ngân hàng</strong>
                            </div>
                            <p style="font-size: 0.875rem; color: var(--muted-light); margin-bottom: 0.5rem;">
                                Chuyển khoản trước khi nhận hàng
                            </p>
                            <div style="background: rgba(182, 230, 51, 0.1); padding: 0.75rem; border-radius: 0.5rem; font-size: 0.875rem;">
                                <p><strong>Ngân hàng:</strong> Vietcombank</p>
                                <p><strong>Số TK:</strong> 1234567890</p>
                                <p><strong>Chủ TK:</strong> CONG TY XANH ORGANIC</p>
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
        <div style="position: sticky; top: 100px; height: fit-content;">
            <div style="background: white; border-radius: 1rem; padding: 1.5rem; border: 1px solid var(--border-light);">
                <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem;">Đơn hàng của bạn</h2>

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

                <!-- Coupon Code -->
                <div style="margin-bottom: 1.5rem;">
                    <?php if ($couponCode && !$couponError): ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; background: rgba(182, 230, 51, 0.1); border-radius: 0.5rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span class="material-symbols-outlined" style="color: var(--primary-dark);">confirmation_number</span>
                                <span style="font-weight: 600;"><?= sanitize($couponCode) ?></span>
                            </div>
                            <button type="submit" name="remove_coupon" style="background: none; border: none; color: var(--danger); cursor: pointer; font-size: 0.875rem;">
                                Xóa
                            </button>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="text" name="coupon_code" placeholder="Mã giảm giá"
                                   style="flex: 1; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem;">
                            <button type="submit" name="apply_coupon" class="btn btn-secondary">
                                Áp dụng
                            </button>
                        </div>
                        <?php if ($couponError): ?>
                            <p style="color: var(--danger); font-size: 0.875rem; margin-top: 0.5rem;"><?= $couponError ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Price Summary -->
                <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--muted-light);">Tạm tính</span>
                        <span class="font-semibold"><?= formatPrice($subtotal) ?></span>
                    </div>

                    <?php if ($discountAmount > 0): ?>
                    <div style="display: flex; justify-content: space-between; color: var(--primary-dark);">
                        <span>Giảm giá</span>
                        <span style="font-weight: 600;">-<?= formatPrice($discountAmount) ?></span>
                    </div>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--muted-light);">Phí vận chuyển</span>
                        <span class="font-semibold" style="color: <?= $isFreeShipping ? 'var(--success)' : 'inherit' ?>;">
                            <?= $isFreeShipping ? 'Miễn phí' : formatPrice($shippingFee) ?>
                        </span>
                    </div>

                    <?php if (!$isFreeShipping && $subtotal < $freeShippingThreshold): ?>
                    <div style="padding: 0.75rem; background: rgba(182, 230, 51, 0.1); border-radius: 0.5rem; font-size: 0.875rem;">
                        Mua thêm <strong><?= formatPrice($freeShippingThreshold - $subtotal) ?></strong> để được miễn phí vận chuyển!
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Total -->
                <div style="display: flex; justify-content: space-between; padding: 1.5rem 0; border-top: 1px solid var(--border-light); font-size: 1.25rem; font-weight: 700;">
                    <span>Tổng cộng</span>
                    <span style="color: var(--primary-dark);"><?= formatPrice($total) ?></span>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="place_order" value="1" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1rem; cursor: pointer; pointer-events: auto; border-radius: 0.5rem;">
                    Đặt hàng
                </button>

                <p style="text-align: center; font-size: 0.875rem; color: var(--muted-light); margin-top: 1rem;">
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
        
        // Clear the form inputs
        document.querySelector('input[name="name"]').value = '';
        document.querySelector('input[name="phone"]').value = '';
        document.querySelector('input[name="email"]').value = '';
        document.querySelector('input[name="address"]').value = '';
        document.querySelector('input[name="ward"]').value = '';
        document.querySelector('input[name="district"]').value = '';
        document.querySelector('input[name="city"]').value = 'TP. Hồ Chí Minh';
        document.querySelector('textarea[name="note"]').value = '';
    } else {
        newAddressForm.style.display = 'none';
        shippingInfo.style.display = 'block';
        
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
                const nameDisplayEl = document.querySelector('input[name="name_display"]');
                const phoneDisplayEl = document.querySelector('input[name="phone_display"]');
                
                if (nameDisplayEl) nameDisplayEl.value = namePhone[0].trim();
                if (phoneDisplayEl) phoneDisplayEl.value = namePhone[1].trim() || '';
            }
            
            const addressDisplayEl = document.querySelector('input[name="address_display"]');
            if (addressDisplayEl) addressDisplayEl.value = (parts[1] || '').trim();
            
            const emailDisplayEl = document.querySelector('input[name="email_display"]');
            if (emailDisplayEl) emailDisplayEl.value = '<?= $user ? sanitize($user['email']) : '' ?>';
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

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateAddressType();
    updatePaymentBorder();
    
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

// Update on radio change
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', updatePaymentBorder);
});

document.querySelectorAll('input[name="address_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        updateAddressType();
    });
});
</script>

<?php include 'includes/footer.php'; ?>