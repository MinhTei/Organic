# Quick Reference - Issues to Fix
**Use this as a checklist**

---

## Issue #1: Unused Email Import
```
📍 File: thanhtoan.php, Line 17
🔴 Severity: MEDIUM
⏱️ Effort: 2 minutes
✂️ Action: DELETE THIS LINE

require_once __DIR__ . '/includes/email_functions.php';

✅ AFTER: Line is removed
```

---

## Issue #2: Empty Script Tag
```
📍 File: order_detail.php, Lines 310-311
🟡 Severity: LOW
⏱️ Effort: 1 minute
✂️ Action: DELETE THESE LINES

<script>
</script>

✅ AFTER: Lines are deleted
```

---

## Issue #3: Favorite Toggle Doesn't Save
```
📍 File: js/scripts.js, Line 88
🔴 Severity: MEDIUM
⏱️ Effort: 30 minutes
🔧 Action: IMPLEMENT PERSISTENCE

CURRENT (BROKEN):
function toggleFavorite(productId) {
    // ... toggle UI ...
    // TODO: Save to server/localStorage  ← PROBLEM
    showNotification('Đã cập nhật yêu thích', 'success');
}

SOLUTION (EASIEST - Use existing function):
function toggleFavorite(productId) {
    toggleWishlist(productId);  // Use the working function!
}

SOLUTION (MEDIUM - Use localStorage):
// See ACTION_PLAN.md for full code

SOLUTION (BEST - Use server):
// See ACTION_PLAN.md for full code

✅ AFTER: Favorites persist across page refreshes
```

---

## Issue #4: Database Compatibility Check
```
📍 File: thanhtoan.php, Lines 213-268
🔴 Severity: MEDIUM
⏱️ Effort: 20 minutes
🔧 Action: SIMPLIFY CODE

BEFORE (BAD - Checks DB on every order):
if (!$error) {
    try {
        // Check if column exists
        $columnCheckStmt = $conn->query("SHOW COLUMNS FROM orders LIKE 'shipping_email'");
        $hasEmailColumn = $columnCheckStmt && $columnCheckStmt->rowCount() > 0;
        
        if ($hasEmailColumn) {
            // Modern version (with email)
            $sql = "INSERT INTO orders (..., shipping_email, ...) ...";
        } else {
            // Old version (without email)  ← DELETE THIS PART
            $sql = "INSERT INTO orders (..., no shipping_email, ...) ...";
        }
        // ...rest of code
    }
}

AFTER (GOOD - No check needed):
if (!$error) {
    try {
        // Just use the modern version, no check
        $sql = "INSERT INTO orders (..., shipping_email, ...) ...";
        // ...rest of code
    }
}

STEPS:
1. Verify your DB has shipping_email column: 
   SHOW COLUMNS FROM orders LIKE 'shipping_email';
2. If it exists, delete the SHOW COLUMNS query (lines 213-215)
3. Delete the else block (old code, lines 236-268)
4. Keep only the modern INSERT (lines 216-235)

✅ AFTER: One less DB query per order, cleaner code
```

---

## Issue #5: Functions in includes/functions.php
```
📍 File: includes/functions.php
🟢 Severity: NONE
⏱️ Effort: 0 minutes (No action needed!)
✅ Status: All functions properly used

NO ACTION REQUIRED
All 10 functions in this file are correctly implemented and used elsewhere.
This file is CLEAN.
```

---

## Issue #6: Duplicate Style Attributes
```
📍 File: admin/order_detail.php, Lines 196-211
🟡 Severity: LOW
⏱️ Effort: 5 minutes
🔧 Action: MERGE STYLES

BEFORE (BAD - Two style="" attributes):
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

AFTER (GOOD - Single merged style):
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

✅ AFTER: Valid HTML, both styles applied correctly
```

---

## Issue #7: Duplicate Status Definitions
```
📍 Files: 4 files have duplicate code
   - order_detail.php (lines 113-127)
   - admin/order_detail.php (lines 52-69)
   - admin/orders.php (lines 124-131)
   - user_info.php (lines 365-371)
🔴 Severity: MEDIUM
⏱️ Effort: 45 minutes
🔧 Action: CREATE CENTRALIZED FUNCTION

STEP 1: Add to includes/functions.php
────────────────────────────────────
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

STEP 2: Replace in order_detail.php
───────────────────────────────────
BEFORE:
$statusColor = 'var(--muted-light)';
$statusLabel = $order['status'];
switch ($order['status']) {
    case 'pending':
        $statusColor = '#f59e0b';
        $statusLabel = 'Chờ xác nhận';
        break;
    // ... 50+ lines of duplicate code
}

AFTER:
$statusInfo = getOrderStatusInfo($order['status']);
$statusColor = $statusInfo['color'];
$statusLabel = $statusInfo['label'];

STEP 3: Repeat STEP 2 in all 4 files

✅ AFTER: Single source of truth, easier to maintain
```

---

## Issue #8: Unused Variable Definitions
```
📍 File: admin/order_detail.php, Lines 52-69 and similar in 3 other files
🟡 Severity: LOW
⏱️ Effort: 15 minutes
🔧 Action: REFACTOR (After fixing Issue #7)

Once Issue #7 is done, simply delete these unused arrays:

BEFORE:
$statusLabels = [...];  // 15 lines
$statusColors = [...];  // 15 lines
$paymentMethods = [...]; // 5 lines

AFTER:
// All deleted - now using getOrderStatusInfo() and getPaymentMethodLabel()

✅ AFTER: Cleaner, single source of truth
```

---

## Priority Summary

### 🚨 DO FIRST (Fixes broken features & performance)
1. Issue #3 - Fix favorite toggle
2. Issue #4 - Remove DB compatibility check

### 🔧 DO SECOND (Code quality)
3. Issue #7 - Centralize status definitions
4. Issue #6 - Fix duplicate style attributes

### 🧹 DO LAST (Cleanup)
5. Issue #8 - Remove unused variables (after #7)
6. Issue #1 - Remove unused import (if email not needed)
7. Issue #2 - Remove empty script tag

---

## Testing Checklist

After each fix, test:

### After #1 & #2 (Imports & Empty Tags)
- [ ] Site still loads
- [ ] No console errors
- [ ] Orders still work

### After #3 (Favorite Toggle)
- [ ] Click favorite on product
- [ ] Icon changes to filled
- [ ] Refresh page
- [ ] Favorite is still marked (persisted)

### After #4 (DB Check)
- [ ] Create a new order
- [ ] Order appears in admin
- [ ] All shipping info saved
- [ ] No database errors

### After #7 (Centralize Status)
- [ ] View order details
- [ ] All statuses show correct colors
- [ ] All statuses show correct labels
- [ ] Test in mobile view

### After #6 & #8 (Style Cleanup)
- [ ] No style conflicts
- [ ] Cancelled orders show dimmed
- [ ] All layouts correct on mobile

---

## Rollback Instructions

If something breaks:

```bash
# See what changed
git diff

# Revert a single file
git checkout -- thanhtoan.php

# Revert all changes
git reset --hard HEAD
```

---

## Estimated Timeline

| Phase | Tasks | Time |
|-------|-------|------|
| 1 | Issues #3, #4 | 50 min |
| 2 | Issue #7 | 45 min |
| 3 | Issues #6, #8 | 20 min |
| 4 | Issues #1, #2 | 3 min |
| 5 | Testing & fixes | 30 min |
| **TOTAL** | | **~2.5 hours** |

Or do just Priority 1 (30 minutes) and leave rest for later.

---

## Files to Modify

### Must modify:
- [ ] `thanhtoan.php` - Issues #1, #4
- [ ] `js/scripts.js` - Issue #3
- [ ] `includes/functions.php` - Issue #7 (add function)
- [ ] `admin/order_detail.php` - Issues #6, #7, #8

### Should modify:
- [ ] `order_detail.php` - Issue #2 and #7
- [ ] `admin/orders.php` - Issue #7
- [ ] `user_info.php` - Issue #7

---

## Helpful Resources

For detailed implementation with full code examples, see:
- `CODE_CLEANUP_ACTION_PLAN.md` - Step-by-step guide
- `CODE_CLEANUP_REPORT.md` - Detailed analysis
- `CLEANUP_SUMMARY.md` - Executive overview

