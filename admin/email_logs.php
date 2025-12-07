<?php
/**
 * admin/email_logs.php - Xem các email đã gửi (dùng cho development)
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    redirect(SITE_URL . '/auth.php');
}

$emailDir = __DIR__ . '/../storage/emails';
$emails = [];

// Lấy danh sách các file email
if (is_dir($emailDir)) {
    $files = array_reverse(scandir($emailDir));
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'html') {
            $emails[] = [
                'filename' => $file,
                'path' => $emailDir . '/' . $file,
                'time' => filemtime($emailDir . '/' . $file)
            ];
        }
    }
}

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_email'])) {
    $filename = sanitize($_POST['delete_email']);
    $filepath = $emailDir . '/' . $filename;
    
    // Validate filename to prevent directory traversal
    if (strpos($filename, '..') === false && file_exists($filepath)) {
        unlink($filepath);
        header('Location: ' . SITE_URL . '/admin/email_logs.php');
        exit;
    }
}

$pageTitle = 'Email Logs';
include __DIR__ . '/../includes/header.php';
?>

<section style="padding: 2rem 1rem; min-height: calc(100vh - 400px);">
    <div style="max-width: 1200px; margin: 0 auto;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 style="font-size: 2rem; font-weight: 700;">📧 Email Logs (Development)</h1>
            <a href="<?= SITE_URL ?>/admin/dashboard.php" class="btn btn-secondary" style="padding: 0.75rem 1.5rem;">← Back</a>
        </div>
        
        <div style="background: white; border-radius: 1rem; padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            
            <?php if (count($emails) === 0): ?>
                <div style="text-align: center; padding: 2rem; color: var(--muted-light);">
                    <p style="font-size: 1.125rem;">Chưa có email nào được lưu</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f5f5f5; border-bottom: 2px solid var(--border-light);">
                                <th style="padding: 1rem; text-align: left; font-weight: 600;">Time</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600;">Filename</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 600;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($emails as $email): ?>
                                <tr style="border-bottom: 1px solid var(--border-light);">
                                    <td style="padding: 1rem;">
                                        <small style="color: var(--muted-light);">
                                            <?= date('Y-m-d H:i:s', $email['time']) ?>
                                        </small>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <code style="background: #f5f5f5; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.875rem;">
                                            <?= htmlspecialchars($email['filename']) ?>
                                        </code>
                                    </td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <a href="<?= SITE_URL ?>/storage/emails/<?= urlencode($email['filename']) ?>" 
                                           target="_blank" 
                                           class="btn btn-primary" 
                                           style="padding: 0.5rem 1rem; font-size: 0.875rem; display: inline-block;">
                                            View
                                        </a>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="delete_email" value="<?= htmlspecialchars($email['filename']) ?>">
                                            <button type="submit" 
                                                    class="btn btn-danger" 
                                                    style="padding: 0.5rem 1rem; font-size: 0.875rem; background: #ef4444; color: white; border: none; border-radius: 0.5rem; cursor: pointer;"
                                                    onclick="return confirm('Delete this email log?');">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-light); text-align: center;">
                    <p style="color: var(--muted-light); margin-bottom: 1rem;">Total: <?= count($emails) ?> email(s)</p>
                    <form method="POST" style="display: inline;">
                        <button type="submit" 
                                name="clear_all" 
                                class="btn btn-danger"
                                style="background: #ef4444; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.5rem; cursor: pointer;"
                                onclick="return confirm('Delete ALL email logs? This cannot be undone.');">
                            Clear All Logs
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        
        <div style="margin-top: 2rem; background: #fef3c7; border-left: 4px solid #f59e0b; padding: 1.5rem; border-radius: 0.5rem;">
            <h3 style="margin-top: 0;">ℹ️ Information</h3>
            <p>This page shows emails that were saved to disk because the server cannot send emails directly (common on localhost).</p>
            <p>Click "View" to open the email in a browser and see the password reset link.</p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
