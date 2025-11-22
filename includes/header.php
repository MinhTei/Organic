<?php
// includes/header.php
if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/../config.php';
}

// Get cart count
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
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
    </style>
</head>
<body class="bg-background-light font-display text-text-light">
    
<header class="header">
    <div class="header-container">
        <!-- Logo -->
        <div class="flex items-center gap-8">
            <a href="<?= SITE_URL ?>" class="logo">
                <svg class="w-8 h-8 text-primary" fill="currentColor" viewBox="0 0 48 48">
                    <path d="M39.5563 34.1455V13.8546C39.5563 15.708 36.8773 17.3437 32.7927 18.3189C30.2914 18.916 27.263 19.2655 24 19.2655C20.737 19.2655 17.7086 18.916 15.2073 18.3189C11.1227 17.3437 8.44365 15.708 8.44365 13.8546V34.1455C8.44365 35.9988 11.1227 37.6346 15.2073 38.6098C17.7086 39.2069 20.737 39.5564 24 39.5564C27.263 39.5564 30.2914 39.2069 32.7927 38.6098C36.8773 37.6346 39.5563 35.9988 39.5563 34.1455Z"></path>
                </svg>
                <span class="logo-text"><?= SITE_NAME ?></span>
            </a>
            
            <!-- Navigation -->
            <nav class="nav">
                <a href="<?= SITE_URL ?>" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Trang chủ</a>
                <a href="<?= SITE_URL ?>/products.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">Sản phẩm</a>
                <a href="<?= SITE_URL ?>/about.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : '' ?>">Về chúng tôi</a>
                <a href="<?= SITE_URL ?>/contact.php" class="nav-link">Liên hệ</a>
            </nav>
        </div>
        
        <!-- Header Actions -->
        <div class="header-actions">
            <!-- Search -->
            <div class="search-box">
                <span class="material-symbols-outlined text-muted-light">search</span>
                <form action="<?= SITE_URL ?>/products.php" method="GET">
                    <input type="text" name="search" placeholder="Tìm kiếm..." value="<?= isset($_GET['search']) ? sanitize($_GET['search']) : '' ?>"/>
                </form>
            </div>
            
            <!-- User -->
            <a href="<?= SITE_URL ?>/user_info.php" class="icon-btn">
                <span class="material-symbols-outlined">person</span>
            </a>
            
            <!-- Cart -->
            <a href="<?= SITE_URL ?>/giohang.php" class="icon-btn" style="position: relative;">
                <span class="material-symbols-outlined">shopping_bag</span>
                <?php if ($cartCount > 0): ?>
                <span style="position: absolute; top: -4px; right: -4px; background: var(--primary); color: #000; font-size: 0.75rem; font-weight: 700; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <?= $cartCount ?>
                </span>
                <?php endif; ?>
            </a>
        </div>
    </div>
</header>