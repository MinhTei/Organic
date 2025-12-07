# Responsive Breakpoints - Hướng dẫn Chi Tiết

## 🎯 Định nghĩa Kích thước Màn hình

### Chuẩn Breakpoint cho Toàn Project

```
┌─────────────────────────────────────────────────────────┐
│ MOBILE         TABLET              DESKTOP              │
│ < 768px        768px - 1024px      >= 1025px            │
└─────────────────────────────────────────────────────────┘
```

### Chi tiết từng Loại Thiết bị

| Loại      | Kích thước      | Thiết bị                          |
|-----------|-----------------|----------------------------------|
| **Mobile** | < 768px        | iPhone, Galaxy S, OnePlus         |
| **Tablet** | 768px-1024px   | iPad Mini, iPad, Galaxy Tab S     |
| **Desktop**| >= 1025px      | Laptop, Desktop, Large Monitor   |

## 📱 Cách Sử dụng Media Query

### Mobile (< 768px)
```css
@media (max-width: 767px) {
    /* CSS cho mobile */
}
```

### Tablet (768px - 1024px)
```css
@media (min-width: 768px) and (max-width: 1024px) {
    /* CSS cho tablet */
}
```

### Desktop (>= 1025px)
```css
@media (min-width: 1025px) {
    /* CSS cho desktop */
}
```

## 🔄 Cách Thay đổi Grid Layout

### Ví dụ: Products Grid

**File:** `index.php`

```html
<!-- Default (Mobile) -->
<div class="products-grid" style="grid-template-columns: repeat(auto-fill, minmax(clamp(160px, 40vw, 280px), 1fr));">

<!-- Với Media Query Tablet -->
<style>
@media (min-width: 768px) and (max-width: 1024px) {
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)) !important;
    }
}
</style>

<!-- Với Media Query Desktop -->
<style>
@media (min-width: 1025px) {
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)) !important;
    }
}
</style>
```

## 📋 Danh sách File đã Áp dụng Breakpoint

### 1. **Header (header.php)**
- ✅ Mobile: < 768px - 2 row layout (Menu | Logo | Cart)
- ✅ Tablet: 768px-1024px - Desktop layout
- ✅ Desktop: >= 1025px - Desktop layout

### 2. **Index (index.php)**
- ✅ Categories Grid
- ✅ Featured Products Grid
- ✅ New Products Grid
- ✅ Featured Admin Products (180px-280px)
- ✅ News/Blog Grid
- ✅ Related Products Grid

### 3. **Order History (order_history.php)**
- ✅ Mobile: ≤480px - 1 column cards
- ✅ Tablet: 481px-768px - Multi-column cards (280px min)
- ✅ Desktop: ≥769px - Table layout

### 4. **Order Detail (order_detail.php)**
- ✅ Mobile: ≤768px - Adjusted fonts & spacing
- ✅ Tablet: 769px-1024px - Optimized layout
- ✅ Desktop: ≥1025px - Full width layout

## 🎨 Các Utility Classes

```html
<!-- Ẩn trên mobile, hiện tablet & desktop -->
<div class="hide-mobile">Nội dung desktop</div>

<!-- Ẩn tablet & desktop, hiện mobile -->
<div class="show-mobile">Nội dung mobile</div>

<!-- Ẩn desktop, hiện mobile & tablet -->
<div class="hide-desktop">Nội dung mobile/tablet</div>

<!-- Ẩn mobile & tablet, hiện desktop -->
<div class="show-desktop">Nội dung desktop</div>
```

## ⚙️ Cách Thêm Responsive vào File Mới

1. Thêm import breakpoints.css:
```html
<link href="<?= SITE_URL ?>/css/breakpoints.css" rel="stylesheet"/>
```

2. Sử dụng media query theo chuẩn:
```css
/* Mobile */
@media (max-width: 767px) {
    /* CSS mobile */
}

/* Tablet */
@media (min-width: 768px) and (max-width: 1024px) {
    /* CSS tablet */
}

/* Desktop */
@media (min-width: 1025px) {
    /* CSS desktop */
}
```

## 💡 Tips Quan Trọng

1. **Luôn viết mobile-first**: Định nghĩa CSS mobile trước, sau đó dùng media query để override
2. **Dùng clamp()**: `clamp(min, preferred, max)` để responsive động mà không cần media query quá nhiều
3. **Test ở tất cả kích thước**: 320px, 375px, 480px (mobile), 768px, 1024px (tablet), 1440px (desktop)
4. **Không lạm dụng !important**: Chỉ dùng khi cần override inline styles

## 🔗 DevTools Inspection

Để kiểm tra responsive:
1. Mở Chrome DevTools: `F12`
2. Click `Toggle Device Toolbar`: `Ctrl+Shift+M`
3. Chọn device từ dropdown
4. Resize để test breakpoint

---

**Cập nhật lần cuối:** 6 Dec 2025
