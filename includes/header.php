<?php
// includes/header.php - Updated
if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/../config.php';
}

// Get cart count
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?><?= SITE_NAME ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    
    <!-- Custom CSS -->
    <link href="<?= SITE_URL ?>/css/styles.css" rel="stylesheet"/>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "primary": "#b6e633",
                        "primary-dark": "#9acc2a",
                        "background-light": "#f7f8f6",
                        "text-light": "#161811",
                        "card-light": "#ffffff",
                        "border-light": "#e3e5dc",
                        "muted-light": "#7e8863",
                    },
                    fontFamily: {
                        "display": ["Be Vietnam Pro", "sans-serif"]
                    },
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        
        /* Dropdown Menu */
        .dropdown {
            position: relative;
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 0.5rem;
            min-width: 200px;
            background: white;
            border: 1px solid var(--border-light);
            border-radius: 0.75rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            transition: background 0.2s;
        }
        
        .dropdown-menu a:hover {
            background: rgba(182, 230, 51, 0.1);
        }
        
        .mobile-menu {
            display: none;
            position: fixed;
            top: 80px;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid var(--border-light);
            padding: 1rem;
            z-index: 999;
        }
        
        .mobile-menu.active {
            display: block;
        }
    </style>
</head>
<body class="bg-background-light font-display text-text-light">
    
<header class="header">
    <div class="header-container">
        <!-- Logo -->
        <div class="flex items-center gap-8">
            <a href="<?= SITE_URL ?>" class="logo flex items-center gap-3">
                <?php if (defined('SITE_LOGO') && SITE_LOGO): ?>
                    <img src="<?= SITE_URL . '/' . SITE_LOGO ?>" alt="<?= SITE_NAME ?>" class="h-10 object-contain">
                <?php else: ?>
                    <svg class="w-8 h-8 text-primary" fill="currentColor" viewBox="0 0 48 48">
                        <path d="M39.5563 34.1455V13.8546C39.5563 15.708 36.8773 17.3437 32.7927 18.3189C30.2914 18.916 27.263 19.2655 24 19.2655C20.737 19.2655 17.7086 18.916 15.2073 18.3189C11.1227 17.3437 8.44365 15.708 8.44365 13.8546V34.1455C8.44365 35.9988 11.1227 37.6346 15.2073 38.6098C17.7086 39.2069 20.737 39.5564 24 39.5564C27.263 39.5564 30.2914 39.2069 32.7927 38.6098C36.8773 37.6346 39.5563 35.9988 39.5563 34.1455Z"></path>
                    </svg>
                <?php endif; ?>
                <span class="logo-text"><?= SITE_NAME ?></span>
            </a>
            
            <!-- Navigation -->
            <nav class="nav">
                <a href="<?= SITE_URL ?>" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Trang chủ</a>
                <a href="<?= SITE_URL ?>/products.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">Sản phẩm</a>
                <a href="<?= SITE_URL ?>/about.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : '' ?>">Về chúng tôi</a>
                <a href="<?= SITE_URL ?>/contact.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : '' ?>">Liên hệ</a>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="<?= defined('ADMIN_URL') ? rtrim(ADMIN_URL, '/') . '/dashboard.php' : SITE_URL . '/admin/dashboard.php' ?>" 
                       class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin') !== false ? 'active' : '' ?>">
                        Quản lý
                    </a>
                <?php endif; ?>
            </nav>
        </div>
        
        <!-- Header Actions -->
        <div class="header-actions">
            <!-- Search (áp dụng toàn site, giao diện lớn ở trang chủ) -->
            <form method="GET" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="display: flex; gap: 1rem; align-items: center; max-width: 400px; background: #fff; border-radius: 0.75rem; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                <input type="text" name="search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" placeholder="Tìm kiếm sản phẩm..." style="flex:1; padding: 0.75rem 1rem; border: none; font-size: 1.1rem; outline: none; background: transparent;">
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.25rem; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; background: #b6e633; border: none; border-radius: 0;">
                    <span class="material-symbols-outlined" style="font-size: 1.5rem; color: #161811;">search</span>
                </button>
            </form>
            
            <!-- User Account -->
            <?php if ($isLoggedIn): ?>
                <div class="dropdown">
                    <button class="icon-btn" style="position: relative;">
                        <span class="material-symbols-outlined">person</span>
                    </button>
                    <div class="dropdown-menu">
                        <div style="padding: 1rem; border-bottom: 1px solid var(--border-light);">
                            <p style="font-weight: 700; margin-bottom: 0.25rem;"><?= sanitize($userName) ?></p>
                            <p style="font-size: 0.75rem; color: var(--muted-light);"><?= sanitize($_SESSION['user_email']) ?></p>
                        </div>
                        <a href="<?= SITE_URL ?>/user_info.php">
                            <span class="material-symbols-outlined" style="font-size: 1.25rem;">account_circle</span>
                            <span>Thông tin cá nhân</span>
                        </a>
                        <a href="<?= SITE_URL ?>/user_info.php?tab=orders">
                            <span class="material-symbols-outlined" style="font-size: 1.25rem;">receipt_long</span>
                            <span>Đơn hàng của tôi</span>
                        </a>
                        <a href="<?= SITE_URL ?>/user_info.php?tab=settings">
                            <span class="material-symbols-outlined" style="font-size: 1.25rem;">settings</span>
                            <span>Cài đặt</span>
                        </a>
                        <hr style="margin: 0.5rem 0; border: none; border-top: 1px solid var(--border-light);">
                        <a href="<?= SITE_URL ?>/user_info.php?logout=1" style="color: var(--danger);">
                            <span class="material-symbols-outlined" style="font-size: 1.25rem;">logout</span>
                            <span>Đăng xuất</span>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/auth.php" class="icon-btn">
                    <span class="material-symbols-outlined">person</span>
                </a>
            <?php endif; ?>
            
            <!-- Cart -->
            <a href="<?= SITE_URL ?>/cart.php" class="icon-btn" style="position: relative;">
                <span class="material-symbols-outlined">shopping_bag</span>
                <?php if ($cartCount > 0): ?>
                <span style="position: absolute; top: -4px; right: -4px; background: var(--primary); color: #000; font-size: 0.75rem; font-weight: 700; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <?= $cartCount ?>
                </span>
                <?php endif; ?>
            </a>
            
            <!-- Mobile Menu Toggle -->
            <button class="icon-btn md:hidden" onclick="toggleMobileMenu()">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </div>
    </div>
</header>

<!-- Mobile Menu -->
<div class="mobile-menu" id="mobileMenu">
    <nav style="display: flex; flex-direction: column; gap: 0.5rem;">
        <a href="<?= SITE_URL ?>" style="padding: 0.75rem; border-radius: 0.5rem; font-weight: 600;">Trang chủ</a>
        <a href="<?= SITE_URL ?>/products.php" style="padding: 0.75rem; border-radius: 0.5rem; font-weight: 600;">Sản phẩm</a>
        <a href="<?= SITE_URL ?>/about.php" style="padding: 0.75rem; border-radius: 0.5rem; font-weight: 600;">Về chúng tôi</a>
        <a href="<?= SITE_URL ?>/contact.php" style="padding: 0.75rem; border-radius: 0.5rem; font-weight: 600;">Liên hệ</a>
        <?php if (!$isLoggedIn): ?>
        <hr style="margin: 0.5rem 0;">
        <a href="<?= SITE_URL ?>/auth.php" style="padding: 0.75rem; border-radius: 0.5rem; background: var(--primary); color: white; text-align: center; font-weight: 700;">
            Đăng nhập / Đăng ký
        </a>
        <?php endif; ?>
    </nav>
</div>

<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('active');
}
</script>