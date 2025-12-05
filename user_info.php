<?php
/**
 * user_info.php - Trang quản lý thông tin và địa chỉ khách hàng
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    redirect(SITE_URL . '/auth.php');
}

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

// Get user data
$conn = getConnection();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    redirect(SITE_URL . '/auth.php');
}

// Lấy danh sách địa chỉ đã lưu
$stmt = $conn->prepare("SELECT * FROM customer_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->execute([$userId]);
$addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = sanitize($_POST['name']);
    $phone = sanitize($_POST['phone']);
    $birthdate = sanitize($_POST['birthdate'] ?? '');

    if (empty($name)) {
        $error = 'Tên không được để trống.';
    } elseif (strlen($name) < 3 || strlen($name) > 100) {
        $error = 'Tên phải từ 3 đến 100 ký tự.';
    } elseif (!empty($phone) && !preg_match('/^(0|\+84)(3|5|7|8|9)[0-9]{8}$/', $phone)) {
        $error = 'Số điện thoại không hợp lệ. Vui lòng nhập đúng định dạng (0XXXXXXXXXX hoặc +84XXXXXXXXX).';
    } else {
        // Start transaction to update profile (and avatar if provided)
        try {
            $conn->beginTransaction();

            // Handle avatar upload if present
            if (!empty($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                $file = $_FILES['avatar'];
                $allowed = ['image/jpeg', 'image/png', 'image/gif'];
                $maxSize = 2 * 1024 * 1024; // 2MB

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('Lỗi khi tải lên ảnh.');
                }

                if ($file['size'] > $maxSize) {
                    throw new Exception('Kích thước ảnh quá lớn. Vui lòng chọn ảnh < 2MB.');
                }

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mime, $allowed)) {
                    throw new Exception('Định dạng ảnh không được hỗ trợ. Vui lòng chọn JPG/PNG/GIF.');
                }

                // Ensure target directory exists
                $uploadDir = __DIR__ . '/images/avatars';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'user_' . $userId . '_' . time() . '.' . $ext;
                $targetPath = $uploadDir . '/' . $filename;

                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    throw new Exception('Không thể lưu ảnh tải lên.');
                }

                // Build relative path to store in DB (web-accessible)
                $avatarPath = 'images/avatars/' . $filename;

                // Delete old avatar file if exists and is a local file
                if (!empty($user['avatar']) && strpos($user['avatar'], 'images/avatars/') === 0) {
                    $oldFile = __DIR__ . '/' . $user['avatar'];
                    if (is_file($oldFile)) {
                        @unlink($oldFile);
                    }
                }

                // Update avatar column
                $stmt = $conn->prepare("UPDATE users SET avatar = :avatar WHERE id = :id");
                $stmt->execute([':avatar' => $avatarPath, ':id' => $userId]);
                // Update local $user array for immediate display
                $user['avatar'] = $avatarPath;
            }

            // Update other profile fields
            $stmt = $conn->prepare("UPDATE users SET name = :name, phone = :phone, birthdate = :birthdate WHERE id = :id");
            $stmt->execute([':name' => $name, ':phone' => $phone, ':birthdate' => $birthdate, ':id' => $userId]);

            $conn->commit();

            $_SESSION['user_name'] = $name;
            $success = 'Cập nhật thông tin thành công!';

            // Reload user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch();
        } catch (Exception $ex) {
            $conn->rollBack();
            $error = $ex->getMessage();
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Vui lòng điền đầy đủ thông tin.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
    } else {
        // Verify current password
        if (password_verify($currentPassword, $user['password'])) {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
            $stmt->execute([':password' => $hashedPassword, ':id' => $userId]);
            $success = 'Đổi mật khẩu thành công!';
        } else {
            $error = 'Mật khẩu hiện tại không chính xác.';
        }
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    redirect(SITE_URL . '/auth.php');
}

// Get user orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5");
$stmt->execute([':user_id' => $userId]);
$orders = $stmt->fetchAll();

$pageTitle = 'Thông tin cá nhân';
include __DIR__ . '/includes/header.php';
?>

<main style="background: var(--background-light); min-height: calc(100vh - 400px);">
    <div style="max-width: 1400px; margin: 0 auto; padding: 2rem 1rem;">
        <div style="display: grid; grid-template-columns: 280px 1fr; gap: 2rem;">
            
            <!-- Sidebar -->
            <aside style="background: white; border-radius: 1rem; padding: 2rem; height: fit-content; position: sticky; top: 100px;">
                <!-- User Avatar -->
                <div style="text-align: center; margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid var(--border-light);">
                    <?php if (!empty($user['avatar'])): ?>
                        <div style="width: 100px; height: 100px; margin: 0 auto 1rem; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #f5f5f5;">
                            <img src="<?= sanitize($user['avatar']) ?>" alt="Avatar" style="width:100%; height:100%; object-fit:cover; display:block;">
                        </div>
                    <?php else: ?>
                        <div style="width: 100px; height: 100px; margin: 0 auto 1rem; border-radius: 50%; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 700; color: white;">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <h3 style="font-size: 1.125rem; font-weight: 700; margin-bottom: 0.25rem;"><?= sanitize($user['name']) ?></h3>
                    <p style="font-size: 0.875rem; color: var(--muted-light);">
                        Thành viên 
                        <?php 
                        $membership = ['bronze' => 'Đồng', 'silver' => 'Bạc', 'gold' => 'Vàng'];
                        echo $membership[$user['membership']];
                        ?>
                    </p>
                </div>
                
                <!-- Menu -->
                <nav style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <a href="?tab=profile" 
                       style="display: flex; align-items: center; gap: 0.75rem; padding: 0.875rem 1rem; border-radius: 0.5rem; transition: all 0.3s; 
                              background: <?= !isset($_GET['tab']) || $_GET['tab'] === 'profile' ? 'rgba(182, 230, 51, 0.15)' : 'transparent' ?>; 
                              color: <?= !isset($_GET['tab']) || $_GET['tab'] === 'profile' ? 'var(--primary-dark)' : 'var(--text-light)' ?>; 
                              font-weight: <?= !isset($_GET['tab']) || $_GET['tab'] === 'profile' ? '700' : '500' ?>;">
                        <span class="material-symbols-outlined">person</span>
                        <span>Thông tin cá nhân</span>
                    </a>
                    
                    <a href="?tab=orders" 
                       style="display: flex; align-items: center; gap: 0.75rem; padding: 0.875rem 1rem; border-radius: 0.5rem; transition: all 0.3s; 
                              background: <?= isset($_GET['tab']) && $_GET['tab'] === 'orders' ? 'rgba(182, 230, 51, 0.15)' : 'transparent' ?>; 
                              color: <?= isset($_GET['tab']) && $_GET['tab'] === 'orders' ? 'var(--primary-dark)' : 'var(--text-light)' ?>; 
                              font-weight: <?= isset($_GET['tab']) && $_GET['tab'] === 'orders' ? '700' : '500' ?>;">
                        <span class="material-symbols-outlined">receipt_long</span>
                        <span>Lịch sử đơn hàng</span>
                    </a>
                    
                    <a href="?tab=addresses" 
                       style="display: flex; align-items: center; gap: 0.75rem; padding: 0.875rem 1rem; border-radius: 0.5rem; transition: all 0.3s; 
                              background: <?= isset($_GET['tab']) && $_GET['tab'] === 'addresses' ? 'rgba(182, 230, 51, 0.15)' : 'transparent' ?>; 
                              color: <?= isset($_GET['tab']) && $_GET['tab'] === 'addresses' ? 'var(--primary-dark)' : 'var(--text-light)' ?>; 
                              font-weight: <?= isset($_GET['tab']) && $_GET['tab'] === 'addresses' ? '700' : '500' ?>;">
                        <span class="material-symbols-outlined">home_pin</span>
                        <span>Địa chỉ đã lưu</span>
                    </a>
                    
                    <a href="?tab=settings" 
                       style="display: flex; align-items: center; gap: 0.75rem; padding: 0.875rem 1rem; border-radius: 0.5rem; transition: all 0.3s; 
                              background: <?= isset($_GET['tab']) && $_GET['tab'] === 'settings' ? 'rgba(182, 230, 51, 0.15)' : 'transparent' ?>; 
                              color: <?= isset($_GET['tab']) && $_GET['tab'] === 'settings' ? 'var(--primary-dark)' : 'var(--text-light)' ?>; 
                              font-weight: <?= isset($_GET['tab']) && $_GET['tab'] === 'settings' ? '700' : '500' ?>;">
                        <span class="material-symbols-outlined">settings</span>
                        <span>Cài đặt</span>
                    </a>
                    
                    <hr style="margin: 1rem 0; border: none; border-top: 1px solid var(--border-light);">
                    
                    <a href="?logout=1" 
                       style="display: flex; align-items: center; gap: 0.75rem; padding: 0.875rem 1rem; border-radius: 0.5rem; transition: all 0.3s; 
                              color: var(--danger); font-weight: 500;">
                        <span class="material-symbols-outlined">logout</span>
                        <span>Đăng xuất</span>
                    </a>
                </nav>
            </aside>
            
            <!-- Main Content -->
            <div>
                <?php if ($success): ?>
                    <div class="alert alert-success" style="margin-bottom: 1.5rem;"><?= $success ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error" style="margin-bottom: 1.5rem;"><?= $error ?></div>
                <?php endif; ?>
                
                <?php
                $tab = $_GET['tab'] ?? 'profile';
                
                switch ($tab) {
                    case 'profile':
                ?>
                        <!-- Profile Tab -->
                        <div style="background: white; border-radius: 1rem; padding: 2rem;">
                            <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem;">Thông tin cá nhân</h2>                  
                            <form method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1.5rem;">
                                <div style="display:flex; gap:1rem; align-items:center;">
                                    <label style="display:block; font-weight:600;">Ảnh đại diện</label>
                                    <div>
                                        <?php if (!empty($user['avatar'])): ?>
                                            <img src="<?= sanitize($user['avatar']) ?>" alt="avatar" style="width:56px; height:56px; object-fit:cover; border-radius:50%; display:block; margin-bottom:0.5rem;">
                                        <?php endif; ?>
                                        <input type="file" name="avatar" accept="image/*" style="display:block;">
                                        <div style="font-size:0.8rem; color:var(--muted-light); margin-top:0.25rem;">Hỗ trợ JPG/PNG/GIF, tối đa 2MB</div>
                                    </div>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                                    <div>
                                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                                            Họ và tên <span style="color: var(--danger);">*</span>
                                        </label>
                                        <input type="text" name="name" value="<?= sanitize($user['name']) ?>" required minlength="3" maxlength="100"
                                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem;">
                                    </div>
                                    
                                    <div>
                                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Ngày sinh</label>
                                             <input type="date" name="birthdate" value="<?= sanitize($user['birthdate'] ?? '') ?>"
                                                 style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem;">
                                    </div>
                                </div>
                                
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                                    <div>
                                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Email</label>
                                        <input type="email" value="<?= sanitize($user['email']) ?>" disabled
                                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem; background: var(--background-light);">
                                    </div>
                                    
                                    <div>
                                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Số điện thoại</label>
                                        <input type="text" name="phone" value="<?= sanitize($user['phone']) ?>" minlength="10" maxlength="13"
                                               placeholder="0xxxxxxxxxx hoặc +84xxxxxxxxx"
                                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem;">
                                        <div id="profile-phone-error" style="color: var(--danger); font-size: 0.875rem; margin-top: 0.25rem; display: none;"></div>
                                    </div>
                                </div>
                                
                                   <!-- Địa chỉ mặc định hiện tại -->
                                <?php
                                $defaultAddr = null;
                                foreach ($addresses as $addr) {
                                    if ($addr['is_default']) {
                                        $defaultAddr = $addr;
                                        break;
                                    }
                                }
                                if ($defaultAddr): ?>
                                    <div style="margin-bottom:2rem;">
                                        <h3 style="font-size:1.1rem; font-weight:600; margin-bottom:0.5rem;">Địa chỉ giao hàng mặc định</h3>
                                        <div style="padding:1rem; background:#f7f8f6; border-radius:0.5rem; border-left: 4px solid var(--primary);">
                                            <div style="font-weight:600; color:#222; margin-bottom:0.25rem;"><?= sanitize($defaultAddr['name']) ?> - <?= sanitize($defaultAddr['phone']) ?></div>
                                            <div style="color:var(--text-light); margin-bottom:0.25rem;"><?= sanitize($defaultAddr['address']) ?></div>
                                            <?php if ($defaultAddr['note']): ?>
                                                <div style="color:var(--muted-light); font-size:0.9rem;">Ghi chú: <?= sanitize($defaultAddr['note']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div style="margin-bottom:2rem;">
                                        <h3 style="font-size:1.1rem; font-weight:600; margin-bottom:0.5rem;">Địa chỉ giao hàng mặc định</h3>
                                        <div style="padding:1rem; background:#f7f8f6; border-radius:0.5rem; color:var(--muted-light);">
                                            Chưa có địa chỉ mặc định. <a href="?tab=addresses" style="color:var(--primary); font-weight:600;">Thêm địa chỉ</a>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div style="display: flex; justify-content: flex-end; gap: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-light);">
                                    <button type="button" class="btn btn-secondary">Hủy</button>
                                    <button type="submit" name="update_profile" class="btn btn-primary">Lưu thay đổi</button>
                                </div>
                            </form>
                        </div>
                <?php
                        break;
                    
                    case 'orders':
                ?>
                        <!-- Orders Tab -->
                        <div style="background: white; border-radius: 1rem; padding: 2rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                                <h2 style="font-size: 1.5rem; font-weight: 700;">Lịch sử đơn hàng</h2>
                                <a href="<?= SITE_URL ?>/order_history.php" class="btn btn-primary" style="padding: 0.5rem 1.5rem; text-decoration: none; display: inline-block;">
                                    Xem tất cả
                                </a>
                            </div>
                            
                            <?php if (empty($orders)): ?>
                                <div style="text-align: center; padding: 3rem;">
                                    <span class="material-symbols-outlined" style="font-size: 4rem; color: var(--muted-light);">receipt_long</span>
                                    <p style="margin-top: 1rem; color: var(--muted-light);">Bạn chưa có đơn hàng nào.</p>
                                    <a href="<?= SITE_URL ?>/products.php" class="btn btn-primary" style="margin-top: 1rem;">
                                        Mua sắm ngay
                                    </a>
                                </div>
                            <?php else: ?>
                                <div style="display: flex; flex-direction: column; gap: 1rem;">
                                    <?php foreach ($orders as $order): 
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
                                    <div style="border: 1px solid var(--border-light); border-radius: 0.75rem; padding: 1.5rem;">
                                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                            <div>
                                                <p style="font-weight: 700;">Đơn hàng #<?= $order['id'] ?></p>
                                                <p style="font-size: 0.875rem; color: var(--muted-light); margin-top: 0.25rem;">
                                                    <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                                </p>
                                            </div>
                                            <span style="padding: 0.5rem 1rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 600; 
                                                         background: <?= $statusColors[$order['status']] ?>20; color: <?= $statusColors[$order['status']] ?>;">
                                                <?= $statusLabels[$order['status']] ?>
                                            </span>
                                        </div>
                                        
                                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid var(--border-light);">
                                            <div>
                                                <p style="font-size: 0.875rem; color: var(--muted-light);">Tổng tiền:</p>
                                                <p style="font-size: 1.125rem; font-weight: 700; color: var(--primary-dark);">
                                                    <?= formatPrice($order['final_amount']) ?>
                                                </p>
                                            </div>
                                            <a href="<?= SITE_URL ?>/order_detail.php?id=<?= $order['id'] ?>" class="btn btn-primary" style="padding: 0.5rem 1.5rem; text-decoration: none; display: inline-block;">
                                                Xem chi tiết
                                            </a>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                <?php
                        break;
                    
                    case 'addresses':
                ?>
                        <!-- Addresses Tab -->
                        <div style="background: white; border-radius: 1rem; padding: 2rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                                <h2 style="font-size: 1.5rem; font-weight: 700;">Địa chỉ đã lưu</h2>
                                <button class="btn btn-primary" onclick="showAddressForm()" type="button">
                                    <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 0.25rem;">add</span>
                                    Thêm địa chỉ mới
                                </button>
                            </div>

                            <!-- Form Add/Edit Address -->
                            <div id="address-form" style="display:none; background: #f9f9f9; padding: 1.5rem; border-radius: 0.75rem; margin-bottom: 2rem; border: 1px solid var(--border-light);">
                                <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem;">
                                    <span id="form-title">Thêm địa chỉ mới</span>
                                </h3>
                                <form id="address-form-element" style="display: grid; gap: 1rem;">
                                    <input type="hidden" id="address-id">
                                    
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                        <div>
                                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Tên người nhận <span style="color: var(--danger);">*</span></label>
                                            <input type="text" id="address-name" placeholder="Nhập tên người nhận" required minlength="3" maxlength="100" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem; font-size: 1rem;">
                                        </div>

                                        <div>
                                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Số điện thoại <span style="color: var(--danger);">*</span></label>
                                            <input type="text" id="address-phone" placeholder="0xxxxxxxxxx hoặc +84xxxxxxxxx" required minlength="10" maxlength="13" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem; font-size: 1rem;">
                                            <div id="phone-error" style="color: var(--danger); font-size: 0.875rem; margin-top: 0.25rem; display: none;"></div>
                                        </div>
                                    </div>

                                    <div>
                                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Địa chỉ <span style="color: var(--danger);">*</span></label>
                                        <textarea id="address-address" placeholder="Nhập địa chỉ giao hàng (số nhà, tên đường,...)" required minlength="5" maxlength="255" rows="3" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem; font-size: 1rem; resize: vertical;"></textarea>
                                    </div>

                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                        <div>
                                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Phường/Xã</label>
                                            <input type="text" id="address-ward" maxlength="100" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem; font-size: 1rem;">
                                        </div>

                                        <div>
                                            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Quận/Huyện</label>
                                            <input type="text" id="address-district" maxlength="100" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem; font-size: 1rem;">
                                        </div>
                                    </div>

                                    <div>
                                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Tỉnh/Thành phố</label>
                                        <input type="text" id="address-city" value="TP. Hồ Chí Minh" minlength="3" maxlength="100" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem; font-size: 1rem;">
                                    </div>

                                    <div>
                                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Ghi chú (tùy chọn)</label>
                                        <input type="text" id="address-note" placeholder="VD: Nhà riêng, Công ty, ..." maxlength="100" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem; font-size: 1rem;">
                                    </div>

                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <input type="checkbox" id="address-default" style="width: 18px; height: 18px; cursor: pointer;">
                                        <label for="address-default" style="cursor: pointer; margin: 0;">Đặt làm địa chỉ mặc định</label>
                                    </div>

                                    <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                                        <button type="button" onclick="hideAddressForm()" class="btn" style="padding: 0.75rem 1.5rem; border: 1px solid var(--border-light); background: #fff; color: var(--text-light); border-radius: 0.5rem; cursor: pointer; font-weight: 500;">Hủy</button>
                                        <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem;">Lưu địa chỉ</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Address List -->
                            <div id="addresses-list" style="display: grid; gap: 1rem;">
                                <?php if (empty($addresses)): ?>
                                    <div style="text-align: center; padding: 3rem 1rem; color: var(--muted-light);">
                                        <span class="material-symbols-outlined" style="font-size: 3rem; display: block; margin-bottom: 1rem; opacity: 0.5;">location_off</span>
                                        <p>Bạn chưa lưu địa chỉ nào. <a href="#" onclick="showAddressForm(); return false;" style="color: var(--primary); font-weight: 600;">Thêm địa chỉ ngay</a></p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($addresses as $addr): ?>
                                        <div class="address-card" data-id="<?= $addr['id'] ?>" style="background: #fff; padding: 1.5rem; border-radius: 0.75rem; border: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: flex-start; position: relative;">
                                            <?php if ($addr['is_default']): ?>
                                                <span style="position: absolute; top: 1rem; right: 1rem; background: var(--primary); color: #fff; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">Mặc định</span>
                                            <?php endif; ?>
                                            
                                            <div style="flex: 1;">
                                                <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">
                                                    <?= sanitize($addr['name']) ?>
                                                    <?php if ($addr['note']): ?>
                                                        <span style="color: var(--muted-light); font-size: 0.9rem; font-weight: 400;">(<?= sanitize($addr['note']) ?>)</span>
                                                    <?php endif; ?>
                                                </h3>
                                                <p style="color: var(--muted-light); margin-bottom: 0.5rem;">
                                                    <span class="material-symbols-outlined" style="vertical-align: middle; font-size: 1rem;">phone</span>
                                                    <?= sanitize($addr['phone']) ?>
                                                </p>
                                                <p style="color: var(--muted-light); margin-bottom: 0.5rem;">
                                                    <span class="material-symbols-outlined" style="vertical-align: middle; font-size: 1rem;">location_on</span>
                                                    <?= sanitize($addr['address']) ?>
                                                </p>
                                                <p style="color: var(--muted-light); font-size: 0.875rem;">
                                                    Thêm <?= date('d/m/Y', strtotime($addr['created_at'])) ?>
                                                </p>
                                            </div>

                                            <div style="display: flex; gap: 0.5rem; margin-left: 1rem;">
                                                <?php if (!$addr['is_default']): ?>
                                                    <button onclick="setDefaultAddress(<?= $addr['id'] ?>)" class="btn-icon" title="Đặt làm mặc định" style="background: none; border: none; cursor: pointer; color: var(--muted-light); padding: 0.5rem; border-radius: 0.5rem; transition: all 0.2s;">
                                                        <span class="material-symbols-outlined">check_circle</span>
                                                    </button>
                                                <?php endif; ?>
                                                <button onclick="editAddress(<?= htmlspecialchars(json_encode($addr)) ?>)" class="btn-icon" title="Chỉnh sửa" style="background: none; border: none; cursor: pointer; color: var(--muted-light); padding: 0.5rem; border-radius: 0.5rem; transition: all 0.2s;">
                                                    <span class="material-symbols-outlined">edit</span>
                                                </button>
                                                <button onclick="deleteAddress(<?= $addr['id'] ?>)" class="btn-icon" title="Xóa" style="background: none; border: none; cursor: pointer; color: var(--danger); padding: 0.5rem; border-radius: 0.5rem; transition: all 0.2s;">
                                                    <span class="material-symbols-outlined">delete</span>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                <?php
                        break;
                    
                    case 'settings':
                ?>
                        <!-- Settings Tab -->
                        <div style="background: white; border-radius: 1rem; padding: 2rem;">
                            <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem;">Cài đặt tài khoản</h2>
                            
                            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                                <!-- Change Password -->
                                <div style="padding: 1.5rem; border: 1px solid var(--border-light); border-radius: 0.75rem;">
                                    <h3 style="font-weight: 700; margin-bottom: 0.5rem;">Đổi mật khẩu</h3>
                                    <p style="font-size: 0.875rem; color: var(--muted-light); margin-bottom: 1rem;">
                                        Cập nhật mật khẩu của bạn để bảo mật tài khoản
                                    </p>
                                    <button onclick="showPasswordForm()" class="btn btn-primary" style="padding: 0.5rem 1.5rem;">Đổi mật khẩu</button>
                                </div>
                                
                                <!-- Notifications -->
                                <div style="padding: 1.5rem; border: 1px solid var(--border-light); border-radius: 0.75rem;">
                                    <h3 style="font-weight: 700; margin-bottom: 1rem;">Thông báo</h3>
                                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                                        <label style="display: flex; justify-content: space-between; align-items: center;">
                                            <div>
                                                <p style="font-weight: 600;">Nhận email khuyến mãi</p>
                                                <p style="font-size: 0.875rem; color: var(--muted-light);">Nhận thông báo về các chương trình ưu đãi</p>
                                            </div>
                                            <input type="checkbox" checked style="width: 48px; height: 24px; accent-color: var(--primary);">
                                        </label>
                                        
                                        <label style="display: flex; justify-content: space-between; align-items: center;">
                                            <div>
                                                <p style="font-weight: 600;">Thông báo đơn hàng</p>
                                                <p style="font-size: 0.875rem; color: var(--muted-light);">Nhận cập nhật về tình trạng đơn hàng</p>
                                            </div>
                                            <input type="checkbox" checked style="width: 48px; height: 24px; accent-color: var(--primary);">
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                        <!-- Password Modal -->
                        <div id="password-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
                            <div style="background: white; border-radius: 1rem; padding: 2rem; max-width: 400px; width: 90%;">
                                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem;">Đổi mật khẩu</h3>
                                <form method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
                                    <div>
                                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Mật khẩu hiện tại</label>
                                        <input type="password" name="current_password" required
                                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem;">
                                    </div>
                                    
                                    <div>
                                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Mật khẩu mới</label>
                                        <input type="password" name="new_password" required
                                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem;">
                                    </div>
                                    
                                    <div>
                                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Xác nhận mật khẩu</label>
                                        <input type="password" name="confirm_password" required
                                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem;">
                                    </div>
                                    
                                    <div style="display: flex; gap: 1rem; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid var(--border-light);">
                                        <button type="button" onclick="hidePasswordForm()" class="btn btn-secondary">Hủy</button>
                                        <button type="submit" name="change_password" class="btn btn-primary">Đổi mật khẩu</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                <?php
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</main>

<script>
// Validate Vietnam phone number format: (0|+84)(3|5|7|8|9)[0-9]{8}
function validateVietnamPhoneNumber(phone) {
    return /^(0|\+84)(3|5|7|8|9)[0-9]{8}$/.test(phone);
}

// Show phone error message
function showPhoneError(phoneInput, errorEl, message) {
    if (message) {
        errorEl.textContent = message;
        errorEl.style.display = 'block';
        phoneInput.style.borderColor = 'var(--danger)';
        phoneInput.style.backgroundColor = 'rgba(239, 68, 68, 0.05)';
    } else {
        errorEl.style.display = 'none';
        phoneInput.style.borderColor = 'var(--border-light)';
        phoneInput.style.backgroundColor = 'transparent';
    }
}

function showPasswordForm() {
    document.getElementById('password-modal').style.display = 'flex';
}

function hidePasswordForm() {
    document.getElementById('password-modal').style.display = 'none';
}

function showAddressForm() {
    document.getElementById('address-form').style.display = 'block';
    document.getElementById('form-title').textContent = 'Thêm địa chỉ mới';
    document.getElementById('address-id').value = '';
    document.getElementById('address-form-element').reset();
}

function hideAddressForm() {
    document.getElementById('address-form').style.display = 'none';
}

function editAddress(address) {
    document.getElementById('address-id').value = address.id;
    document.getElementById('address-name').value = address.name;
    document.getElementById('address-phone').value = address.phone;
    document.getElementById('address-address').value = address.address;
    document.getElementById('address-ward').value = address.ward || '';
    document.getElementById('address-district').value = address.district || '';
    document.getElementById('address-city').value = address.city || 'TP. Hồ Chí Minh';
    document.getElementById('address-note').value = address.note || '';
    document.getElementById('address-default').checked = address.is_default;
    
    document.getElementById('address-form').style.display = 'block';
    document.getElementById('form-title').textContent = 'Chỉnh sửa địa chỉ';
    document.getElementById('address-form-element').scrollIntoView({ behavior: 'smooth' });
}

function setDefaultAddress(id) {
    // Use relative path based on current location
    const apiPath = './api/customer_addresses.php';
    
    fetch(apiPath + '?action=set_default', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(res => res.text())
    .then(text => {
        console.log('set_default response:', text);
        try {
            const data = JSON.parse(text);
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        } catch (e) {
            console.error('JSON parse error:', e);
            alert('Lỗi: ' + text);
        }
    })
    .catch(err => {
        console.error('Fetch error:', err);
        alert('Lỗi: ' + err.message);
    });
}

function deleteAddress(id) {
    if (!confirm('Bạn có chắc muốn xóa địa chỉ này?')) return;
    
    const apiPath = './api/customer_addresses.php';
    
    fetch(apiPath + '?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(res => res.text())
    .then(text => {
        console.log('delete response:', text);
        try {
            const data = JSON.parse(text);
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        } catch (e) {
            console.error('JSON parse error:', e);
            alert('Lỗi: ' + text);
        }
    })
    .catch(err => {
        console.error('Fetch error:', err);
        alert('Lỗi: ' + err.message);
    });
}

// Handle form submit
document.getElementById('address-form-element').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const phoneInput = document.getElementById('address-phone');
    const phone = phoneInput.value.trim();
    
    // Validate phone number format: (0|+84)(3|5|7|8|9)[0-9]{8}
    if (!validateVietnamPhoneNumber(phone)) {
        document.getElementById('phone-error').textContent = 'Định dạng: 0XXXXXXXXXX (10 số) hoặc +84XXXXXXXXX (11 ký tự)';
        document.getElementById('phone-error').style.display = 'block';
        phoneInput.style.borderColor = 'var(--danger)';
        phoneInput.style.backgroundColor = 'rgba(239, 68, 68, 0.05)';
        return false;
    }
    
    // Clear error
    document.getElementById('phone-error').style.display = 'none';
    phoneInput.style.borderColor = 'var(--border-light)';
    phoneInput.style.backgroundColor = 'transparent';
    
    const id = document.getElementById('address-id').value;
    const action = id ? 'update' : 'add';
    const apiPath = './api/customer_addresses.php';
    
    const data = {
        name: document.getElementById('address-name').value,
        phone: phone,
        address: document.getElementById('address-address').value,
        ward: document.getElementById('address-ward').value,
        district: document.getElementById('address-district').value,
        city: document.getElementById('address-city').value,
        note: document.getElementById('address-note').value,
        is_default: document.getElementById('address-default').checked ? 1 : 0
    };
    
    if (id) data.id = id;
    
    fetch(apiPath + '?action=' + action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => {
        console.log('Response status:', res.status);
        return res.text();
    })
    .then(text => {
        console.log('Response text:', text);
        try {
            const data = JSON.parse(text);
            if (data.success) {
                alert(data.message || 'Thành công');
                location.reload();
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        } catch (e) {
            console.error('JSON parse error:', e);
            alert('Lỗi: ' + text);
        }
    })
    .catch(err => {
        console.error('Fetch error:', err);
        alert('Lỗi: ' + err.message);
    });
});

// Validate Vietnam phone number format: (0|+84)(3|5|7|8|9)[0-9]{8}
function validateVietnamPhoneNumber(phone) {
    return /^(0|\+84)(3|5|7|8|9)[0-9]{8}$/.test(phone);
}

// Add real-time phone validation
document.getElementById('address-phone').addEventListener('input', function() {
    const phone = this.value.trim();
    if (phone.length > 0 && !validateVietnamPhoneNumber(phone)) {
        document.getElementById('phone-error').textContent = 'Định dạng: 0XXXXXXXXXX (10 số) hoặc +84XXXXXXXXX (11 ký tự)';
        document.getElementById('phone-error').style.display = 'block';
        this.style.borderColor = 'var(--danger)';
        this.style.backgroundColor = 'rgba(239, 68, 68, 0.05)';
    } else {
        document.getElementById('phone-error').style.display = 'none';
        this.style.borderColor = 'var(--border-light)';
        this.style.backgroundColor = 'transparent';
    }
});

document.getElementById('address-phone').addEventListener('focus', function() {
    document.getElementById('phone-error').style.display = 'none';
    this.style.borderColor = 'var(--border-light)';
    this.style.backgroundColor = 'transparent';
});

// Profile phone validation
const profilePhoneInput = document.querySelector('input[name="phone"]');
if (profilePhoneInput) {
    profilePhoneInput.addEventListener('input', function() {
        const phone = this.value.trim();
        const errorEl = document.getElementById('profile-phone-error');
        
        if (phone.length > 0 && !validateVietnamPhoneNumber(phone)) {
            showPhoneError(this, errorEl, 'Định dạng: 0XXXXXXXXXX (10 số) hoặc +84XXXXXXXXX (11 ký tự)');
        } else {
            showPhoneError(this, errorEl, '');
        }
    });
    
    profilePhoneInput.addEventListener('focus', function() {
        document.getElementById('profile-phone-error').style.display = 'none';
        this.style.borderColor = 'var(--border-light)';
        this.style.backgroundColor = 'transparent';
    });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>