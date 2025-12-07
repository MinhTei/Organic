# Code Cleanup Action Plan - Organic Project
**Implementation Guide for Developers**

---

## Quick Reference: Issues to Fix

| ID | Issue | File | Action | Effort |
|----|-------|------|--------|--------|
| 1 | Unused Import | `thanhtoan.php:17` | Remove line | 2 min |
| 2 | Empty Script Tag | `order_detail.php:310-311` | Delete lines | 1 min |
| 3 | TODO Unimplemented | `js/scripts.js:88` | Implement persistence | 30 min |
| 4 | Schema Check Code | `thanhtoan.php:213-268` | Remove if block, keep modern code | 20 min |
| 5 | Functions OK | `includes/functions.php` | No action | 0 min |
| 6 | Duplicate Attributes | `admin/order_detail.php:196-218` | Merge styles | 5 min |
| 7 | Duplicate Status Arrays | Multiple files | Centralize function | 45 min |
| 8 | Unused Variables | `admin/order_detail.php:135` | Refactor with centralized function | 15 min |

---

## Detailed Implementation Guide

### ISSUE #1: Remove Unused Email Import
**Severity:** Medium  
**File:** `thanhtoan.php`  
**Line:** 17  
**Time:** 2 minutes

#### Current Code:
```php
<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/email_functions.php';  // <-- REMOVE THIS LINE
```

#### Action:
1. Delete line 17: `require_once __DIR__ . '/includes/email_functions.php';`

#### Why:
- The email_functions.php is imported but the function appears to fail silently
- Emails are likely not being sent in the order confirmation
- If email notifications are truly needed, this must be fixed properly first

#### Or Alternative (If emails should work):
If email notifications are required, instead of removing, fix the email sending:
1. Debug why `sendOrderConfirmationEmail()` doesn't send emails
2. Check if PHPMailer is configured or mail() function works
3. Add error logging to email_functions.php
4. Test email delivery end-to-end

---

### ISSUE #2: Remove Empty Script Tag
**Severity:** Low  
**File:** `order_detail.php`  
**Lines:** 310-311  
**Time:** 1 minute

#### Current Code:
```php
    </form>
    <?php endif; ?>
</div>

<script>
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
```

#### Action:
Delete lines 310-311 (the empty `<script>` tag).

#### Replacement:
```php
    </form>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
```

---

### ISSUE #3: Implement toggleFavorite Persistence
**Severity:** Medium  
**File:** `js/scripts.js`  
**Line:** 88  
**Time:** 30 minutes

#### Current Code (BROKEN):
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
    
    // TODO: Save to server/localStorage  // <-- PROBLEM
    showNotification('Đã cập nhật yêu thích', 'success');
}
```

#### Solution A: Use Existing API (Recommended)
The codebase already has `toggleWishlist()` function that works properly. Just use that instead:

```javascript
function toggleFavorite(productId) {
    // Delegate to the proper wishlist function
    toggleWishlist(productId);
}
```

Note: The existing `toggleWishlist()` at line 353+ already implements proper API calls to `/api/wishlist.php`.

#### Solution B: Implement Client-Side (localStorage)
For anonymous users:

```javascript
function toggleFavorite(productId) {
    const btn = event.target.closest('.product-favorite') || event.currentTarget;
    const icon = btn.querySelector('.material-symbols-outlined');
    
    // Get favorites from localStorage
    let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    const isFavorited = favorites.includes(productId);
    
    // Toggle state
    if (isFavorited) {
        icon.style.fontVariationSettings = "'FILL' 0";
        icon.style.color = '';
        favorites = favorites.filter(id => id !== productId);
        showNotification('Đã xóa khỏi yêu thích', 'success');
    } else {
        icon.style.fontVariationSettings = "'FILL' 1";
        icon.style.color = '#ef4444';
        favorites.push(productId);
        showNotification('Đã thêm vào yêu thích', 'success');
    }
    
    // Save to localStorage
    localStorage.setItem('favorites', JSON.stringify(favorites));
}
```

#### Solution C: Use Server-Side (Best for Logged-in Users)
```javascript
function toggleFavorite(productId) {
    // For logged-in users, use the proper wishlist API
    if (document.querySelector('.header-actions a[href*="user_info"]')) {
        toggleWishlist(productId);
        return;
    }
    
    // For anonymous users, use localStorage
    const btn = event.target.closest('.product-favorite') || event.currentTarget;
    const icon = btn.querySelector('.material-symbols-outlined');
    
    let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    const isFavorited = favorites.includes(productId);
    
    if (isFavorited) {
        icon.style.fontVariationSettings = "'FILL' 0";
        icon.style.color = '';
        favorites = favorites.filter(id => id !== productId);
    } else {
        icon.style.fontVariationSettings = "'FILL' 1";
        icon.style.color = '#ef4444';
        favorites.push(productId);
    }
    
    localStorage.setItem('favorites', JSON.stringify(favorites));
    showNotification(isFavorited ? 'Đã xóa khỏi yêu thích' : 'Đã thêm vào yêu thích', 'success');
}
```

**Recommended:** Use Solution A (delegate to existing working function)

---

### ISSUE #4: Remove Database Compatibility Check Code
**Severity:** Medium  
**File:** `thanhtoan.php`  
**Lines:** 213-268  
**Time:** 20 minutes

#### Current Problem Code:
```php
if (!$error) {
    try {
        $conn->beginTransaction();
        
        // Generate order code
        $orderCode = 'ORD' . date('Ymd') . rand(1000, 9999);
        
        // Create order
        // Kiểm tra xem cột shipping_email có tồn tại không
        $columnCheckStmt = $conn->query("SHOW COLUMNS FROM orders LIKE 'shipping_email'");
        $hasEmailColumn = $columnCheckStmt && $columnCheckStmt->rowCount() > 0;
        
        if ($hasEmailColumn) {
            // INSERT with shipping_email - LINES 216-235
            $sql = "INSERT INTO orders (
                user_id, order_code, total_amount, discount_amount, shipping_fee, 
                final_amount, status, payment_method, payment_status,
                shipping_name, shipping_phone, shipping_email, shipping_address, shipping_ward, 
                shipping_district, shipping_city, note, coupon_code
            ) VALUES (
                :user_id, :order_code, :total_amount, :discount_amount, :shipping_fee,
                :final_amount, 'pending', :payment_method, 'pending',
                :shipping_name, :shipping_phone, :shipping_email, :shipping_address, :shipping_ward,
                :shipping_district, :shipping_city, :note, :coupon_code
            )";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':order_code' => $orderCode,
                ':total_amount' => $subtotal,
                ':discount_amount' => $discountAmount,
                ':shipping_fee' => $shippingFee,
                ':final_amount' => $total,
                ':payment_method' => $paymentMethod,
                ':shipping_name' => $name,
                ':shipping_phone' => $phone,
                ':shipping_email' => $email,
                ':shipping_address' => $address,
                ':shipping_ward' => $ward,
                ':shipping_district' => $district,
                ':shipping_city' => $city,
                ':note' => $note,
                ':coupon_code' => $couponCode ?: null
            ]);
        } else {
            // FALLBACK CODE - LINES 236-263 (OLD SCHEMA)
            // ... This code should be deleted
        }
```

#### Action:
1. First, verify your production database HAS the `shipping_email` column:
   ```sql
   SHOW COLUMNS FROM orders LIKE 'shipping_email';
   ```

2. If it exists, delete lines 213-215 and 236-268 (the entire conditional block and fallback code)

3. Keep only the modern INSERT statement (lines 216-235)

#### Refactored Code:
```php
if (!$error) {
    try {
        $conn->beginTransaction();
        
        // Generate order code
        $orderCode = 'ORD' . date('Ymd') . rand(1000, 9999);
        
        // Create order (with shipping_email column)
        $sql = "INSERT INTO orders (
            user_id, order_code, total_amount, discount_amount, shipping_fee, 
            final_amount, status, payment_method, payment_status,
            shipping_name, shipping_phone, shipping_email, shipping_address, shipping_ward, 
            shipping_district, shipping_city, note, coupon_code
        ) VALUES (
            :user_id, :order_code, :total_amount, :discount_amount, :shipping_fee,
            :final_amount, 'pending', :payment_method, 'pending',
            :shipping_name, :shipping_phone, :shipping_email, :shipping_address, :shipping_ward,
            :shipping_district, :shipping_city, :note, :coupon_code
        )";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':order_code' => $orderCode,
            ':total_amount' => $subtotal,
            ':discount_amount' => $discountAmount,
            ':shipping_fee' => $shippingFee,
            ':final_amount' => $total,
            ':payment_method' => $paymentMethod,
            ':shipping_name' => $name,
            ':shipping_phone' => $phone,
            ':shipping_email' => $email,
            ':shipping_address' => $address,
            ':shipping_ward' => $ward,
            ':shipping_district' => $district,
            ':shipping_city' => $city,
            ':note' => $note,
            ':coupon_code' => $couponCode ?: null
        ]);
        
        $orderId = $conn->lastInsertId();
        
        // Rest of the code continues...
```

#### Impact:
- Removes unnecessary database query on every order creation
- Simplifies maintenance
- Assumes modern database schema

---

### ISSUE #5: includes/functions.php
**Status:** ✅ No action needed - all functions are properly used.

---

### ISSUE #6: Fix Duplicate Style Attributes
**Severity:** Low  
**File:** `admin/order_detail.php`  
**Lines:** 196-211  
**Time:** 5 minutes

#### Current Code (BROKEN):
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
    <?= $statusLabels[$order['status']] ?>
</span>
```

**Problem:** Two `style` attributes. Second one overrides first.

#### Fixed Code:
```php
<span style="
    display: inline-block;
    background: <?= $statusColors[$order['status']] ?>20;
    color: <?= $statusColors[$order['status']] ?>;
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    font-weight: 600;
    font-size: 0.875rem;
    <?= $order['status'] === 'cancelled' ? 'opacity: 0.5;' : '' ?>
">
    <?= $statusLabels[$order['status']] ?>
</span>
```

---

### ISSUE #7: Create Centralized Status Function
**Severity:** Medium  
**Files:** Multiple  
**Time:** 45 minutes

#### Step 1: Add to `includes/functions.php`

Add this function at the end of the file:

```php
/**
 * Get order status display information (color, label, and CSS classes)
 * @param string $status
 * @return array
 */
function getOrderStatusInfo($status) {
    $statuses = [
        'pending' => [
            'color' => '#f59e0b',
            'label' => 'Chờ xác nhận',
            'css_class' => 'bg-yellow-100 text-yellow-800'
        ],
        'confirmed' => [
            'color' => '#3b82f6',
            'label' => 'Đã xác nhận',
            'css_class' => 'bg-blue-100 text-blue-800'
        ],
        'processing' => [
            'color' => '#8b5cf6',
            'label' => 'Đang xử lý',
            'css_class' => 'bg-purple-100 text-purple-800'
        ],
        'shipping' => [
            'color' => '#06b6d4',
            'label' => 'Đang giao',
            'css_class' => 'bg-cyan-100 text-cyan-800'
        ],
        'delivered' => [
            'color' => '#22c55e',
            'label' => 'Đã giao',
            'css_class' => 'bg-green-100 text-green-800'
        ],
        'cancelled' => [
            'color' => '#ef4444',
            'label' => 'Đã hủy',
            'css_class' => 'bg-red-100 text-red-800'
        ],
        'refunded' => [
            'color' => '#8b5cf6',
            'label' => 'Hoàn tiền',
            'css_class' => 'bg-purple-100 text-purple-800'
        ]
    ];
    
    return $statuses[$status] ?? [
        'color' => 'var(--muted-light)',
        'label' => $status,
        'css_class' => 'bg-gray-100 text-gray-800'
    ];
}

/**
 * Get payment method display label
 * @param string $method
 * @return string
 */
function getPaymentMethodLabel($method) {
    $methods = [
        'cod' => 'Thanh toán khi nhận hàng (COD)',
        'bank_transfer' => 'Chuyển khoản ngân hàng',
        'bank_transfer_short' => 'Chuyển khoản'
    ];
    return $methods[$method] ?? $method;
}
```

#### Step 2: Update `order_detail.php`

**Lines 113-127** - Remove the switch statement and use:

```php
<?php
$statusInfo = getOrderStatusInfo($order['status']);
$statusColor = $statusInfo['color'];
$statusLabel = $statusInfo['label'];
?>
```

#### Step 3: Update `admin/order_detail.php`

**Lines 52-69** - Replace the arrays with function call:

```php
$statusInfo = getOrderStatusInfo($order['status']);
$statusLabel = $statusInfo['label'];
$statusColor = $statusInfo['color'];
```

And similarly for payment methods:

```php
$paymentLabel = getPaymentMethodLabel($order['payment_method']);
```

#### Step 4: Update `admin/orders.php`

**Lines 124-131** - Use the centralized function:

```php
// Remove the $statusLabels and $statusColors arrays
// Use function instead when needed:
$statusInfo = getOrderStatusInfo($status);
```

#### Step 5: Update `user_info.php`

**Lines 365-371** - Remove duplicate arrays, use function instead

---

### ISSUE #8: Refactor Unused Variables
**Severity:** Low  
**File:** `admin/order_detail.php`  
**Lines:** 52-69  
**Time:** 15 minutes (do after Issue #7)

Once Issue #7 is done, these arrays become redundant.

#### Before:
```php
$statusLabels = [
    'pending' => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    // ... all the duplicates
];

$statusColors = [
    'pending' => '#f59e0b',
    'confirmed' => '#3b82f6',
    // ... all the duplicates
];

$paymentMethods = [
    'cod' => 'Thanh toán khi nhận',
    'bank_transfer' => 'Chuyển khoản'
];
```

#### After:
```php
// Use functions from includes/functions.php instead
// No need to define these here anymore
```

---

## Implementation Checklist

### Phase 1: Quick Wins (5 minutes total)
- [ ] Remove empty `<script>` tag from `order_detail.php:310-311`
- [ ] Remove unused email import from `thanhtoan.php:17` (if email not needed)

### Phase 2: Database & Compatibility (20 minutes)
- [ ] Verify `shipping_email` column exists in orders table
- [ ] Remove schema check code from `thanhtoan.php:213-268`
- [ ] Test order creation still works

### Phase 3: Centralization (45 minutes)
- [ ] Add `getOrderStatusInfo()` function to `includes/functions.php`
- [ ] Add `getPaymentMethodLabel()` function to `includes/functions.php`
- [ ] Update `order_detail.php` to use new functions
- [ ] Update `admin/order_detail.php` to use new functions
- [ ] Update `admin/orders.php` to use new functions
- [ ] Update `user_info.php` to use new functions
- [ ] Fix duplicate style attribute in `admin/order_detail.php:196-211`

### Phase 4: Frontend Polish (30 minutes)
- [ ] Implement `toggleFavorite` persistence (Solution A recommended)
- [ ] Test favorite toggle works across page refreshes
- [ ] Test favorite toggle for logged-in and anonymous users

### Phase 5: Testing (varies)
- [ ] Test order creation with new code
- [ ] Test all order status displays show correctly
- [ ] Test payment method labels display correctly
- [ ] Test on mobile devices (responsive)
- [ ] Test in different browsers

---

## Testing Commands

### Verify Database Schema:
```sql
-- Check if shipping_email column exists
SHOW COLUMNS FROM orders LIKE 'shipping_email';

-- Should return one row with Field: shipping_email
```

### Test Order Creation:
```bash
# 1. Go to checkout page
# 2. Select saved address
# 3. Complete form
# 4. Place order
# 5. Verify order appears in admin
# 6. Check shipping_email is saved correctly
```

### Test Status Display:
```javascript
// In browser console, test all status types:
console.log(getOrderStatusInfo('pending'));
console.log(getOrderStatusInfo('delivered'));
console.log(getOrderStatusInfo('cancelled'));
```

---

## Rollback Plan

If something breaks during implementation:

1. **Database Changes:** N/A - no database changes needed
2. **Code Changes:** Can revert using Git:
   ```bash
   git diff  # See what changed
   git checkout -- <filename>  # Revert specific file
   ```
3. **Testing:** Keep backup of original order IDs

---

## Performance Impact

| Issue | Before | After | Impact |
|-------|--------|-------|--------|
| #4 | SHOW COLUMNS query on every order | No extra query | -1 DB query per order |
| #7 | Duplicate arrays in multiple files | Single source of truth | -5KB code size |
| #3 | Visual-only favorites | Persisted favorites | Better UX, more data sent |

---

## Documentation Updates Needed

After implementation, update:
1. `README.md` - Database schema requirements
2. Database migration notes - document shipping_email column requirement
3. API documentation - document favorite/wishlist endpoints
4. Developer guidelines - reference new helper functions

