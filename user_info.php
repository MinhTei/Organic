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

// Handle avatar delete (separate from profile update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_avatar'])) {
    try {
        $conn->beginTransaction();

        // Delete old avatar file if exists and is a local file
        if (!empty($user['avatar']) && strpos($user['avatar'], 'images/avatars/') === 0) {
            $oldFile = __DIR__ . '/' . $user['avatar'];
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        // Clear avatar column in DB
        $stmt = $conn->prepare("UPDATE users SET avatar = NULL WHERE id = :id");
        $stmt->execute([':id' => $userId]);

        $conn->commit();

        // Update local $user for immediate display
        $user['avatar'] = null;
        $success = 'Đã xóa ảnh đại diện.';
    } catch (Exception $ex) {
        $conn->rollBack();
        $error = $ex->getMessage();
    }
}

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
                    // Ghi lỗi chi tiết vào log để chẩn đoán (mã lỗi upload)
                    error_log("Avatar upload error for user {$userId}: upload error code=" . intval($file['error']));
                    throw new Exception('Lỗi khi tải lên ảnh. Mã lỗi: ' . intval($file['error']));
                }

                if ($file['size'] > $maxSize) {
                    throw new Exception('Kích thước ảnh quá lớn. Vui lòng chọn ảnh < 2MB.');
                }

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);

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
                    // Ghi thêm thông tin để debug: xem tmp file có tồn tại và thư mục đích có thể ghi
                    $tmpExists = is_file($file['tmp_name']) ? 'yes' : 'no';
                    $dirWritable = is_writable($uploadDir) ? 'yes' : 'no';
                    error_log("move_uploaded_file failed for user {$userId}: tmpExists={$tmpExists}, uploadDir={$uploadDir}, dirWritable={$dirWritable}, tmpName={$file['tmp_name']}");
                    throw new Exception('Không thể lưu ảnh tải lên. Vui lòng kiểm tra quyền ghi của thư mục hoặc cấu hình PHP.');
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

<style>
    /* Responsive layout for user info page */
    @media (max-width: 768px) {
        .user-info-container {
            grid-template-columns: 1fr !important;
        }

        aside {
            position: relative !important;
            top: 0 !important;
            display: none;
        }

        .sidebar-mobile-toggle {
            display: flex;
        }
    }

    @media (min-width: 769px) {
        .sidebar-mobile-toggle {
            display: none;
        }
    }

    /* Form responsive improvements */
    @media (max-width: 768px) {
        .form-grid-2col {
            grid-template-columns: 1fr !important;
        }

        .form-container {
            padding: clamp(0.75rem, 2vw, 1.25rem) !important;
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        input[type="password"],
        textarea,
        select {
            font-size: 16px !important;
            /* Prevent zoom on iOS */
            padding: clamp(0.6rem, 1.5vw, 0.875rem) !important;
        }

        .form-label {
            font-size: clamp(0.8rem, 1.5vw, 0.95rem) !important;
            margin-bottom: clamp(0.4rem, 0.8vw, 0.6rem) !important;
        }

        .btn {
            font-size: clamp(0.85rem, 1.8vw, 0.95rem) !important;
            padding: clamp(0.65rem, 1.2vw, 0.875rem) clamp(1rem, 2vw, 1.25rem) !important;
        }

        textarea {
            min-height: 120px !important;
        }

        .modal-content {
            width: 95% !important;
            max-width: 100% !important;
            border-radius: clamp(0.5rem, 1.5vw, 1rem) !important;
            padding: clamp(1rem, 2.5vw, 1.5rem) !important;
        }
    }

    @media (max-width: 500px) {

        input[type="text"],
        input[type="email"],
        input[type="date"],
        input[type="password"],
        textarea,
        select {
            font-size: 16px !important;
            padding: 0.75rem !important;
        }

        textarea {
            min-height: 100px !important;
        }

        .form-row {
            display: flex;
            flex-direction: column;
            gap: clamp(0.5rem, 1.5vw, 1rem) !important;
        }
    }
</style>

<main style="background: var(--background-light); min-height: calc(100vh - 400px);">
    <div style="max-width: 1400px; margin: 0 auto; padding: clamp(1rem, 3vw, 2rem);">
        <div class="user-info-container" style="display: grid; grid-template-columns: clamp(250px, 25vw, 280px) 1fr; gap: clamp(1rem, 2vw, 2rem);">


            <!-- Sidebar -->
            <aside style="background: white; border-radius: clamp(0.5rem, 1.5vw, 1rem); padding: clamp(1.25rem, 2vw, 2rem); height: fit-content; position: sticky; top: clamp(60px, 10vw, 100px);">
                <!-- User Avatar -->
                <div style="text-align: center; margin-bottom: clamp(1rem, 2vw, 2rem); padding-bottom: clamp(1rem, 2vw, 2rem); border-bottom: 1px solid var(--border-light);">
                    <?php if (!empty($user['avatar'])): ?>
                        <div style="width: clamp(80px, 20vw, 100px); height: clamp(80px, 20vw, 100px); margin: 0 auto clamp(0.5rem, 1vw, 1rem); border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #f5f5f5;">
                            <img src="<?= sanitize($user['avatar']) ?>" alt="Avatar" style="width:100%; height:100%; object-fit:cover; display:block;">
                        </div>
                    <?php else: ?>
                        <div style="width: clamp(80px, 20vw, 100px); height: clamp(80px, 20vw, 100px); margin: 0 auto clamp(0.5rem, 1vw, 1rem); border-radius: 50%; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); display: flex; align-items: center; justify-content: center; font-size: clamp(1.5rem, 4vw, 2.5rem); font-weight: 700; color: white;">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <h3 style="font-size: clamp(1rem, 2vw, 1.125rem); font-weight: 700; margin-bottom: clamp(0.25rem, 0.5vw, 0.25rem);"><?= sanitize($user['name']) ?></h3>
                    <p style="font-size: clamp(0.75rem, 1.5vw, 0.875rem); color: var(--muted-light);">
                        Thành viên
                        <?php
                        $membership = ['bronze' => 'Đồng', 'silver' => 'Bạc', 'gold' => 'Vàng'];
                        echo $membership[$user['membership']];
                        ?>
                    </p>
                </div>

                <!-- Menu -->
                <nav style="display: flex; flex-direction: column; gap: clamp(0.3rem, 0.8vw, 0.5rem);">
                    <a href="?tab=profile"
                        style="display: flex; align-items: center; gap: clamp(0.5rem, 1vw, 0.75rem); padding: clamp(0.6rem, 1.2vw, 0.875rem) clamp(0.75rem, 1.5vw, 1rem); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); transition: all 0.3s; font-size: clamp(0.8rem, 1.5vw, 0.95rem);
                              background: <?= !isset($_GET['tab']) || $_GET['tab'] === 'profile' ? 'rgba(182, 230, 51, 0.15)' : 'transparent' ?>; 
                              color: <?= !isset($_GET['tab']) || $_GET['tab'] === 'profile' ? 'var(--primary-dark)' : 'var(--text-light)' ?>; 
                              font-weight: <?= !isset($_GET['tab']) || $_GET['tab'] === 'profile' ? '700' : '500' ?>;">
                        <span class="material-symbols-outlined" style="font-size: clamp(1.2rem, 2vw, 1.5rem);">person</span>
                        <span>Thông tin cá nhân</span>
                    </a>

                    <a href="?tab=orders"
                        style="display: flex; align-items: center; gap: clamp(0.5rem, 1vw, 0.75rem); padding: clamp(0.6rem, 1.2vw, 0.875rem) clamp(0.75rem, 1.5vw, 1rem); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); transition: all 0.3s; font-size: clamp(0.8rem, 1.5vw, 0.95rem);
                              background: <?= isset($_GET['tab']) && $_GET['tab'] === 'orders' ? 'rgba(182, 230, 51, 0.15)' : 'transparent' ?>; 
                              color: <?= isset($_GET['tab']) && $_GET['tab'] === 'orders' ? 'var(--primary-dark)' : 'var(--text-light)' ?>; 
                              font-weight: <?= isset($_GET['tab']) && $_GET['tab'] === 'orders' ? '700' : '500' ?>;">
                        <span class="material-symbols-outlined" style="font-size: clamp(1.2rem, 2vw, 1.5rem);">receipt_long</span>
                        <span>Lịch sử đơn hàng</span>
                    </a>

                    <a href="?tab=addresses"
                        style="display: flex; align-items: center; gap: clamp(0.5rem, 1vw, 0.75rem); padding: clamp(0.6rem, 1.2vw, 0.875rem) clamp(0.75rem, 1.5vw, 1rem); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); transition: all 0.3s; font-size: clamp(0.8rem, 1.5vw, 0.95rem);
                              background: <?= isset($_GET['tab']) && $_GET['tab'] === 'addresses' ? 'rgba(182, 230, 51, 0.15)' : 'transparent' ?>; 
                              color: <?= isset($_GET['tab']) && $_GET['tab'] === 'addresses' ? 'var(--primary-dark)' : 'var(--text-light)' ?>; 
                              font-weight: <?= isset($_GET['tab']) && $_GET['tab'] === 'addresses' ? '700' : '500' ?>;">
                        <span class="material-symbols-outlined" style="font-size: clamp(1.2rem, 2vw, 1.5rem);">home_pin</span>
                        <span>Địa chỉ đã lưu</span>
                    </a>

                    <a href="?tab=settings"
                        style="display: flex; align-items: center; gap: clamp(0.5rem, 1vw, 0.75rem); padding: clamp(0.6rem, 1.2vw, 0.875rem) clamp(0.75rem, 1.5vw, 1rem); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); transition: all 0.3s; font-size: clamp(0.8rem, 1.5vw, 0.95rem);
                              background: <?= isset($_GET['tab']) && $_GET['tab'] === 'settings' ? 'rgba(182, 230, 51, 0.15)' : 'transparent' ?>; 
                              color: <?= isset($_GET['tab']) && $_GET['tab'] === 'settings' ? 'var(--primary-dark)' : 'var(--text-light)' ?>; 
                              font-weight: <?= isset($_GET['tab']) && $_GET['tab'] === 'settings' ? '700' : '500' ?>;">
                        <span class="material-symbols-outlined" style="font-size: clamp(1.2rem, 2vw, 1.5rem);">settings</span>
                        <span>Cài đặt</span>
                    </a>

                    <hr style="margin: clamp(0.75rem, 1.5vw, 1rem) 0; border: none; border-top: 1px solid var(--border-light);">

                    <a href="?logout=1"
                        style="display: flex; align-items: center; gap: clamp(0.5rem, 1vw, 0.75rem); padding: clamp(0.6rem, 1.2vw, 0.875rem) clamp(0.75rem, 1.5vw, 1rem); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); transition: all 0.3s; font-size: clamp(0.8rem, 1.5vw, 0.95rem);
                              color: var(--danger); font-weight: 500;">
                        <span class="material-symbols-outlined" style="font-size: clamp(1.2rem, 2vw, 1.5rem);">logout</span>
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
                        <div class="form-container" style="background: white; border-radius: clamp(0.5rem, 1.5vw, 1rem); padding: clamp(1.25rem, 2vw, 2rem);">
                            <h2 style="font-size: clamp(1.3rem, 3vw, 1.5rem); font-weight: 700; margin-bottom: clamp(1rem, 2vw, 1.5rem);">Thông tin cá nhân</h2>
                            <form method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: clamp(1.25rem, 2.5vw, 1.75rem);">
                                <!-- Avatar Section -->
                                <div style="display: grid; grid-template-columns: auto 1fr; gap: clamp(1rem, 2vw, 1.5rem); align-items: start;">
                                    <div style="text-align: center;">
                                        <?php if (!empty($user['avatar'])): ?>
                                            <img src="<?= sanitize($user['avatar']) ?>" alt="avatar" style="width: clamp(80px, 20vw, 100px); height: clamp(80px, 20vw, 100px); object-fit: cover; border-radius: 50%; display: block; margin-bottom: clamp(0.5rem, 1vw, 0.75rem); border: 3px solid var(--border-light);">
                                        <?php else: ?>
                                            <div style="width: clamp(80px, 20vw, 100px); height: clamp(80px, 20vw, 100px); background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: clamp(2rem, 5vw, 2.5rem); font-weight: 700; margin-bottom: clamp(0.5rem, 1vw, 0.75rem);">
                                                <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <label class="form-label" style="display: block; font-weight: 600;">Ảnh đại diện</label>
                                        <input type="file" name="avatar" accept="image/*" style="display: block; width: 100%; padding: clamp(0.5rem, 1vw, 0.75rem); border: 2px solid var(--border-light); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); font-size: clamp(0.75rem, 1.5vw, 0.9rem); margin-bottom: clamp(0.35rem, 0.8vw, 0.5rem);">
                                        <div style="font-size: clamp(0.7rem, 1.3vw, 0.85rem); color: var(--muted-light); line-height: 1.4;">JPG, PNG, GIF • Tối đa 2MB</div>
                                    </div>
                                    <!-- Delete avatar button - part of main profile form (avoid nested forms) -->
                                    <button type="submit" name="delete_avatar" value="1" class="btn" style="margin-top:0.5rem; background: transparent; border: 1px solid var(--border-light); color: var(--danger); padding: 0.35rem 0.6rem; border-radius: 0.35rem; font-size: 0.85rem;" onclick="return confirm('Bạn có chắc muốn xóa ảnh đại diện?');">Xóa ảnh</button>
                                </div>

                                <hr style="border: none; border-top: 1px solid var(--border-light); margin: clamp(0.5rem, 1vw, 1rem) 0;">

                                <!-- Name and Birthdate -->
                                <div class="form-grid-2col" style="display: grid; grid-template-columns: 1fr 1fr; gap: clamp(1rem, 2vw, 1.5rem);">
                                    <div>
                                        <label class="form-label" style="display: block; font-weight: 600;">
                                            Họ và tên <span style="color: var(--danger);">*</span>
                                        </label>
                                        <input type="text" name="name" value="<?= sanitize($user['name']) ?>" required minlength="3" maxlength="100"
                                            style="width: 100%; padding: clamp(0.6rem, 1vw, 0.875rem); border: 1px solid var(--border-light); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); font-size: clamp(0.85rem, 1.8vw, 1rem); transition: all 0.2s; box-sizing: border-box;"
                                            onblur="this.style.borderColor='var(--border-light)'"
                                            onfocus="this.style.borderColor='var(--primary)'">
                                    </div>

                                    <div>
                                        <label class="form-label" style="display: block; font-weight: 600;">Ngày sinh</label>
                                        <input type="date" name="birthdate" value="<?= sanitize($user['birthdate'] ?? '') ?>"
                                            style="width: 100%; padding: clamp(0.6rem, 1vw, 0.875rem); border: 1px solid var(--border-light); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); font-size: clamp(0.85rem, 1.8vw, 1rem); transition: all 0.2s; box-sizing: border-box;"
                                            onblur="this.style.borderColor='var(--border-light)'"
                                            onfocus="this.style.borderColor='var(--primary)'">
                                    </div>
                                </div>

                                <!-- Email and Phone -->
                                <div class="form-grid-2col" style="display: grid; grid-template-columns: 1fr 1fr; gap: clamp(1rem, 2vw, 1.5rem);">
                                    <div>
                                        <label class="form-label" style="display: block; font-weight: 600;">Email</label>
                                        <input type="email" value="<?= sanitize($user['email']) ?>" disabled
                                            style="width: 100%; padding: clamp(0.6rem, 1vw, 0.875rem); border: 1px solid var(--border-light); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); background: var(--background-light); font-size: clamp(0.85rem, 1.8vw, 1rem); box-sizing: border-box; cursor: not-allowed; color: var(--text-light);">
                                    </div>

                                    <div>
                                        <label class="form-label" style="display: block; font-weight: 600;">Số điện thoại</label>
                                        <input type="text" name="phone" value="<?= sanitize($user['phone']) ?>" minlength="10" maxlength="13"
                                            placeholder="0xxxxxxxxxx"
                                            style="width: 100%; padding: clamp(0.6rem, 1vw, 0.875rem); border: 1px solid var(--border-light); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); font-size: clamp(0.85rem, 1.8vw, 1rem); transition: all 0.2s; box-sizing: border-box;"
                                            onblur="this.style.borderColor='var(--border-light)'"
                                            onfocus="this.style.borderColor='var(--primary)'">
                                        <div id="profile-phone-error" style="color: var(--danger); font-size: clamp(0.75rem, 1.3vw, 0.875rem); margin-top: clamp(0.25rem, 0.5vw, 0.35rem); display: none; line-height: 1.3;"></div>
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
                                    <div style="margin-bottom: clamp(1rem, 2vw, 2rem);">
                                        <h3 style="font-size: clamp(0.95rem, 1.8vw, 1.1rem); font-weight:600; margin-bottom: clamp(0.35rem, 0.8vw, 0.5rem);">Địa chỉ giao hàng mặc định</h3>
                                        <div style="padding: clamp(0.75rem, 1.5vw, 1rem); background:#f7f8f6; border-radius: clamp(0.3rem, 0.8vw, 0.5rem); border-left: 4px solid var(--primary);">
                                            <div style="font-weight:600; color:#222; margin-bottom: clamp(0.15rem, 0.3vw, 0.25rem); font-size: clamp(0.8rem, 1.5vw, 0.95rem);"><?= sanitize($defaultAddr['name']) ?> - <?= sanitize($defaultAddr['phone']) ?></div>
                                            <div style="color:var(--text-light); margin-bottom: clamp(0.15rem, 0.3vw, 0.25rem); font-size: clamp(0.75rem, 1.3vw, 0.9rem);"><?= sanitize($defaultAddr['address']) ?></div>
                                            <div style="color:var(--text-light); margin-bottom: clamp(0.15rem, 0.3vw, 0.25rem); font-size: clamp(0.75rem, 1.3vw, 0.9rem);">
                                                <?php
                                                $location_parts = [];
                                                if (!empty($defaultAddr['ward'])) $location_parts[] = sanitize($defaultAddr['ward']);
                                                if (!empty($defaultAddr['district'])) $location_parts[] = sanitize($defaultAddr['district']);
                                                if (!empty($defaultAddr['city'])) $location_parts[] = sanitize($defaultAddr['city']);
                                                if (!empty($location_parts)) {
                                                    echo implode(', ', $location_parts);
                                                }
                                                ?>
                                            </div>
                                            <?php if ($defaultAddr['note']): ?>
                                                <div style="color:var(--muted-light); font-size: clamp(0.7rem, 1.2vw, 0.9rem);">Ghi chú: <?= sanitize($defaultAddr['note']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div style="margin-bottom: clamp(1rem, 2vw, 2rem);">
                                        <h3 style="font-size:1.1rem; font-weight:600; margin-bottom:0.5rem;">Địa chỉ giao hàng mặc định</h3>
                                        <div style="padding:1rem; background:#f7f8f6; border-radius:0.5rem; color:var(--muted-light);">
                                            Chưa có địa chỉ mặc định. <a href="?tab=addresses" style="color:var(--primary); font-weight:600;">Thêm địa chỉ</a>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div style="display: flex; justify-content: flex-end; gap: clamp(0.75rem, 1.5vw, 1rem); padding-top: clamp(1rem, 2vw, 1.25rem); border-top: 1px solid var(--border-light);">
                                    <button type="submit" name="update_profile" class="btn btn-primary" style="padding: clamp(0.65rem, 1.2vw, 0.875rem) clamp(1.5rem, 3vw, 2rem); background: var(--primary); color: white; border: none; border-radius: clamp(0.3rem, 0.8vw, 0.5rem); cursor: pointer; font-weight: 600; font-size: clamp(0.85rem, 1.8vw, 0.95rem); transition: all 0.2s;"
                                        onmouseover="this.style.background='var(--primary-dark)'" onmouseout="this.style.background='var(--primary)'">Lưu thay đổi</button>
                                </div>
                            </form>
                        </div>
                    <?php
                        break;

                    case 'orders':
                    ?>
                        <!-- Orders Tab -->
                        <div class="form-container" style="background: white; border-radius: clamp(0.5rem, 1.5vw, 1rem); padding: clamp(1.25rem, 2vw, 2rem);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: clamp(1.25rem, 2.5vw, 1.75rem); flex-wrap: wrap; gap: clamp(0.75rem, 1.5vw, 1rem);">
                                <h2 style="font-size: clamp(1.3rem, 3vw, 1.5rem); font-weight: 700; margin: 0;">Lịch sử đơn hàng</h2>
                                <a href="<?= SITE_URL ?>/order_history.php" class="btn btn-primary" style="padding: clamp(0.65rem, 1.2vw, 0.875rem) clamp(1.25rem, 2.5vw, 1.75rem); text-decoration: none; display: inline-block; background: var(--primary); color: white; border: none; border-radius: clamp(0.3rem, 0.8vw, 0.5rem); cursor: pointer; font-weight: 600; font-size: clamp(0.85rem, 1.8vw, 0.95rem); transition: all 0.2s;"
                                    onmouseover="this.style.background='var(--primary-dark)'" onmouseout="this.style.background='var(--primary)'">
                                    Xem tất cả
                                </a>
                            </div>

                            <?php if (empty($orders)): ?>
                                <div style="text-align: center; padding: clamp(2rem, 5vw, 3rem) clamp(1rem, 2vw, 1.5rem);">
                                    <span class="material-symbols-outlined" style="font-size: clamp(3rem, 10vw, 4rem); color: var(--muted-light); display: block; margin-bottom: clamp(0.75rem, 1.5vw, 1rem); opacity: 0.5;">receipt_long</span>
                                    <p style="margin: 0; color: var(--muted-light); font-size: clamp(0.9rem, 1.8vw, 1rem);">Bạn chưa có đơn hàng nào.</p>
                                    <a href="<?= SITE_URL ?>/products.php" class="btn btn-primary" style="margin-top: clamp(1rem, 2vw, 1.5rem); padding: clamp(0.65rem, 1.2vw, 0.875rem) clamp(1.25rem, 2.5vw, 1.75rem); display: inline-block; background: var(--primary); color: white; border: none; border-radius: clamp(0.3rem, 0.8vw, 0.5rem); cursor: pointer; font-weight: 600; font-size: clamp(0.85rem, 1.8vw, 0.95rem); text-decoration: none; transition: all 0.2s;"
                                        onmouseover="this.style.background='var(--primary-dark)'" onmouseout="this.style.background='var(--primary)'">
                                        Mua sắm ngay
                                    </a>
                                </div>
                            <?php else: ?>
                                <div style="display: flex; flex-direction: column; gap: clamp(1rem, 2vw, 1.25rem);">
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
                                        <div style="border: 1px solid var(--border-light); border-radius: clamp(0.5rem, 1.5vw, 0.75rem); padding: clamp(1rem, 2vw, 1.25rem); display: flex; flex-direction: column; gap: clamp(0.75rem, 1.5vw, 1rem);">
                                            <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: clamp(0.75rem, 1.5vw, 1rem);">
                                                <div>
                                                    <p style="font-weight: 700; margin: 0; font-size: clamp(0.9rem, 1.8vw, 1rem);">Đơn hàng #<?= $order['id'] ?></p>
                                                    <p style="font-size: clamp(0.8rem, 1.5vw, 0.875rem); color: var(--muted-light); margin-top: clamp(0.2rem, 0.4vw, 0.35rem);">
                                                        <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                                    </p>
                                                </div>
                                                <span style="padding: clamp(0.35rem, 0.8vw, 0.5rem) clamp(0.75rem, 1.5vw, 1rem); border-radius: 9999px; font-size: clamp(0.8rem, 1.5vw, 0.875rem); font-weight: 600; 
                                                         background: <?= $statusColors[$order['status']] ?>20; color: <?= $statusColors[$order['status']] ?>; white-space: nowrap;">
                                                    <?= $statusLabels[$order['status']] ?>
                                                </span>
                                            </div>

                                            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: clamp(0.75rem, 1.5vw, 1rem); border-top: 1px solid var(--border-light); flex-wrap: wrap; gap: clamp(0.75rem, 1.5vw, 1rem);">
                                                <div>
                                                    <p style="font-size: clamp(0.8rem, 1.5vw, 0.875rem); color: var(--muted-light); margin: 0 0 clamp(0.2rem, 0.4vw, 0.35rem);">Tổng tiền:</p>
                                                    <p style="font-size: clamp(1rem, 2vw, 1.125rem); font-weight: 700; color: var(--primary-dark); margin: 0;">
                                                        <?= formatPrice($order['final_amount']) ?>
                                                    </p>
                                                </div>
                                                <a href="<?= SITE_URL ?>/order_detail.php?id=<?= $order['id'] ?>" class="btn btn-primary" style="padding: clamp(0.65rem, 1.2vw, 0.875rem) clamp(1.25rem, 2.5vw, 1.75rem); text-decoration: none; display: inline-block; background: var(--primary); color: white; border: none; border-radius: clamp(0.3rem, 0.8vw, 0.5rem); cursor: pointer; font-weight: 600; font-size: clamp(0.85rem, 1.8vw, 0.95rem); transition: all 0.2s; white-space: nowrap;"
                                                    onmouseover="this.style.background='var(--primary-dark)'" onmouseout="this.style.background='var(--primary)'">
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
                        <div class="form-container" style="background: white; border-radius: clamp(0.5rem, 1.5vw, 1rem); padding: clamp(1.25rem, 2vw, 2rem);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: clamp(1.25rem, 2.5vw, 1.75rem); flex-wrap: wrap; gap: clamp(0.75rem, 1.5vw, 1rem);">
                                <h2 style="font-size: clamp(1.3rem, 3vw, 1.5rem); font-weight: 700; margin: 0;">Địa chỉ đã lưu</h2>
                                <button class="btn btn-primary" onclick="showAddressForm()" type="button" style="padding: clamp(0.65rem, 1.2vw, 0.875rem) clamp(1.25rem, 2.5vw, 1.75rem); background: var(--primary); color: white; border: none; border-radius: clamp(0.3rem, 0.8vw, 0.5rem); cursor: pointer; font-weight: 600; font-size: clamp(0.85rem, 1.8vw, 0.95rem); display: inline-flex; align-items: center; gap: clamp(0.25rem, 0.5vw, 0.35rem); transition: all 0.2s;"
                                    onmouseover="this.style.background='var(--primary-dark)'" onmouseout="this.style.background='var(--primary)'">
                                    <span class="material-symbols-outlined" style="font-size: clamp(1rem, 1.8vw, 1.25rem);">add</span>
                                    <span>Thêm địa chỉ mới</span>
                                </button>
                            </div>

                            <!-- Form Add/Edit Address -->
                            <div id="address-form" style="display: none; background: #f9f9f9; padding: clamp(1.25rem, 2.5vw, 2rem); border-radius: clamp(0.5rem, 1.5vw, 1rem); margin-bottom: clamp(1.5rem, 2vw, 2rem); border: 1px solid var(--border-light);">
                                <h3 style="font-size: clamp(1rem, 2vw, 1.25rem); font-weight: 600; margin-bottom: clamp(1rem, 2vw, 1.5rem);">
                                    <span id="form-title">Thêm địa chỉ mới</span>
                                </h3>
                                <form id="address-form-element" style="display: grid; gap: clamp(1.25rem, 2.5vw, 1.75rem);">
                                    <input type="hidden" id="address-id">

                                    <!-- Name and Phone Row -->
                                    <div class="form-grid-2col" style="display: grid; grid-template-columns: 1fr 1fr; gap: clamp(1rem, 2vw, 1.5rem);">
                                        <div>
                                            <label class="form-label" style="display: block; font-weight: 600; margin-bottom: clamp(0.4rem, 0.8vw, 0.6rem);">Tên người nhận <span style="color: var(--danger);">*</span></label>
                                            <input type="text" id="address-name" placeholder="Nhập tên người nhận" required minlength="3" maxlength="100" style="width: 100%; padding: clamp(0.6rem, 1vw, 0.875rem); border: 1px solid var(--border-light); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); font-size: clamp(0.85rem, 1.8vw, 1rem); box-sizing: border-box; transition: all 0.2s;" onblur="this.style.borderColor='var(--border-light)'" onfocus="this.style.borderColor='var(--primary)'">
                                        </div>

                                        <div>
                                            <label class="form-label" style="display: block; font-weight: 600; margin-bottom: clamp(0.4rem, 0.8vw, 0.6rem);">Số điện thoại <span style="color: var(--danger);">*</span></label>
                                            <input type="text" id="address-phone" placeholder="0xxxxxxxxxx" required minlength="10" maxlength="13" style="width: 100%; padding: clamp(0.6rem, 1vw, 0.875rem); border: 1px solid var(--border-light); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); font-size: clamp(0.85rem, 1.8vw, 1rem); box-sizing: border-box; transition: all 0.2s;" onblur="this.style.borderColor='var(--border-light)'" onfocus="this.style.borderColor='var(--primary)'">
                                            <div id="phone-error" style="color: var(--danger); font-size: clamp(0.75rem, 1.3vw, 0.875rem); margin-top: clamp(0.25rem, 0.5vw, 0.35rem); display: none; line-height: 1.3;"></div>
                                        </div>
                                    </div>

                                    <!-- Address Textarea -->
                                    <div>
                                        <label class="form-label" style="display: block; font-weight: 600; margin-bottom: clamp(0.4rem, 0.8vw, 0.6rem);">Địa chỉ <span style="color: var(--danger);">*</span></label>
                                        <textarea id="address-address" placeholder="Nhập địa chỉ giao hàng (số nhà, tên đường,...)" required minlength="5" maxlength="255" style="width: 100%; padding: clamp(0.6rem, 1vw, 0.875rem); border: 1px solid var(--border-light); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); font-size: clamp(0.85rem, 1.8vw, 1rem); box-sizing: border-box; resize: vertical; min-height: clamp(100px, 20vw, 140px); transition: all 0.2s;" onblur="this.style.borderColor='var(--border-light)'" onfocus="this.style.borderColor='var(--primary)'"></textarea>
                                    </div>

                                    <!-- Ward and District Row -->
                                    <div class="form-grid-2col" style="display: grid; grid-template-columns: 1fr 1fr; gap: clamp(1rem, 2vw, 1.5rem);">
                                        <div>
                                            <label class="form-label" style="display: block; font-weight: 600; margin-bottom: clamp(0.4rem, 0.8vw, 0.6rem);">Quận/Huyện</label>
                                            <select id="address-district" style="width: 100%; padding: clamp(0.6rem, 1vw, 0.875rem); border: 1px solid var(--border-light); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); font-size: clamp(0.85rem, 1.8vw, 1rem); box-sizing: border-box; transition: all 0.2s;" onchange="updateWardsDropdown()" onblur="this.style.borderColor='var(--border-light)'" onfocus="this.style.borderColor='var(--primary)';">
                                                <option value="">-- Chọn quận --</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="form-label" style="display: block; font-weight: 600; margin-bottom: clamp(0.4rem, 0.8vw, 0.6rem);">Phường/Xã</label>
                                            <select id="address-ward" style="width: 100%; padding: clamp(0.6rem, 1vw, 0.875rem); border: 1px solid var(--border-light); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); font-size: clamp(0.85rem, 1.8vw, 1rem); box-sizing: border-box; transition: all 0.2s;" onblur="this.style.borderColor='var(--border-light)'" onfocus="this.style.borderColor='var(--primary)';">
                                                <option value="">-- Chọn phường/xã --</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- City (Fixed as TP. Hồ Chí Minh) -->
                                    <div>
                                        <label class="form-label" style="display: block; font-weight: 600; margin-bottom: clamp(0.4rem, 0.8vw, 0.6rem);">Tỉnh/Thành phố</label>
                                        <input type="text" id="address-city" value="TP. Hồ Chí Minh" readonly style="width: 100%; padding: clamp(0.6rem, 1vw, 0.875rem); border: 1px solid var(--border-light); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); font-size: clamp(0.85rem, 1.8vw, 1rem); box-sizing: border-box; background-color: #f5f5f5; color: var(--muted-light);">
                                        <input type="hidden" name="city" value="TP. Hồ Chí Minh">
                                    </div>

                                    <!-- Note -->
                                    <div>
                                        <label class="form-label" style="display: block; font-weight: 600; margin-bottom: clamp(0.4rem, 0.8vw, 0.6rem);">Ghi chú (tùy chọn)</label>
                                        <input type="text" id="address-note" placeholder="VD: Nhà riêng, Công ty, ..." maxlength="100" style="width: 100%; padding: clamp(0.6rem, 1vw, 0.875rem); border: 1px solid var(--border-light); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); font-size: clamp(0.85rem, 1.8vw, 1rem); box-sizing: border-box; transition: all 0.2s;" onblur="this.style.borderColor='var(--border-light)'" onfocus="this.style.borderColor='var(--primary)'">
                                    </div>

                                    <!-- Default Address Checkbox -->
                                    <div style="display: flex; align-items: center; gap: clamp(0.6rem, 1.2vw, 0.75rem); padding: clamp(0.75rem, 1.5vw, 1rem); background: white; border-radius: clamp(0.3rem, 0.8vw, 0.5rem); border: 1px solid var(--border-light);">
                                        <input type="checkbox" id="address-default" style="width: clamp(18px, 4vw, 20px); height: clamp(18px, 4vw, 20px); cursor: pointer; accent-color: var(--primary);">
                                        <label for="address-default" style="cursor: pointer; margin: 0; font-weight: 500; font-size: clamp(0.85rem, 1.8vw, 0.95rem);">Đặt làm địa chỉ mặc định</label>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="form-row" style="display: flex; gap: clamp(0.75rem, 1.5vw, 1rem); justify-content: flex-end;">
                                        <button type="button" onclick="hideAddressForm()" class="btn" style="padding: clamp(0.65rem, 1.2vw, 0.875rem) clamp(1.25rem, 2.5vw, 1.75rem); border: 1px solid var(--border-light); background: #fff; color: var(--text-light); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); cursor: pointer; font-weight: 500; font-size: clamp(0.85rem, 1.8vw, 0.95rem); transition: all 0.2s;"
                                            onmouseover="this.style.background='var(--background-light)'" onmouseout="this.style.background='#fff'">Hủy</button>
                                        <button type="submit" class="btn btn-primary" style="padding: clamp(0.65rem, 1.2vw, 0.875rem) clamp(1.25rem, 2.5vw, 1.75rem); background: var(--primary); color: #fff; border: none; border-radius: clamp(0.3rem, 0.8vw, 0.5rem); cursor: pointer; font-weight: 600; font-size: clamp(0.85rem, 1.8vw, 0.95rem); transition: all 0.2s;"
                                            onmouseover="this.style.background='var(--primary-dark)'" onmouseout="this.style.background='var(--primary)'">Lưu địa chỉ</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Address List -->
                            <div id="addresses-list" style="display: grid; gap: clamp(1rem, 2vw, 1.25rem);">
                                <?php if (empty($addresses)): ?>
                                    <div style="text-align: center; padding: clamp(2rem, 5vw, 3rem) clamp(1rem, 2vw, 1.5rem); color: var(--muted-light);">
                                        <span class="material-symbols-outlined" style="font-size: clamp(2.5rem, 8vw, 3rem); display: block; margin-bottom: clamp(0.75rem, 1.5vw, 1rem); opacity: 0.5;">location_off</span>
                                        <p style="margin: 0 0 clamp(0.75rem, 1.5vw, 1rem); font-size: clamp(0.9rem, 1.8vw, 1rem);">Bạn chưa lưu địa chỉ nào. <a href="#" onclick="showAddressForm(); return false;" style="color: var(--primary); font-weight: 600; text-decoration: none;">Thêm địa chỉ ngay</a></p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($addresses as $addr): ?>
                                        <div class="address-card" data-id="<?= $addr['id'] ?>" style="background: #fff; padding: clamp(1.25rem, 2.5vw, 1.5rem); border-radius: clamp(0.5rem, 1.5vw, 0.75rem); border: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: flex-start; position: relative; gap: clamp(1rem, 2vw, 1.5rem); flex-wrap: wrap;">
                                            <?php if ($addr['is_default']): ?>
                                                <span style="position: absolute; top: clamp(0.75rem, 1.5vw, 1rem); right: clamp(0.75rem, 1.5vw, 1rem); background: var(--primary); color: #fff; padding: clamp(0.25rem, 0.5vw, 0.35rem) clamp(0.6rem, 1.2vw, 0.75rem); border-radius: 9999px; font-size: clamp(0.7rem, 1.2vw, 0.8rem); font-weight: 600; white-space: nowrap;">Mặc định</span>
                                            <?php endif; ?>

                                            <div style="flex: 1; min-width: 200px;">
                                                <h3 style="font-size: clamp(1rem, 1.8vw, 1.1rem); font-weight: 600; margin: 0 0 clamp(0.5rem, 1vw, 0.75rem);">
                                                    <?= sanitize($addr['name']) ?>
                                                    <?php if ($addr['note']): ?>
                                                        <span style="color: var(--muted-light); font-size: clamp(0.85rem, 1.5vw, 0.9rem); font-weight: 400;">(<?= sanitize($addr['note']) ?>)</span>
                                                    <?php endif; ?>
                                                </h3>
                                                <p style="color: var(--muted-light); margin: 0 0 clamp(0.35rem, 0.8vw, 0.5rem); display: flex; align-items: center; gap: clamp(0.4rem, 0.8vw, 0.5rem); font-size: clamp(0.85rem, 1.5vw, 0.95rem);">
                                                    <span class="material-symbols-outlined" style="font-size: clamp(1rem, 1.8vw, 1.1rem);">phone</span>
                                                    <?= sanitize($addr['phone']) ?>
                                                </p>
                                                <p style="color: var(--muted-light); margin: 0 0 clamp(0.35rem, 0.8vw, 0.5rem); display: flex; align-items: flex-start; gap: clamp(0.4rem, 0.8vw, 0.5rem); font-size: clamp(0.85rem, 1.5vw, 0.95rem);">
                                                    <span class="material-symbols-outlined" style="font-size: clamp(1rem, 1.8vw, 1.1rem); flex-shrink: 0;">location_on</span>
                                                    <span><?= sanitize($addr['address']) ?></span>
                                                </p>
                                                <p style="color: var(--muted-light); margin: 0 0 clamp(0.35rem, 0.8vw, 0.5rem); font-size: clamp(0.8rem, 1.5vw, 0.875rem);">
                                                    <?php
                                                    $location_parts = [];
                                                    if (!empty($addr['ward'])) $location_parts[] = sanitize($addr['ward']);
                                                    if (!empty($addr['district'])) $location_parts[] = sanitize($addr['district']);
                                                    if (!empty($addr['city'])) $location_parts[] = sanitize($addr['city']);
                                                    if (!empty($location_parts)) {
                                                        echo implode(', ', $location_parts);
                                                    }
                                                    ?>
                                                </p>
                                                <p style="color: var(--muted-light); font-size: clamp(0.8rem, 1.5vw, 0.875rem); margin: 0; margin-top: clamp(0.35rem, 0.8vw, 0.5rem);">
                                                    Thêm <?= date('d/m/Y', strtotime($addr['created_at'])) ?>
                                                </p>
                                            </div>

                                            <div style="display: flex; gap: clamp(0.35rem, 0.8vw, 0.5rem); flex-shrink: 0; margin-top: clamp(1.25rem, 2.5vw, 1.5rem);">
                                                <?php if (!$addr['is_default']): ?>
                                                    <button onclick="setDefaultAddress(<?= $addr['id'] ?>)" class="btn-icon" title="Đặt làm mặc định" style="background: transparent; border: 1px solid var(--border-light); cursor: pointer; color: var(--muted-light); padding: clamp(0.4rem, 0.8vw, 0.5rem); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); transition: all 0.2s; display: flex; align-items: center; justify-content: center;"
                                                        onmouseover="this.style.background='var(--background-light)'; this.style.color='var(--primary)'" onmouseout="this.style.background='transparent'; this.style.color='var(--muted-light)'">
                                                        <span class="material-symbols-outlined">check_circle</span>
                                                    </button>
                                                <?php endif; ?>
                                                <button onclick="editAddress(<?= htmlspecialchars(json_encode($addr)) ?>)" class="btn-icon" title="Chỉnh sửa" style="background: transparent; border: 1px solid var(--border-light); cursor: pointer; color: var(--muted-light); padding: clamp(0.4rem, 0.8vw, 0.5rem); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); transition: all 0.2s; display: flex; align-items: center; justify-content: center;"
                                                    onmouseover="this.style.background='var(--background-light)'; this.style.color='var(--primary)'" onmouseout="this.style.background='transparent'; this.style.color='var(--muted-light)'">
                                                    <span class="material-symbols-outlined">edit</span>
                                                </button>
                                                <button onclick="deleteAddress(<?= $addr['id'] ?>)" class="btn-icon" title="Xóa" style="background: transparent; border: 1px solid var(--danger-light, rgba(239, 68, 68, 0.2)); cursor: pointer; color: var(--danger); padding: clamp(0.4rem, 0.8vw, 0.5rem); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); transition: all 0.2s; display: flex; align-items: center; justify-content: center;"
                                                    onmouseover="this.style.background='rgba(239, 68, 68, 0.1)'" onmouseout="this.style.background='transparent'">
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
                        <div class="form-container" style="background: white; border-radius: clamp(0.5rem, 1.5vw, 1rem); padding: clamp(1.25rem, 2vw, 2rem);">
                            <h2 style="font-size: clamp(1.3rem, 3vw, 1.5rem); font-weight: 700; margin-bottom: clamp(1.25rem, 2.5vw, 1.75rem);">Cài đặt tài khoản</h2>

                            <div style="display: flex; flex-direction: column; gap: clamp(1.25rem, 2vw, 1.5rem);">
                                <!-- Change Password -->
                                <div style="padding: clamp(1.25rem, 2.5vw, 1.5rem); border: 1px solid var(--border-light); border-radius: clamp(0.5rem, 1.5vw, 0.75rem);">
                                    <h3 style="font-weight: 700; margin-bottom: clamp(0.35rem, 0.8vw, 0.5rem); font-size: clamp(0.95rem, 1.8vw, 1.1rem);">Đổi mật khẩu</h3>
                                    <p style="font-size: clamp(0.8rem, 1.5vw, 0.875rem); color: var(--muted-light); margin-bottom: clamp(1rem, 2vw, 1.25rem); line-height: 1.4;">
                                        Cập nhật mật khẩu của bạn để bảo mật tài khoản
                                    </p>
                                    <button onclick="showPasswordForm()" class="btn btn-primary" style="padding: clamp(0.65rem, 1.2vw, 0.875rem) clamp(1.25rem, 2.5vw, 1.75rem); background: var(--primary); color: white; border: none; border-radius: clamp(0.3rem, 0.8vw, 0.5rem); cursor: pointer; font-weight: 600; font-size: clamp(0.85rem, 1.8vw, 0.95rem); transition: all 0.2s;"
                                        onmouseover="this.style.background='var(--primary-dark)'" onmouseout="this.style.background='var(--primary)'">Đổi mật khẩu</button>
                                </div>

                                <!-- Notifications -->
                                <div style="padding: clamp(1.25rem, 2.5vw, 1.5rem); border: 1px solid var(--border-light); border-radius: clamp(0.5rem, 1.5vw, 0.75rem);">
                                    <h3 style="font-weight: 700; margin-bottom: clamp(0.75rem, 1.5vw, 1rem); font-size: clamp(0.95rem, 1.8vw, 1.1rem);">Thông báo</h3>
                                    <div style="display: flex; flex-direction: column; gap: clamp(1rem, 2vw, 1.25rem);">
                                        <label style="display: flex; justify-content: space-between; align-items: center; cursor: pointer; padding: clamp(0.75rem, 1.5vw, 1rem); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); transition: all 0.2s;" onmouseover="this.style.background='var(--background-light)'" onmouseout="this.style.background='transparent'">
                                            <div>
                                                <p style="font-weight: 600; margin: 0 0 clamp(0.25rem, 0.5vw, 0.35rem); font-size: clamp(0.9rem, 1.8vw, 1rem);">Nhận email khuyến mãi</p>
                                                <p style="font-size: clamp(0.8rem, 1.5vw, 0.875rem); color: var(--muted-light); margin: 0; line-height: 1.3;">Nhận thông báo về các chương trình ưu đãi</p>
                                            </div>
                                            <input type="checkbox" checked style="width: clamp(40px, 8vw, 48px); height: clamp(20px, 4vw, 24px); cursor: pointer; accent-color: var(--primary); flex-shrink: 0; margin-left: 1rem;">
                                        </label>

                                        <label style="display: flex; justify-content: space-between; align-items: center; cursor: pointer; padding: clamp(0.75rem, 1.5vw, 1rem); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); transition: all 0.2s;" onmouseover="this.style.background='var(--background-light)'" onmouseout="this.style.background='transparent'">
                                            <div>
                                                <p style="font-weight: 600; margin: 0 0 clamp(0.25rem, 0.5vw, 0.35rem); font-size: clamp(0.9rem, 1.8vw, 1rem);">Thông báo đơn hàng</p>
                                                <p style="font-size: clamp(0.8rem, 1.5vw, 0.875rem); color: var(--muted-light); margin: 0; line-height: 1.3;">Nhận cập nhật về tình trạng đơn hàng</p>
                                            </div>
                                            <input type="checkbox" checked style="width: clamp(40px, 8vw, 48px); height: clamp(20px, 4vw, 24px); cursor: pointer; accent-color: var(--primary); flex-shrink: 0; margin-left: 1rem;">
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Password Modal -->
                        <div id="password-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: clamp(0.75rem, 1.5vw, 1rem);">
                            <div class="modal-content" style="background: white; border-radius: clamp(0.5rem, 1.5vw, 1rem); padding: clamp(1.5rem, 2.5vw, 2rem); max-width: 450px; width: 100%;">
                                <h3 style="font-size: clamp(1.2rem, 2.5vw, 1.5rem); font-weight: 700; margin-bottom: clamp(1.25rem, 2vw, 1.5rem);">Đổi mật khẩu</h3>
                                <form method="POST" style="display: flex; flex-direction: column; gap: clamp(1.25rem, 2vw, 1.5rem);">
                                    <div>
                                        <label class="form-label" style="display: block; font-weight: 600; margin-bottom: clamp(0.4rem, 0.8vw, 0.6rem);">Mật khẩu hiện tại</label>
                                        <input type="password" name="current_password" required
                                            style="width: 100%; padding: clamp(0.6rem, 1vw, 0.875rem); border: 1px solid var(--border-light); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); font-size: clamp(0.85rem, 1.8vw, 1rem); box-sizing: border-box; transition: all 0.2s;"
                                            onblur="this.style.borderColor='var(--border-light)'" onfocus="this.style.borderColor='var(--primary)'">
                                    </div>

                                    <div>
                                        <label class="form-label" style="display: block; font-weight: 600; margin-bottom: clamp(0.4rem, 0.8vw, 0.6rem);">Mật khẩu mới</label>
                                        <input type="password" name="new_password" required
                                            style="width: 100%; padding: clamp(0.6rem, 1vw, 0.875rem); border: 1px solid var(--border-light); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); font-size: clamp(0.85rem, 1.8vw, 1rem); box-sizing: border-box; transition: all 0.2s;"
                                            onblur="this.style.borderColor='var(--border-light)'" onfocus="this.style.borderColor='var(--primary)'">
                                    </div>

                                    <div>
                                        <label class="form-label" style="display: block; font-weight: 600; margin-bottom: clamp(0.4rem, 0.8vw, 0.6rem);">Xác nhận mật khẩu</label>
                                        <input type="password" name="confirm_password" required
                                            style="width: 100%; padding: clamp(0.6rem, 1vw, 0.875rem); border: 1px solid var(--border-light); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); font-size: clamp(0.85rem, 1.8vw, 1rem); box-sizing: border-box; transition: all 0.2s;"
                                            onblur="this.style.borderColor='var(--border-light)'" onfocus="this.style.borderColor='var(--primary)'">
                                    </div>

                                    <div style="display: flex; gap: clamp(0.75rem, 1.5vw, 1rem); justify-content: flex-end; padding-top: clamp(1rem, 2vw, 1.25rem); border-top: 1px solid var(--border-light);">
                                        <button type="button" onclick="hidePasswordForm()" class="btn" style="padding: clamp(0.65rem, 1.2vw, 0.875rem) clamp(1.25rem, 2.5vw, 1.75rem); background: var(--background-light); color: var(--text); border: 1px solid var(--border-light); border-radius: clamp(0.3rem, 0.8vw, 0.5rem); cursor: pointer; font-weight: 600; font-size: clamp(0.85rem, 1.8vw, 0.95rem); transition: all 0.2s;"
                                            onmouseover="this.style.background='#e0e0e0'" onmouseout="this.style.background='var(--background-light)'">Hủy</button>
                                        <button type="submit" name="change_password" class="btn btn-primary" style="padding: clamp(0.65rem, 1.2vw, 0.875rem) clamp(1.25rem, 2.5vw, 1.75rem); background: var(--primary); color: #fff; border: none; border-radius: clamp(0.3rem, 0.8vw, 0.5rem); cursor: pointer; font-weight: 600; font-size: clamp(0.85rem, 1.8vw, 0.95rem); transition: all 0.2s;"
                                            onmouseover="this.style.background='var(--primary-dark)'" onmouseout="this.style.background='var(--primary)'">Đổi mật khẩu</button>
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
    // Lưu trữ dữ liệu quận/huyện và phường/xã từ file JSON
    let districtsData = {};

    // Hàm tải dữ liệu địa chỉ từ file JSON và khởi tạo dropdown quận
    async function loadDistrictsData() {
        try {
            // Gọi API lấy file districts.json
            const response = await fetch('./js/districts.json');
            // Chuyển đổi dữ liệu từ JSON thành object JavaScript
            const data = await response.json();
            // Duyệt qua từng quận trong file JSON
            data.districts.forEach(district => {
                // Lưu tên quận làm key, danh sách phường làm value
                districtsData[district.name] = district.wards;
            });
            // Đưa dữ liệu quận vào dropdown
            populateDistrictsDropdown();
        } catch (error) {
            // In lỗi ra console nếu không thể tải file
            console.error('Error loading districts data:', error);
        }
    }

    // Hàm điền dữ liệu quận vào dropdown quận
    function populateDistrictsDropdown() {
        // Lấy element dropdown quận từ HTML
        const districtSelect = document.getElementById('address-district');
        // Lấy tất cả tên quận, sắp xếp theo thứ tự ABC
        Object.keys(districtsData).sort().forEach(district => {
            // Tạo element <option> mới
            const option = document.createElement('option');
            // Đặt giá trị cho option (tên quận)
            option.value = district;
            // Đặt text hiển thị cho option (tên quận)
            option.textContent = district;
            // Thêm option vào dropdown
            districtSelect.appendChild(option);
        });
    }

    // Hàm cập nhật danh sách phường khi người dùng chọn quận
    function updateWardsDropdown() {
        // Lấy element dropdown quận
        const districtSelect = document.getElementById('address-district');
        // Lấy element dropdown phường
        const wardSelect = document.getElementById('address-ward');
        // Lấy giá trị quận được chọn
        const selectedDistrict = districtSelect.value;

        // Xóa tất cả option phường cũ (giữ lại option mặc định)
        wardSelect.innerHTML = '<option value="">-- Chọn phường/xã --</option>';

        // Nếu đã chọn quận và quận tồn tại trong dữ liệu
        if (selectedDistrict && districtsData[selectedDistrict]) {
            // Duyệt qua từng phường của quận được chọn
            districtsData[selectedDistrict].forEach(ward => {
                // Tạo element <option> mới cho phường
                const option = document.createElement('option');
                // Đặt giá trị cho option (tên phường)
                option.value = ward;
                // Đặt text hiển thị cho option (tên phường)
                option.textContent = ward;
                // Thêm option vào dropdown phường
                wardSelect.appendChild(option);
            });
        }
    }

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
        document.getElementById('address-form-element').scrollIntoView({
            behavior: 'smooth'
        });
    }

    function setDefaultAddress(id) {
        // Use relative path based on current location
        const apiPath = './api/customer_addresses.php';

        fetch(apiPath + '?action=set_default', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: id
                })
            })
            .then(res => res.text())
            .then(text => {
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
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: id
                })
            })
            .then(res => res.text())
            .then(text => {
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
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(res => {
                return res.text();
            })
            .then(text => {
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

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadDistrictsData();
    });

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