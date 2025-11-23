<?php
/**
 * user_info.php - Trang thông tin người dùng (cải tiến)
 */

require_once 'config.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    redirect(SITE_URL . '/auth.php');
}

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

// Get user data
$conn = getConnection();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    redirect(SITE_URL . '/auth.php');
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = sanitize($_POST['name']);
    $phone = sanitize($_POST['phone']);
    $birthdate = sanitize($_POST['birthdate'] ?? '');
    
    if (empty($name)) {
        $error = 'Tên không được để trống.';
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = :name, phone = :phone WHERE id = :id");
        if ($stmt->execute([':name' => $name, ':phone' => $phone, ':id' => $userId])) {
            $_SESSION['user_name'] = $name;
            $success = 'Cập nhật thông tin thành công!';
            // Reload user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch();
        } else {
            $error = 'Có lỗi xảy ra, vui lòng thử lại.';
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
include 'includes/header.php';
?>

<main style="background: var(--background-light); min-height: calc(100vh - 400px);">
    <div style="max-width: 1400px; margin: 0 auto; padding: 2rem 1rem;">
        <div style="display: grid; grid-template-columns: 280px 1fr; gap: 2rem;">
            
            <!-- Sidebar -->
            <aside style="background: white; border-radius: 1rem; padding: 2rem; height: fit-content; position: sticky; top: 100px;">
                <!-- User Avatar -->
                <div style="text-align: center; margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid var(--border-light);">
                    <div style="width: 100px; height: 100px; margin: 0 auto 1rem; border-radius: 50%; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 700; color: white;">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
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
                            
                            <form method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                                    <div>
                                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Họ và tên</label>
                                        <input type="text" name="name" value="<?= sanitize($user['name']) ?>" required
                                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem;">
                                    </div>
                                    
                                    <div>
                                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Ngày sinh</label>
                                        <input type="date" name="birthdate" value=""
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
                                        <input type="tel" name="phone" value="<?= sanitize($user['phone']) ?>"
                                               style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: 0.5rem;">
                                    </div>
                                </div>
                                
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
                            <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem;">Lịch sử đơn hàng</h2>
                            
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
                                                    <?= formatPrice($order['total_amount']) ?>
                                                </p>
                                            </div>
                                            <button class="btn btn-primary" style="padding: 0.5rem 1.5rem;">Xem chi tiết</button>
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
                                <button class="btn btn-primary">
                                    <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 0.25rem;">add</span>
                                    Thêm địa chỉ mới
                                </button>
                            </div>
                            
                            <p style="text-align: center; padding: 3rem; color: var(--muted-light);">
                                Bạn chưa có địa chỉ nào được lưu.
                            </p>
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
                                    <button class="btn btn-secondary">Đổi mật khẩu</button>
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
                <?php
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>