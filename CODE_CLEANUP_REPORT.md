# Code Cleanup Report - Organic Project
**Generated:** December 7, 2025

---

## Executive Summary

Scanned 7 key files for dead code, unused imports, duplicate functions, and commented code. Found **8 major issues** affecting 4 files.

---

## Detailed Findings

### 1. ⚠️ UNUSED IMPORT - thanhtoan.php
**File:** `thanhtoan.php`  
**Line:** 17  
**Issue Type:** Unused Include  
**Severity:** Medium

```php
require_once __DIR__ . '/includes/email_functions.php';
```

**Description:** The email_functions.php file is imported but `sendOrderConfirmationEmail()` is called at line 322, which appears to fail silently. The function exists but may not be working properly or may not send emails as expected.

**Impact:** Dead code that clutters imports. Email notifications may not be sent.

**Recommendation:** 
- Verify if email sending is actually working in production
- If email notifications are disabled, remove this import entirely
- If needed, check email configuration and error handling

**Replacement:** Remove line 17 if email notifications are not required, OR fix the email function to work properly.

---

### 2. ❌ DEAD CODE - order_detail.php
**File:** `order_detail.php`  
**Line:** 310  
**Issue Type:** Empty Script Tag  
**Severity:** Low

```php
<script>
</script>
```

**Description:** An empty `<script>` tag that serves no purpose and adds unnecessary DOM nodes.

**Impact:** Minimal - just empty markup, but adds unnecessary clutter to the HTML.

**Recommendation:** Remove entirely.

**Replacement:** Delete lines 310-311 completely.

---

### 3. ⚠️ INCOMPLETE/TODO FUNCTIONALITY - js/scripts.js
**File:** `js/scripts.js`  
**Line:** 88  
**Issue Type:** TODO Comment - Unimplemented Feature  
**Severity:** Medium

```javascript
// TODO: Save to server/localStorage
```

**Description:** In the `toggleFavorite()` function, the favorite toggle only updates the UI but doesn't persist data to server or localStorage. This means favorite status is lost on page refresh.

**Impact:** Features appear to work but don't save, creating poor UX. Users' favorite selections are not persisted.

**Context:**
```javascript
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
    
    // TODO: Save to server/localStorage  // <-- LINE 88
    showNotification('Đã cập nhật yêu thích', 'success');
}
```

**Recommendation:** 
- Implement server-side persistence via API endpoint (preferably `api/wishlist.php`)
- OR implement localStorage-based persistence for anonymous users
- Note: `toggleWishlist()` function at line 353+ already implements proper API calls, so copy that pattern

**Replacement:** Implement one of these approaches:
1. Use API endpoint like `api/wishlist.php`
2. Use localStorage for client-side persistence
3. Delegate to the existing `toggleWishlist()` function which already works

---

### 4. ⚠️ DATABASE COMPATIBILITY CODE - thanhtoan.php
**File:** `thanhtoan.php`  
**Lines:** 209-268  
**Issue Type:** Dead Code - Conditional Database Schema Check  
**Severity:** Medium

```php
// Kiểm tra xem cột shipping_email có tồn tại không
$columnCheckStmt = $conn->query("SHOW COLUMNS FROM orders LIKE 'shipping_email'");
$hasEmailColumn = $columnCheckStmt && $columnCheckStmt->rowCount() > 0;

if ($hasEmailColumn) {
    // INSERT with shipping_email column
    // ... LINES 216-235
} else {
    // INSERT without shipping_email column (fallback)
    // ... LINES 236-263
}
```

**Description:** Code attempts to handle two different database schemas - with and without `shipping_email` column. This is backward compatibility code for old databases, but modern codebase should have standardized schema.

**Impact:** 
- Doubles database operation overhead (SHOW COLUMNS query on every order)
- Makes code harder to maintain
- Indicates schema migrations are incomplete

**Context Location:** Lines 213-268 in thanhtoan.php

**Recommendation:**
- Verify that ALL production databases have the `shipping_email` column
- Run migration if needed: `ALTER TABLE orders ADD COLUMN shipping_email VARCHAR(255);`
- Remove the conditional check and keep only the modern version (with shipping_email)
- Keep only lines 216-235, delete lines 213-215 and 236-268

**Files Involved:**
- `thanhtoan.php` - Has the check
- `admin/order_detail.php` - Already uses `shipping_email` without checking
- Database schema should standardize

---

### 5. ✅ WELL-IMPLEMENTED FUNCTIONS (No Issues)
**File:** `includes/functions.php`

The following functions are properly used and have no dead code issues:

✓ `getCategories()` - Used in multiple places  
✓ `getProducts()` - Used in multiple places  
✓ `getProduct()` - Used in multiple places  
✓ `getFeaturedProducts()` - Used in `index.php` and `admin/index.php`  
✓ `getLatestPosts()` - Used in `index.php` and `admin/index.php`  
✓ `imageUrl()` - Used extensively throughout the project  
✓ `getRelatedProducts()` - Used in `product_detail.php`  
✓ `buildPaginationUrl()` - Used in pagination logic  
✓ `renderPagination()` - Used in page templates  
✓ `renderProductCard()` - Used in page templates  

**Status:** All functions in `includes/functions.php` are properly used. No cleanup needed here.

---

### 6. ⚠️ UNUSED DROPDOWN INSTANCE - admin/order_detail.php
**File:** `admin/order_detail.php`  
**Lines:** 196-218  
**Issue Type:** Duplicate/Problematic HTML Attribute  
**Severity:** Low

```php
<span style="
    display: inline-block;
    background: <?= $statusColors[$order['status']] ?>20;
    color: <?= $statusColors[$order['status']] ?>;
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    font-weight: 600;
    font-size: 0.875rem;
" <?= $order['status'] === 'cancelled' ? 'style="opacity: 0.5;"' : '' ?>>
```

**Description:** The element has two `style` attributes - one inline and one conditional. The second one will override the first, making the code confusing.

**Impact:** Minor - code works but is not clean. Second style attribute will take precedence, potentially breaking the intended styling.

**Recommendation:** Merge style attributes into a single line.

**Replacement:** See cleaned code section below.

---

### 7. ⚠️ STATUS COLOR ARRAYS DUPLICATION
**File:** Multiple files  
**Issue Type:** Code Duplication  
**Severity:** Medium

**Files Affected:**
- `order_detail.php` - Lines 113-127 (status color mapping)
- `admin/orders.php` - Lines 124-131 (status color mapping)
- `admin/order_detail.php` - Lines 52-60 (status color mapping)
- `user_info.php` - Lines 365-371 (status color mapping)

Each file independently defines the same status color/label mappings. This creates maintenance issues if colors need to change.

**Current Duplicates:**
```php
// Example from order_detail.php
$statusColor = 'var(--muted-light)';
$statusLabel = $order['status'];

switch ($order['status']) {
    case 'pending':
        $statusColor = '#f59e0b';
        $statusLabel = 'Chờ xác nhận';
        break;
    case 'confirmed':
        $statusColor = '#3b82f6';
        $statusLabel = 'Đã xác nhận';
        break;
    // ... etc
}
```

**Recommendation:** Create a centralized function in `includes/functions.php`:

```php
function getOrderStatusInfo($status) {
    $statuses = [
        'pending' => ['color' => '#f59e0b', 'label' => 'Chờ xác nhận'],
        'confirmed' => ['color' => '#3b82f6', 'label' => 'Đã xác nhận'],
        'processing' => ['color' => '#8b5cf6', 'label' => 'Đang xử lý'],
        'shipping' => ['color' => '#06b6d4', 'label' => 'Đang giao'],
        'delivered' => ['color' => '#22c55e', 'label' => 'Đã giao'],
        'cancelled' => ['color' => '#ef4444', 'label' => 'Đã hủy'],
        'refunded' => ['color' => '#8b5cf6', 'label' => 'Hoàn tiền']
    ];
    return $statuses[$status] ?? ['color' => 'var(--muted-light)', 'label' => $status];
}
```

Then use: `$statusInfo = getOrderStatusInfo($order['status']);`

---

### 8. ⚠️ UNUSED VARIABLE - admin/order_detail.php
**File:** `admin/order_detail.php`  
**Lines:** 135-211  
**Issue Type:** Unused Array Definition  
**Severity:** Low

**Variables Defined:**
```php
$statusLabels = [
    'pending' => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    // ... etc
];

$statusColors = [
    'pending' => '#f59e0b',
    'confirmed' => '#3b82f6',
    // ... etc
];

$paymentMethods = [
    'cod' => 'Thanh toán khi nhận',
    'bank_transfer' => 'Chuyển khoản'
];
```

**Where Used:**
- `$statusLabels` - Used 1 time at line 135 in a style attribute
- `$statusColors` - Used 1 time at line 136 in a style attribute  
- `$paymentMethods` - Used 1 time at line 168

**Better Approach:** Define these variables only where needed or use the centralized function approach mentioned in issue #7.

---

## Summary Table

| # | File | Line(s) | Type | Severity | Status |
|---|------|---------|------|----------|--------|
| 1 | `thanhtoan.php` | 17 | Unused Import | Medium | Can Remove |
| 2 | `order_detail.php` | 310-311 | Empty Script Tag | Low | Delete |
| 3 | `js/scripts.js` | 88 | TODO/Unimplemented | Medium | Needs Implementation |
| 4 | `thanhtoan.php` | 213-268 | Dead Code (Schema Check) | Medium | Refactor |
| 5 | `includes/functions.php` | N/A | All Clean | N/A | ✅ No Action |
| 6 | `admin/order_detail.php` | 196-218 | Duplicate Attributes | Low | Refactor |
| 7 | Multiple | Various | Duplicate Arrays | Medium | Consolidate |
| 8 | `admin/order_detail.php` | 135-211 | Unused Variables | Low | Refactor |

---

## Cleaning Recommendations

### Priority 1 (Critical): 
- **Issue #4** - Remove database compatibility code (Schema check)
- **Issue #3** - Implement toggleFavorite persistence

### Priority 2 (High):
- **Issue #7** - Create centralized status function
- **Issue #1** - Remove unused email import or fix email functionality

### Priority 3 (Medium):
- **Issue #6** - Fix duplicate style attributes
- **Issue #8** - Refactor unused variables
- **Issue #2** - Remove empty script tag

---

## Additional Notes

### No Commented Code Found
- No large blocks of commented code requiring cleanup
- No obvious debugging statements left in production code

### Good Practices Observed
- Proper use of require_once for includes
- Good separation of concerns (functions.php for utilities)
- API endpoints properly structured (/api/ directory)
- Error handling in place for most operations

### Architecture Recommendations
1. Consider creating a `includes/constants.php` for status definitions
2. Create a `includes/helpers.php` for UI helper functions like `getOrderStatusInfo()`
3. Add proper logging for email sending failures
4. Document database schema version requirements

---

## Files Analyzed

✅ `thanhtoan.php` (836 lines)  
✅ `order_detail.php` (394 lines)  
✅ `user_info.php` (870 lines)  
✅ `admin/orders.php` (323 lines)  
✅ `admin/order_detail.php` (297 lines)  
✅ `includes/functions.php` (300+ lines)  
✅ `js/scripts.js` (477 lines)  

**Total Lines Scanned:** ~3,500+

---

## Next Steps

1. Review this report with the development team
2. Prioritize issues based on project timeline
3. Create tickets for each issue
4. Test changes thoroughly after cleanup
5. Update documentation after refactoring

