<?php
// admin/posts.php - Simple admin UI to manage blog posts / news
require_once __DIR__ . '/../config.php';

// basic auth: ensure admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . SITE_URL . '/auth.php');
    exit;
}

$conn = getConnection();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Handle create/update/delete via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = isset($_POST['act']) ? $_POST['act'] : '';
    if ($act === 'create' || $act === 'update') {
        $title = isset($_POST['title']) ? sanitize($_POST['title']) : '';
        $slug = isset($_POST['slug']) && $_POST['slug'] !== '' ? sanitize($_POST['slug']) : preg_replace('/[^a-z0-9-]+/','-',strtolower(trim($title)));
        $excerpt = isset($_POST['excerpt']) ? sanitize($_POST['excerpt']) : '';
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        $featured_image = isset($_POST['featured_image']) ? sanitize($_POST['featured_image']) : '';
        $status = isset($_POST['status']) && $_POST['status'] === 'published' ? 'published' : 'draft';

        if ($act === 'create') {
            $sql = "INSERT INTO blog_posts (author_id, title, slug, excerpt, content, featured_image, status, published_at) VALUES (:author_id, :title, :slug, :excerpt, :content, :featured_image, :status, :published_at)";
            $stmt = $conn->prepare($sql);
            $published_at = $status === 'published' ? date('Y-m-d H:i:s') : null;
            $stmt->execute([
                ':author_id' => $_SESSION['user_id'],
                ':title' => $title,
                ':slug' => $slug,
                ':excerpt' => $excerpt,
                ':content' => $content,
                ':featured_image' => $featured_image,
                ':status' => $status,
                ':published_at' => $published_at
            ]);
            header('Location: posts.php?success=1');
            exit;
        } else {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $sql = "UPDATE blog_posts SET title = :title, slug = :slug, excerpt = :excerpt, content = :content, featured_image = :featured_image, status = :status, published_at = :published_at, updated_at = NOW() WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $published_at = $status === 'published' ? date('Y-m-d H:i:s') : null;
            $stmt->execute([
                ':title' => $title,
                ':slug' => $slug,
                ':excerpt' => $excerpt,
                ':content' => $content,
                ':featured_image' => $featured_image,
                ':status' => $status,
                ':published_at' => $published_at,
                ':id' => $id
            ]);
            header('Location: posts.php?edited=1');
            exit;
        }
    }
    if (isset($_POST['act']) && $_POST['act'] === 'delete') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $stmt = $conn->prepare('DELETE FROM blog_posts WHERE id = :id');
        $stmt->execute([':id' => $id]);
        header('Location: posts.php?deleted=1');
        exit;
    }
}

// Fetch data for list or edit
if ($action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare('SELECT * FROM blog_posts WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $post = $stmt->fetch();
}

// Pagination / list
$stmt = $conn->query('SELECT SQL_CALC_FOUND_ROWS id, title, slug, status, published_at, created_at FROM blog_posts ORDER BY published_at DESC, created_at DESC');
$posts = $stmt->fetchAll();

// include admin header/footer if available
$pageTitle = 'Quản lý bài viết';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    <style>body { font-family: 'Be Vietnam Pro', sans-serif; }</style>
</head>
<body class="bg-gray-50">

    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-green-600 text-3xl">admin_panel_settings</span>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">Admin Dashboard</h1>
                        <p class="text-xs text-gray-500">Xanh Organic</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="<?= SITE_URL ?>" class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-1">
                        <span class="material-symbols-outlined text-lg">storefront</span>
                        <span>Về trang chủ</span>
                    </a>
                    <div class="flex items-center gap-2 pl-3 border-l border-gray-200">
                        <div class="w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center font-bold">
                            <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
                        </div>
                        <span class="text-sm font-medium text-gray-700"><?= sanitize($_SESSION['user_name']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="flex">
        <!-- Sidebar -->
        <?php include __DIR__ . '/_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <div style="max-width:1100px; margin:2rem auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h1 style="margin:0;">Quản lý bài viết / Tin tức</h1>
        <a href="posts.php?action=create" class="btn" style="background:var(--primary); color:#000; padding:0.5rem 1rem; border-radius:6px;">Tạo bài viết mới</a>
    </div>

<?php if (isset($_GET['success'])): ?>
    <div style="padding:0.75rem; background:#e6f7d6; border-radius:6px; margin-bottom:1rem;">Bài viết đã được tạo.</div>
<?php endif; ?>
<?php if (isset($_GET['edited'])): ?>
    <div style="padding:0.75rem; background:#e6f7d6; border-radius:6px; margin-bottom:1rem;">Bài viết đã được cập nhật.</div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
    <div style="padding:0.75rem; background:#fdecea; border-radius:6px; margin-bottom:1rem;">Bài viết đã bị xóa.</div>
<?php endif; ?>

<?php if ($action === 'create' || $action === 'edit'): ?>
    <?php $editing = ($action === 'edit' && !empty($post)); ?>
    <form method="POST" action="posts.php" style="background:#fff; padding:1rem; border-radius:8px; box-shadow:0 6px 18px rgba(0,0,0,0.04);">
        <input type="hidden" name="act" value="<?= $editing ? 'update' : 'create' ?>">
        <?php if ($editing): ?>
            <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
        <?php endif; ?>

        <div style="display:grid; grid-template-columns: 1fr 280px; gap:1rem;">
            <div>
                <label>Tiêu đề</label>
                <input name="title" value="<?= $editing ? htmlspecialchars($post['title']) : '' ?>" required style="width:100%; padding:0.5rem; margin-top:0.25rem; border:1px solid var(--border-light); border-radius:6px;">

                <label style="margin-top:0.75rem; display:block;">Slug (tùy chọn)</label>
                <input name="slug" value="<?= $editing ? htmlspecialchars($post['slug']) : '' ?>" style="width:100%; padding:0.5rem; margin-top:0.25rem; border:1px solid var(--border-light); border-radius:6px;">

                <label style="margin-top:0.75rem; display:block;">Tóm tắt (excerpt)</label>
                <textarea name="excerpt" style="width:100%; padding:0.5rem; margin-top:0.25rem; border:1px solid var(--border-light); border-radius:6px;" rows="3"><?= $editing ? htmlspecialchars($post['excerpt']) : '' ?></textarea>

                <label style="margin-top:0.75rem; display:block;">Nội dung</label>
                <textarea name="content" style="width:100%; padding:0.5rem; margin-top:0.25rem; border:1px solid var(--border-light); border-radius:6px; height:260px;"><?= $editing ? htmlspecialchars($post['content']) : '' ?></textarea>
            </div>

            <div>
                <label>Ảnh đại diện (URL hoặc path)</label>
                <input name="featured_image" value="<?= $editing ? htmlspecialchars($post['featured_image']) : '' ?>" style="width:100%; padding:0.5rem; margin-top:0.25rem; border:1px solid var(--border-light); border-radius:6px;">

                <label style="margin-top:0.75rem; display:block;">Trạng thái</label>
                <select name="status" style="width:100%; padding:0.5rem; margin-top:0.25rem; border:1px solid var(--border-light); border-radius:6px;">
                    <option value="draft" <?= $editing && $post['status'] === 'draft' ? 'selected' : '' ?>>Nháp</option>
                    <option value="published" <?= $editing && $post['status'] === 'published' ? 'selected' : '' ?>>Đã đăng</option>
                </select>

                <div style="margin-top:1rem; display:flex; gap:0.5rem;">
                    <button type="submit" class="btn" style="background:var(--primary); padding:0.5rem 1rem; border-radius:6px;">Lưu</button>
                    <a href="posts.php" class="btn" style="background:#f3f3f3; padding:0.5rem 1rem; border-radius:6px;">Hủy</a>
                </div>
            </div>
        </div>
    </form>

<?php else: ?>
    <div style="background:#fff; padding:1rem; border-radius:8px; box-shadow:0 6px 18px rgba(0,0,0,0.04);">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="text-align:left; border-bottom:1px solid var(--border-light);">
                    <th style="padding:0.75rem;">Tiêu đề</th>
                    <th style="padding:0.75rem;">Trạng thái</th>
                    <th style="padding:0.75rem;">Đăng</th>
                    <th style="padding:0.75rem;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $r): ?>
                <tr style="border-bottom:1px solid var(--border-light);">
                    <td style="padding:0.75rem; vertical-align: top;"><?= htmlspecialchars($r['title']) ?></td>
                    <td style="padding:0.75rem; vertical-align: top;"><?= htmlspecialchars($r['status']) ?></td>
                    <td style="padding:0.75rem; vertical-align: top;"><?= $r['published_at'] ? htmlspecialchars($r['published_at']) : htmlspecialchars($r['created_at']) ?></td>
                    <td style="padding:0.75rem; vertical-align: top;">
                        <a href="posts.php?action=edit&id=<?= $r['id'] ?>" style="margin-right:0.5rem;">Sửa</a>
                        <form method="POST" action="posts.php" style="display:inline-block;" onsubmit="return confirm('Xác nhận xóa?');">
                            <input type="hidden" name="act" value="delete">
                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                            <button type="submit" style="background:none; border:none; color:#d33; cursor:pointer;">Xóa</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

            </div>
        </main>
    </div>

</body>
</html>
