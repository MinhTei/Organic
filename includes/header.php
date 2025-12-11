<?php
// includes/header.php - Updated with new mobile layout
if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/config.php';
}

// Load settings helper để lấy logo từ database
if (!function_exists('getSystemSetting')) {
    require_once __DIR__ . '/settings_helper.php';
}

// Get cart count
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';

// Get logo từ settings hoặc dùng constant SITE_LOGO nếu có
$siteLogo = getSystemSetting('site_logo', (defined('SITE_LOGO') ? SITE_LOGO : ''));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="site-url" content="<?= SITE_URL ?>"/>
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
        
        /* Header Base Styles */
        .header {
            background: white;
            border-bottom: 1px solid var(--border-light);
            position: sticky;
            top: 0;
            z-index: 50;
        }
        
        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        /* Desktop & Tablet - Original Layout */
        @media (min-width: 768px) {
            .header-container {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 2rem;
                padding: 0.75rem 1rem;
            }
            
            .logo-section {
                display: flex;
                align-items: center;
                gap: 3rem;
                min-width: fit-content;
            }
            
            .nav {
                display: flex !important;
            }
            
            .header-actions {
                display: flex;
                align-items: center;
                gap: 1rem;
            }
            
            /* Hide mobile elements on desktop */
            #mobileMenuBtn {
                display: none !important;
            }
            
            .header-row-1,
            .header-row-2 {
                display: none !important;
            }
        }
        
        /* Mobile - New 2-Row Layout */
        @media (max-width: 767px) {
            .header-container {
                display: flex;
                flex-direction: column;
                gap: 0;
                padding: 0;
            }
            
            /* Hide desktop layout elements */
            .logo-section,
            .nav,
            .header-actions {
                display: none !important;
            }
            
            /* Row 1: Menu - User - Logo - Cart Icon */
            .header-row-1 {
                display: grid !important;
                grid-template-columns: 1fr 2fr 1fr;
                align-items: center;
                gap: 0.5rem;
                padding: 0.75rem 1rem;
                min-height: 70px;
            }
            
            @media (max-width: 767px) {
                .header-row-1 {
                    min-height: 85px;
                }
            }
            
            .header-row-1 .left-icons {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                justify-content: flex-start;
            }
            
            .header-row-1 .logo {
                display: flex;
                justify-content: center;
                align-items: center;
            }
            
            .header-row-1 .logo img,
            .header-row-1 .logo svg {
                max-height: 50px !important;
                max-width: 130px;
                object-fit: contain;
            }
            
            @media (max-width: 767px) {
                .header-row-1 .logo img,
                .header-row-1 .logo svg {
                    max-height: 65px !important;
                    max-width: 150px;
                }
            }
            
            .header-row-1 .right-icons {
                display: flex;
                align-items: center;
                justify-content: flex-end;
            }
            
            /* Icon buttons on mobile */
            .header-row-1 .icon-btn {
                min-width: 44px;
                min-height: 44px;
                padding: 0.5rem;
            }
            
            /* Row 2: Search Bar */
            .header-row-2 {
                display: flex !important;
                padding: 0.5rem 1rem !important;
                width: 100%;
                background: white;
                border-top: 1px solid #e0e0e0;
                border-bottom: 1px solid #e0e0e0;
            }
            
            .mobile-search-form {
                display: flex;
                gap: 0;
                align-items: center;
                background: #f9f9f9;
                border-radius: 0.4rem;
                overflow: hidden;
                width: 100%;
                margin: 0;
                border: 1px solid #e0e0e0;
            }
            
            .mobile-search-form input {
                flex: 1;
                padding: 0.5rem 0.75rem;
                border: none;
                font-size: 0.85rem;
                outline: none;
                background: transparent;
                color: #666;
            }
            
            .mobile-search-form input::placeholder {
                color: #999;
            }
            
            .mobile-search-form button {
                padding: 0.5rem 0.75rem;
                background: transparent;
                border: none;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .mobile-search-form button .material-symbols-outlined {
                color: var(--primary);
            }

            /* Add top margin to page content on mobile to avoid search bar overlap */
            body > *:not(.header):not(.mobile-menu-overlay):not(.mobile-menu-sidebar):not(script):not(style):not(noscript) {
                margin-top: 1rem;
            }

            .container {
                margin-top: 2rem !important;
            }

            main {
                margin-top: 2rem !important;
            }

            section:first-of-type {
                margin-top: 2rem !important;
            }
        }
        
        /* Icon Button Styles */
        .icon-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0.75rem;
            color: inherit;
            border-radius: 0.5rem;
            transition: background 0.2s;
            position: relative;
            min-width: 48px;
            min-height: 48px;
        }
        
        .icon-btn:hover {
            background: rgba(182, 230, 51, 0.1);
        }

        /* Dropdown Menu */
        .dropdown {
            position: relative;
            display: inline-block;
        }

        /* Nav typography */
        .nav-link {
            font-weight: 700;
            font-size: 1rem;
            color: inherit;
            padding: 0.5rem 0.75rem;
            background: transparent;
            border: none;
            cursor: pointer;
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
            color: inherit;
            text-decoration: none;
        }
        
        .dropdown-menu a:hover {
            background: rgba(182, 230, 51, 0.1);
        }
        
        /* Mobile Sidebar Menu */
        .mobile-menu-sidebar {
            display: none;
            position: fixed;
            top: 0;
            left: -100%;
            width: 80%;
            max-width: 320px;
            height: 100vh;
            background: white;
            z-index: 1001;
            overflow-y: auto;
            transition: left 0.3s ease;
            box-shadow: 2px 0 12px rgba(0,0,0,0.15);
        }
        
        .mobile-menu-sidebar.active {
            display: block;
            left: 0;
        }
        
        .mobile-menu-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .mobile-menu-overlay.active {
            display: block;
            opacity: 1;
        }
        
        /* Menu Header */
        .mobile-menu-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-light);
            background: var(--primary);
        }
        
        .mobile-menu-header h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #161811;
            margin: 0;
        }
        
        .mobile-menu-header .close-btn {
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0.25rem;
            color: #161811;
        }
        
        .mobile-menu-sidebar nav {
            display: flex;
            flex-direction: column;
            gap: 0;
        }
        
        .mobile-menu-sidebar nav a,
        .mobile-menu-sidebar nav button {
            display: block;
            padding: 1rem 1.5rem;
            color: var(--text-light);
            text-decoration: none;
            background: transparent;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
            width: 100%;
            text-align: left;
            font-size: 0.95rem;
            border-bottom: 1px solid var(--border-light);
        }
        
        .mobile-menu-sidebar nav a:hover,
        .mobile-menu-sidebar nav button:hover {
            background: rgba(182, 230, 51, 0.1);
        }
        
        .mobile-menu-sidebar nav a.active {
            background: rgba(182, 230, 51, 0.15);
            color: var(--primary-dark);
            font-weight: 700;
            border-left: 4px solid var(--primary);
            padding-left: calc(1.5rem - 4px);
        }
        
        .mobile-menu-sidebar nav hr {
            margin: 0;
            border: none;
            border-top: 2px solid var(--border-light);
        }
        
        .menu-submenu {
            display: none;
            flex-direction: column;
            background: rgba(182, 230, 51, 0.08);
            border-bottom: 1px solid var(--border-light);
        }
        
        .menu-submenu.active {
            display: flex;
        }
        
        .menu-submenu a {
            padding: 0.7rem 1.5rem !important;
            border: none !important;
            margin: 0 !important;
            font-size: 0.9rem !important;
            font-weight: 500 !important;
            padding-left: 2.5rem !important;
            color: var(--muted-light) !important;
        }
        
        .menu-submenu a:hover {
            background: rgba(182, 230, 51, 0.1) !important;
            color: var(--primary) !important;
        }
        
        .menu-item.has-submenu {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            background: transparent;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-light);
            text-align: left;
            transition: all 0.2s;
            gap: 1rem;
        }
        
        .menu-item.has-submenu:hover {
            background: rgba(182, 230, 51, 0.1);
        }
        
        .menu-item.has-submenu span {
            flex: 1;
        }
        
        .menu-item.has-submenu::after {
            content: '▼';
            font-size: 0.7rem;
            transition: transform 0.3s;
            color: var(--muted-light);
            flex-shrink: 0;
            display: inline-block;
            width: 1rem;
            text-align: center;
        }
        
        .menu-item.has-submenu.open::after {
            transform: rotate(180deg);
        }
        
        /* Mobile Menu Footer */
        .mobile-menu-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-light);
            background: white;
        }
        
        .language-selector {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .language-selector button {
            flex: 1;
            height: 44px;
            border: 2px solid var(--border-light);
            border-radius: 0.5rem;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            gap: 0.25rem;
        }
        
        .language-selector button:hover {
            border-color: var(--primary);
            background: rgba(182, 230, 51, 0.05);
        }
        
        .mobile-menu-footer a {
            display: block;
            padding: 0.9rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
            border: 2px solid var(--border-light);
            transition: all 0.2s;
            text-align: center;
            color: inherit;
        }
        
        .mobile-menu-footer a.btn-login {
            background: var(--primary);
            color: #161811;
            border-color: var(--primary);
            font-weight: 700;
        }
        
        .mobile-menu-footer a.btn-login:active {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .mobile-menu-footer a.btn-signup {
            background: transparent;
            color: var(--text-light);
            border-color: var(--border-light);
        }
        
        .mobile-menu-footer a.btn-signup:active {
            background: var(--background-light);
        }
        
        /* Admin Dropdown Menu - Mobile */
        .admin-dropdown {
            position: relative;
        }

        .admin-dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border: 1px solid var(--border-light);
            min-width: 240px;
            z-index: 1002;
            margin-top: 0.5rem;
            overflow: hidden;
        }

        .admin-dropdown-menu.active {
            display: block;
        }

        .admin-dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: inherit;
            text-decoration: none;
            border-bottom: 1px solid var(--border-light);
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .admin-dropdown-menu a:last-child {
            border-bottom: none;
        }

        .admin-dropdown-menu a:hover {
            background: rgba(182, 230, 51, 0.1);
            color: var(--primary-dark);
        }

        .admin-dropdown-menu a .material-symbols-outlined {
            font-size: 1rem;
            flex-shrink: 0;
        }

        .admin-dropdown-header {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border-light);
            background: rgba(182, 230, 51, 0.08);
            font-weight: 700;
            color: var(--primary-dark);
            font-size: 0.9rem;
        }

        /* Desktop - Hide mobile menu */
        @media (min-width: 768px) {
            .mobile-menu-sidebar,
            .mobile-menu-overlay {
                display: none !important;
            }
            
            /* Hide mobile search row on desktop */
            .header-row-2 {
                display: none !important;
            }

            /* Hide admin dropdown on desktop */
            .admin-dropdown {
                display: none !important;
            }
        }
    </style>

    <!-- Main JavaScript -->
    <script src="<?= SITE_URL ?>/js/scripts.js" defer></script>
    
    <script>
        // Mobile Menu Functions
        function openMobileMenu() {
            const sidebar = document.getElementById('mobileMenuSidebar');
            const overlay = document.getElementById('mobileMenuOverlay');
            if (sidebar) sidebar.classList.add('active');
            if (overlay) overlay.classList.add('active');
        }
        
        function closeMobileMenu() {
            const sidebar = document.getElementById('mobileMenuSidebar');
            const overlay = document.getElementById('mobileMenuOverlay');
            if (sidebar) sidebar.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
        }

        // Mobile User Dropdown Functions
        function toggleMobileUserDropdown(event) {
            event.preventDefault();
            event.stopPropagation();
            const dropdown = document.getElementById('mobileUserDropdown');
            const button = event.target.closest('.icon-btn');
            if (dropdown && button) {
                if (dropdown.style.display === 'none') {
                    // Hiển thị dropdown
                    dropdown.style.display = 'block';
                    // Tính toán vị trí
                    const rect = button.getBoundingClientRect();
                    const topPosition = rect.bottom + window.scrollY + 8;
                    dropdown.style.top = topPosition + 'px';
                } else {
                    dropdown.style.display = 'none';
                }
            }
        }

        function closeMobileUserDropdown() {
            const dropdown = document.getElementById('mobileUserDropdown');
            if (dropdown) {
                dropdown.style.display = 'none';
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('mobileUserDropdown');
            const userButton = event.target.closest('.left-icons .dropdown');
            if (dropdown && !userButton) {
                dropdown.style.display = 'none';
            }
        });
        
        // Simple Toggle for Category Menu
        function toggleCategoryMenu(event) {
            event.preventDefault();
            event.stopPropagation();
            
            const button = document.getElementById('categoriesBtn');
            const submenu = document.getElementById('categoriesSubmenu');
            
            // Toggle classes
            button.classList.toggle('open');
            submenu.classList.toggle('active');
        }
        
        // Toggle Submenu (Categories, Language, etc.)
        function toggleSubmenu(event, submenuId, buttonElement) {
            event.preventDefault();
            event.stopPropagation();
            
            const submenu = document.getElementById(submenuId);
            const button = buttonElement;
            
            if (!submenu || !button) {
                return;
            }
            
            // Toggle active class
            submenu.classList.toggle('active');
            button.classList.toggle('open');
        }

        // Add click handler for mobile menu button
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', openMobileMenu);
            }

            // Admin dropdown toggle
            const adminDropdownBtn = document.getElementById('adminDropdownBtn');
            if (adminDropdownBtn) {
                adminDropdownBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const menu = document.getElementById('adminDropdownMenu');
                    if (menu) {
                        menu.classList.toggle('active');
                    }
                });
            }

            // Close admin dropdown when clicking outside
            document.addEventListener('click', function(e) {
                const adminDropdown = document.getElementById('adminDropdownMenu');
                const adminBtn = document.getElementById('adminDropdownBtn');
                if (adminDropdown && adminBtn && !adminDropdown.contains(e.target) && !adminBtn.contains(e.target)) {
                    adminDropdown.classList.remove('active');
                }
            });

            // Close admin dropdown when clicking on a link
            const adminLinks = document.querySelectorAll('.admin-dropdown-menu a');
            adminLinks.forEach(link => {
                link.addEventListener('click', function() {
                    const menu = document.getElementById('adminDropdownMenu');
                    if (menu) {
                        menu.classList.remove('active');
                    }
                });
            });
        });
    </script>
</head>
<body class="bg-background-light font-display text-text-light">
    
<header class="header">
    <div class="header-container">
        <!-- MOBILE LAYOUT - 2 Rows -->
        <!-- Row 1: Menu - User Icon - Logo - Cart Icon -->
        <div class="header-row-1">
            <!-- Left Icons: Menu + User -->
            <div class="left-icons">
                <button class="icon-btn" id="mobileMenuBtn">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                
                <?php if ($isLoggedIn): ?>
                    <div class="dropdown" style="position: relative; z-index: 1002;">
                        <button class="icon-btn" onclick="toggleMobileUserDropdown(event)" style="position: relative;">
                            <span class="material-symbols-outlined">person</span>
                        </button>
                        <div class="mobile-user-dropdown" id="mobileUserDropdown" style="display: none; position: fixed; top: auto; left: 50%; transform: translateX(-50%); background: white; border-radius: 0.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 1002; min-width: 220px; border: 1px solid var(--border-light); margin-top: 0.5rem;">
                            <div style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-light);">
                                <p style="font-weight: 700; margin: 0; margin-bottom: 0.25rem; font-size: 0.85rem;"><?= sanitize($userName) ?></p>
                                <p style="font-size: 0.7rem; color: var(--muted-light); margin: 0;"><?= sanitize($_SESSION['user_email']) ?></p>
                            </div>
                            <a href="<?= SITE_URL ?>/user_info.php" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; color: inherit; text-decoration: none; border-bottom: 1px solid var(--border-light); font-size: 0.9rem;" onclick="closeMobileUserDropdown()">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">account_circle</span>
                                <span>Thông tin cá nhân</span>
                            </a>
                            <a href="<?= SITE_URL ?>/user_info.php?tab=orders" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; color: inherit; text-decoration: none; border-bottom: 1px solid var(--border-light); font-size: 0.9rem;" onclick="closeMobileUserDropdown()">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">receipt_long</span>
                                <span>Xem đơn hàng</span>
                            </a>
                            <a href="<?= SITE_URL ?>/user_info.php?tab=addresses" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; color: inherit; text-decoration: none; border-bottom: 1px solid var(--border-light); font-size: 0.9rem;" onclick="closeMobileUserDropdown()">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">location_on</span>
                                <span>Địa chỉ đã lưu</span>
                            </a>
                            <a href="<?= SITE_URL ?>/user_info.php?tab=settings" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; color: inherit; text-decoration: none; border-bottom: 1px solid var(--border-light); font-size: 0.9rem;" onclick="closeMobileUserDropdown()">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">settings</span>
                                <span>Cài đặt</span>
                            </a>
                            <a href="<?= SITE_URL ?>/user_info.php?logout=1" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; color: var(--danger); text-decoration: none; font-size: 0.9rem;" onclick="closeMobileUserDropdown()">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">logout</span>
                                <span>Đăng xuất</span>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/auth.php" class="icon-btn">
                        <span class="material-symbols-outlined">person</span>
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Center: Logo -->
            <a href="<?= SITE_URL ?>" class="logo">
                <?php if (!empty($siteLogo)): ?>
                    <img src="<?= SITE_URL . '/' . htmlspecialchars($siteLogo) ?>" alt="<?= SITE_NAME ?>">
                <?php elseif (defined('SITE_LOGO') && SITE_LOGO): ?>
                    <img src="<?= SITE_URL . '/' . SITE_LOGO ?>" alt="<?= SITE_NAME ?>">
                <?php else: ?>
                    <svg class="w-12 h-12 text-primary" fill="currentColor" viewBox="0 0 48 48">
                        <path d="M39.5563 34.1455V13.8546C39.5563 15.708 36.8773 17.3437 32.7927 18.3189C30.2914 18.916 27.263 19.2655 24 19.2655C20.737 19.2655 17.7086 18.916 15.2073 18.3189C11.1227 17.3437 8.44365 15.708 8.44365 13.8546V34.1455C8.44365 35.9988 11.1227 37.6346 15.2073 38.6098C17.7086 39.2069 20.737 39.5564 24 39.5564C27.263 39.5564 30.2914 39.2069 32.7927 38.6098C36.8773 37.6346 39.5563 35.9988 39.5563 34.1455Z"></path>
                    </svg>
                <?php endif; ?>
            </a>
            
            <!-- Right Icons: Admin + Cart -->
            <div class="right-icons">
                <?php if ($isLoggedIn && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <div class="admin-dropdown">
                        <button class="icon-btn" id="adminDropdownBtn" title="Quản lý">
                            <span class="material-symbols-outlined">admin_panel_settings</span>
                        </button>
                        <div class="admin-dropdown-menu" id="adminDropdownMenu">
                            <div class="admin-dropdown-header">🔧 Quản lý Admin</div>
                            <a href="<?= SITE_URL ?>/admin/dashboard.php">
                                <span class="material-symbols-outlined">dashboard</span>
                                <span>Dashboard</span>
                            </a>
                            <a href="<?= SITE_URL ?>/admin/products.php">
                                <span class="material-symbols-outlined">inventory_2</span>
                                <span>Sản phẩm</span>
                            </a>
                            <a href="<?= SITE_URL ?>/admin/categories.php">
                                <span class="material-symbols-outlined">category</span>
                                <span>Danh mục</span>
                            </a>
                            <a href="<?= SITE_URL ?>/admin/orders.php">
                                <span class="material-symbols-outlined">receipt_long</span>
                                <span>Đơn hàng</span>
                            </a>
                            <a href="<?= SITE_URL ?>/admin/customers.php">
                                <span class="material-symbols-outlined">people</span>
                                <span>Khách hàng</span>
                            </a>
                            <a href="<?= SITE_URL ?>/admin/reviews.php">
                                <span class="material-symbols-outlined">star_rate</span>
                                <span>Đánh giá</span>
                            </a>
                            <a href="<?= SITE_URL ?>/admin/posts.php">
                                <span class="material-symbols-outlined">article</span>
                                <span>Bài viết</span>
                            </a>
                            <a href="<?= SITE_URL ?>/admin/settings.php">
                                <span class="material-symbols-outlined">settings</span>
                                <span>Cài đặt</span>
                            </a>
                            <a href="<?= SITE_URL ?>/admin/statistics.php">
                                <span class="material-symbols-outlined">analytics</span>
                                <span>Thống kê</span>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <a href="<?= SITE_URL ?>/cart.php" class="icon-btn" style="position: relative;">
                    <span class="material-symbols-outlined">shopping_bag</span>
                    <?php if ($cartCount > 0): ?>
                    <span style="position: absolute; top: -4px; right: -4px; background: var(--primary); color: #000; font-size: 0.75rem; font-weight: 700; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <?= $cartCount ?>
                    </span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
        
        <!-- Row 2: Search Bar -->
        <div class="header-row-2">
            <form method="GET" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="mobile-search-form">
                <input type="text" name="search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" placeholder="Gõ từ khóa sản phẩm bạn cần tìm ...">
                <button type="submit">
                    <span class="material-symbols-outlined">search</span>
                </button>
            </form>
        </div>
        
        <!-- DESKTOP LAYOUT - Original Single Row -->
        <div class="logo-section">
            <a href="<?= SITE_URL ?>" class="logo flex items-center gap-3">
                <?php if (!empty($siteLogo)): ?>
                    <img src="<?= SITE_URL . '/' . htmlspecialchars($siteLogo) ?>" alt="<?= SITE_NAME ?>" class="h-20 md:h-20 object-contain">
                <?php elseif (defined('SITE_LOGO') && SITE_LOGO): ?>
                    <img src="<?= SITE_URL . '/' . SITE_LOGO ?>" alt="<?= SITE_NAME ?>" class="h-25 md:h-25 object-contain">
                <?php else: ?>
                    <svg class="w-16 h-16 md:w-20 md:h-20 text-primary" fill="currentColor" viewBox="0 0 48 48">
                        <path d="M39.5563 34.1455V13.8546C39.5563 15.708 36.8773 17.3437 32.7927 18.3189C30.2914 18.916 27.263 19.2655 24 19.2655C20.737 19.2655 17.7086 18.916 15.2073 18.3189C11.1227 17.3437 8.44365 15.708 8.44365 13.8546V34.1455C8.44365 35.9988 11.1227 37.6346 15.2073 38.6098C17.7086 39.2069 20.737 39.5564 24 39.5564C27.263 39.5564 30.2914 39.2069 32.7927 38.6098C36.8773 37.6346 39.5563 35.9988 39.5563 34.1455Z"></path>
                    </svg>
                <?php endif; ?>
            </a>
            
            <!-- Navigation (Desktop only) -->
            <?php $navCategories = function_exists('getCategories') ? getCategories() : []; ?>
            <nav class="nav hidden md:flex" style="gap: 1.5rem;">
                <a href="<?= SITE_URL ?>" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" style="font-size: 0.9rem; padding: 0.25rem 0;">Trang chủ</a>

                <!-- Categories dropdown -->
                <div class="dropdown" style="display:inline-block;">
                    <button class="nav-link" style="display:flex; align-items:center; gap:0.5rem; font-size: 0.9rem; padding: 0.25rem 0;">Danh mục
                        <span class="material-symbols-outlined" style="font-size:1rem;">arrow_drop_down</span>
                    </button>
                    <div class="dropdown-menu" style="left: 50%; transform: translateX(-50%); min-width: 240px;">
                        <?php if (!empty($navCategories)): ?>
                            <?php foreach ($navCategories as $c): ?>
                                <a href="<?= SITE_URL ?>/products.php?category=<?= $c['id'] ?>">
                                    <?php if (!empty($c['icon'])): ?>
                                        <img src="<?= imageUrl($c['icon']) ?>" alt="<?= sanitize($c['name']) ?>" style="width:36px;height:36px;border-radius:6px;object-fit:cover;">
                                    <?php else: ?>
                                        <span class="material-symbols-outlined" style="font-size:1.25rem;color:var(--primary-dark);">category</span>
                                    <?php endif; ?>
                                    <span style="font-weight:600;"><?= htmlspecialchars_decode(sanitize($c['name'])) ?></span>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="padding:0.75rem 1rem; color:var(--muted-light);">Không có danh mục</div>
                        <?php endif; ?>
                    </div>
                </div>

                <a href="<?= SITE_URL ?>/products.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>" style="font-size: 0.9rem; padding: 0.25rem 0;">Sản phẩm</a>
                <a href="<?= SITE_URL ?>/about.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : '' ?>" style="font-size: 0.9rem; padding: 0.25rem 0;">Về chúng tôi</a>
                <a href="<?= SITE_URL ?>/contact.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : '' ?>" style="font-size: 0.9rem; padding: 0.25rem 0;">Liên hệ</a>
                
                <!-- Lab Thực Hành dropdown -->
                <div class="dropdown" style="display:inline-block;">
                    <button class="nav-link" style="display:flex; align-items:center; gap:0.5rem; font-size: 0.9rem; padding: 0.25rem 0;">Lab Thực Hành
                        <span class="material-symbols-outlined" style="font-size:1rem;">arrow_drop_down</span>
                    </button>
                    <div class="dropdown-menu" style="left: 50%; transform: translateX(-50%); min-width: 200px;">
                        <a href="<?= SITE_URL ?>/LabThucHanh/Hau/lab-hau.php" style="display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1.25rem; text-decoration:none; color:inherit;">
                            <span class="material-symbols-outlined" style="font-size:1.25rem;color:var(--primary-dark);">school</span>
                            <span>LabTH Hậu</span>
                        </a>
                        <a href="<?= SITE_URL ?>/LabThucHanh/Tai/lab-tai.php" style="display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1.25rem; text-decoration:none; color:inherit;">
                            <span class="material-symbols-outlined" style="font-size:1.25rem;color:var(--primary-dark);">school</span>
                            <span>LabTH Tài</span>
                        </a>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="<?= defined('ADMIN_URL') ? rtrim(ADMIN_URL, '/') . '/dashboard.php' : SITE_URL . '/admin/dashboard.php' ?>" 
                       class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin') !== false ? 'active' : '' ?>" style="font-size: 0.9rem; padding: 0.25rem 0;">
                        Quản lý
                    </a>
                <?php endif; ?>
            </nav>
        </div>
        
        <!-- Header Actions (Desktop) -->
        <div class="header-actions">
            <!-- Search - Desktop -->
            <form method="GET" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="display: flex; gap: 0; align-items: center; background: #fff; border-radius: 0.75rem; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                <input type="text" name="search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" placeholder="Tìm..." style="flex:1; padding: 0.6rem 0.75rem; border: none; font-size: 0.9rem; outline: none; background: transparent;">
                <button type="submit" class="btn btn-primary" style="padding: 0.6rem 0.8rem; font-size: 1rem; display: flex; align-items: center; justify-content: center; background: #b6e633; border: none; border-radius: 0; cursor: pointer;">
                    <span class="material-symbols-outlined" style="font-size: 1.25rem; color: #161811;">search</span>
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
                        <a href="<?= SITE_URL ?>/user_info.php?tab=addresses">
                            <span class="material-symbols-outlined" style="font-size: 1.25rem;">location_on</span>
                            <span>Địa chỉ đã lưu</span>
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
        </div>
    </div>
</header>

<!-- Mobile Menu Overlay -->
<div class="mobile-menu-overlay" id="mobileMenuOverlay" onclick="closeMobileMenu()"></div>

<!-- Mobile Menu Sidebar -->
<div class="mobile-menu-sidebar" id="mobileMenuSidebar">
    <!-- Menu Header -->
    <div class="mobile-menu-header">
        <h3>MENU</h3>
        <button class="close-btn" onclick="closeMobileMenu()">
            <span class="material-symbols-outlined" style="font-size: 1.75rem;">close</span>
        </button>
    </div>
    
    <nav>
        <a href="<?= SITE_URL ?>" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" onclick="closeMobileMenu()">
            Trang chủ
        </a>
        
        <a href="<?= SITE_URL ?>/products.php" class="<?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>" onclick="closeMobileMenu()">
            Sản phẩm
        </a>
        
        <!-- Categories Submenu -->
        <button class="menu-item has-submenu" id="categoriesBtn" onclick="toggleCategoryMenu(event)">
            <span>Danh mục sản phẩm</span>
        </button>
        <div class="menu-submenu" id="categoriesSubmenu">
            <?php 
                $navCategories = function_exists('getCategories') ? getCategories() : []; 
                
                // Fallback: Nếu không có danh mục, dùng dữ liệu test
                if (empty($navCategories)) {
                    $navCategories = [
                        ['id' => 1, 'name' => 'Rau Sạch'],
                        ['id' => 2, 'name' => 'Trái Cây'],
                        ['id' => 3, 'name' => 'Trứng & Bơ Sữa'],
                        ['id' => 4, 'name' => 'Bánh mì & Bánh ngọt'],
                        ['id' => 5, 'name' => 'Thịt & Hải sản']
                    ];
                }
            ?>
            <?php if (!empty($navCategories)): ?>
                <?php foreach ($navCategories as $c): ?>
                    <a href="<?= SITE_URL ?>/products.php?category=<?= $c['id'] ?>" onclick="closeMobileMenu()">
                        <?= htmlspecialchars_decode(sanitize($c['name'])) ?>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="padding: 1rem 1.5rem; color: var(--muted-light); font-size: 0.9rem;">Không có danh mục</div>
            <?php endif; ?>
        </div>
        
        <a href="<?= SITE_URL ?>/about.php" class="<?= basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : '' ?>" onclick="closeMobileMenu()">
            Về chúng tôi
        </a>
        
        <a href="<?= SITE_URL ?>/contact.php" class="<?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : '' ?>" onclick="closeMobileMenu()">
            Liên hệ
        </a>
        
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <hr>
        <?php endif; ?>
    </nav>
    
    <!-- Mobile Menu Footer -->
    <div class="mobile-menu-footer">
        <!-- Language Selector -->
        <div class="language-selector">
            <button title="Vietnamese" onclick="setLanguage('vi')">🇻🇳 Tiếng Việt</button>
            <button title="English" onclick="setLanguage('en')">🇬🇧 English</button>
        </div>
        
        <!-- Auth Buttons (Only for non-logged in users) -->
        <?php if (!$isLoggedIn): ?>
        <a href="<?= SITE_URL ?>/auth.php" class="btn-login" onclick="closeMobileMenu()">
            Đăng nhập
        </a>
        <a href="<?= SITE_URL ?>/auth.php?mode=register" class="btn-signup" onclick="closeMobileMenu()">
            Đăng ký
        </a>
        <?php endif; ?>
    </div>
</div>