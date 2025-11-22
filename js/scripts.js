/**
 * Xanh Organic - Main JavaScript
 */

// Site URL (set from PHP)
const SITE_URL = document.querySelector('meta[name="site-url"]')?.content || '';

/**
 * Add product to cart
 */
function addToCart(productId, quantity = 1) {
    fetch(`${window.location.origin}/organic/giohang.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add&product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            updateCartCount(data.cart_count);
        } else {
            showNotification('Có lỗi xảy ra', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra', 'error');
    });
}

/**
 * Toggle favorite product
 */
function toggleFavorite(productId) {
    const btn = event.currentTarget;
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
    const cartBadge = document.querySelector('.header-actions .icon-btn span:last-child');
    if (cartBadge && cartBadge.style) {
        cartBadge.textContent = count;
        cartBadge.style.display = count > 0 ? 'flex' : 'none';
    }
    
    // Reload page to update cart badge
    location.reload();
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
window.addToCart = addToCart;
window.toggleFavorite = toggleFavorite;
window.showNotification = showNotification;