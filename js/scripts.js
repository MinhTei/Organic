/**
 * Xanh Organic - Main JavaScript
 */

// Site URL (set from PHP)
let SITE_URL = document.querySelector('meta[name="site-url"]')?.content || '';

// Fallback to window location if SITE_URL not set
if (!SITE_URL) {
    const path = window.location.pathname;
    if (path.includes('/organic')) {
        SITE_URL = window.location.origin + '/organic';
    }
}

/**
 * Add product to cart
 */
function addToCart(productId, quantity = 1) {
    // Sử dụng SITE_URL để đảm bảo đường dẫn tuyệt đối
    const url = SITE_URL + '/cart.php';
    
    // Create abort controller for timeout
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        credentials: 'include',
        signal: controller.signal,
        body: `action=add&product_id=${productId}&quantity=${quantity}`
    })
    .then(response => {
        clearTimeout(timeoutId);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                showNotification('Đã thêm vào giỏ hàng', 'success');
                updateCartCount(data.cart_count);
            } else {
                showNotification(data.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (e) {
            console.error('JSON parse error:', text);
            showNotification('Lỗi xử lý phản hồi từ máy chủ', 'error');
        }
    })
    .catch(error => {
        clearTimeout(timeoutId);
        console.error('Fetch error:', error);
        console.error('Fetch URL was:', url);
        console.error('Full error:', error.toString());
        
        if (error.name === 'AbortError') {
            showNotification('Lỗi: Yêu cầu quá lâu', 'error');
        } else {
            showNotification('Lỗi kết nối: ' + error.message, 'error');
        }
    });
}


/**
 * Toggle favorite product
 */
function toggleFavorite(productId) {
    const btn = event.target.closest('.product-favorite') || event.currentTarget;
    const icon = btn.querySelector('.material-symbols-outlined');
    
    // Toggle visual state
    if (icon.style.fontVariationSettings?.includes("'FILL' 1")) {
        icon.style.fontVariationSettings = "'FILL' 0";
        icon.style.color = '';
    } else {
        icon.style.fontVariationSettings = "'FILL' 1";
        icon.style.color = '#ef4444';
    }
    
    // Delegate to the proper wishlist function for persistence
    toggleWishlist(productId);
    showNotification('Đã cập nhật yêu thích', 'success');
}

/**
 * Update cart count in header
 */
function updateCartCount(count) {
    // Only update the cart icon badge (shopping_bag)
    const cartIconBtn = document.querySelector('.header-actions a.icon-btn[href$="cart.php"]');
    if (cartIconBtn) {
        let cartBadge = cartIconBtn.querySelector('span:not(.material-symbols-outlined)');
        if (!cartBadge && count > 0) {
            // Create badge if it doesn't exist
            cartBadge = document.createElement('span');
            cartIconBtn.appendChild(cartBadge);
        }
        if (cartBadge) {
            cartBadge.textContent = count;
            cartBadge.style.display = count > 0 ? 'flex' : 'none';
            cartBadge.style.position = 'absolute';
            cartBadge.style.top = '-4px';
            cartBadge.style.right = '-4px';
            cartBadge.style.background = '#b6e633';
            cartBadge.style.color = '#000';
            cartBadge.style.fontSize = '0.75rem';
            cartBadge.style.fontWeight = '700';
            cartBadge.style.width = '20px';
            cartBadge.style.height = '20px';
            cartBadge.style.borderRadius = '50%';
            cartBadge.style.display = 'flex';
            cartBadge.style.alignItems = 'center';
            cartBadge.style.justifyContent = 'center';
        }
    }
}

/**
 * Show notification toast
 */
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existing = document.querySelector('.notification-toast');
    if (existing) existing.remove();
    
    const toast = document.createElement('div');
    toast.className = 'notification-toast';
    toast.innerHTML = `
        <span class="material-symbols-outlined">${type === 'success' ? 'check_circle' : type === 'error' ? 'error' : 'info'}</span>
        <span>${message}</span>
    `;
    
    // Styles
    toast.style.cssText = `
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem 1.5rem;
        background: ${type === 'success' ? '#22c55e' : type === 'error' ? '#ef4444' : '#3b82f6'};
        color: white;
        border-radius: 0.5rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);

/**
 * Mobile menu toggle
 */
function toggleMobileMenu() {
    const nav = document.querySelector('.nav');
    nav.classList.toggle('active');
}

/**
 * Search form handler
 */
document.addEventListener('DOMContentLoaded', function() {
    // Handle search form
    const searchInput = document.querySelector('.search-box input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = this.value.trim();
                if (query) {
                    window.location.href = `${window.location.origin}/organic/products.php?search=${encodeURIComponent(query)}`;
                }
            }
        });
    }
    
    // Lazy load images
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
});

/**
 * Format price in VND
 */
function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price);
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Export functions for global use
// Cart quantity update (AJAX, no reload)
document.addEventListener('DOMContentLoaded', function() {
    document.body.addEventListener('change', function(e) {
        if (e.target.classList.contains('cart-qty-input')) {
            const input = e.target;
            const productId = input.dataset.productId;
            let qty = parseInt(input.value);
            if (isNaN(qty) || qty < 1) qty = 1;
            const cartUrl = SITE_URL + '/cart.php';
            fetch(cartUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                credentials: 'include',
                body: `action=update&product_id=${productId}&quantity=${qty}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showNotification('Đã cập nhật số lượng', 'success');
                        updateCartCount(data.cart_count);
                        // Optionally update cart total, etc. via JS here
                } else {
                    showNotification('Có lỗi khi cập nhật', 'error');
                }
            });
        }
    });
});
window.addToCart = addToCart;
window.toggleFavorite = toggleFavorite;
window.showNotification = showNotification;

/**
 * Toggle wishlist
 */
function toggleWishlist(productId) {
    const wishlistUrl = SITE_URL + '/api/wishlist.php';
    fetch(wishlistUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        credentials: 'include',
        body: `action=toggle&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const btn = event.currentTarget;
            const icon = btn.querySelector('.material-symbols-outlined');
            
            if (data.action === 'added') {
                icon.style.fontVariationSettings = "'FILL' 1";
                icon.style.color = '#ef4444';
            } else {
                icon.style.fontVariationSettings = "'FILL' 0";
                icon.style.color = '';
            }
            
            showNotification(data.message, 'success');
            updateWishlistCount(data.count);
        } else {
            showNotification(data.message, 'error');
            
            // Redirect to login if not logged in
            if (data.message.includes('đăng nhập')) {
                setTimeout(() => {
                    window.location.href = SITE_URL + '/auth.php';
                }, 1500);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra', 'error');
    });
}

/**
 * Update wishlist count in header
 */
function updateWishlistCount(count) {
    // Implement if you add wishlist count to header
}

/**
 * Mobile Menu Functions
 */
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
    
    // Also close user dropdown when menu closes
    const userDropdown = document.querySelector('.mobile-user-dropdown');
    if (userDropdown) userDropdown.classList.remove('active');
}

function toggleSubmenu(event, submenuId) {
    event.preventDefault();
    const submenu = document.getElementById(submenuId);
    const button = event.currentTarget;
    
    if (submenu) {
        submenu.classList.toggle('open');
        button.classList.toggle('open');
    }
}

function toggleCategoriesDropdown(event, menuId) {
    event.preventDefault();
    const menu = document.getElementById(menuId);
    const button = event.currentTarget;
    
    if (menu && button) {
        menu.classList.toggle('open');
        button.classList.toggle('open');
    }
}

/**
 * Mobile User Dropdown Functions
 */
function setupMobileUserDropdown() {
    const userDropdown = document.querySelector('.mobile-user-dropdown');
    if (!userDropdown) return;
    
    const toggleBtn = userDropdown.querySelector('.dropdown-toggle');
    const menu = userDropdown.querySelector('.mobile-dropdown-menu');
    
    if (!toggleBtn || !menu) return;
    
    // Toggle dropdown on button click
    toggleBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        userDropdown.classList.toggle('active');
    });
    
    // Close dropdown when clicking on menu items
    menu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function() {
            userDropdown.classList.remove('active');
        });
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!userDropdown.contains(e.target)) {
            userDropdown.classList.remove('active');
        }
    });
    
    // Close dropdown when overlay is clicked (menu opened)
    const overlay = document.getElementById('mobileMenuOverlay');
    if (overlay) {
        overlay.addEventListener('click', function() {
            userDropdown.classList.remove('active');
        });
    }
}

/**
 * Mobile Admin Dropdown Functions
 */
function setupMobileAdminDropdown() {
    const adminDropdown = document.querySelector('.mobile-admin-dropdown');
    if (!adminDropdown) return;
    
    const toggleBtn = adminDropdown.querySelector('.dropdown-toggle');
    const menu = adminDropdown.querySelector('.mobile-admin-menu');
    
    if (!toggleBtn || !menu) return;
    
    // Toggle dropdown on button click
    toggleBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        adminDropdown.classList.toggle('active');
    });
    
    // Close dropdown when clicking on menu items
    menu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function() {
            adminDropdown.classList.remove('active');
        });
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!adminDropdown.contains(e.target)) {
            adminDropdown.classList.remove('active');
        }
    });
    
    // Close dropdown when overlay is clicked (menu opened)
    const overlay = document.getElementById('mobileMenuOverlay');
    if (overlay) {
        overlay.addEventListener('click', function() {
            adminDropdown.classList.remove('active');
        });
    }
}

// Initialize mobile menu and dropdown on page load
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu button
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', openMobileMenu);
    }
    
    // Setup mobile user dropdown
    setupMobileUserDropdown();
    
    // Setup mobile admin dropdown
    setupMobileAdminDropdown();
});

// Export for global use
window.toggleWishlist = toggleWishlist;
window.openMobileMenu = openMobileMenu;
window.closeMobileMenu = closeMobileMenu;
window.toggleSubmenu = toggleSubmenu;
window.toggleCategoriesDropdown = toggleCategoriesDropdown;