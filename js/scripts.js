/**
 * Xanh Organic - Main JavaScript
 */

// Site URL (set from PHP)
let SITE_URL = document.querySelector('meta[name="site-url"]')?.content || '';

/**
 * Add product to cart
 */
function addToCart(productId, quantity = 1) {
    const url = SITE_URL ? `${SITE_URL}/cart.php` : '/Organic/cart.php';
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add&product_id=${productId}&quantity=${quantity}`
    })
    .then(response => {
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
        console.error('Fetch error:', error);
        showNotification('Lỗi kết nối: ' + error.message, 'error');
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
    
    // TODO: Save to server/localStorage
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
            fetch(`${window.location.origin}/organic/cart.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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
    fetch(`${window.location.origin}/organic/api/wishlist.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
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
                    window.location.href = '/organic/auth.php';
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
    console.log('Wishlist count:', count);
}

// Export for global use
window.toggleWishlist = toggleWishlist;